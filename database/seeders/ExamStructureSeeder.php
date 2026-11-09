<?php

namespace Database\Seeders;

use App\Models\ExamStructure;
use App\Models\Subject;
use App\Models\Standard;
use App\Models\StandardWiseSubject;
use App\Helpers\ExamStructureHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamStructureSeeder extends Seeder
{
    public function run(): void
    {
        // 🚨 Truncate ONLY exam_structures
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('exam_structures')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // =========================================================================
        // 🧹 TARGETED CLEANUP BLOCK
        // =========================================================================
        $sciStandardIds = Standard::whereIn(DB::raw('UPPER(TRIM(standard_name))'), [
            'ELEVENTH SCIENCE', 'TWELFTH SCIENCE', '11TH SCIENCE', '12TH SCIENCE', '11SCI', '12SCI'
        ])->pluck('id')->toArray();

        $csSubject = Subject::whereRaw('UPPER(TRIM(subject_name)) = ?', ['COMPUTER SCIENCE'])->first();

        if (!empty($sciStandardIds) && $csSubject) {
            DB::table('standard_wise_subjects')->where('subject_id', $csSubject->id)->whereIn('standard_id', $sciStandardIds)->delete();
            DB::table('exam_master_subjects')->where('subject_id', $csSubject->id)->whereIn('standard_id', $sciStandardIds)->delete();
            DB::table('student_marks')->where('subject_id', $csSubject->id)->whereIn('standard_id', $sciStandardIds)->delete();
        }

        $invalidSubjects = ['SOCIOLOGY1'];
        foreach ($invalidSubjects as $invalidName) {
            $subj = Subject::whereRaw('UPPER(TRIM(subject_name)) = ?', [strtoupper(trim($invalidName))])->first();
            if ($subj) {
                DB::table('standard_wise_subjects')->where('subject_id', $subj->id)->delete();
                DB::table('exam_master_subjects')->where('subject_id', $subj->id)->delete();
                DB::table('student_marks')->where('subject_id', $subj->id)->delete();
                if (\Schema::hasTable('student_result_details')) {
                    DB::table('student_result_details')->where('subject_id', $subj->id)->delete();
                }
            }
        }
        // =========================================================================

        $rows = [];
        $missingSubjects = [];

        // ✅ Standard name variations
        $getMatchingStandardNames = function ($standardKey) {
            $key = strtoupper(trim($standardKey));
            return match ($key) {
                '11SCI'   => ['ELEVENTH SCIENCE', '11TH SCIENCE', '11 SCIENCE', 'XI SCIENCE', 'XISCI'],
                '11COM'   => ['ELEVENTH COMMERCE', '11TH COMMERCE', '11 COMMERCE', 'XI COMMERCE', 'XICOM'],
                '11ARTS'  => ['ELEVENTH ARTS', '11TH ARTS', '11 ARTS', 'XI ARTS', 'XIART', 'ELEVENTH ART', '11TH ART', 'XI ART', 'ELEVENTH HUMANITIES'],
                '12SCI'   => ['TWELFTH SCIENCE', '12TH SCIENCE', '12 SCIENCE', 'XII SCIENCE', 'XIISCI'],
                '12COM'   => ['TWELFTH COMMERCE', '12TH COMMERCE', '12 COMMERCE', 'XII COMMERCE', 'XIICOM'],
                '12ARTS'  => ['TWELFTH ARTS', '12TH ARTS', '12 ARTS', 'XII ARTS', 'XIIART', 'TWELFTH ART', '12TH ART', 'XII ART', 'TWELFTH HUMANITIES'],
                'NURSERY' => ['NURSERY', 'NUR'],
                'JRKG'    => ['JR KG', 'JRKG', 'JUNIOR KG'],
                'SRKG'    => ['SR KG', 'SRKG', 'SENIOR KG', 'SR'],
                '1ST'     => ['FIRST', '1ST', '1'],
                '2ND'     => ['SECOND', '2ND', '2'],
                '3RD'     => ['THIRD', '3RD', '3'],
                '4TH'     => ['FOURTH', 'FORTH', '4TH', '4'],
                '5TH'     => ['FIFTH', '5TH', '5'],
                '6TH'     => ['SIXTH', '6TH', '6'],
                '7TH'     => ['SEVENTH', '7TH', '7'],
                '8TH'     => ['EIGHTH', '8TH', '8'],
                '9TH'     => ['NINTH', 'NINETH', '9TH', '9'],
                '10TH'    => ['TENTH', '10TH', '10'],
                default   => [$key]
            };
        };

        // ✅ Robust resolver
        $resolveStandard = function ($standardKey) use ($getMatchingStandardNames) {
            $variants = $getMatchingStandardNames($standardKey);

            foreach ($variants as $variant) {
                $v = strtoupper(trim($variant));
                $std = Standard::whereRaw('UPPER(TRIM(standard_name)) = ?', [$v])->first();
                if ($std) return $std;
            }

            foreach ($variants as $variant) {
                $v = str_replace([' ', '-'], '', strtoupper(trim($variant)));
                $std = Standard::whereRaw("TRIM(REPLACE(REPLACE(UPPER(standard_name), ' ', ''), '-', '')) = ?", [$v])->first();
                if ($std) return $std;
            }

            return null;
        };

        // 🎯 MASTER SUBJECT MAPPING (IDs Removed to prevent conflicts)
        $masterSubjects = [
            'ENGLISH' => ['type' => 1], 'MARATHI' => ['type' => 1], 'HINDI' => ['type' => 1],
            'NUMERACY' => ['type' => 1], 'GENERAL KNOWLEDGE' => ['type' => 1], 'SANSKRIT' => ['type' => 1],
            'MATHEMATICS' => ['type' => 1], 'EVS' => ['type' => 1], 'MATHEMATICS I' => ['type' => 1],
            'MATHEMATICS II' => ['type' => 1], 'SCIENCE I' => ['type' => 1], 'SCIENCE II' => ['type' => 1],
            'HISTORY' => ['type' => 1], 'GEOGRAPHY' => ['type' => 1], 'PHYSICS' => ['type' => 1],
            'CHEMISTRY' => ['type' => 1], 'BIOLOGY' => ['type' => 1], 'INFORMATION TECHNOLOGY' => ['type' => 1],
            'ACCOUNTS' => ['type' => 1], 'ECONOMICS' => ['type' => 1], 'ORGANIZATION OF COMMERCE AND MANAGEMENT' => ['type' => 1],
            'ORGANISATION OF COMMERCE & MANAGEMENT' => ['type' => 1], 'SECRETARIAL PRACTICE' => ['type' => 1],
            'POLITICAL SCIENCE' => ['type' => 1], 'PSYCHOLOGY' => ['type' => 1], 'EVS1' => ['type' => 1],
            'EVS2' => ['type' => 1], 'SCIENCE' => ['type' => 1], 'CONVERSATION' => ['type' => 1],
            'ENVIRONMENTAL STUDIES' => ['type' => 1], 'POEM' => ['type' => 1], 'STORY' => ['type' => 1],
            'PT' => ['type' => 2], 'ART' => ['type' => 2], 'WE' => ['type' => 2], 'DRAWING' => ['type' => 2],
            'SANSKRIT BHARTI' => ['type' => 2], 'DANCE' => ['type' => 2], 'ACTING' => ['type' => 2],
            'TABLA' => ['type' => 2], 'LIBRARY' => ['type' => 2], 'ABACUS' => ['type' => 2],
            'COMPUTER' => ['type' => 2], 'ROBOTICS' => ['type' => 2], 'SONG' => ['type' => 2],
            'CRAFT' => ['type' => 2], 'INDOOR GAME' => ['type' => 2], 'OUTDOOR GAME' => ['type' => 2],
            'CLAY' => ['type' => 2], 'LIFESKILL' => ['type' => 2], 'TOYS' => ['type' => 2],
            'DRAMA' => ['type' => 2], 'PHYSICAL EDUCATION' => ['type' => 2], 'MUSIC' => ['type' => 2],
            'GROUND ACTIVITIES' => ['type' => 2], 'T.SENSE' => ['type' => 2], 'ASSEMBLY' => ['type' => 2],
            'ACTIVITY BOOK' => ['type' => 2], 'GENERAL SKILLS' => ['type' => 2],
            'DEFENCE STUDIES' => ['type' => 2], 'WATER SECURITY' => ['type' => 2],
        ];

        // 🚀 SAFE SUBJECT CREATION & MAPPING HELPER
        $ensureSubjectMapped = function ($standard, $subjectName, $isSkill = false) use ($masterSubjects) {
            $cleanName = strtoupper(trim($subjectName));
            $typeId    = $isSkill ? 2 : 1;

            $subject = Subject::whereRaw('UPPER(TRIM(subject_name)) = ?', [$cleanName])
                ->where('subject_type_id', $typeId)
                ->first();

            if (!$subject) {
                $subject = Subject::whereRaw('UPPER(TRIM(subject_name)) = ?', [$cleanName])->first();

                if ($subject && (int) $subject->subject_type_id !== $typeId) {
                    $conflict = Subject::where('subject_code', $subject->subject_code)
                        ->where('section_id', $subject->section_id)
                        ->where('subject_type_id', $typeId)
                        ->where('id', '!=', $subject->id)
                        ->first();

                    if ($conflict) {
                        $subject = $conflict;
                    } else {
                        try {
                            $subject->subject_type_id = $typeId;
                            $subject->save();
                        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) { }
                    }
                }
            }

            if (!$subject) {
                $baseCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $cleanName), 0, 4));
                if (empty($baseCode)) $baseCode = 'SUBJ';

                $code    = $baseCode;
                $counter = 1;
                while (Subject::where('subject_code', $code)->where('section_id', $standard->section_id ?? 1)->exists()) {
                    $code = substr($baseCode, 0, 3) . $counter;
                    $counter++;
                }

                $subject = Subject::create([
                    'subject_name'    => $cleanName,
                    'subject_code'    => $code,
                    'section_id'      => $standard->section_id ?? 1,
                    'subject_type_id' => $typeId,
                    'is_active'       => 1,
                ]);
            }

            $mapping = StandardWiseSubject::where('standard_id', $standard->id)
                ->where('subject_name', $subject->subject_name)
                ->first();

            if ($mapping) {
                if ((int)$mapping->subject_id !== (int)$subject->id) {
                    $mapping->subject_id = $subject->id;
                    $mapping->save();
                }
            } else {
                StandardWiseSubject::create([
                    'standard_id'   => $standard->id,
                    'subject_id'    => $subject->id,
                    'standard_name' => $standard->standard_name,
                    'subject_name'  => $subject->subject_name,
                    'sort_order'    => 0,
                    'is_optional'   => 0,
                    'is_active'     => 1,
                ]);
            }

            return $subject;
        };

        // ─────────────── HELPER ───────────────
        $push = function (
            $stdKey, $et, $names,
            $tM, $tP, $oM, $oP, $pM, $pP,
            $isSkill = false,
            $skipRow = false
        ) use (&$rows, &$missingSubjects, $resolveStandard, $ensureSubjectMapped) {
            
            $standard = $resolveStandard($stdKey);
            if (!$standard) return;

            // ✅ CRITICAL FIX: Use the exact same Helper as the Controller
            $sheetKey = class_exists('\App\Helpers\ExamStructureHelper') 
                ? \App\Helpers\ExamStructureHelper::getStandardSheetKey($standard->standard_name) 
                : $stdKey;

            if (empty($sheetKey)) {
                $sheetKey = $stdKey;
            }

            foreach ((array) $names as $n) {
                $subject = $ensureSubjectMapped($standard, $n, $isSkill);

                if ($skipRow) continue;

                if (!$subject) {
                    $missingSubjects[] = "{$stdKey}::{$n} (exam={$et})";
                    continue;
                }

                $totalMax  = $tM + $oM + $pM;
                $totalPass = $tP + $oP + $pP;
                
                $stdName = strtoupper($standard->standard_name);
                $isHighSchool = preg_match('/\b(10|11|12|X|XI|XII|TENTH|ELEVENTH|TWELFTH|ARTS|SCIENCE|COMMERCE)\b/', $stdName);
                
                $passPct = $isHighSchool ? 35 : ($totalMax > 0 ? round(($totalPass / $totalMax) * 100) : 40);

                $rows[] = [
                    'standard_key'            => $sheetKey, // ✅ Matches Controller
                    'standard_name'           => $standard->standard_name,
                    'exam_type'               => $et,
                    'exam_type_order'         => ExamStructure::EXAM_TYPE_ORDER[$et] ?? 0,
                    'subject_type'            => $isSkill ? 'Skill' : 'Academics',
                    'subject_name'            => $n,
                    'subject_id'              => $subject->id,
                    'subject_code'            => $subject->subject_code ?? '',
                    'display_order'           => 0,
                    'theory_max_marks'        => $tM,
                    'theory_passing_marks'    => $tP,
                    'oral_max_marks'          => $oM,
                    'oral_passing_marks'      => $oP,
                    'practical_max_marks'     => $pM,
                    'practical_passing_marks' => $pP,
                    'total_max_marks'         => $totalMax,
                    'total_passing_marks'     => $totalPass,
                    'passing_percentage'      => $passPct,
                    'is_compulsory'           => true,
                    'is_active'               => true,
                ];
            }
        };

        // =========================================================================
        // EXACT REPLICA OF EXCEL SHEET
        // =========================================================================

        // ── NURSERY ──
        $nurAcad  = ['ENGLISH', 'MATHEMATICS', 'GENERAL KNOWLEDGE', 'CONVERSATION', 'POEM', 'STORY'];
        $nurSkill = ['CRAFT', 'COLOURING', 'CLAY', 'LIFESKILL', 'SONG', 'MARATHI STORY', 'INDOOR GAME', 'OUTDOOR GAME', 'TOY LIB', 'SHLOKA', 'T.SENSE'];
        foreach (['UNIT TEST 1', 'UNIT TEST 2', 'TERM 1'] as $et) $push('NURSERY', $et, $nurAcad, 0, 0, 25, 10, 0, 0);
        $push('NURSERY', 'TERM 2', $nurAcad, 20, 8, 5, 0, 0, 0);
        $push('NURSERY', 'TERM 1', $nurSkill, 0, 0, 0, 0, 10, 4, true);
        $push('NURSERY', 'TERM 2', $nurSkill, 0, 0, 0, 0, 10, 4, true);

        // ── JR KG ──
        $jrSkill = ['CRAFT', 'COLOURING', 'CLAY', 'LIFESKILL', 'SONG', 'MARATHI STORY', 'INDOOR GAME', 'OUTDOOR GAME', 'TOY LIB', 'SHLOKA', 'T.SENSE'];
        foreach (['UNIT TEST 1', 'UNIT TEST 2', 'TERM 1', 'TERM 2'] as $et) {
            $push('JRKG', $et, ['ENGLISH', 'MATHEMATICS'], 20, 8, 5, 2, 0, 0);
            $push('JRKG', $et, ['MARATHI'], 0, 0, 10, 4, 0, 0);
            $push('JRKG', $et, ['GENERAL KNOWLEDGE', 'CONVERSATION', 'POEM', 'STORY'], 0, 0, 25, 10, 0, 0);
        }
        $push('JRKG', 'TERM 1', $jrSkill, 0, 0, 0, 0, 10, 4, true);
        $push('JRKG', 'TERM 2', $jrSkill, 0, 0, 0, 0, 10, 4, true);

        // ── SR KG ──
        $srSkill = ['CRAFT', 'DRAWING', 'CLAY', 'LIFESKILL', 'SONG', 'MARATHI STORY', 'INDOOR GAME', 'OUTDOOR GAME', 'TOY LIB', 'SHLOKA', 'T.SENSE'];
        foreach (['UNIT TEST 1', 'UNIT TEST 2', 'TERM 1', 'TERM 2'] as $et) {
            $push('SRKG', $et, ['ENGLISH', 'MARATHI', 'MATHEMATICS'], 20, 8, 5, 2, 0, 0);
            $push('SRKG', $et, ['GENERAL KNOWLEDGE', 'CONVERSATION', 'POEM', 'STORY'], 0, 0, 25, 10, 0, 0);
        }
        $push('SRKG', 'TERM 1', $srSkill, 0, 0, 0, 0, 10, 4, true);
        $push('SRKG', 'TERM 2', $srSkill, 0, 0, 0, 0, 10, 4, true);

        $activitySkills = ['PT', 'ART', 'WE', 'DRAWING', 'T/S/A/D', 'SANSKRIT BHARTI', 'COMPUTER', 'ROBOTICS', 'ABACUS', 'MUSIC'];

        // ── 1st & 2nd ──
        foreach (['1ST', '2ND'] as $std) {
            $acad = ['MARATHI', 'ENGLISH', 'MATHEMATICS', 'EVS'];
            foreach (['UNIT TEST 1', 'UNIT TEST 2'] as $et) $push($std, $et, $acad, 20, 8, 0, 0, 0, 0);
            foreach (['TERM 1', 'TERM 2'] as $et) {
                $push($std, $et, $acad, 20, 8, 10, 4, 70, 28);
                $push($std, $et, $activitySkills, 0, 0, 0, 0, 0, 0, true, true);
            }
        }

        // ── 3rd & 4th ──
        foreach (['3RD', '4TH'] as $std) {
            $acad = ['MARATHI', 'ENGLISH', 'MATHEMATICS', 'EVS1', 'EVS2'];
            foreach (['UNIT TEST 1', 'UNIT TEST 2'] as $et) $push($std, $et, $acad, 20, 8, 0, 0, 0, 0);
            foreach (['TERM 1', 'TERM 2'] as $et) {
                $push($std, $et, $acad, 30, 12, 10, 4, 60, 24);
                $push($std, $et, $activitySkills, 0, 0, 0, 0, 0, 0, true, true);
            }
        }

        // ── 5th ──
        foreach (['UNIT TEST 1', 'UNIT TEST 2'] as $et) {
            $push('5TH', $et, ['ENGLISH', 'HINDI', 'MARATHI', 'MATHEMATICS'], 40, 16, 0, 0, 0, 0);
            $push('5TH', $et, ['EVS1', 'EVS2'], 20, 8, 0, 0, 0, 0);
        }
        foreach (['TERM 1', 'TERM 2'] as $et) {
            $push('5TH', $et, ['ENGLISH', 'HINDI', 'MARATHI', 'MATHEMATICS'], 40, 16, 10, 4, 50, 20);
            $push('5TH', $et, ['EVS1', 'EVS2'], 20, 8, 5, 2, 25, 10);
            $push('5TH', $et, $activitySkills, 0, 0, 0, 0, 0, 0, true, true);
        }

        // ── 6th ──
        foreach (['UNIT TEST 1', 'UNIT TEST 2'] as $et) {
            $push('6TH', $et, ['ENGLISH', 'HINDI', 'MARATHI', 'MATHEMATICS', 'SCIENCE'], 40, 16, 0, 0, 0, 0);
            $push('6TH', $et, ['HISTORY', 'GEOGRAPHY'], 20, 8, 0, 0, 0, 0);
        }
        $push('6TH', 'TERM 1', ['ENGLISH', 'HINDI', 'MARATHI', 'MATHEMATICS'], 40, 16, 10, 4, 50, 20);
        $push('6TH', 'TERM 1', ['SCIENCE'], 20, 8, 10, 4, 50, 20);
        $push('6TH', 'TERM 1', ['HISTORY', 'GEOGRAPHY'], 20, 8, 5, 2, 25, 10);
        $push('6TH', 'TERM 1', $activitySkills, 0, 0, 0, 0, 0, 0, true, true);
        $push('6TH', 'TERM 2', ['ENGLISH', 'HINDI', 'MARATHI', 'MATHEMATICS'], 40, 16, 10, 4, 50, 20);
        $push('6TH', 'TERM 2', ['SCIENCE'], 20, 8, 10, 4, 50, 20);
        $push('6TH', 'TERM 2', ['HISTORY', 'GEOGRAPHY'], 20, 8, 10, 4, 25, 10);
        $push('6TH', 'TERM 2', $activitySkills, 0, 0, 0, 0, 0, 0, true, true);

        // ── 7th ──
        foreach (['UNIT TEST 1', 'UNIT TEST 2'] as $et) {
            $push('7TH', $et, ['ENGLISH', 'HINDI', 'MARATHI', 'MATHEMATICS', 'SCIENCE'], 40, 16, 0, 0, 0, 0);
            $push('7TH', $et, ['HISTORY', 'GEOGRAPHY'], 20, 8, 0, 0, 0, 0);
        }
        foreach (['TERM 1', 'TERM 2'] as $et) {
            $push('7TH', $et, ['ENGLISH', 'HINDI', 'MARATHI', 'MATHEMATICS', 'SCIENCE'], 50, 20, 10, 4, 40, 16);
            $push('7TH', $et, ['HISTORY', 'GEOGRAPHY'], 25, 10, 5, 2, 20, 8);
            $push('7TH', $et, $activitySkills, 0, 0, 0, 0, 0, 0, true, true);
        }

        // ── 8th ──
        foreach (['UNIT TEST 1', 'UNIT TEST 2'] as $et) {
            $push('8TH', $et, ['ENGLISH', 'SANSKRIT', 'MARATHI', 'MATHEMATICS', 'SCIENCE'], 40, 16, 0, 0, 0, 0);
            $push('8TH', $et, ['HISTORY', 'GEOGRAPHY'], 20, 8, 0, 0, 0, 0);
        }
        $push('8TH', 'TERM 1', ['ENGLISH', 'SANSKRIT', 'MARATHI', 'MATHEMATICS', 'SCIENCE'], 50, 20, 10, 4, 40, 16);
        $push('8TH', 'TERM 1', ['HISTORY', 'GEOGRAPHY'], 25, 10, 5, 2, 20, 8);
        $push('8TH', 'TERM 1', $activitySkills, 0, 0, 0, 0, 0, 0, true, true);
        $push('8TH', 'TERM 2', ['ENGLISH', 'SANSKRIT', 'MARATHI', 'MATHEMATICS', 'SCIENCE'], 50, 20, 10, 4, 0, 0);
        $push('8TH', 'TERM 2', ['HISTORY', 'GEOGRAPHY'], 25, 10, 5, 2, 20, 8);
        $push('8TH', 'TERM 2', $activitySkills, 0, 0, 0, 0, 0, 0, true, true);

        // ── 9th ──
        foreach (['UNIT TEST 1', 'UNIT TEST 2'] as $et) {
            $push('9TH', $et, ['ENGLISH', 'SANSKRIT', 'MARATHI'], 40, 14, 0, 0, 0, 0);
            $push('9TH', $et, ['MATHEMATICS I', 'MATHEMATICS II', 'SCIENCE I', 'SCIENCE II'], 20, 7, 0, 0, 0, 0);
            $push('9TH', $et, ['HISTORY', 'GEOGRAPHY'], 20, 7, 0, 0, 0, 0);
        }
        foreach (['TERM 1', 'TERM 2'] as $et) {
            $push('9TH', $et, ['ENGLISH', 'SANSKRIT', 'MARATHI'], 80, 28, 20, 7, 0, 0);
            $push('9TH', $et, ['MATHEMATICS I', 'MATHEMATICS II', 'SCIENCE I', 'SCIENCE II'], 40, 14, 0, 0, 10, 4);
            $push('9TH', $et, ['HISTORY', 'GEOGRAPHY'], 40, 14, 10, 4, 0, 0);
            $push('9TH', $et, $activitySkills, 0, 0, 0, 0, 0, 0, true, true);
        }

        // ── 10th ──
        foreach (['UNIT TEST 1', 'UNIT TEST 2'] as $et) {
            $push('10TH', $et, ['ENGLISH', 'SANSKRIT', 'MARATHI'], 40, 14, 0, 0, 0, 0);
            $push('10TH', $et, ['MATHEMATICS I', 'MATHEMATICS II', 'SCIENCE I', 'SCIENCE II', 'HISTORY', 'GEOGRAPHY'], 20, 7, 0, 0, 0, 0);
        }
        foreach (['TERM 1', 'TERM 2'] as $et) {
            $push('10TH', $et, ['ENGLISH', 'SANSKRIT', 'MARATHI'], 80, 28, 20, 7, 0, 0);
            $push('10TH', $et, ['MATHEMATICS I', 'MATHEMATICS II', 'SCIENCE I', 'SCIENCE II', 'HISTORY', 'GEOGRAPHY'], 40, 14, 10, 4, 0, 0);
            $push('10TH', $et, $activitySkills, 0, 0, 0, 0, 0, 0, true, true);
        }

        // ── 11SCI & 12SCI ──
        $sci11 = ['MARATHI', 'ENGLISH', 'MATHEMATICS', 'GEOGRAPHY', 'PHYSICS', 'CHEMISTRY', 'BIOLOGY', 'INFORMATION TECHNOLOGY'];
        $sci12 = ['ENGLISH', 'MATHEMATICS', 'GEOGRAPHY', 'PHYSICS', 'CHEMISTRY', 'BIOLOGY', 'INFORMATION TECHNOLOGY'];

        foreach (['11SCI' => $sci11, '12SCI' => $sci12] as $std => $subjects) {
            foreach (['UNIT TEST 1', 'UNIT TEST 2'] as $et) $push($std, $et, $subjects, 25, 8, 0, 0, 0, 0);
            $push($std, 'TERM 1', $subjects, 50, 17, 0, 0, 0, 0);
        }
        $push('11SCI', 'TERM 2', ['MARATHI', 'ENGLISH', 'MATHEMATICS', 'GEOGRAPHY', 'INFORMATION TECHNOLOGY'], 80, 28, 20, 7, 0, 0);
        $push('11SCI', 'TERM 2', ['PHYSICS', 'CHEMISTRY', 'BIOLOGY'], 70, 25, 0, 0, 30, 11);
        $push('12SCI', 'TERM 2', ['ENGLISH', 'MATHEMATICS', 'GEOGRAPHY', 'INFORMATION TECHNOLOGY'], 80, 28, 20, 7, 0, 0);
        $push('12SCI', 'TERM 2', ['PHYSICS', 'CHEMISTRY', 'BIOLOGY'], 70, 25, 0, 0, 30, 11);

        // ── 11COM & 12COM ──
        foreach (['11COM', '12COM'] as $std) {
            $com = ['MARATHI', 'ENGLISH', 'ECONOMICS', 'ACCOUNTS', 'ORGANIZATION OF COMMERCE AND MANAGEMENT', 'SECRETARIAL PRACTICE', 'INFORMATION TECHNOLOGY'];
            foreach (['UNIT TEST 1', 'UNIT TEST 2'] as $et) $push($std, $et, $com, 25, 8, 0, 0, 0, 0);
            $push($std, 'TERM 1', $com, 50, 17, 0, 0, 0, 0);
            $push($std, 'TERM 2', $com, 80, 28, 20, 7, 0, 0);
        }

        // ── 11ARTS & 12ARTS ──
        foreach (['11ARTS', '12ARTS'] as $std) {
            $arts = ['MARATHI', 'ENGLISH', 'ECONOMICS', 'GEOGRAPHY', 'PSYCHOLOGY', 'POLITICAL SCIENCE', 'INFORMATION TECHNOLOGY'];
            foreach (['UNIT TEST 1', 'UNIT TEST 2'] as $et) $push($std, $et, $arts, 25, 8, 0, 0, 0, 0);
            $push($std, 'TERM 1', $arts, 50, 17, 0, 0, 0, 0);
            $push($std, 'TERM 2', $arts, 80, 28, 20, 7, 0, 0);
        }

        // Insert rows in chunks
        foreach (array_chunk($rows, 500) as $chunk) {
            ExamStructure::insert($chunk);
        }

        $this->command->info('Successfully inserted ' . count($rows) . ' exact replica exam-structure rows from Excel.');

        if (!empty($missingSubjects)) {
            $this->command->warn('Unresolved (standard::subject) pairs — check `standards` table spellings:');
            foreach (array_unique($missingSubjects) as $miss) {
                $this->command->warn('  • ' . $miss);
            }
        }
    }
}