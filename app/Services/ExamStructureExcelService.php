<?php

namespace App\Services;

use App\Models\Standard;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExamStructureExcelService
{
    private const FILE_NAME = 'Academic Exam Structure.xlsx';

    private const EXAM_ORDER = [
        'UNIT TEST 1',
        'UNIT TEST 2',
        'TERM 1',
        'TERM 2',
        'ANNUAL',
    ];

    /**
     * Returns the workbook file path inside storage/.
     */
    public function path(): string
    {
        return storage_path(self::FILE_NAME);
    }

    /**
     * Returns SHA-256 hash of the current workbook.
     */
    public function fileHash(): string
    {
        $path = $this->path();

        if (!is_file($path)) {
            throw new \RuntimeException(
                'Exam structure Excel file was not found: ' . $path
            );
        }

        $hash = hash_file('sha256', $path);

        if ($hash === false) {
            throw new \RuntimeException('Unable to calculate Excel file hash.');
        }

        return $hash;
    }

    /**
     * Return exam types actually present in the selected standard sheet.
     */
    public function getExamTypes(int $standardId): array
    {
        $standard = Standard::find($standardId);

        if (!$standard) {
            return [];
        }

        $sheet = $this->sheetNameForStandard($standard->standard_name);

        if (!$sheet) {
            return [];
        }

        $workbook = $this->loadWorkbook();
        $worksheet = $workbook->getSheetByName($sheet);

        if (!$worksheet) {
            return [];
        }

        $groups = $this->detectExamGroups($worksheet);

        return collect($groups)
            ->pluck('exam_type')
            ->unique()
            ->sortBy(fn (string $type) => array_search($type, self::EXAM_ORDER, true))
            ->values()
            ->all();
    }

    /**
     * Return normalized structure for a standard + exam type.
     *
     * This data is read from Excel and matched to the school's
     * standard_wise_subjects -> subjects mapping.
     */
    public function getStructure(int $standardId, string $examType): array
    {
        $examType = $this->normalizeExamType($examType);
        $standard = Standard::find($standardId);

        if (!$standard) {
            throw new \InvalidArgumentException('Invalid Standard.');
        }

        $sheetName = $this->sheetNameForStandard($standard->standard_name);

        if (!$sheetName) {
            return [
                'success' => false,
                'file_hash' => $this->fileHash(),
                'standard_id' => $standardId,
                'standard_name' => $standard->standard_name,
                'sheet_name' => null,
                'exam_type' => $examType,
                'subjects' => [],
                'warnings' => [],
                'errors' => [
                    'No Excel sheet mapping was found for Standard: ' . $standard->standard_name,
                ],
            ];
        }

        $hash = $this->fileHash();
        $cacheKey = 'exam-structure:' . $hash . ':' . $standardId . ':' . Str::slug($examType);

        return Cache::remember(
            $cacheKey,
            now()->addHours(12),
            function () use ($standardId, $standard, $sheetName, $examType, $hash) {
                $workbook = $this->loadWorkbook();
                $worksheet = $workbook->getSheetByName($sheetName);

                if (!$worksheet) {
                    return [
                        'success' => false,
                        'file_hash' => $hash,
                        'standard_id' => $standardId,
                        'standard_name' => $standard->standard_name,
                        'sheet_name' => $sheetName,
                        'exam_type' => $examType,
                        'subjects' => [],
                        'warnings' => [],
                        'errors' => ['Excel sheet "' . $sheetName . '" was not found.'],
                    ];
                }

                $groups = $this->detectExamGroups($worksheet);
                $group = collect($groups)->firstWhere('exam_type', $examType);

                if (!$group) {
                    return [
                        'success' => false,
                        'file_hash' => $hash,
                        'standard_id' => $standardId,
                        'standard_name' => $standard->standard_name,
                        'sheet_name' => $sheetName,
                        'exam_type' => $examType,
                        'subjects' => [],
                        'warnings' => [],
                        'errors' => [
                            'Exam type "' . $examType . '" is not present in Excel sheet "' . $sheetName . '".',
                        ],
                    ];
                }

                $mappings = DB::table('standard_wise_subjects as sws')
                    ->join('subjects as s', 's.id', '=', 'sws.subject_id')
                    ->where('sws.standard_id', $standardId)
                    ->where('sws.is_active', 1)
                    ->where('s.is_active', 1)
                    ->select([
                        'sws.id as mapping_id',
                        'sws.subject_id',
                        's.subject_name',
                        's.subject_code',
                        's.short_name',
                        'sws.is_optional',
                        'sws.sort_order',
                    ])
                    ->orderBy('sws.sort_order')
                    ->orderBy('sws.id')
                    ->get();

                $headers = $this->detectHeaderAnchors($worksheet, $group['start_col'], $group['end_col']);
                $rows = [];
                $warnings = [];
                $errors = [];
                $usedSubjectIds = [];

                for ($rowNumber = $group['data_start_row']; $rowNumber <= $worksheet->getHighestRow(); $rowNumber++) {
                    $subjectType = $this->text($worksheet->getCellByColumnAndRow(1, $rowNumber)->getValue());
                    $excelSubjectName = $this->text($worksheet->getCellByColumnAndRow(2, $rowNumber)->getValue());

                    if ($excelSubjectName === '' || $excelSubjectName === '-') {
                        continue;
                    }

                    $config = $this->readRowConfiguration($worksheet, $rowNumber, $headers, $group);

                    if (!$config['has_any_marks']) {
                        continue;
                    }

                    $resolvedMappings = $this->resolveSubjectMappings(
                        $excelSubjectName,
                        $mappings,
                        $usedSubjectIds,
                        $standard->standard_name
                    );

                    if ($resolvedMappings->isEmpty()) {
                        $warnings[] = sprintf(
                            '%s row %d: Excel subject "%s" could not be matched to an active subject in standard_wise_subjects.',
                            $sheetName,
                            $rowNumber,
                            $excelSubjectName
                        );
                        continue;
                    }

                    $config = $this->finalizeConfiguration(
                        $config,
                        $excelSubjectName,
                        $standard->standard_name,
                        $rowNumber,
                        $warnings
                    );

                    foreach ($resolvedMappings as $mapping) {
                        $usedSubjectIds[] = (int) $mapping->subject_id;

                        $rows[] = [
                            'standard_id' => $standardId,
                            'mapping_id' => (int) $mapping->mapping_id,
                            'subject_id' => (int) $mapping->subject_id,
                            'subject_name' => $mapping->subject_name,
                            'excel_subject_name' => $excelSubjectName,
                            'subject_code' => $mapping->subject_code,
                            'short_name' => $mapping->short_name,
                            'subject_type' => $subjectType,
                            'is_optional' => (int) ($mapping->is_optional ?? 0),
                            'sort_order' => count($rows) + 1,
                            'excel_row' => $rowNumber,
                            'theory_max_marks' => $config['theory_max_marks'],
                            'theory_passing_marks' => $config['theory_passing_marks'],
                            'oral_max_marks' => $config['oral_max_marks'],
                            'oral_passing_marks' => $config['oral_passing_marks'],
                            'practical_max_marks' => $config['practical_max_marks'],
                            'practical_passing_marks' => $config['practical_passing_marks'],
                            'aggregate_max_marks' => $config['aggregate_max_marks'],
                            'aggregate_passing_marks' => $config['aggregate_passing_marks'],
                            'max_marks' => $config['max_marks'],
                            'passing_marks' => $config['passing_marks'],
                            'secondary_component' => $config['secondary_component'],
                            'source' => [
                                'sheet' => $sheetName,
                                'row' => $rowNumber,
                                'file_hash' => $hash,
                            ],
                            'warnings' => $config['warnings'],
                        ];
                    }
                }

                if (empty($rows)) {
                    $errors[] = 'No usable subject rows with marks were found for this Standard + Exam Type.';
                }

                $flags = [
                    'has_theory' => collect($rows)->contains(fn (array $row) => $row['theory_max_marks'] > 0),
                    'has_oral' => collect($rows)->contains(fn (array $row) => $row['oral_max_marks'] > 0),
                    'has_practical' => collect($rows)->contains(fn (array $row) => $row['practical_max_marks'] > 0),
                ];

                return [
                    'success' => !empty($rows) && empty($errors),
                    'file_hash' => $hash,
                    'standard_id' => $standardId,
                    'standard_name' => $standard->standard_name,
                    'sheet_name' => $sheetName,
                    'exam_type' => $examType,
                    'subjects' => $rows,
                    'warnings' => array_values(array_unique($warnings)),
                    'errors' => array_values(array_unique($errors)),
                    'flags' => $flags,
                    'exam_total_max_marks' => (float) collect($rows)->sum('max_marks'),
                    'exam_total_passing_marks' => (float) collect($rows)->sum('passing_marks'),
                ];
            }
        );
    }

    /**
     * Validate every workbook sheet against the current DB subject mappings.
     */
    public function validateAll(): array
    {
        $results = [];

        foreach (Standard::where('is_active', 1)->orderBy('display_order')->get() as $standard) {
            $types = $this->getExamTypes((int) $standard->id);

            $standardResult = [
                'standard_id' => (int) $standard->id,
                'standard_name' => $standard->standard_name,
                'sheet_name' => $this->sheetNameForStandard($standard->standard_name),
                'exam_types' => [],
            ];

            foreach ($types as $type) {
                $structure = $this->getStructure((int) $standard->id, $type);
                $standardResult['exam_types'][$type] = [
                    'subject_count' => count($structure['subjects'] ?? []),
                    'warnings' => $structure['warnings'] ?? [],
                    'errors' => $structure['errors'] ?? [],
                    'success' => (bool) ($structure['success'] ?? false),
                ];
            }

            $results[] = $standardResult;
        }

        return [
            'file' => $this->path(),
            'hash' => $this->fileHash(),
            'results' => $results,
        ];
    }

    private function loadWorkbook()
    {
        $reader = IOFactory::createReaderForFile($this->path());
        $reader->setReadDataOnly(true);

        return $reader->load($this->path());
    }

    private function detectExamGroups(Worksheet $sheet): array
    {
        $groups = [];
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $highestRow = $sheet->getHighestRow();
        $starts = [];

        for ($column = 1; $column <= $highestColumn; $column++) {
            $value = $this->normalizeHeader($sheet->getCellByColumnAndRow($column, 2)->getValue());
            $examType = $this->normalizeExamType($value);

            if ($this->isKnownExamType($examType)) {
                $starts[$column] = $examType;
            }
        }

        $columns = array_keys($starts);
        sort($columns);

        foreach ($columns as $index => $startColumn) {
            $endColumn = ($columns[$index + 1] ?? ($highestColumn + 1)) - 1;
            $examType = $starts[$startColumn];

            $groups[] = [
                'exam_type' => $examType,
                'start_col' => $startColumn,
                'end_col' => $endColumn,
                'data_start_row' => 5,
                'data_end_row' => $highestRow,
            ];
        }

        return $groups;
    }

    private function detectHeaderAnchors(Worksheet $sheet, int $startColumn, int $endColumn): array
    {
        $anchors = [];

        foreach ([3, 4] as $headerRow) {
            for ($column = $startColumn; $column <= $endColumn; $column++) {
                $raw = $this->text($sheet->getCellByColumnAndRow($column, $headerRow)->getValue());
                if ($raw === '') {
                    continue;
                }

                $classification = $this->classifyHeader($raw);
                if ($classification === null) {
                    continue;
                }

                $normalized = $this->normalizeHeader($raw);

                // Only the first column of a Max/Pass pair should become a
                // component anchor. The workbook contains labels such as
                // "Theory Min Pass", "Practical/Oral Min (Pass)" and
                // "Grand Total Min (35%)" in the second column. Treating
                // those as anchors breaks the Max -> Pass pairing.
                if (
                    str_contains($normalized, 'PASSING')
                    || str_contains($normalized, 'MINPASS')
                    || str_contains($normalized, 'AGGREGATEPASS')
                    || str_contains($normalized, 'MINIMUM')
                    || str_ends_with($normalized, 'MIN')
                ) {
                    continue;
                }

                // In the older primary/pre-primary layouts row 4 contains generic
                // pair labels (Total Marks / Passing Marks). Row 3/4 already tells
                // us the semantic component, so generic row-4 labels must not
                // overwrite it.
                if (
                    $headerRow === 4
                    && in_array($normalized, ['TOTALMARKS', 'PASSINGMARKS'], true)
                ) {
                    continue;
                }

                if (!isset($anchors[$column])) {
                    $anchors[$column] = [
                        'column' => $column,
                        'kind' => $classification,
                        'raw' => $raw,
                        'row' => $headerRow,
                        'normalized' => $normalized,
                    ];
                    continue;
                }

                // Row 4 is preferred when it contains an explicit component label.
                if ($headerRow === 4 && $classification !== 'ignore') {
                    $anchors[$column] = [
                        'column' => $column,
                        'kind' => $classification,
                        'raw' => $raw,
                        'row' => $headerRow,
                        'normalized' => $normalized,
                    ];
                }
            }
        }

        ksort($anchors);

        return array_values($anchors);
    }

    private function classifyHeader(string $raw): ?string
    {
        $header = $this->normalizeHeader($raw);

        if ($header === '') {
            return null;
        }

        if (str_contains($header, 'THEORYANDORAL') || str_contains($header, 'THEORYORAL')) {
            return 'aggregate_subtotal';
        }

        if (
            str_contains($header, 'GRANDTOTAL') ||
            $header === 'TOTAL' ||
            str_contains($header, 'TOTALMARKS')
        ) {
            return 'aggregate_total';
        }

        if (str_contains($header, 'PRACTICAL') || str_contains($header, 'PRACTIVAL')) {
            if (str_contains($header, 'ORAL')) {
                return 'secondary';
            }

            return 'practical';
        }

        if (str_contains($header, 'ORAL')) {
            return 'oral';
        }

        if (str_contains($header, 'THEORY')) {
            return 'theory';
        }

        return 'ignore';
    }

    private function readRowConfiguration(Worksheet $sheet, int $rowNumber, array $headers, array $group): array
    {
        $result = [
            'theory_max_marks' => 0,
            'theory_passing_marks' => 0,
            'oral_max_marks' => 0,
            'oral_passing_marks' => 0,
            'practical_max_marks' => 0,
            'practical_passing_marks' => 0,
            'aggregate_max_marks' => 0,
            'aggregate_passing_marks' => 0,
            'aggregate_kind' => null,
            'has_explicit_total' => false,
            'max_marks' => 0,
            'passing_marks' => 0,
            'secondary_component' => null,
            'has_any_marks' => false,
            'warnings' => [],
            '_anchor_columns' => array_map(fn (array $a) => $a['column'], $headers),
            '_secondary_values' => [],
        ];

        foreach ($headers as $anchor) {
            $column = $anchor['column'];
            $kind = $anchor['kind'];
            $max = $this->number($sheet->getCellByColumnAndRow($column, $rowNumber)->getValue());

            if ($max > 0) {
                $result['has_any_marks'] = true;
            }

            $pass = null;
            $passColumn = $column + 1;

            // The workbook has a few overlapping header layouts (notably Nursery UT1/UT2).
            // When the next column is itself another header anchor, its meaning is ambiguous.
            if ($passColumn <= $group['end_col'] && !in_array($passColumn, $result['_anchor_columns'], true)) {
                $pass = $this->nullableNumber(
                    $sheet->getCellByColumnAndRow($passColumn, $rowNumber)->getValue()
                );
            }

            if ($pass === null) {
                $pass = 0;
            }

            switch ($kind) {
                case 'theory':
                    $result['theory_max_marks'] = $max;
                    $result['theory_passing_marks'] = $pass;
                    break;

                case 'oral':
                    $result['oral_max_marks'] = $max;
                    $result['oral_passing_marks'] = $pass;
                    break;

                case 'practical':
                    $result['practical_max_marks'] = $max;
                    $result['practical_passing_marks'] = $pass;
                    break;

                case 'secondary':
                    $result['_secondary_values'][] = [
                        'max' => $max,
                        'pass' => $pass,
                    ];
                    break;

                case 'aggregate_subtotal':
                    $result['aggregate_max_marks'] = $max;
                    $result['aggregate_passing_marks'] = $pass;
                    $result['aggregate_kind'] = 'subtotal';
                    break;

                case 'aggregate_total':
                    $result['aggregate_max_marks'] = $max;
                    $result['aggregate_passing_marks'] = $pass;
                    $result['aggregate_kind'] = 'total';
                    $result['has_explicit_total'] = true;
                    break;
            }
        }

        $subjectName = $this->text($sheet->getCellByColumnAndRow(2, $rowNumber)->getValue());
        $secondary = $result['_secondary_values'][0] ?? null;

        if ($secondary) {
            $result['secondary_component'] = $this->isLikelyPracticalSubject($subjectName)
                ? 'practical'
                : 'oral';

            if ($result['secondary_component'] === 'practical') {
                $result['practical_max_marks'] = $secondary['max'];
                $result['practical_passing_marks'] = $secondary['pass'];
            } else {
                $result['oral_max_marks'] = $secondary['max'];
                $result['oral_passing_marks'] = $secondary['pass'];
            }
        }

        return $result;
    }

    private function finalizeConfiguration(
        array $config,
        string $excelSubjectName,
        string $standardName,
        int $rowNumber,
        array &$globalWarnings
    ): array {
        $componentBase = $config['theory_max_marks'] + $config['oral_max_marks'] + $config['practical_max_marks'];
        $componentPass = $config['theory_passing_marks'] + $config['oral_passing_marks'] + $config['practical_passing_marks'];

        $maxMarks = 0;
        $passingMarks = 0;

        if ($config['has_explicit_total']) {
            $maxMarks = $config['aggregate_max_marks'];
            $passingMarks = $config['aggregate_passing_marks'];

            if ($componentBase > 0 && abs($componentBase - $maxMarks) > 0.0001) {
                $message = sprintf(
                    '%s row %d (%s): Explicit Total Max %.2f differs from raw Theory+Oral+Practical %.2f.',
                    $standardName,
                    $rowNumber,
                    $excelSubjectName,
                    $maxMarks,
                    $componentBase
                );
                $config['warnings'][] = $message;
                $globalWarnings[] = $message;
            }

            if ($componentPass > 0 && abs($componentPass - $passingMarks) > 0.0001) {
                $message = sprintf(
                    '%s row %d (%s): Explicit Total Pass %.2f differs from raw component passes %.2f.',
                    $standardName,
                    $rowNumber,
                    $excelSubjectName,
                    $passingMarks,
                    $componentPass
                );
                $config['warnings'][] = $message;
                $globalWarnings[] = $message;
            }
        } elseif ($config['aggregate_kind'] === 'subtotal' && $config['aggregate_max_marks'] > 0) {
            $maxMarks = $config['aggregate_max_marks'] + $config['practical_max_marks'];
            $passingMarks = $config['aggregate_passing_marks'] + $config['practical_passing_marks'];

            $rawTheoryOral = $config['theory_max_marks'] + $config['oral_max_marks'];
            if ($rawTheoryOral > 0 && abs($rawTheoryOral - $config['aggregate_max_marks']) > 0.0001) {
                $message = sprintf(
                    '%s row %d (%s): "Theory + Oral" aggregate %.2f differs from Theory+Oral components %.2f.',
                    $standardName,
                    $rowNumber,
                    $excelSubjectName,
                    $config['aggregate_max_marks'],
                    $rawTheoryOral
                );
                $config['warnings'][] = $message;
                $globalWarnings[] = $message;
            }

            $rawTheoryOralPass = $config['theory_passing_marks'] + $config['oral_passing_marks'];
            if ($rawTheoryOralPass > 0 && abs($rawTheoryOralPass - $config['aggregate_passing_marks']) > 0.0001) {
                $message = sprintf(
                    '%s row %d (%s): "Theory + Oral" passing aggregate %.2f differs from Theory+Oral component passes %.2f.',
                    $standardName,
                    $rowNumber,
                    $excelSubjectName,
                    $config['aggregate_passing_marks'],
                    $rawTheoryOralPass
                );
                $config['warnings'][] = $message;
                $globalWarnings[] = $message;
            }
        } else {
            $maxMarks = $componentBase;
            $passingMarks = $componentPass;
        }

        if ($maxMarks <= 0) {
            $maxMarks = $componentBase;
        }

        if ($passingMarks < 0) {
            $passingMarks = 0;
        }

        $components = [
            'theory' => [$config['theory_max_marks'], $config['theory_passing_marks']],
            'oral' => [$config['oral_max_marks'], $config['oral_passing_marks']],
            'practical' => [$config['practical_max_marks'], $config['practical_passing_marks']],
        ];

        foreach ($components as $name => [$max, $pass]) {
            if ($max > 0 && $pass > $max) {
                $message = sprintf(
                    '%s row %d (%s): %s passing marks %.2f exceed maximum marks %.2f.',
                    $standardName,
                    $rowNumber,
                    $excelSubjectName,
                    ucfirst($name),
                    $pass,
                    $max
                );
                $config['warnings'][] = $message;
                $globalWarnings[] = $message;
            }
        }

        if ($passingMarks > $maxMarks && $maxMarks > 0) {
            $message = sprintf(
                '%s row %d (%s): Total passing marks %.2f exceed Total maximum marks %.2f.',
                $standardName,
                $rowNumber,
                $excelSubjectName,
                $passingMarks,
                $maxMarks
            );
            $config['warnings'][] = $message;
            $globalWarnings[] = $message;
        }

        $config['max_marks'] = $maxMarks;
        $config['passing_marks'] = $passingMarks;

        unset($config['_anchor_columns'], $config['_secondary_values']);

        return $config;
    }

    private function resolveSubjectMappings(
        string $excelName,
        $mappings,
        array $usedSubjectIds,
        string $standardName
    ) {
        $normalized = $this->normalizeSubjectName($excelName);

        // 1) Exact subject name.
        $exact = $mappings->first(function ($mapping) use ($normalized, $usedSubjectIds) {
            return !in_array((int) $mapping->subject_id, $usedSubjectIds, true)
                && $this->normalizeSubjectName($mapping->subject_name) === $normalized;
        });

        if ($exact) {
            return collect([$exact]);
        }

        // 2) Common naming aliases.
        $aliases = [
            'MATHS' => ['MATHEMATICS'],
            'MATHEMATICS' => ['MATHS'],
            'MATHSI' => ['MATHEMATICSI', 'MATHS1', 'MATHEMATICS1'],
            'MATHSII' => ['MATHEMATICSII', 'MATHS2', 'MATHEMATICS2'],
            'SCIENCEI' => ['SCIENCEI', 'SCIENCE1'],
            'SCIENCEII' => ['SCIENCEII', 'SCIENCE2'],
            'EVS1' => ['ENVIRONMENTALSTUDIESI', 'EVSI', 'ENVIRONMENTALSTUDIES1'],
            'EVS2' => ['ENVIRONMENTALSTUDIESII', 'EVSII', 'ENVIRONMENTALSTUDIES2'],
            'ENGLISH' => ['COMPULSORYLANGUAGEENGLISH'],
        ];

        $aliasNames = $aliases[$normalized] ?? [];
        if (!empty($aliasNames)) {
            $candidate = $mappings->first(function ($mapping) use ($aliasNames, $usedSubjectIds) {
                return !in_array((int) $mapping->subject_id, $usedSubjectIds, true)
                    && in_array($this->normalizeSubjectName($mapping->subject_name), $aliasNames, true);
            });

            if ($candidate) {
                return collect([$candidate]);
            }
        }

        // 3) Junior-college semantic slots. The workbook uses one row to
        // represent a selectable subject slot, while the database contains
        // the actual subjects. Apply the same marks configuration to each
        // active subject matching that slot.
        if (str_contains($normalized, 'SECONDLANGUAGE')) {
            return $mappings->filter(function ($mapping) use ($usedSubjectIds) {
                if (in_array((int) $mapping->subject_id, $usedSubjectIds, true)) {
                    return false;
                }
                $name = $this->normalizeSubjectName($mapping->subject_name);
                return str_contains($name, 'MARATHI')
                    || str_contains($name, 'HINDI')
                    || $name === 'IT'
                    || str_contains($name, 'INFORMATIONTECHNOLOGY');
            })->values();
        }

        if (str_contains($normalized, 'GEOGRAPHYECONOMICSOTHERELECTIVE')) {
            return $mappings->filter(function ($mapping) use ($usedSubjectIds) {
                if (in_array((int) $mapping->subject_id, $usedSubjectIds, true)) {
                    return false;
                }
                $name = $this->normalizeSubjectName($mapping->subject_name);
                return !in_array($name, ['COMPUTER', 'ROBOTICS', 'PHYSICALEDUCATION'], true);
            })->values();
        }

        if (str_contains($normalized, 'ELECTIVESELECT1') || str_contains($normalized, 'SECRETARIALPRACTICE')) {
            return $mappings->filter(function ($mapping) use ($usedSubjectIds) {
                if (in_array((int) $mapping->subject_id, $usedSubjectIds, true)) {
                    return false;
                }
                $name = $this->normalizeSubjectName($mapping->subject_name);
                return str_contains($name, 'SECRETARIALPRACTICE')
                    || $name === 'SP'
                    || str_contains($name, 'MATHEMATICSSTATISTICS');
            })->values();
        }

        return collect();
    }

    private function isLikelyPracticalSubject(string $name): bool
    {
        $normalized = $this->normalizeSubjectName($name);

        return str_contains($normalized, 'PHYSICS')
            || str_contains($normalized, 'CHEMISTRY')
            || str_contains($normalized, 'BIOLOGY')
            || $normalized === 'IT'
            || str_contains($normalized, 'INFORMATIONTECHNOLOGY');
    }

    public function sheetNameForStandard(string $standardName): ?string
    {
        $normalized = $this->normalizeSubjectName($standardName);

        if ($normalized === 'NURSERY' || $normalized === 'NUR') {
            return 'Nursery';
        }
        if (in_array($normalized, ['JRKG', 'JUNIORKG', 'JUNIORKINDERGARTEN'], true)) {
            return 'Jrkg';
        }
        if (in_array($normalized, ['SRKG', 'SENIORKG', 'SENIORKINDERGARTEN'], true)) {
            return 'Srkg';
        }

        // Common school naming variants already used by the ERP/old ERP.
        if (in_array($normalized, ['XISCI', '11SCI'], true)) return '11Sci';
        if (in_array($normalized, ['XIISCI', '12SCI'], true)) return '12Sci';
        if (in_array($normalized, ['XICOM', '11COM'], true)) return '11Com';
        if (in_array($normalized, ['XIICOM', '12COM'], true)) return '12Com';
        if ($normalized === 'FORTH') return '4th';
        if ($normalized === 'NINETH') return '9th';

        foreach (range(1, 10) as $number) {
            $suffix = $this->ordinalSuffix($number);
            if (
                $normalized === $number . $suffix
                || $normalized === $this->numberWord($number)
                || str_starts_with($normalized, $number . $suffix)
                || str_starts_with($normalized, $this->numberWord($number))
            ) {
                return $number === 8 ? '8TH ' : $number . $suffix;
            }
        }

        if ((str_contains($normalized, '11') || str_contains($normalized, 'ELEVENTH')) && str_contains($normalized, 'SCIENCE')) {
            return '11Sci';
        }
        if ((str_contains($normalized, '12') || str_contains($normalized, 'TWELFTH')) && str_contains($normalized, 'SCIENCE')) {
            return '12Sci';
        }
        if ((str_contains($normalized, '11') || str_contains($normalized, 'ELEVENTH')) && str_contains($normalized, 'COMMERCE')) {
            return '11Com';
        }
        if ((str_contains($normalized, '12') || str_contains($normalized, 'TWELFTH')) && str_contains($normalized, 'COMMERCE')) {
            return '12Com';
        }

        return null;
    }

    private function ordinalSuffix(int $number): string
    {
        if ($number === 1) return 'ST';
        if ($number === 2) return 'ND';
        if ($number === 3) return 'RD';
        return 'TH';
    }

    private function numberWord(int $number): string
    {
        return [
            1 => 'FIRST',
            2 => 'SECOND',
            3 => 'THIRD',
            4 => 'FOURTH',
            5 => 'FIFTH',
            6 => 'SIXTH',
            7 => 'SEVENTH',
            8 => 'EIGHTH',
            9 => 'NINTH',
            10 => 'TENTH',
        ][$number] ?? '';
    }

    public function normalizeExamType(string $value): string
    {
        $value = $this->normalizeHeader($value);

        return match (true) {
            $value === 'UNITTEST1' => 'UNIT TEST 1',
            $value === 'UNITTEST2' => 'UNIT TEST 2',
            $value === 'UNITTEST3' => 'UNIT TEST 3',
            $value === 'UNITTEST4' => 'UNIT TEST 4',
            $value === 'TERM1' => 'TERM 1',
            $value === 'TERM2' => 'TERM 2',
            $value === 'TERM2FINAL' => 'TERM 2',
            $value === 'ANNUAL' => 'ANNUAL',
            default => strtoupper(trim($value)),
        };
    }

    private function isKnownExamType(string $value): bool
    {
        return in_array($value, self::EXAM_ORDER, true);
    }

    private function normalizeSubjectName(string $value): string
    {
        return $this->normalizeHeader($value);
    }

    private function normalizeHeader(string $value): string
    {
        $value = strtoupper(trim($value));
        return preg_replace('/[^A-Z0-9]+/', '', $value) ?? '';
    }

    private function text($value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function number($value): float
    {
        $number = $this->nullableNumber($value);
        return $number ?? 0.0;
    }

    private function nullableNumber($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        if ($text === '' || $text === '-') {
            return null;
        }

        $text = str_replace(',', '', $text);

        return is_numeric($text) ? (float) $text : null;
    }
}
