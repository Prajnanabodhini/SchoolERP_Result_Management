<?php
// ==========================================
// 1. DATABASE CONFIGURATION
// ==========================================
$host = '127.0.0.1';
$db   = 'school_management'; 
$user = 'root';               
$pass = '123';                   
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// ==========================================
// 2. HELPER FUNCTIONS
// ==========================================
function normalizeName($str) {
    $str = strtoupper(trim((string)$str));
    $str = preg_replace('/[^A-Z ]/', '', $str);
    $str = preg_replace('/\s+/', ' ', $str);
    return trim($str);
}

function wordsMatch($w1, $w2) {
    if ($w1 === $w2) return true;
    if (strlen($w1) > 2 && strlen($w2) > 2) {
        if (levenshtein($w1, $w2) <= 2) return true;
        similar_text($w1, $w2, $percent);
        if ($percent >= 80) return true;
    }
    return false;
}

// ==========================================
// 3. FETCH DB STUDENTS
// ==========================================
$tableName = 'FeeMstStudent';
$stmt = $pdo->query("SELECT Studentid, studid, studname as name, fathername FROM $tableName");
$dbStudents = [];
while ($row = $stmt->fetch()) {
    $row['n_name'] = normalizeName($row['name']);
    $row['n_father'] = normalizeName($row['fathername']);
    $dbStudents[] = $row;
}
echo "Loaded " . count($dbStudents) . " students from $tableName for matching.\n";

// ==========================================
// 4. MAPPING CONFIGURATION
// ==========================================
$standardMap = [
    'I' => 14, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6,
    'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10, 'XI' => 11, 'XII' => 12
];
$divisionMap = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4];

// ==========================================
// 5. READ AND PROCESS FILE
// ==========================================
$csvFile = __DIR__ . '/Students_APAAR.txt'; 
$firstLine = fgets(fopen($csvFile, 'r'));
$delimiter = (strpos($firstLine, "\t") !== false) ? "\t" : ",";

$file = fopen($csvFile, 'r');
for ($i = 0; $i < 5; $i++) fgetcsv($file, 0, $delimiter);

$successCount = 0;
$failCount = 0;
$notFoundCount = 0;
$noRecordCount = 0;

echo "Starting Update Process...\n";
echo "----------------------------------------\n";

while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
    if (count($row) < 7) continue;

    $class       = strtoupper(trim($row[0]));
    $section     = strtoupper(trim($row[1]));
    $studentName = strtoupper(trim($row[2]));
    $fatherName  = strtoupper(trim($row[5]));

    if (empty($studentName)) continue;

    $standardId = $standardMap[$class] ?? null;
    $divisionId = $divisionMap[$section] ?? null;

    if ($standardId === null || $divisionId === null) continue;

    $csvName = normalizeName($studentName);
    $csvFather = normalizeName($fatherName);
    $csvNameWords = array_values(array_filter(explode(' ', $csvName)));
    $csvCount = count($csvNameWords);
    
    if ($csvCount == 0) continue;
    $minRequired = ($csvCount <= 2) ? $csvCount : 2;

    $bestMatch = null;
    $bestScore = 0;

    foreach ($dbStudents as $db) {
        $dbNameWords = array_values(array_filter(explode(' ', $db['n_name'])));
        
        $matchCount = 0;
        foreach ($csvNameWords as $cw) {
            foreach ($dbNameWords as $dw) {
                if (wordsMatch($cw, $dw)) {
                    $matchCount++;
                    break; 
                }
            }
        }
        
        if ($matchCount >= $minRequired) {
            $score = $matchCount;
            if (!empty($csvFather) && !empty($db['n_father'])) {
                $fatherWords = explode(' ', $db['n_father']);
                foreach ($fatherWords as $fw) {
                    if (wordsMatch($csvFather, $fw) || strpos($csvFather, $fw) !== false) {
                        $score += 2;
                        break;
                    }
                }
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $db;
            }
        }
    }

    if ($bestMatch) {
        $studentId = $bestMatch['Studentid'];
        
        try {
            // Check if a progress card actually exists for this ID before trying to update
            $checkStmt = $pdo->prepare("SELECT id FROM student_progress_cards WHERE student_id = :id LIMIT 1");
            $checkStmt->execute(['id' => $studentId]);
            
            if ($checkStmt->fetch()) {
                $pdo->beginTransaction();
                $updateProgress = $pdo->prepare("UPDATE student_progress_cards SET standard_id = :std, division_id = :div WHERE student_id = :id");
                $updateProgress->execute(['std' => $standardId, 'div' => $divisionId, 'id' => $studentId]);
                $pdo->commit();

                echo "[+] UPDATED: $studentName -> Std: $standardId, Div: $divisionId (ID: $studentId)\n";
                $successCount++;
            } else {
                echo "[!] NO RECORD: $studentName found in FeeMstStudent (ID: $studentId), but no progress card exists.\n";
                $noRecordCount++;
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo "[-] DB ERROR: Failed to update $studentName - " . $e->getMessage() . "\n";
            $failCount++;
        }
    } else {
        echo "[-] NOT FOUND: $studentName (Name not found in FeeMstStudent)\n";
        $notFoundCount++;
    }
}

fclose($file);

echo "----------------------------------------\n";
echo "Process Complete!\n";
echo "Successfully Updated in DB: $successCount\n";
echo "Found in FeeMstStudent, But NO Record in Progress Cards: $noRecordCount\n";
echo "Not Found in FeeMstStudent: $notFoundCount\n";
echo "DB Errors: $failCount\n";