<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use Throwable;

final class LibraryBooksChikhaliReadFilter implements IReadFilter
{
    public function __construct(private int $from, private int $to) {}

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return $row >= $this->from && $row <= $this->to
            && in_array(strtoupper($columnAddress), range('B', 'M'), true);
    }
}

class ImportLibraryBooks_chikhali extends Command
{
    protected $signature = 'library:import-xlsx-chikhali {file : Path to Chikhali Excel file} {--dry-run : Preview only; do not insert}';
    protected $description = 'Import Chikhali library books; skip only blank and duplicate book codes';

    private const SHEET = 'पुस्तक ग्रंथ यादी';
    private const CHUNK = 300;
    private const BATCH = 200;

    private const CATEGORIES = [
        1 => 'आध्यात्मिक',
        2 => 'चरित्र-आत्मचरित्र, चरित्र कादंबरी',
        3 => 'कादंबरी/कथा',
        4 => 'नाटक',
        5 => 'काव्यसंग्रह',
        6 => 'विज्ञान-तंत्रज्ञान',
        7 => 'आरोग्य,योग,क्रीडा,पर्यावरण',
        8 => 'संकीर्ण',
        9 => 'संदर्भ ग्रंथ',
        10 => 'बलवाड:मय',
    ];

    private const ALIASES = [
        1 => ['आध्यात्मिक'],
        2 => ['चरित्र-आत्मचरित्र, चरित्र कादंबरी', 'चरित्र आत्मचरित्र चरित्र कादंबरी', 'चरित्रात्मक'],
        3 => ['कादंबरी/कथा', 'कादंबरी', 'कथा'],
        4 => ['नाटक', 'नाट्य छटा'],
        5 => ['काव्यसंग्रह'],
        6 => ['विज्ञान-तंत्रज्ञान', 'विज्ञान आणि तंत्रज्ञान', 'विद्यान आणि तंत्रद्यान'],
        7 => ['आरोग्य,योग,क्रीडा,पर्यावरण', 'आरोग्य', 'योग', 'क्रीडा', 'पर्यावरण'],
        8 => ['संकीर्ण'],
        9 => ['संदर्भ ग्रंथ'],
        10 => ['बलवाड:मय', 'बालवाड्मय', 'बाल वाडमय', 'बालवाङ्मय'],
    ];

    private const DESCRIPTIONS = [
        1 => 'Spiritual books',
        2 => 'Biography and autobiography',
        3 => 'Novels and stories',
        4 => 'Drama',
        5 => 'Poetry collection',
        6 => 'Science and technology',
        7 => 'Health, yoga, sports and environment',
        8 => 'Miscellaneous books',
        9 => 'Reference books',
        10 => 'Balwadi / children literature',
    ];

    private array $digits = [
        '०'=>'0', '१'=>'1', '२'=>'2', '३'=>'3', '४'=>'4',
        '५'=>'5', '६'=>'6', '७'=>'7', '८'=>'8', '९'=>'9',
    ];

    private array $seenCodes = [];

