<?php

namespace App\Services;

use App\Helpers\ResultSheetHelper;

class ResultSheetAnalysisService
{
    public function buildOverallGradeAnalysis($results): array
    {
        $analysis = [
            'A1' => $this->row('91-100%'),
            'A2' => $this->row('81-90%'),
            'B1' => $this->row('71-80%'),
            'B2' => $this->row('61-70%'),
            'C1' => $this->row('51-60%'),
            'C2' => $this->row('41-50%'),
            'D'  => $this->row('33-40%'),
            'F'  => $this->row('Below 33%'),
            'PASS' => $this->row('PASS'),
            'FAIL' => $this->row('FAIL'),
            'PENDING' => $this->row('PENDING'),
            'TOTAL' => $this->row('TOTAL'),
        ];

        foreach ($results as $student) {
            $result = strtoupper(trim((string) ($student->result ?? '-')));
            if (!in_array($result, ['PASS', 'FAIL'], true)) {
                continue;
            }

            $gender = ResultSheetHelper::normalizeGender($student->gender ?? '');
            $genderKey = match ($gender) {
                'FEMALE' => 'girls',
                'MALE' => 'boys',
                default => null,
            };

            $analysis['TOTAL']['total']++;
            if ($genderKey) {
                $analysis['TOTAL'][$genderKey]++;
            }

            $grade = strtoupper(trim((string) ($student->calculated_grade ?? '-')));
            if (isset($analysis[$grade]) && in_array($grade, ['A1','A2','B1','B2','C1','C2','D','F'], true)) {
                $analysis[$grade]['total']++;
                if ($genderKey) {
                    $analysis[$grade][$genderKey]++;
                }
            }

            $analysis[$result]['total']++;
            if ($genderKey) {
                $analysis[$result][$genderKey]++;
            }
        }

        foreach ($results as $student) {
            if (strtoupper(trim((string) ($student->result ?? ''))) === 'PENDING') {
                $analysis['PENDING']['total']++;
                $gender = ResultSheetHelper::normalizeGender($student->gender ?? '');
                $genderKey = match ($gender) {
                    'FEMALE' => 'girls',
                    'MALE' => 'boys',
                    default => null,
                };
                if ($genderKey) {
                    $analysis['PENDING'][$genderKey]++;
                }
            }
        }

        return $analysis;
    }

    public function buildSubjectAnalysis($results, $columns, string $wantedGender): array
    {
        $wantedGender = ResultSheetHelper::normalizeGender($wantedGender);
        $analysis = [];

        foreach ($columns as $column) {
            $code = trim((string) ($column->subject_code ?? ''));
            if ($code === '') {
                $code = (string) ((int) ($column->subject_id ?? 0));
            }

            $analysis[$code] = array_merge(
                [
                    'subject' => $code,
                    'subject_name' => $column->subject_name ?? '-',
                    'subject_code' => $code,
                ],
                ResultSheetHelper::emptySubjectAnalysis()
            );
        }

        foreach ($results as $student) {
            if (ResultSheetHelper::normalizeGender($student->gender ?? '') !== $wantedGender) {
                continue;
            }

            $studentId = (int) ($student->student_id ?? 0);
            if ($studentId <= 0) {
                continue;
            }

            foreach ($columns as $column) {
                $code = trim((string) ($column->subject_code ?? ''));
                if ($code === '') {
                    $code = (string) ((int) ($column->subject_id ?? 0));
                }

                if (!isset($analysis[$code])) {
                    continue;
                }

                $mark = ResultSheetHelper::getStudentMark($student, $column);
                $grade = ResultSheetHelper::getStudentGrade($student, $column);
                $isOptional = ResultSheetHelper::isStudentOptional($student, $column);

                if ($isOptional) {
                    continue;
                }

                $markText = strtoupper(trim((string) ($mark ?? '')));
                $gradeText = strtoupper(trim((string) ($grade ?? '')));

                if ($markText === '' || $markText === '-') {
                    if ($markText === '-') {
                        // Missing compulsory mark is pending; it is not a fail.
                        $analysis[$code]['pending']++;
                    }
                    continue;
                }

                if ($markText === 'OPT' || $gradeText === 'OPT') {
                    continue;
                }

                if ($markText === 'AB' || $gradeText === 'AB') {
                    $analysis[$code]['absent']++;
                    $analysis[$code]['total']++;
                    continue;
                }

                if ($gradeText === 'F') {
                    $analysis[$code]['fail']++;
                    $analysis[$code]['total']++;
                    continue;
                }

                if (isset($analysis[$code][$gradeText])) {
                    $analysis[$code][$gradeText]++;
                    $analysis[$code]['total']++;
                }
            }
        }

        return array_values($analysis);
    }

    private function row(string $range): array
    {
        return [
            'range' => $range,
            'girls' => 0,
            'boys' => 0,
            'total' => 0,
        ];
    }
}
