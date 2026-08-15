<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Standard;
use App\Models\Division;
use App\Models\StudentAcademicRecord;

class ImportOldSchoolData extends Command
{
    protected $signature = 'import:oldschool';

    protected $description = 'Import old school academic history';

    public function handle()
    {
$this->info('Starting Academic Record Import...');

$academicFile = storage_path('app/import/StudentAcademicHistory.csv');

$imported = 0;

if (($handle = fopen($academicFile, 'r')) !== false) {

    $header = fgetcsv($handle);

    while (($row = fgetcsv($handle)) !== false) {

        $data = array_combine($header, $row);

        $student = Student::where(
            'old_student_id',
            $data['Studentid']
        )->first();

        if (!$student) {
            continue;
        }

        /*
        |------------------------------------------------------
        | Academic Year
        |------------------------------------------------------
        */
        $yearName = trim($data['AcademicYear']);

        $year = AcademicYear::firstOrCreate(
            ['year_name' => $yearName],
            [
                'start_date' => now(),
                'end_date'   => now(),
                'is_current' => 0,
                'is_active'  => 1,
            ]
        );

        /*
        |------------------------------------------------------
        | Standard
        |------------------------------------------------------
        */
        $standardName = strtoupper(trim($data['StandardName']));

        $standard = Standard::firstOrCreate(
            ['standard_name' => $standardName],
            [
                'display_order' => 999,
                'is_active'     => 1,
            ]
        );

        /*
        |------------------------------------------------------
        | Division
        |------------------------------------------------------
        */
        $divisionName = strtoupper(trim($data['DivisionName']));

        $division = Division::firstOrCreate(
            ['division_name' => $divisionName],
            [
                'display_order' => 999,
                'is_active'     => 1,
            ]
        );

        /*
        |------------------------------------------------------
        | Insert ALL Academic Records
        |------------------------------------------------------
        */
        StudentAcademicRecord::create([
    'student_id'        => $student->id,
    'academic_year_id'  => $year->id,
    'standard_id'       => $standard->id,
    'division_id'       => $division->id,
    'roll_no'           => $data['RollNo'] ?? null,
    'registration_no'   => $data['RegistrationNo'] ?? null,
    'student_name'      => $data['StudentName'] ?? null,
    'old_substudent_id' => null,
    'is_active'         => 1,
	]);

        $imported++;
    }

    fclose($handle);
}

$this->info("Academic Records Imported: {$imported}");

        $this->info('Academic Records Imported');
        $this->info('Import Completed Successfully');

        return Command::SUCCESS;
    }
}