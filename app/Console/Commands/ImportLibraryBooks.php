<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Loads only the requested rows and columns of the Excel worksheet.
 *
 * This prevents PhpSpreadsheet from loading the complete worksheet
 * into memory during every import pass.
 */
final class LibraryBooksChunkReadFilter implements IReadFilter
{
    private const COLUMNS = [
        'B', 'C', 'D', 'E', 'F', 'G',
        'H', 'I', 'J', 'K', 'L', 'M',
    ];

    public function __construct(
        private int $startRow,
        private int $endRow
    ) {
    }

    public function readCell(
        string $columnAddress,
        int $row,
        string $worksheetName = ''
    ): bool {
        return $row >= $this->startRow
            && $row <= $this->endRow
            && in_array($columnAddress, self::COLUMNS, true);
    }
}

class ImportLibraryBooks extends Command
{
    protected $signature = 'library:import-xlsx {file : Path to the .xlsx file}';

    protected $description = 'Import library books from the original Excel file using memory-efficient chunks';

    private const CHUNK_SIZE = 500;

    private const INSERT_BATCH_SIZE = 200;

    private const EMPTY_CHUNKS_TO_STOP = 2;

    private array $categoryMap = [
        'आध्यात्मिक'                       => 1,
        'चरित्र-आत्मचरित्र, चरित्र कादंबरी' => 2,
        'कादंबरी/कथा'                      => 3,
        'नाटक'                              => 4,
        'काव्यसंग्रह'                       => 5,
        'विज्ञान-तंत्रज्ञान'                => 6,
        'आरोग्य,योग,क्रीडा,पर्यावरण'        => 7,
        'संकीर्ण'                           => 8,
        'संदर्भ ग्रंथ'                      => 9,
        'बलवाड:मय'                         => 10,
    ];

    private array $devDigits = [
        '०' => '0',
        '१' => '1',
        '२' => '2',
        '३' => '3',
        '४' => '4',
        '५' => '5',
        '६' => '6',
        '७' => '7',
        '८' => '8',
        '९' => '9',
    ];