    public function handle(): int
    {
        $path = (string) $this->argument('file');
        $dry = (bool) $this->option('dry-run');

        if (!is_file($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        try {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);

            if (method_exists($reader, 'setReadEmptyCells')) {
                $reader->setReadEmptyCells(false);
            }

            $names = $reader->listWorksheetNames($path);

            $sheetName = in_array(self::SHEET, $names, true)
                ? self::SHEET
                : ($names[1] ?? $names[0] ?? null);

            if ($sheetName === null) {
                $this->error('No worksheets found.');
                return self::FAILURE;
            }

            $total = 0;

            foreach ($reader->listWorksheetInfo($path) as $info) {
                if (($info['worksheetName'] ?? '') === $sheetName) {
                    $total = (int) ($info['totalRows'] ?? 0);
                    break;
                }
            }

            if ($total < 3) {
                $this->warn('No book rows found.');
                return self::SUCCESS;
            }

            $reader->setLoadSheetsOnly([$sheetName]);

            $this->info("Worksheet: {$sheetName}; reported rows: {$total}");
            $this->info('Columns: B=code, C=category, D=book name, E=author, F=publisher, G=year, H=edition, I=pages, J=donor, K=bill no, L=price, M=remarks.');

            if ($dry) {
                $this->warn('DRY RUN: nothing will be inserted.');
            }

            $categoryCache = $this->loadCategoryCache();

            $batch = [];
            $inserted = 0;
            $duplicates = 0;
            $blank = 0;
            $valid = 0;
            $sample = 0;
            $now = now();

            // Chikhali workbook records start on row 3; data columns start at B.
            for ($from = 3; $from <= $total; $from += self::CHUNK) {
                $to = min($from + self::CHUNK - 1, $total);
                $book = null;

                try {
                    $reader->setReadFilter(
                        new LibraryBooksChikhaliReadFilter($from, $to)
                    );

                    $book = $reader->load($path);
                    $sheet = $book->getSheetByName($sheetName);

                    if (!$sheet) {
                        throw new \RuntimeException(
                            "Could not load worksheet {$sheetName}."
                        );
                    }

                    $rows = $sheet->rangeToArray(
                        "B{$from}:M{$to}",
                        null,
                        true,
                        true,
                        false
                    );

                    foreach ($rows as $offset => $r) {
                        $excelRow = $from + $offset;
                        $r = array_values($r);

                        if (!$this->hasData($r)) {
                            continue;
                        }

                        $code = $this->code($r[0] ?? null);

                        if ($code === null) {
                            $blank++;
                            continue;
                        }

                        $raw = trim((string) ($r[1] ?? ''));
                        $num = $this->categoryNumber($raw);

                        $category = $num !== null
                            ? self::CATEGORIES[$num]
                            : ($raw !== '' ? $raw : null);

                        $categoryId = $category !== null
                            ? $this->categoryId(
                                $category,
                                $num,
                                $dry,
                                $categoryCache
                            )
                            : null;

                        $name = trim((string) ($r[2] ?? ''));

                        $batch[] = [
                            'book_code'        => $code,
                            'book_name'        => $name !== '' ? $name : '(unknown)',
                            'author'           => $this->str($r[3] ?? null),
                            'publisher'        => $this->str($r[4] ?? null),
                            'publication_year' => $this->year($r[5] ?? null),
                            'edition'          => $this->str($r[6] ?? null),
                            'total_pages'      => $this->integer($r[7] ?? null),
                            'donor_name'       => $this->str($r[8] ?? null),
                            'bill_no'          => $this->str($r[9] ?? null),
                            'bill_date'        => null,
                            'printed_price'    => $this->price($r[10] ?? null),
                            'remarks'          => $this->str($r[11] ?? null),
                            'category_id'      => $categoryId,
                            'total_copies'     => 1,
                            'available_copies' => 1,
                            'shelf_location'   => null,
                            'is_active'        => 1,
                            'created_at'       => $now,
                            'updated_at'       => $now,
                        ];

                        $valid++;

                        if ($dry && $sample < 5) {
                            $this->line(
                                "Sample row {$excelRow}: code={$code}; "
                                . 'category=' . ($category ?? '(blank)')
                                . '; book=' . ($name !== '' ? $name : '(unknown)')
                            );

                            $sample++;
                        }

                        if (count($batch) >= self::BATCH) {
                            $this->flush(
                                $batch,
                                $inserted,
                                $duplicates,
                                $dry
                            );
                        }
                    }

                    $this->line(
                        "Rows {$from}-{$to}; codes {$valid}; "
                        . "inserted/would insert {$inserted}; "
                        . "blank {$blank}; duplicates {$duplicates}."
                    );

                    unset($rows, $sheet);

                } catch (Throwable $e) {
                    $this->error(
                        "Failed on rows {$from}-{$to}: {$e->getMessage()}"
                    );

                    return self::FAILURE;

                } finally {
                    if ($book !== null) {
                        $book->disconnectWorksheets();
                    }

                    unset($book);
                    gc_collect_cycles();
                }
            }

            if ($batch !== []) {
                $this->flush($batch, $inserted, $duplicates, $dry);
            }

            $this->newLine();
            $this->info(
                $dry
                    ? 'Dry run complete; no rows inserted.'
                    : 'Chikhali import complete.'
            );

            $this->line("Rows with code: {$valid}");
            $this->line("Inserted/would insert: {$inserted}");
            $this->line("Skipped blank codes: {$blank}");
            $this->line("Skipped duplicates: {$duplicates}");

            return self::SUCCESS;

        } catch (Throwable $e) {
            $this->error('Import failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function loadCategoryCache(): array
    {
        $out = [];

        foreach (
            DB::table('library_book_categories')
                ->select('id', 'category_name')
                ->get() as $r
        ) {
            $k = $this->normCategory((string) $r->category_name);

            if ($k !== '' && !isset($out[$k])) {
                $out[$k] = (int) $r->id;
            }
        }

        return $out;
    }

    private function categoryId(
        string $name,
        ?int $number,
        bool $dry,
        array &$cache
    ): ?int {
        $key = $this->normCategory($name);

        if ($key === '') {
            return null;
        }

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        if ($dry) {
            return null;
        }

        $id = (int) DB::table('library_book_categories')->insertGetId([
            'category_name' => $name,
            'description' => $number !== null
                ? (self::DESCRIPTIONS[$number] ?? 'Imported from Chikhali workbook')
                : 'Imported from Chikhali workbook',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cache[$key] = $id;

        $this->info("Created category '{$name}' (ID {$id}).");

        return $id;
    }

    private function categoryNumber(string $label): ?int
    {
        $key = $this->normCategory($label);

        foreach (self::ALIASES as $n => $aliases) {
            foreach ($aliases as $alias) {
                if (
                    $key !== ''
                    && $key === $this->normCategory($alias)
                ) {
                    return $n;
                }
            }
        }

        return null;
    }

    private function normCategory(string $v): string
    {
        return (string) preg_replace(
            '/[^\p{L}\p{N}]+/u',
            '',
            trim($v)
        );
    }

    private function code($v): ?string
    {
        if ($v === null || is_bool($v)) {
            return null;
        }

        $v = trim(strtr((string) $v, $this->digits));

        if ($v === '') {
            return null;
        }

        if (is_numeric($v) && stripos($v, 'e') !== false) {
            $v = sprintf('%.0f', (float) $v);
        }

        if (preg_match('/^\d+\.0+$/', $v)) {
            $v = preg_replace('/\.0+$/', '', $v);
        }

        return $v === '' ? null : $v;
    }

    private function hasData(array $r): bool
    {
        foreach ($r as $v) {
            if ($v !== null && trim((string) $v) !== '') {
                return true;
            }
        }

        return false;
    }

    private function flush(
        array &$batch,
        int &$inserted,
        int &$duplicates,
        bool $dry
    ): void {
        if (!$batch) {
            return;
        }

        $codes = array_values(array_unique(array_column($batch, 'book_code')));

        $existing = DB::table('library_books')
            ->whereIn('book_code', $codes)
            ->pluck('book_code')
            ->all();

        $seen = $this->seenCodes;

        foreach ($existing as $v) {
            $seen[mb_strtolower(trim((string) $v), 'UTF-8')] = true;
        }

        $add = [];

        foreach ($batch as $r) {
            $k = mb_strtolower(trim((string) $r['book_code']), 'UTF-8');

            if (isset($seen[$k])) {
                $duplicates++;
                continue;
            }

            $seen[$k] = true;
            $add[] = $r;
        }

        $this->seenCodes = $seen;

        if (!$dry && $add) {
            DB::table('library_books')->insert($add);
        }

        $inserted += count($add);
        $batch = [];

        if ($add) {
            $this->info(
                ($dry ? ' Would insert ' : ' Inserted ')
                . count($add)
                . "; total {$inserted}."
            );
        }
    }

    private function str($v): ?string
    {
        if ($v === null) {
            return null;
        }

        $v = trim(strtr((string) $v, $this->digits));

        return $v === '' ? null : $v;
    }

    private function year($v): ?int
    {
        if ($v === null) {
            return null;
        }

        $v = strtr(trim((string) $v), $this->digits);

        if (preg_match('/\d{4}/', $v, $m)) {
            $y = (int) $m[0];

            if ($y >= 1000 && $y <= 2100) {
                return $y;
            }
        }

        return preg_match('/\d{1,4}/', $v, $m)
            ? (int) $m[0]
            : null;
    }

    private function integer($v): ?int
    {
        if ($v === null) {
            return null;
        }

        $v = strtr(trim((string) $v), $this->digits);

        return preg_match('/\d+/', $v, $m)
            ? (int) $m[0]
            : null;
    }

    private function price($v): ?string
    {
        if ($v === null) {
            return null;
        }

        $v = strtr(trim((string) $v), $this->digits);

        return preg_match('/\d+(?:\.\d+)?/', $v, $m)
            ? $m[0]
            : null;
    }
}
