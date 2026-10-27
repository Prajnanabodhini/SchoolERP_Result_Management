<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Standard;
use App\Models\AcademicYear;

class SyncErpMappings extends Command
{
    // Command to run: php artisan erp:sync-mappings
    protected $signature = 'erp:sync-mappings';
    protected $description = 'Sync all Standard and Division mappings from MSSQL ERP to MySQL';

    public function handle()
    {
        $this->info('Starting ERP Mapping Sync...');

        // 1. Define aliases to handle spelling mistakes in MSSQL (like NINETH/NIGNTH)
        $aliases = [
            'NINTH'             => ['NINTH', 'NINETH', 'NIGNTH'],
            'FOURTH'            => ['FOURTH', 'FORTH'],
            'JR KG'             => ['JR KG', 'JRKG', 'JR'],
            'SR KG'             => ['SR KG', 'SRKG', 'SR'],
            'NURSERY'           => ['NURSERY', 'NUR'],
            'ELEVENTH SCIENCE'  => ['ELEVENTH SCIENCE', 'XISCI', 'SCIENCE'],
            'TWELFTH SCIENCE'   => ['TWELFTH SCIENCE', 'XIISCI', 'SCIENCE'],
            'ELEVENTH COMMERCE' => ['ELEVENTH COMMERCE', 'XICOM', 'COMMERCE'],
            'TWELFTH COMMERCE'  => ['TWELFTH COMMERCE', 'XIICOM', 'COMMERCE'],
        ];

        // 2. Get all Laravel Academic Years
        $academicYears = AcademicYear::all();
        
        if ($academicYears->isEmpty()) {
            $this->error('No Academic Years found in MySQL.');
            return;
        }

        foreach ($academicYears as $year) {
            // Extract "2026" from "2026-2027"
            $erpYearId = substr($year->year_name, 0, 4);
            if (!ctype_digit($erpYearId)) continue;

            $this->info("Processing Year: {$year->year_name} (ERP ID: {$erpYearId})...");

            // Fetch standards from MSSQL for this specific year
            $erpStandards = DB::connection('sqlsrv_olderp')
                ->table('StandardMst')
                ->where('yearid', $erpYearId)
                ->get();

            if ($erpStandards->isEmpty()) {
                $this->warn(" - No standards found in ERP for year {$erpYearId}");
                continue;
            }

            $mappedCount = 0;

            foreach ($erpStandards as $erp) {
                $erpName = strtoupper(trim($erp->name));
                $erpStandardCode = strtoupper(trim($erp->standard));

                // Find the matching Laravel standard
                $laravelStandard = Standard::all()->first(function ($std) use ($erpName, $erpStandardCode, $aliases) {
                    $stdName = strtoupper(trim($std->standard_name));
                    
                    // Exact match
                    if ($stdName === $erpName || $stdName === $erpStandardCode) return true;

                    // Check aliases
                    if (isset($aliases[$stdName])) {
                        return in_array($erpName, $aliases[$stdName]) || in_array($erpStandardCode, $aliases[$stdName]);
                    }
                    return false;
                });

                if ($laravelStandard) {
                    // A. Update the fallback ID in the main standards table
                    DB::table('standards')->where('id', $laravelStandard->id)->update([
                        'old_standard_id' => $erp->standardid
                    ]);

                    // B. Update the year-specific mapping table
                    DB::table('standard_year_mappings')->updateOrInsert(
                        [
                            'academic_year_id' => $year->id,
                            'standard_id'      => $laravelStandard->id
                        ],
                        [
                            'old_standard_id'  => $erp->standardid,
                            'updated_at'       => now(),
                            'created_at'       => now()
                        ]
                    );
                    
                    $mappedCount++;
                }
            }
            $this->info(" - Mapped {$mappedCount} standards for {$year->year_name}.");
        }

        // 3. Sync Divisions
        $this->info("Syncing Divisions...");
        
        // Get all unique divisions from MSSQL across all years
        $erpDivisions = DB::connection('sqlsrv_olderp')
            ->table('DivisionMst')
            ->select('division')
            ->distinct()
            ->get();

        $divCount = 0;
        foreach ($erpDivisions as $div) {
            $divName = strtoupper(trim($div->division));
            if (empty($divName)) continue;

            // Ensure this division exists in Laravel's divisions table
            $exists = DB::table('divisions')->whereRaw('UPPER(TRIM(division_name)) = ?', [$divName])->exists();
            
            if (!$exists) {
                DB::table('divisions')->insert([
                    'division_name' => $divName,
                    'is_active'     => 1,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ]);
                $this->info(" - Created missing Division: {$divName}");
                $divCount++;
            }
        }
        
        if ($divCount === 0) {
            $this->info(" - All ERP Divisions already exist in Laravel.");
        }

        // 4. Clear Cache
        $this->call('cache:clear');
        $this->call('config:clear');

        $this->info('✅ All Standard and Division mappings synced successfully!');
    }
}