<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class MarksEntryBladeHelper
{
    public static function styles(): string
    {
        return <<<'CSS'


    /*
    |--------------------------------------------------------------------------
    | PAGE
    |--------------------------------------------------------------------------
    */

    .marks-entry-page,
    .marks-entry-page * {
        box-sizing: border-box;
        font-family: Arial, sans-serif !important;
    }


    /*
    |--------------------------------------------------------------------------
    | CARD
    |--------------------------------------------------------------------------
    */

    .marks-entry-card {
        background: #ffffff;
        border: 1px solid #c7cdd4;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
        padding: 20px;
    }


    /*
    |--------------------------------------------------------------------------
    | TITLE
    |--------------------------------------------------------------------------
    */

    .marks-entry-title {
        margin: 0 0 15px;
        font-size: 20px;
        font-weight: 700;
        color: #2563eb;
    }


    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    .filter-row {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 10px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .filter-label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
    }

    .filter-select-wrapper {
        position: relative;
        display: inline-block;
    }

    .filter-select {
        height: 34px;
        padding: 5px 30px 5px 10px;
        border: 1px solid #aeb6c1;
        border-radius: 5px;
        background: #ffffff;
        color: #111827;
        font-size: 12px;
        outline: none;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
    }

    .filter-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 1px #2563eb;
    }

    .dropdown-arrow {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 6px solid #6b7280;
        pointer-events: none;
    }

    .academic-year-select {
        width: 175px;
    }

    .exam-select {
        width: 270px;
    }

    .assignment-select {
        width: 350px;
    }


    /*
    |--------------------------------------------------------------------------
    | BUTTONS
    |--------------------------------------------------------------------------
    */

    .erp-btn {
        min-height: 34px;
        height: auto;
        min-width: max-content;
        width: auto;
        padding: 7px 16px;
        border: 0;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
        line-height: 1.2;
    }

    .erp-btn-save {
        background: #2563eb;
        color: #ffffff;
    }

    .erp-btn-save:hover {
        background: #1d4ed8;
    }

    .erp-btn-green {
        background: #16a34a;
        color: #ffffff;
        min-width: 150px;
    }

    .erp-btn-green:hover {
        background: #15803d;
    }

    .erp-btn-green:disabled {
        background: #9ca3af !important;
        color: #ffffff !important;
        cursor: not-allowed !important;
        opacity: 0.85;
    }


    /*
    |--------------------------------------------------------------------------
    | MESSAGE BOXES
    |--------------------------------------------------------------------------
    */

    .error-box {
        margin-top: 15px;
        padding: 10px 12px;
        border-radius: 5px;
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        font-size: 12px;
    }

    .warning-box {
        margin-top: 15px;
        padding: 10px 12px;
        border-radius: 5px;
        background: #fffbeb;
        border: 1px solid #fcd34d;
        color: #92400e;
        font-size: 12px;
    }

    .saved-box {
        margin-top: 15px;
        padding: 10px 12px;
        border-radius: 5px;
        background: #fffbeb;
        border: 1px solid #f59e0b;
        color: #92400e;
        font-size: 12px;
        font-weight: 600;
    }

    .success-box {
        margin-top: 15px;
        padding: 10px 12px;
        border-radius: 5px;
        background: #ecfdf5;
        border: 1px solid #86efac;
        color: #166534;
        font-size: 12px;
        font-weight: 600;
    }


    /*
    |--------------------------------------------------------------------------
    | SELECTED INFORMATION
    |--------------------------------------------------------------------------
    */

    .selected-info {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 15px;
        padding: 10px 12px;
        border-radius: 5px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e3a8a;
        font-size: 12px;
    }

    .selected-info-item {
        font-weight: 700;
    }

    .selected-info-separator {
        color: #93c5fd;
    }


    /*
    |--------------------------------------------------------------------------
    | MARKS TABLE
    |--------------------------------------------------------------------------
    */

    .marks-table-wrapper {
        margin-top: 18px;
        overflow-x: auto;
        border: 1px solid #aeb6c1;
        border-radius: 5px;
    }

    .marks-table {
        width: 100%;
        border-collapse: collapse;
        background: #ffffff;
        font-size: 12px;
    }

    .marks-table th {
        background: #dbeafe;
        color: #1e3a8a;
        border: 1px solid #9aa5b1;
        padding: 8px 6px;
        text-align: center;
        white-space: nowrap;
        font-weight: 700;
    }

    .marks-table td {
        border: 1px solid #aeb6c1;
        padding: 7px 6px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .marks-table tbody tr:hover {
        background: #f8fafc;
    }

    .center {
        text-align: center;
    }

    .student-name-cell {
        min-width: 280px;
        white-space: normal !important;
    }


    /*
    |--------------------------------------------------------------------------
    | MARK INPUT
    |--------------------------------------------------------------------------
    */

    .mark-input {
        width: 62px;
        height: 30px;
        padding: 3px;
        border: 1px solid #9ca3af;
        border-radius: 4px;
        text-align: center;
        font-size: 13px;
        -moz-appearance: textfield;
        appearance: textfield;
    }

    .mark-input::-webkit-outer-spin-button,
    .mark-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .mark-input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 1px #2563eb;
    }

    .mark-input:read-only {
        background: #f3f4f6;
        color: #6b7280;
        cursor: not-allowed;
    }


    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE
    |--------------------------------------------------------------------------
    */

    .attendance-btn {
        min-width: 82px;
        padding: 5px 9px;
        border: 0;
        border-radius: 4px;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }

    .attendance-btn:focus {
        outline: none;
    }

    .present-btn {
        background: #16a34a;
    }

    .present-btn:hover {
        background: #15803d;
    }

    .absent-btn {
        background: #dc2626;
    }

    .absent-btn:hover {
        background: #b91c1c;
    }


    /*
    |--------------------------------------------------------------------------
    | OPTIONAL BUTTON
    |--------------------------------------------------------------------------
    */

    .optional-btn {
        min-width: 82px;
        padding: 5px 9px;
        border: 0;
        border-radius: 4px;
        color: #ffffff;
        background: #6b7280;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }

    .optional-btn:focus {
        outline: none;
    }

    .optional-btn:hover {
        background: #4b5563;
    }

    .optional-active-btn {
        background: #d97706 !important;
    }

    .optional-active-btn:hover {
        background: #b45309 !important;
    }

    .status-present {
        color: #16a34a;
        font-weight: 700;
    }

    .status-absent {
        color: #dc2626;
        font-weight: 700;
    }

    .status-optional {
        color: #d97706;
        font-weight: 700;
    }


    /*
    |--------------------------------------------------------------------------
    | OPTIONAL HEADER
    |--------------------------------------------------------------------------
    */

    .optional-header {
        background: #fef3c7 !important;
        color: #92400e !important;
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS CELL
    |--------------------------------------------------------------------------
    */

    .status-cell-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }


    /*
    |--------------------------------------------------------------------------
    | ACTION ROW
    |--------------------------------------------------------------------------
    */

    .marks-action-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
        flex-wrap: wrap;
    }

    .student-count {
        margin-left: auto;
        background: #dbeafe;
        color: #1e40af;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }


    /*
    |--------------------------------------------------------------------------
    | MOBILE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 900px) {

        .filter-group {
            width: 100%;
        }

        .academic-year-select,
        .exam-select,
        .assignment-select {
            width: 100%;
        }

        .student-count {
            margin-left: 0;
        }
    }


CSS;
    }

    public static function formatMark($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $number = (float) $value;

        if (floor($number) == $number) {
            return (string) (int) $number;
        }

        return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
    }

    public static function getStudentId($student): int|string|null
    {
        $id = $student->Studentid
            ?? $student->student_id
            ?? $student->id
            ?? null;

        if ($id === null || $id === '') {
            return null;
        }

        return is_numeric($id) ? (int) $id : $id;
    }

    public static function getGrNo($student): string
    {
        return trim((string) (
            $student->regno
            ?? $student->registration_no
            ?? $student->admission_no
            ?? $student->gr_no
            ?? '-'
        ));
    }

    public static function getRollNo($student): string
    {
        return trim((string) (
            $student->rollno
            ?? $student->roll_no
            ?? $student->roll_number
            ?? $student->roll
            ?? '-'
        ));
    }

    public static function getStudentName($student): string
    {
        return trim((string) (
            $student->studname
            ?? $student->student_name
            ?? $student->full_student_name
            ?? ''
        ));
    }

    public static function getFatherName($student): string
    {
        return trim((string) (
            $student->fathername
            ?? $student->father_name
            ?? $student->father_full_name
            ?? ''
        ));
    }

    public static function getFullStudentName($student): string
    {
        $name = trim(self::getStudentName($student) . ' ' . self::getFatherName($student));
        return $name !== '' ? $name : '-';
    }

    public static function getExistingMark($existingMarks, $studentId)
    {
        if (!$existingMarks) {
            return null;
        }

        if ($existingMarks instanceof Collection) {
            return $existingMarks->get((string) $studentId)
                ?? $existingMarks->get($studentId);
        }

        if (is_array($existingMarks)) {
            return $existingMarks[(string) $studentId]
                ?? $existingMarks[$studentId]
                ?? null;
        }

        return null;
    }

    public static function isAbsent($mark): bool
    {
        if (!$mark) {
            return false;
        }

        if ((int) ($mark->is_absent ?? 0) === 1) {
            return true;
        }

        $status = strtoupper(trim((string) ($mark->status ?? '')));
        return in_array($status, ['AB', 'ABSENT'], true);
    }

    public static function isOptional($mark, bool $optionalEnabled = true): bool
    {
        return $optionalEnabled
            && $mark
            && (int) ($mark->is_optional ?? 0) === 1;
    }

    public static function getStatusText($mark): string
    {
        if (!$mark) {
            return 'PRESENT';
        }

        if ((int) ($mark->is_optional ?? 0) === 1) {
            return 'OPT';
        }

        if (self::isAbsent($mark)) {
            return 'ABSENT';
        }

        $status = strtoupper(trim((string) ($mark->status ?? '')));
        return $status !== '' ? $status : 'PRESENT';
    }

    public static function getObtained($mark, string $field): string
    {
        if (!$mark || !isset($mark->{$field}) || $mark->{$field} === null || $mark->{$field} === '') {
            return '';
        }

        return self::formatMark($mark->{$field});
    }

    public static function getSelectedStandardId($selectedClassAllocation, $teacherSubjectAllocation = null): int
    {
        return (int) (
            $teacherSubjectAllocation?->allocation?->standard_id
            ?? $selectedClassAllocation?->standard_id
            ?? 0
        );
    }

    public static function isOptionalStandard(int $standardId): bool
    {
        return in_array($standardId, [19, 20, 21, 22, 23, 24], true);
    }

    public static function shouldShowOptionalColumn($isOptionalEnabled, $selectedStandardId): bool
    {
        return (bool) $isOptionalEnabled
            || self::isOptionalStandard((int) $selectedStandardId);
    }

    public static function getAssignmentSelectionKey($assignment): string
    {
        return (string) ($assignment->id ?? '');
    }

    public static function getAssignmentTeacherName($assignment): string
    {
        return optional(optional($assignment->allocation)->teacher)->name ?? '-';
    }

    public static function getAssignmentSubjectName($assignment): string
    {
        return optional($assignment->subject)->subject_name ?? '-';
    }

    public static function getAssignmentStandardName($assignment): string
    {
        return optional(optional($assignment->allocation)->standard)->standard_name ?? '-';
    }

    public static function getAssignmentDivisionName($assignment): string
    {
        return optional(optional($assignment->allocation)->division)->division_name ?? '-';
    }

    public static function getAssignmentStatus($assignment): string
    {
        return strtoupper(trim((string) ($assignment->resolved_status ?? 'PENDING')));
    }

    public static function getSelectedTeacherName($classAllocation): string
    {
        return optional(optional($classAllocation)->teacher)->name ?? '-';
    }

    public static function getSelectedSubjectName($tsa): string
    {
        return optional($tsa?->subject)->subject_name ?? '-';
    }

    public static function getSelectedStandardName($classAllocation): string
    {
        return optional($classAllocation?->standard)->standard_name ?? '-';
    }

    public static function getSelectedDivisionName($classAllocation): string
    {
        return optional($classAllocation?->division)->division_name ?? '-';
    }

    public static function getSelectedExamName($exam): string
    {
        return $exam?->display_exam_name ?? $exam?->exam_name ?? '-';
    }

    public static function getSelectedStatus($tsa): string
    {
        return strtoupper(trim((string) ($tsa?->resolved_status ?? 'PENDING')));
    }

    public static function getSelectedInformation($tsa, $classAllocation, $exam, $studentCount = null): array
    {
        return [
            'teacher' => self::getSelectedTeacherName($classAllocation),
            'subject' => self::getSelectedSubjectName($tsa),
            'standard' => self::getSelectedStandardName($classAllocation),
            'division' => self::getSelectedDivisionName($classAllocation),
            'exam' => self::getSelectedExamName($exam),
            'status' => self::getSelectedStatus($tsa),
            'students' => $studentCount,
        ];
    }

    public static function getMarkIdForStudent($student, $existingMarks = null): int|string|null
    {
        if (isset($student->mark_id) && $student->mark_id !== '') {
            return is_numeric($student->mark_id) ? (int) $student->mark_id : $student->mark_id;
        }

        $studentId = self::getStudentId($student);
        $mark = self::getExistingMark($existingMarks, $studentId);

        if (!$mark || !isset($mark->id)) {
            return null;
        }

        return (int) $mark->id;
    }

    public static function prepareStudentRow(
        $student,
        $existingMarks,
        bool $optionalEnabled,
        bool $marksLocked,
        string $mode = 'entry'
    ): array {
        $studentId = self::getStudentId($student);
        $mark = self::getExistingMark($existingMarks, $studentId);
        $isOptional = self::isOptional($mark, $optionalEnabled);
        $isAbsent = $isOptional ? false : self::isAbsent($mark);
        $markId = self::getMarkIdForStudent($student, $existingMarks);

        return [
            'student_id' => $studentId,
            'mark_id' => $markId,
            'input_key' => $mode === 'edit' ? $markId : $studentId,
            'gr_no' => self::getGrNo($student),
            'roll_no' => self::getRollNo($student),
            'name' => self::getFullStudentName($student),
            'mark' => $mark,
            'is_absent' => $isAbsent,
            'is_optional' => $isOptional,
            'status' => self::getStatusText($mark),
            'theory' => self::getObtained($mark, 'theory_obtained_marks'),
            'oral' => self::getObtained($mark, 'oral_obtained_marks'),
            'practical' => self::getObtained($mark, 'practical_obtained_marks'),
            'marks_readonly' => $marksLocked || $isAbsent || $isOptional,
            'mode' => $mode,
        ];
    }

    public static function prepareStudentRows(
        $students,
        $existingMarks,
        bool $optionalEnabled,
        bool $marksLocked,
        string $mode = 'entry'
    ): Collection {
        return collect($students)->map(
            fn ($student) => self::prepareStudentRow(
                $student,
                $existingMarks,
                $optionalEnabled,
                $marksLocked,
                $mode
            )
        )->values();
    }

    public static function prepareViewRow($row): array
    {
        $isOptional = (int) ($row->is_optional ?? 0) === 1;
        $isAbsent = !$isOptional && self::isAbsent($row);
        $status = self::getStatusText($row);

        return [
            'gr_no' => self::getGrNo($row),
            'roll_no' => self::getRollNo($row),
            'name' => self::getFullStudentName($row),
            'is_optional' => $isOptional,
            'is_absent' => $isAbsent,
            'status' => $status,
            'theory' => self::getObtained($row, 'theory_obtained_marks'),
            'oral' => self::getObtained($row, 'oral_obtained_marks'),
            'practical' => self::getObtained($row, 'practical_obtained_marks'),
            'theory_max' => $row->theory_max_marks ?? null,
            'theory_pass' => $row->theory_passing_marks ?? null,
            'oral_max' => $row->oral_max_marks ?? null,
            'oral_pass' => $row->oral_passing_marks ?? null,
            'practical_max' => $row->practical_max_marks ?? null,
            'practical_pass' => $row->practical_passing_marks ?? null,
        ];
    }

    public static function prepareViewRows($records): Collection
    {
        return collect($records)->map(
            fn ($row) => self::prepareViewRow($row)
        )->values();
    }

    public static function getStatusClass(string $status): string
    {
        return match (strtoupper(trim($status))) {
            'OPT' => 'status-optional',
            'PASS' => 'status-pass',
            'FAIL' => 'status-fail',
            'ABSENT', 'AB' => 'status-absent',
            default => 'status-other',
        };
    }
}
