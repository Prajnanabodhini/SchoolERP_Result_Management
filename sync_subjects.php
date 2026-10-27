<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$START = microtime(true);
echo "\n========================================\n ULTIMATE MASTER SYNC (SYNC_SUBJECTS)\n========================================\n\n";

DB::beginTransaction();
try {
    $subjectColumns = Schema::getColumnListing('subjects');
    $fkTables = [
        'exam_master_subjects', 'exam_subjects', 'skill_teacher_subject_allocations',
        'student_skill_observations', 'student_skill_subjects', 'exam_structures',
        'teacher_marks_status', 'student_result_details', 'student_skill_marks', 'student_marks'
    ];

    /* ============================================================== */
    /* STEP 1: MERGE ALIASES (STORY, COMP SCI, ETC)                   */
    /* ============================================================== */
    echo "--- STEP 1: MERGING ALIASES ---\n";
    $aliasMap = [
        'MARATHI STORY'     => 'STORY',
        'STORIES'           => 'STORY',
        'INDOOR GAMES'      => 'INDOOR GAME',
        'OUTDOOR GAMES'     => 'OUTDOOR GAME',
        'LIFESKILL'         => 'LIFE SKILL',
        'SANSKRIT BHARATI'  => 'SANSKRIT BHARTI',
        'MATHMATICS'        => 'MATHEMATICS',
        'MATHEMATIC'        => 'MATHEMATICS',
        'TOY LIB'           => 'TOY',
        'PHYSICAL EDUCATION'=> 'PT',
        'ART & CRAFT'       => 'ART',
        'DRAWINGS'          => 'DRAWING',
        'T/S/A/D'           => 'A/D/M/T',
        'COMPUTER SCIENCE'  => 'INFORMATION TECHNOLOGY',
    ];

    foreach ($aliasMap as $oldName => $newName) {
        $oldRow = DB::table('subjects')->whereRaw('UPPER(TRIM(subject_name)) = ?', [$oldName])->first();
        if (!$oldRow) continue;

        $newRow = DB::table('subjects')->whereRaw('UPPER(TRIM(subject_name)) = ?', [$newName])->first();
        
        if ($newRow) {
            $oldId = $oldRow->id;
            $newId = $newRow->id;
            echo "  🔗 Merging '{$oldName}' → '{$newName}'\n";
            
            foreach ($fkTables as $t) {
                if (Schema::hasTable($t) && Schema::hasColumn($t, 'subject_id')) {
                    try { DB::table($t)->where('subject_id', $oldId)->update(['subject_id' => $newId]); } 
                    catch (\Exception $e) { DB::table($t)->where('subject_id', $oldId)->delete(); }
                }
            }
            
            $mappings = DB::table('standard_wise_subjects')->where('subject_id', $oldId)->get();
            foreach ($mappings as $m) {
                $exists = DB::table('standard_wise_subjects')->where('standard_id', $m->standard_id)->where('subject_id', $newId)->exists();
                if ($exists) DB::table('standard_wise_subjects')->where('id', $m->id)->delete();
                else DB::table('standard_wise_subjects')->where('id', $m->id)->update(['subject_id' => $newId, 'subject_name' => $newName]);
            }
            DB::table('subjects')->where('id', $oldId)->delete();
        } else {
            DB::table('subjects')->where('id', $oldRow->id)->update(['subject_name' => $newName]);
            DB::table('standard_wise_subjects')->where('subject_id', $oldRow->id)->update(['subject_name' => $newName]);
        }
    }

    /* ============================================================== */
    /* STEP 2: CONSOLIDATE REMAINING DUPLICATES                       */
    /* ============================================================== */
    echo "\n--- STEP 2: CONSOLIDATING REMAINING DUPLICATES ---\n";
    $duplicates = DB::select("
        SELECT UPPER(TRIM(subject_name)) as clean_name
        FROM subjects
        GROUP BY UPPER(TRIM(subject_name))
        HAVING COUNT(*) > 1
    ");

    foreach ($duplicates as $dup) {
        $name = $dup->clean_name;
        $rows = DB::table('subjects')->whereRaw('UPPER(TRIM(subject_name)) = ?', [$name])->get();
        
        $masterId = null;
        $maxMarks = -1;
        
        foreach ($rows as $row) {
            $count = DB::table('student_marks')->where('subject_id', $row->id)->count();
            if ($count > $maxMarks) { $maxMarks = $count; $masterId = $row->id; }
        }

        echo "  Found " . count($rows) . " duplicates for '{$name}'. Master ID: {$masterId}\n";

        foreach ($rows as $row) {
            if ($row->id == $masterId) continue; 

            $oldMarks = DB::table('student_marks')->where('subject_id', $row->id)->get();
            foreach ($oldMarks as $om) {
                $existing = DB::table('student_marks')->where('subject_id', $masterId)->where('student_id', $om->student_id)->where('exam_master_id', $om->exam_master_id)->first();
                if ($existing) {
                    DB::table('student_marks')->where('id', $existing->id)->update([
                        'theory_obtained_marks' => max((float)$existing->theory_obtained_marks, (float)$om->theory_obtained_marks),
                        'oral_obtained_marks' => max((float)$existing->oral_obtained_marks, (float)$om->oral_obtained_marks),
                        'practical_obtained_marks' => max((float)$existing->practical_obtained_marks, (float)$om->practical_obtained_marks),
                    ]);
                    DB::table('student_marks')->where('id', $om->id)->delete();
                } else {
                    DB::table('student_marks')->where('id', $om->id)->update(['subject_id' => $masterId]);
                }
            }

            foreach ($fkTables as $t) {
                if (Schema::hasTable($t) && Schema::hasColumn($t, 'subject_id')) {
                    try { DB::table($t)->where('subject_id', $row->id)->update(['subject_id' => $masterId]); } 
                    catch (\Exception $e) { DB::table($t)->where('subject_id', $row->id)->delete(); }
                }
            }

            $mappings = DB::table('standard_wise_subjects')->where('subject_id', $row->id)->get();
            foreach ($mappings as $m) {
                $exists = DB::table('standard_wise_subjects')->where('standard_id', $m->standard_id)->where('subject_id', $masterId)->exists();
                if ($exists) DB::table('standard_wise_subjects')->where('id', $m->id)->delete();
                else DB::table('standard_wise_subjects')->where('id', $m->id)->update(['subject_id' => $masterId]);
            }
            DB::table('subjects')->where('id', $row->id)->delete();
        }
    }

    /* ============================================================== */
    /* STEP 3: FORCE STORY AS ACADEMIC & INJECT TO PRE-PRIMARY EXAMS  */
    /* ============================================================== */
    echo "\n--- STEP 3: FORCING STORY AS ACADEMIC & INJECTING TO EXAMS ---\n";
    $story = DB::table('subjects')->whereRaw('UPPER(TRIM(subject_name)) = ?', ['STORY'])->first();
    if (!$story) {
        $payload = ['subject_name' => 'STORY'];
        if (in_array('subject_code', $subjectColumns, true)) $payload['subject_code'] = 'STORY';
        if (in_array('subject_type_id', $subjectColumns, true)) $payload['subject_type_id'] = 1;
        if (in_array('section_id', $subjectColumns, true)) $payload['section_id'] = 1;
        if (in_array('is_active', $subjectColumns, true)) $payload['is_active'] = 1;
        if (in_array('created_at', $subjectColumns, true)) $payload['created_at'] = now();
        if (in_array('updated_at', $subjectColumns, true)) $payload['updated_at'] = now();
        
        $storyId = DB::table('subjects')->insertGetId($payload);
    } else {
        $storyId = $story->id;
        DB::table('subjects')->where('id', $storyId)->update(['subject_type_id' => 1]);
    }
    echo "  ✅ STORY ID: {$storyId}\n";

    $prePrimaryStds = DB::table('standards')->whereIn(DB::raw('UPPER(TRIM(standard_name))'), ['NURSERY', 'JR KG', 'SR KG'])->get();
    foreach ($prePrimaryStds as $std) {
        $exists = DB::table('standard_wise_subjects')->where('standard_id', $std->id)->where('subject_id', $storyId)->exists();
        if (!$exists) {
            DB::table('standard_wise_subjects')->insert([
                'standard_id' => $std->id, 'standard_name' => $std->standard_name,
                'subject_id' => $storyId, 'subject_name' => 'STORY',
                'sort_order' => 99, 'is_optional' => 0, 'is_active' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $exams = DB::table('exam_master_subjects')->where('standard_id', $std->id)->pluck('exam_master_id')->unique();
        foreach ($exams as $examId) {
            $hasStory = DB::table('exam_master_subjects')->where('exam_master_id', $examId)->where('standard_id', $std->id)->where('subject_id', $storyId)->exists();
            if (!$hasStory) {
                $sample = DB::table('exam_master_subjects')->where('exam_master_id', $examId)->where('standard_id', $std->id)->whereIn('subject_id', function($q) { $q->select('id')->from('subjects')->where('subject_name', 'POEM'); })->first();
                DB::table('exam_master_subjects')->insert([
                    'exam_master_id' => $examId, 'standard_id' => $std->id, 'subject_id' => $storyId,
                    'max_marks' => $sample ? $sample->max_marks : 25,
                    'passing_marks' => $sample ? $sample->passing_marks : 10,
                    'display_order' => 99, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    /* ============================================================== */
    /* STEP 4: REBUILD EXAM STRUCTURES                                */
    /* ============================================================== */
    echo "\n--- STEP 4: REBUILDING EXAM STRUCTURES ---\n";
    DB::table('exam_structures')->delete();
    $rows = [];
    $stdMap = [
        'NURSERY' => 'NURSERY', 'JRKG' => 'JR KG', 'SRKG' => 'SR KG',
        '1ST' => 'FIRST', '2ND' => 'SECOND', '3RD' => 'THIRD', '4TH' => 'FOURTH',
        '5TH' => 'FIFTH', '6TH' => 'SIXTH', '7TH' => 'SEVENTH', '8TH' => 'EIGHTH',
        '9TH' => 'NINTH', '10TH' => 'TENTH',
        '11SCI' => 'ELEVENTH SCIENCE', '12SCI' => 'TWELFTH SCIENCE',
        '11COM' => 'ELEVENTH COMMERCE', '12COM' => 'TWELFTH COMMERCE',
    ];
    $push = function ($std, $et, $names, $tM, $tP, $oM, $oP, $pM, $pP, $totMax = null, $totPass = null) use (&$rows, $stdMap) {
        foreach ((array) $names as $n) {
            $rows[] = [
                'standard_key' => $std, 'standard_name' => $stdMap[$std] ?? $std,
                'exam_type' => $et, 'exam_type_order' => 1,
                'subject_type' => 'Academics', 'subject_name' => $n,
                'theory_max_marks' => $tM, 'theory_passing_marks' => $tP,
                'oral_max_marks' => $oM, 'oral_passing_marks' => $oP,
                'practical_max_marks' => $pM, 'practical_passing_marks' => $pP,
                'total_max_marks' => $totMax !== null ? $totMax : ($tM + $oM + $pM),
                'total_passing_marks' => $totPass !== null ? $totPass : ($tP + $oP + $pP),
                'passing_percentage' => 40, 'is_compulsory' => true, 'is_active' => true,
            ];
        }
    };

    // NURSERY
    $nurAcad = ['ENGLISH','MATHEMATICS','GENERAL KNOWLEDGE','CONVERSATION','POEM','STORY'];
    foreach (['UNIT TEST 1','UNIT TEST 2','TERM 1'] as $et) $push('NURSERY',$et,$nurAcad, 0,0,25,10,0,0);
    $push('NURSERY','TERM 2',$nurAcad, 20,8,5,0,0,0, 25, 10);
    $nurSkill = ['CRAFT','COLOURING','CLAY','LIFESKILL','SONG','INDOOR GAME','OUTDOOR GAME','TOY LIB','SHLOKA','T.SENSE'];
    $push('NURSERY','TERM 1',$nurSkill, 0,0,0,0,10,4);
    $push('NURSERY','TERM 2',$nurSkill, 0,0,0,0,10,4);

    // JRKG
    foreach (['UNIT TEST 1','UNIT TEST 2','TERM 1','TERM 2'] as $et) {
        $push('JRKG',$et,['MARATHI'], 0,0,10,4,0,0);
        $push('JRKG',$et,['ENGLISH'], 20,8,5,2,0,0);
        $push('JRKG',$et,['MATHEMATICS'],20,8,5,2,0,0);
        $push('JRKG',$et,['GENERAL KNOWLEDGE','CONVERSATION','POEM','STORY'], 0,0,25,10,0,0);
    }
    $jrSkill = ['CRAFT','COLOURING','CLAY','LIFESKILL','SONG','INDOOR GAME','OUTDOOR GAME','TOY LIB','SHLOKA','T.SENSE'];
    $push('JRKG','TERM 1',$jrSkill, 0,0,0,0,10,4);
    $push('JRKG','TERM 2',$jrSkill, 0,0,0,0,10,4);

    // SRKG
    foreach (['UNIT TEST 1','UNIT TEST 2','TERM 1','TERM 2'] as $et) {
        $push('SRKG',$et,['MARATHI','ENGLISH','MATHEMATICS'], 20,8,5,2,0,0);
        $push('SRKG',$et,['GENERAL KNOWLEDGE','CONVERSATION','POEM','STORY'], 0,0,25,10,0,0);
    }
    $srSkill = ['CRAFT','DRAWING','CLAY','LIFESKILL','SONG','INDOOR GAME','OUTDOOR GAME','TOY LIB','SHLOKA','T.SENSE'];
    $push('SRKG','TERM 1',$srSkill, 0,0,0,0,10,4);
    $push('SRKG','TERM 2',$srSkill, 0,0,0,0,10,4);

    // 1ST / 2ND
    foreach (['1ST','2ND'] as $std) {
        $acad = ['MARATHI','ENGLISH','MATHEMATICS','EVS'];
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) $push($std,$et,$acad, 20,8,0,0,0,0);
        foreach (['TERM 1','TERM 2'] as $et) $push($std,$et,$acad, 20,8,10,4,70,28, 100, 40);
        foreach (['TERM 1','TERM 2'] as $et) $push($std,$et,['PT','Art','WE'], 0,0,0,0,100,40);
    }

    // 3RD / 4TH
    foreach (['3RD','4TH'] as $std) {
        $acad = ['MARATHI','ENGLISH','MATHEMATICS','EVS1','EVS2'];
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) $push($std,$et,$acad, 20,8,0,0,0,0);
        foreach (['TERM 1','TERM 2'] as $et) $push($std,$et,$acad, 30,12,10,4,60,24, 100, 40);
        foreach (['TERM 1','TERM 2'] as $et) $push($std,$et,['PT','Art','WE'], 0,0,0,0,100,40);
    }

    // 5TH
    $a5 = ['ENGLISH','HINDI','MARATHI','MATHEMATICS'];
    $b5 = ['EVS1','EVS2'];
    foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
        $push('5TH',$et,$a5, 40,16,0,0,0,0);
        $push('5TH',$et,$b5, 20,8,0,0,0,0);
    }
    foreach (['TERM 1','TERM 2'] as $et) {
        $push('5TH',$et,$a5, 40,16,10,4,50,20, 100, 40);
        $push('5TH',$et,$b5, 20,8,5,2,25,10, 50, 20);
        $push('5TH',$et,['PT','Art','WE'], 0,0,0,0,100,40);
    }

    // 6TH
    foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
        $push('6TH',$et,$a5, 40,16,0,0,0,0);
        $push('6TH',$et,['SCIENCE'], 40,16,0,0,0,0);
        $push('6TH',$et,['HISTORY','GEOGRAPHY'], 20,8,0,0,0,0);
    }
    foreach (['TERM 1','TERM 2'] as $et) {
        $push('6TH',$et,$a5, 40,16,10,4,50,20, 100, 40);
        $push('6TH',$et,['SCIENCE'], 20,8,10,4,50,20, 100, 40);
        $push('6TH',$et,['HISTORY','GEOGRAPHY'], 20,8,5,2,25,10, 50, 20);
        $push('6TH',$et,['PT','Art','WE'], 0,0,0,0,100,40);
    }

    // 7TH
    $a7 = ['ENGLISH','HINDI','MARATHI','MATHEMATICS','SCIENCE'];
    foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
        $push('7TH',$et,$a7, 40,16,0,0,0,0);
        $push('7TH',$et,['HISTORY','GEOGRAPHY'], 20,8,0,0,0,0);
    }
    foreach (['TERM 1','TERM 2'] as $et) {
        $push('7TH',$et,$a7, 50,20,10,4,40,16, 100, 40);
        $push('7TH',$et,['HISTORY','GEOGRAPHY'], 25,10,5,2,20,8, 50, 20);
        $push('7TH',$et,['PT','Art','WE'], 0,0,0,0,100,40);
    }

    // 8TH
    $a8 = ['ENGLISH','SANSKRIT','MARATHI','MATHEMATICS','SCIENCE'];
    foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
        $push('8TH',$et,$a8, 40,16,0,0,0,0);
        $push('8TH',$et,['HISTORY','GEOGRAPHY'], 20,8,0,0,0,0);
    }
    $push('8TH','TERM 1',$a8, 50,20,10,4,40,16, 100, 40);
    $push('8TH','TERM 1',['HISTORY','GEOGRAPHY'], 25,10,5,2,20,8, 50, 20);
    $push('8TH','TERM 1',['PT','Art','WE'], 0,0,0,0,100,40);
    $push('8TH','TERM 2',$a8, 50,20,10,4,0,0, 60, 24);
    $push('8TH','TERM 2',['HISTORY','GEOGRAPHY'], 25,10,5,2,20,8, 50, 20);

    // 9TH
    foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
        $push('9TH',$et,['ENGLISH','SANSKRIT','MARATHI'], 40,14,0,0,0,0);
        $push('9TH',$et,['MATHEMATICS I','MATHEMATICS II','SCIENCE I','SCIENCE II'], 20,7,0,0,0,0);
        $push('9TH',$et,['HISTORY','GEOGRAPHY'], 20,7,0,0,0,0);
    }
    foreach (['TERM 1','TERM 2'] as $et) {
        $push('9TH',$et,['ENGLISH','SANSKRIT','MARATHI'], 80,28,20,7,0,0, 100, 35);
        $push('9TH',$et,['MATHEMATICS I','MATHEMATICS II','SCIENCE I','SCIENCE II'], 40,14,0,0,10,4, 50, 18);
        $push('9TH',$et,['HISTORY','GEOGRAPHY'], 40,14,10,4,0,0, 50, 18);
    }

    // 10TH
    foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
        $push('10TH',$et,['ENGLISH','SANSKRIT','MARATHI'], 40,14,0,0,0,0);
        $push('10TH',$et,['MATHEMATICS I','MATHEMATICS II','SCIENCE I','SCIENCE II','HISTORY','GEOGRAPHY'], 20,7,0,0,0,0);
    }
    foreach (['TERM 1','TERM 2'] as $et) {
        $push('10TH',$et,['ENGLISH','SANSKRIT','MARATHI'], 80,28,20,7,0,0, 100, 35);
        $push('10TH',$et,['MATHEMATICS I','MATHEMATICS II','SCIENCE I','SCIENCE II','HISTORY','GEOGRAPHY'], 40,14,10,4,0,0, 50, 18);
    }

    // 11SCI / 12SCI
    foreach (['11SCI','12SCI'] as $std) {
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push($std,$et,['MARATHI','ENGLISH','MATHEMATICS','GEOGRAPHY'], 25,8,0,0,0,0);
            $push($std,$et,['PHYSICS','CHEMISTRY','BIOLOGY'], 25,8,0,0,0,0);
            $push($std,$et,['INFORMATION TECHNOLOGY'], 25,8,0,0,0,0);
        }
        $push($std,'TERM 1',['MARATHI','ENGLISH','MATHEMATICS','GEOGRAPHY'], 50,16,25,8,0,0, 100, 35);
        $push($std,'TERM 1',['PHYSICS','CHEMISTRY','BIOLOGY'], 50,16,25,8,0,0, 100, 35);
        $push($std,'TERM 1',['INFORMATION TECHNOLOGY'], 50,16,0,0,50,17, 100, 35);
        $push($std,'TERM 2',['MARATHI','ENGLISH','MATHEMATICS','GEOGRAPHY'], 80,28,20,7,0,0, 100, 35);
        $push($std,'TERM 2',['PHYSICS','CHEMISTRY','BIOLOGY'], 70,25,0,0,30,11, 100, 35);
        $push($std,'TERM 2',['INFORMATION TECHNOLOGY'], 50,18,0,0,50,17, 100, 35);
    }

    // 11COM / 12COM
    $com = ['MARATHI','ENGLISH','ECONOMICS','ACCOUNTS','ORGANIZATION OF COMMERCE AND MANAGEMENT','SECRETARIAL PRACTICE'];
    foreach (['11COM','12COM'] as $std) {
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push($std,$et,$com, 25,8,0,0,0,0);
        }
        $push($std,'TERM 1',$com, 50,16,25,8,0,0, 100, 35);
        $push($std,'TERM 2',$com, 80,28,20,7,0,0, 100, 35);
    }

    foreach (array_chunk($rows, 500) as $chunk) {
        DB::table('exam_structures')->insert($chunk);
    }
    echo "  ✅ Exam structures rebuilt.\n";

    /* ============================================================== */
    /* STEP 5: FIX 11TH & 12TH SCI / COM MAPPINGS AND EXAMS           */
    /* ============================================================== */
    echo "\n--- STEP 5: FIXING 11TH & 12TH SCI / COM MAPPINGS & EXAMS ---\n";
    $standardMap = [
        'ELEVENTH SCIENCE' => ['MARATHI', 'ENGLISH', 'PHYSICS', 'CHEMISTRY', 'MATHEMATICS', 'BIOLOGY', 'GEOGRAPHY', 'INFORMATION TECHNOLOGY'],
        'TWELFTH SCIENCE' => ['ENGLISH', 'PHYSICS', 'CHEMISTRY', 'MATHEMATICS', 'BIOLOGY', 'GEOGRAPHY', 'INFORMATION TECHNOLOGY'], 
        'ELEVENTH COMMERCE' => ['MARATHI', 'ENGLISH', 'ECONOMICS', 'ACCOUNTS', 'ORGANIZATION OF COMMERCE AND MANAGEMENT', 'SECRETARIAL PRACTICE'],
        'TWELFTH COMMERCE' => ['MARATHI', 'ENGLISH', 'ECONOMICS', 'ACCOUNTS', 'ORGANIZATION OF COMMERCE AND MANAGEMENT', 'SECRETARIAL PRACTICE']
    ];

    $getMarks = function($examType) {
        if (str_contains($examType, 'UNIT TEST')) return ['tM'=>25, 'tP'=>8, 'oM'=>0, 'oP'=>0, 'pM'=>0, 'pP'=>0];
        return ['tM'=>100, 'tP'=>35, 'oM'=>0, 'oP'=>0, 'pM'=>0, 'pP'=>0]; 
    };

    foreach ($standardMap as $stdName => $subjects) {
        $standard = DB::table('standards')->whereRaw('UPPER(TRIM(standard_name)) = ?', [$stdName])->first();
        if (!$standard) continue;

        DB::table('standard_wise_subjects')->where('standard_id', $standard->id)->delete();
        $sort = 0;
        foreach ($subjects as $subName) {
            $subject = DB::table('subjects')->whereRaw('UPPER(TRIM(subject_name)) = ?', [$subName])->first();
            
            if (!$subject) {
                echo "  ➕ Creating missing subject: {$subName}\n";
                $payload = ['subject_name' => $subName];
                if (in_array('subject_code', $subjectColumns, true)) $payload['subject_code'] = substr(preg_replace('/[^A-Z]/', '', $subName), 0, 4) . rand(10, 99);
                if (in_array('subject_type_id', $subjectColumns, true)) $payload['subject_type_id'] = 1;
                if (in_array('section_id', $subjectColumns, true)) $payload['section_id'] = 1;
                if (in_array('short_name', $subjectColumns, true)) $payload['short_name'] = substr($subName, 0, 10);
                if (in_array('is_active', $subjectColumns, true)) $payload['is_active'] = 1;
                if (in_array('created_at', $subjectColumns, true)) $payload['created_at'] = now();
                if (in_array('updated_at', $subjectColumns, true)) $payload['updated_at'] = now();
                
                $subjectId = DB::table('subjects')->insertGetId($payload);
                $subject = DB::table('subjects')->find($subjectId);
            }

            // ⭐ NEW RULE: Only ENGLISH, PHYSICS, CHEMISTRY are compulsory in Science streams
            $isOptional = 0;
            if (in_array($stdName, ['ELEVENTH SCIENCE', 'TWELFTH SCIENCE'])) {
                if (!in_array($subName, ['ENGLISH', 'PHYSICS', 'CHEMISTRY'])) {
                    $isOptional = 1;
                }
            }

            DB::table('standard_wise_subjects')->insert([
                'standard_id' => $standard->id, 'standard_name' => $standard->standard_name,
                'subject_id' => $subject->id, 'subject_name' => $subject->subject_name,
                'sort_order' => ++$sort, 'is_optional' => $isOptional, 'is_active' => 1,
                'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // Fix active exams
        $exams = DB::table('exam_masters')->get();
        foreach ($exams as $exam) {
            $marks = $getMarks($exam->exam_name);
            DB::table('exam_master_subjects')->where('exam_master_id', $exam->id)->where('standard_id', $standard->id)->delete();
            
            $displayOrder = 0;
            foreach ($subjects as $subName) {
                $subject = DB::table('subjects')->whereRaw('UPPER(TRIM(subject_name)) = ?', [$subName])->first();
                if ($subject) {
                    $isOptional = 0;
                    if (in_array($stdName, ['ELEVENTH SCIENCE', 'TWELFTH SCIENCE'])) {
                        if (!in_array($subName, ['ENGLISH', 'PHYSICS', 'CHEMISTRY'])) {
                            $isOptional = 1;
                        }
                    }
                    
                    DB::table('exam_master_subjects')->insert([
                        'exam_master_id' => $exam->id, 'standard_id' => $standard->id, 'subject_id' => $subject->id,
                        'max_marks' => $marks['tM'],
                        'passing_marks' => $marks['tP'],
                        'display_order' => ++$displayOrder, 
                        'is_optional' => $isOptional, 
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        }
        echo "  ✅ Rebuilt mappings and exams for {$stdName}\n";
    }

    /* ============================================================== */
    /* STEP 6: FIX NINTH STANDARD MARKS (20/7)                        */
    /* ============================================================== */
    echo "\n--- STEP 6: FIXING NINTH STANDARD MARKS ---\n";
    $ninthStandard = DB::table('standards')->whereRaw('UPPER(TRIM(standard_name)) = ?', ['NINTH'])->first();
    
    if ($ninthStandard) {
        $subjectsToUpdate = ['MATHEMATICS I', 'MATHEMATICS II', 'SCIENCE I', 'SCIENCE II', 'HISTORY', 'GEOGRAPHY'];
        $subjectIds = DB::table('subjects')->whereIn('subject_name', $subjectsToUpdate)->pluck('id')->toArray();

        if (Schema::hasTable('exam_master_subjects')) {
            $updated = DB::table('exam_master_subjects')
                ->where('standard_id', $ninthStandard->id)
                ->whereIn('subject_id', $subjectIds)
                ->update(['max_marks' => 20, 'passing_marks' => 7]);
            echo "  ✅ Updated {$updated} records in 'exam_master_subjects'.\n";
        }
    } else {
        echo "  ❌ NINTH standard not found!\n";
    }

    /* ============================================================== */
    /* STEP 7: RESTORE TENTH STANDARD MAPPINGS                        */
    /* ============================================================== */
    echo "\n--- STEP 7: RESTORING TENTH STANDARD MAPPINGS ---\n";
    $tenthStandard = DB::table('standards')->whereRaw('UPPER(TRIM(standard_name)) = ?', ['TENTH'])->first();
    
    if ($tenthStandard) {
        $missingSubjects = ['ENGLISH','SANSKRIT','MARATHI','HISTORY','GEOGRAPHY'];
        foreach ($missingSubjects as $subName) {
            $subject = DB::table('subjects')->whereRaw('UPPER(TRIM(subject_name)) = ?', [$subName])->first();
            if ($subject) {
                $exists = DB::table('standard_wise_subjects')->where('standard_id', $tenthStandard->id)->where('subject_id', $subject->id)->exists();
                if (!$exists) {
                    DB::table('standard_wise_subjects')->insert([
                        'subject_id' => $subject->id, 'standard_id' => $tenthStandard->id,
                        'standard_name' => $tenthStandard->standard_name, 'subject_name' => $subject->subject_name,
                        'sort_order' => 99, 'is_optional' => 0, 'is_active' => 1,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    echo "  🔗 Mapped {$subName} to TENTH\n";
                }
            }
        }
    }

    /* ============================================================== */
    /* STEP 8: FIX EXISTING STUDENT MARKS FOR OPTIONAL SUBJECTS       */
    /* ============================================================== */
    echo "\n--- STEP 8: FIXING EXISTING STUDENT MARKS FOR OPTIONAL SUBJECTS ---\n";
    
    // Get Science stream standard IDs
    $scienceStandards = DB::table('standards')->whereIn(DB::raw('UPPER(TRIM(standard_name))'), ['ELEVENTH SCIENCE', 'TWELFTH SCIENCE'])->pluck('id')->toArray();
    
    if (!empty($scienceStandards)) {
        // All subjects EXCEPT English, Physics, Chemistry
        $optionalScienceSubjects = ['MARATHI', 'MATHEMATICS', 'BIOLOGY', 'GEOGRAPHY', 'INFORMATION TECHNOLOGY'];
        $optionalSubjectIds = DB::table('subjects')->whereIn('subject_name', $optionalScienceSubjects)->pluck('id')->toArray();
        
        if (!empty($optionalSubjectIds)) {
            $updated = DB::table('student_marks')
                ->whereIn('standard_id', $scienceStandards)
                ->whereIn('subject_id', $optionalSubjectIds)
                ->where(function($q) {
                    $q->where('theory_obtained_marks', 0)
                      ->orWhere('theory_obtained_marks', '<=', 0)
                      ->orWhereNull('theory_obtained_marks');
                })
                ->update(['is_optional' => 1]);
                
            echo "  ✅ Updated {$updated} student marks to be Optional (ignoring 0s).\n";
        }
    } else {
        echo "  ⚠️ Science standards not found.\n";
    }

    DB::commit();
    echo "\n=== 🎉 MASTER SYNC COMPLETE (Changes Saved!) ===\n";
    echo "Time taken: " . round(microtime(true) - $START, 2) . "s\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n!!! ERROR — ROLLED BACK !!!\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}