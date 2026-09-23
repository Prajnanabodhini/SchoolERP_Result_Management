<?php

namespace App\Console\Commands;

use App\Services\ExamStructureExcelService;
use Illuminate\Console\Command;

class ValidateExamStructureCommand extends Command
{
    protected $signature = 'exam:structure-validate';

    protected $description = 'Validate Academic Exam Structure.xlsx against active Standard Wise Subject mappings.';

    public function handle(ExamStructureExcelService $service): int
    {
        try {
            $report = $service->validateAll();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info('File: ' . $report['file']);
        $this->info('SHA-256: ' . $report['hash']);
        $this->newLine();

        $errorCount = 0;
        $warningCount = 0;

        foreach ($report['results'] as $standard) {
            $this->line(str_repeat('-', 80));
            $this->line(sprintf(
                '%s | Sheet: %s',
                $standard['standard_name'],
                $standard['sheet_name'] ?? 'NOT FOUND'
            ));

            foreach ($standard['exam_types'] as $examType => $result) {
                $errors = $result['errors'] ?? [];
                $warnings = $result['warnings'] ?? [];
                $errorCount += count($errors);
                $warningCount += count($warnings);

                $status = empty($errors) ? '<fg=green>OK</>' : '<fg=red>ERROR</>';
                $this->line(sprintf(
                    '  %-14s %-8s Subjects: %d',
                    $examType,
                    $status,
                    $result['subject_count']
                ));

                foreach ($errors as $error) {
                    $this->error('    ERROR: ' . $error);
                }
                foreach ($warnings as $warning) {
                    $this->warn('    WARNING: ' . $warning);
                }
            }
        }

        $this->newLine();
        $this->info("Validation complete. Errors: {$errorCount}; Warnings: {$warningCount}.");

        return $errorCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
