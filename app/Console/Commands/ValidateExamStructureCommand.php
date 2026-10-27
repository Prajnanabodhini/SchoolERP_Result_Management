<?php

namespace App\Console\Commands;

use App\Services\ExamStructureExcelService;
use App\Services\ExamStructureSyncService;
use Illuminate\Console\Command;

class ValidateExamStructureCommand extends Command
{
    protected $signature = 'exam:structure-validate
                            {--sync       : After validating, rebuild exam_master_subjects from exam_structures}
                            {--sync-only  : Skip validation and just run the DB sync}
                            {--standard-id= : Limit --sync / --sync-only to one standard id}
                            {--exam-type=   : Limit --sync / --sync-only to one exam type (e.g. "TERM 1")}';

    protected $description = 'Validate the Exam Structure (Excel) AND/OR rebuild exam_master_subjects from exam_structures.';

    public function handle(
        ExamStructureExcelService $excel,
        ExamStructureSyncService $sync
    ): int {

        if ($this->option('sync-only')) {
            return $this->runSync($sync);
        }

        try {
            $report = $excel->validateAll();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            if ($this->option('sync')) {
                $this->newLine();
                $this->warn('Continuing with --sync despite validation error...');
                return $this->runSync($sync);
            }
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
                $errors   = $result['errors']   ?? [];
                $warnings = $result['warnings'] ?? [];
                $errorCount   += count($errors);
                $warningCount += count($warnings);

                $status = empty($errors) ? '<fg=green>OK</>' : '<fg=red>ERROR</>';
                $this->line(sprintf(
                    '  %-14s %-8s Subjects: %d',
                    $examType, $status, $result['subject_count']
                ));

                foreach ($errors   as $e) $this->error('    ERROR: ' . $e);
                foreach ($warnings as $w) $this->warn('    WARNING: ' . $w);
            }
        }

        $this->newLine();
        $this->info("Validation complete. Errors: {$errorCount}; Warnings: {$warningCount}.");

        if ($this->option('sync')) {
            $this->newLine();
            $this->runSync($sync);
            if ($errorCount > 0) return self::FAILURE;
        }

        return $errorCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function runSync(ExamStructureSyncService $sync): int
    {
        $standardId = $this->option('standard-id');
        $examType   = $this->option('exam-type');

        if ($standardId) {
            $this->info("Syncing standard_id={$standardId}" . ($examType ? " exam_type={$examType}" : '') . ' ...');
            $count = $sync->syncStandardFromDatabase((int) $standardId, $examType);
        } else {
            $this->info('Syncing ALL active Exam Masters from exam_structures ...');
            $count = $sync->syncAllFromDatabase();
        }

        $this->info("✔ Exam Masters updated: {$count}");

        return self::SUCCESS;
    }
}