    public function handle(): int
    {
        $path = $this->argument('file');

        if (!is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $this->info("Preparing workbook: {$path}");

        /*
         * Read workbook metadata first. Do not load the complete
         * workbook just to determine the worksheet name or row count.
         */
        try {
            $reader = IOFactory::createReaderForFile($path);

            $reader->setReadDataOnly(true);

            // Supported by PhpSpreadsheet versions that provide this option.
            if (method_exists($reader, 'setReadEmptyCells')) {
                $reader->setReadEmptyCells(false);
            }

            $sheetNames = $reader->listWorksheetNames($path);

            /*
             * The original command used getSheet(1), which means the
             * SECOND worksheet. Preserve that behavior.
             */
            if (!isset($sheetNames[1])) {
                $this->error(
                    'The workbook does not contain a second worksheet.'
                );

                return self::FAILURE;
            }

            $sheetName = $sheetNames[1];

            $worksheetInfo = $reader->listWorksheetInfo($path);

            $totalRows = 0;

            foreach ($worksheetInfo as $info) {
                if (($info['worksheetName'] ?? '') === $sheetName) {
                    $totalRows = (int) ($info['totalRows'] ?? 0);
                    break;
                }
            }

            if ($totalRows < 3) {
                $this->warn(
                    "No importable data rows found in worksheet: {$sheetName}"
                );

                return self::SUCCESS;
            }

            // Load only the original second worksheet on every pass.
            $reader->setLoadSheetsOnly([$sheetName]);

        } catch (\Throwable $e) {
            $this->error('Unable to prepare workbook: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info("Worksheet: {$sheetName}");
        $this->info("Reported worksheet rows: {$totalRows}");
        $this->info('Reading columns B:M in chunks of ' . self::CHUNK_SIZE . ' rows.');

        $imported = 0;
        $skipped = 0;
        $batch = [];
        $now = now();

        $emptyChunks = 0;

        /*
         * The first two Excel rows contain the title and header.
         * Start from row 3, as the original command did.
         */
        for (
            $startRow = 3;
            $startRow <= $totalRows;
            $startRow += self::CHUNK_SIZE
        ) {
            $endRow = min(
                $startRow + self::CHUNK_SIZE - 1,
                $totalRows
            );

            $spreadsheet = null;
            $sheet = null;
            $rows = [];

            $chunkDataRows = 0;

            try {
                $reader->setReadFilter(
                    new LibraryBooksChunkReadFilter($startRow, $endRow)
                );

                /*
                 * Only the selected worksheet, rows and columns are
                 * loaded. This replaces the full-sheet toArray() call.
                 */
                $spreadsheet = $reader->load($path);

                $sheet = $spreadsheet->getSheetByName($sheetName);

                if ($sheet === null) {
                    throw new \RuntimeException(
                        "Worksheet '{$sheetName}' could not be loaded."
                    );
                }

                /*
                 * B:M produces 12 values per row:
                 *
                 * [0] B = Book Code
                 * [1] C = Category
                 * [2] D = Book Name
                 * [3] E = Author
                 * [4] F = Publisher
                 * [5] G = Publication Year
                 * [6] H = Edition
                 * [7] I = Total Pages
                 * [8] J = Donor Name
                 * [9] K = Bill Number
                 * [10] L = Printed Price
                 * [11] M = Remarks
                 */
                $rows = $sheet->rangeToArray(
                    "B{$startRow}:M{$endRow}",
                    null,
                    true,
                    true,
                    false
                );

                foreach ($rows as $row) {
                    $row = array_values($row);

                    // Ignore completely empty Excel rows.
                    $hasData = false;

                    foreach ($row as $value) {
                        if (
                            $value !== null
                            && trim((string) $value) !== ''
                        ) {
                            $hasData = true;
                            break;
                        }
                    }

                    if (!$hasData) {
                        continue;
                    }

                    $chunkDataRows++;

                    /*
                     * Convert Marathi numerals before validating the
                     * book code. This also supports codes entered with
                     * Marathi digits in Excel.
                     */
                    $bookCode = trim(
                        strtr(
                            (string) ($row[0] ?? ''),
                            $this->devDigits
                        )
                    );

                    if (
                        $bookCode === ''
                        || !ctype_digit($bookCode)
                    ) {
                        $skipped++;
                        continue;
                    }

                    $categoryMr = trim(
                        (string) ($row[1] ?? '')
                    );

                    $bookName = trim(
                        (string) ($row[2] ?? '')
                    );

                    $categoryId = $this->categoryMap[$categoryMr] ?? null;

                    $batch[] = [
                        'book_code'       => $bookCode,
                        'book_name'       => $bookName !== ''
                            ? $bookName
                            : '(unknown)',

                        'author'           => $this->str($row[3] ?? null),
                        'publisher'        => $this->str($row[4] ?? null),
                        'publication_year' => $this->year($row[5] ?? null),
                        'edition'          => $this->str($row[6] ?? null),
                        'total_pages'      => $this->int($row[7] ?? null),
                        'donor_name'       => $this->str($row[8] ?? null),
                        'bill_no'          => $this->str($row[9] ?? null),
                        'bill_date'        => null,
                        'printed_price'    => $this->price($row[10] ?? null),
                        'remarks'          => $this->str($row[11] ?? null),

                        'category_id'      => $categoryId,

                        'total_copies'     => 1,
                        'available_copies' => 1,
                        'shelf_location'   => null,
                        'is_active'        => 1,

                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];

                    // Insert small batches rather than accumulating
                    // every book record in memory.
                    if (count($batch) >= self::INSERT_BATCH_SIZE) {
                        $this->insertBatch($batch, $imported);
                    }
                }

            } catch (\Throwable $e) {
                $this->error(
                    "Import failed while processing Excel rows "
                    . "{$startRow}-{$endRow}: "
                    . $e->getMessage()
                );

                return self::FAILURE;

            } finally {
                /*
                 * Release the worksheet and its cells after each chunk.
                 */
                if ($spreadsheet !== null) {
                    $spreadsheet->disconnectWorksheets();
                }

                unset($sheet, $spreadsheet, $rows);

                gc_collect_cycles();
            }

            $this->line(
                "Processed Excel rows {$startRow}-{$endRow}; "
                . "inserted {$imported}; skipped {$skipped}."
            );

            /*
             * Excel files can report an inflated last row because of
             * previously formatted empty cells. Stop after two
             * consecutive chunks containing no data.
             */
            if ($chunkDataRows === 0) {
                $emptyChunks++;
            } else {
                $emptyChunks = 0;
            }

            if ($emptyChunks >= self::EMPTY_CHUNKS_TO_STOP) {
                $this->warn(
                    'Two consecutive empty chunks detected. '
                    . 'Stopping to avoid processing unused worksheet rows.'
                );

                break;
            }
        }

        // Insert any records remaining in the final batch.
        if (!empty($batch)) {
            $this->insertBatch($batch, $imported);
        }

        $this->newLine();

        $this->info('Import process completed.');
        $this->line("Imported: {$imported}");
        $this->line("Skipped : {$skipped}");

        return self::SUCCESS;
    }

    /**
     * Insert the current database batch and release its memory.
     */
    private function insertBatch(array &$batch, int &$imported): void
    {
        if (empty($batch)) {
            return;
        }

        $count = count($batch);

        DB::table('library_books')->insert($batch);

        $imported += $count;

        $this->info("  {$imported} book records inserted.");

        $batch = [];
    }

    /**
     * Trim, convert Marathi digits, and return null for an empty value.
     */
    private function str($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(
            strtr((string) $value, $this->devDigits)
        );

        return $value === '' ? null : $value;
    }

    /**
     * Extract a four-digit year, ignoring additional text.
     * Example: 1998(1920) returns 1998.
     */
    private function year($value): ?int
    {
        if ($value === null) {
            return null;
        }

        $value = strtr(
            trim((string) $value),
            $this->devDigits
        );

        if (preg_match('/\d{4}/', $value, $matches)) {
            $year = (int) $matches[0];

            if ($year >= 1000 && $year <= 2100) {
                return $year;
            }
        }

        if (preg_match('/\d{1,4}/', $value, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    /**
     * Extract the first integer.
     * Example: "13.5" returns 13.
     */
    private function int($value): ?int
    {
        if ($value === null) {
            return null;
        }

        $value = strtr(
            trim((string) $value),
            $this->devDigits
        );

        if (preg_match('/\d+/', $value, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    /**
     * Extract a numeric price.
     * Example: "३००रु." returns "300".
     */
    private function price($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = strtr(
            trim((string) $value),
            $this->devDigits
        );

        if (preg_match('/\d+(?:\.\d+)?/', $value, $matches)) {
            return $matches[0];
        }

        return null;
    }
}
