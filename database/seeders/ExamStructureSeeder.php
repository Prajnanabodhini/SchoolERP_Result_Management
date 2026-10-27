<?php

namespace Database\Seeders;

use App\Models\ExamStructure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamStructureSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('exam_structures')->truncate();

        $rows = [];

        // Translate short keys to actual DB standard names
        $stdMap = [
            'NURSERY' => 'NURSERY', 'JRKG' => 'JR KG', 'SRKG' => 'SR KG',
            '1ST' => 'FIRST', '2ND' => 'SECOND', '3RD' => 'THIRD', '4TH' => 'FOURTH',
            '5TH' => 'FIFTH', '6TH' => 'SIXTH', '7TH' => 'SEVENTH', '8TH' => 'EIGHTH',
            '9TH' => 'NINTH', '10TH' => 'TENTH',
            '11SCI' => 'ELEVENTH SCIENCE', '12SCI' => 'TWELFTH SCIENCE',
            '11COM' => 'ELEVENTH COMMERCE', '12COM' => 'TWELFTH COMMERCE',
        ];

        $push = function ($std, $et, $names, $tM, $tP, $oM, $oP, $pM, $pP, $totMax = null, $totPass = null) use (&$rows, $stdMap) {
            foreach ((array) $names as $n) {
                $rows[] = [
                    'standard_key'            => $std,
                    'standard_name'           => $stdMap[$std] ?? $std,
                    'exam_type'               => $et,
                    'exam_type_order'         => ExamStructure::EXAM_TYPE_ORDER[$et] ?? 0,
                    'subject_type'            => 'Academics',
                    'subject_name'            => $n,
                    'theory_max_marks'        => $tM,
                    'theory_passing_marks'    => $tP,
                    'oral_max_marks'          => $oM,
                    'oral_passing_marks'      => $oP,
                    'practical_max_marks'     => $pM,
                    'practical_passing_marks' => $pP,
                    'total_max_marks'         => $totMax !== null ? $totMax : ($tM + $oM + $pM),
                    'total_passing_marks'     => $totPass !== null ? $totPass : ($tP + $oP + $pP),
                    'passing_percentage'      => 40,
                    'is_compulsory'           => true,
                    'is_active'               => true,
                ];
            }
        };

        /* ================= NURSERY ================= */
        $nurAcad = ['ENGLISH','MATHEMATICS','GENERAL KNOWLEDGE','CONVERSATION','POEM','STORY'];
        foreach (['UNIT TEST 1','UNIT TEST 2','TERM 1'] as $et) {
            $push('NURSERY',$et,$nurAcad, 0,0,25,10,0,0);
        }
        $push('NURSERY','TERM 2',$nurAcad, 20,8,5,0,0,0, 25, 10);

        // Skill: only Term 1 and Term 2 (MARATHI STORY removed)
        $nurSkill = ['CRAFT','COLOURING','CLAY','LIFESKILL','SONG',
                     'INDOOR GAME','OUTDOOR GAME','TOY LIB','SHLOKA','T.SENSE'];
        $push('NURSERY','TERM 1',$nurSkill, 0,0,0,0,10,4);
        $push('NURSERY','TERM 2',$nurSkill, 0,0,0,0,10,4);

        /* ================= JRKG ================= */
        foreach (['UNIT TEST 1','UNIT TEST 2','TERM 1','TERM 2'] as $et) {
            $push('JRKG',$et,['MARATHI'],    0,0,10,4,0,0);
            $push('JRKG',$et,['ENGLISH'],   20,8,5,2,0,0);
            $push('JRKG',$et,['MATHEMATICS'],20,8,5,2,0,0);
            $push('JRKG',$et,['GENERAL KNOWLEDGE','CONVERSATION','POEM','STORY'], 0,0,25,10,0,0);
        }
        // Skill: (MARATHI STORY removed)
        $jrSkill = ['CRAFT','COLOURING','CLAY','LIFESKILL','SONG',
                    'INDOOR GAME','OUTDOOR GAME','TOY LIB','SHLOKA','T.SENSE'];
        $push('JRKG','TERM 1',$jrSkill, 0,0,0,0,10,4);
        $push('JRKG','TERM 2',$jrSkill, 0,0,0,0,10,4);

        /* ================= SRKG ================= */
        foreach (['UNIT TEST 1','UNIT TEST 2','TERM 1','TERM 2'] as $et) {
            $push('SRKG',$et,['MARATHI','ENGLISH','MATHEMATICS'], 20,8,5,2,0,0);
            $push('SRKG',$et,['GENERAL KNOWLEDGE','CONVERSATION','POEM','STORY'], 0,0,25,10,0,0);
        }
        // Skill: (MARATHI STORY removed)
        $srSkill = ['CRAFT','DRAWING','CLAY','LIFESKILL','SONG',
                    'INDOOR GAME','OUTDOOR GAME','TOY LIB','SHLOKA','T.SENSE'];
        $push('SRKG','TERM 1',$srSkill, 0,0,0,0,10,4);
        $push('SRKG','TERM 2',$srSkill, 0,0,0,0,10,4);

        /* ================= 1ST / 2ND ================= */
        foreach (['1ST','2ND'] as $std) {
            $acad = ['MARATHI','ENGLISH','MATHEMATICS','EVS'];
            foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) $push($std,$et,$acad, 20,8,0,0,0,0);
            foreach (['TERM 1','TERM 2'] as $et)             $push($std,$et,$acad, 20,8,10,4,70,28);
            foreach (['TERM 1','TERM 2'] as $et)             $push($std,$et,['PT','Art','WE'], 0,0,0,0,100,40);
        }

        /* ================= 3RD / 4TH ================= */
        foreach (['3RD','4TH'] as $std) {
            $acad = ['MARATHI','ENGLISH','MATHEMATICS','EVS1','EVS2'];
            foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) $push($std,$et,$acad, 20,8,0,0,0,0);
            foreach (['TERM 1','TERM 2'] as $et)             $push($std,$et,$acad, 30,12,10,4,60,24);
            foreach (['TERM 1','TERM 2'] as $et)             $push($std,$et,['PT','Art','WE'], 0,0,0,0,100,40);
        }

        /* ================= 5TH ================= */
        $a5 = ['ENGLISH','HINDI','MARATHI','MATHEMATICS'];
        $b5 = ['EVS1','EVS2'];
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push('5TH',$et,$a5, 40,16,0,0,0,0);
            $push('5TH',$et,$b5, 20,8,0,0,0,0);
        }
        foreach (['TERM 1','TERM 2'] as $et) {
            $push('5TH',$et,$a5, 40,16,10,4,50,20);
            $push('5TH',$et,$b5, 20,8,5,2,25,10);
            $push('5TH',$et,['PT','Art','WE'], 0,0,0,0,100,40);
        }

        /* ================= 6TH ================= */
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push('6TH',$et,$a5, 40,16,0,0,0,0);
            $push('6TH',$et,['SCIENCE'], 40,16,0,0,0,0);
            $push('6TH',$et,['HISTORY','GEOGRAPHY'], 20,8,0,0,0,0);
        }
        foreach (['TERM 1','TERM 2'] as $et) {
            $push('6TH',$et,$a5, 40,16,10,4,50,20);
            $push('6TH',$et,['SCIENCE'], 20,8,10,4,50,20);
            $push('6TH',$et,['HISTORY','GEOGRAPHY'], 20,8,5,2,25,10);
            $push('6TH',$et,['PT','Art','WE'], 0,0,0,0,100,40);
        }

        /* ================= 7TH ================= */
        $a7 = ['ENGLISH','HINDI','MARATHI','MATHEMATICS','SCIENCE'];
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push('7TH',$et,$a7, 40,16,0,0,0,0);
            $push('7TH',$et,['HISTORY','GEOGRAPHY'], 20,8,0,0,0,0);
        }
        foreach (['TERM 1','TERM 2'] as $et) {
            $push('7TH',$et,$a7, 50,20,10,4,40,16);
            $push('7TH',$et,['HISTORY','GEOGRAPHY'], 25,10,5,2,20,8);
            $push('7TH',$et,['PT','Art','WE'], 0,0,0,0,100,40);
        }

        /* ================= 8TH ================= */
        $a8 = ['ENGLISH','SANSKRIT','MARATHI','MATHEMATICS','SCIENCE'];
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push('8TH',$et,$a8, 40,16,0,0,0,0);
            $push('8TH',$et,['HISTORY','GEOGRAPHY'], 20,8,0,0,0,0);
        }
        $push('8TH','TERM 1',$a8, 50,20,10,4,40,16);
        $push('8TH','TERM 1',['HISTORY','GEOGRAPHY'], 25,10,5,2,20,8);
        $push('8TH','TERM 1',['PT','Art','WE'], 0,0,0,0,100,40);
        $push('8TH','TERM 2',$a8, 50,20,10,4,0,0, 60, 24);
        $push('8TH','TERM 2',['HISTORY','GEOGRAPHY'], 25,10,5,2,20,8);

        /* ================= 9TH ================= */
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push('9TH',$et,['ENGLISH','SANSKRIT','MARATHI'], 40,14,0,0,0,0);
            $push('9TH',$et,['MATHEMATICS I','MATHEMATICS II','SCIENCE I','SCIENCE II'], 20,7,0,0,0,0);
            $push('9TH',$et,['HISTORY','GEOGRAPHY'], 20,7,0,0,0,0);
        }
        foreach (['TERM 1','TERM 2'] as $et) {
            $push('9TH',$et,['ENGLISH','SANSKRIT','MARATHI'], 80,28,20,7,0,0);
            $push('9TH',$et,['MATHEMATICS I','MATHEMATICS II','SCIENCE I','SCIENCE II'], 40,14,0,0,10,4, 50, 18);
            $push('9TH',$et,['HISTORY','GEOGRAPHY'], 40,14,10,4,0,0, 50, 18);
        }

        /* ================= 10TH ================= */
        foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
            $push('10TH',$et,['ENGLISH','SANSKRIT','MARATHI'], 40,14,0,0,0,0);
            $push('10TH',$et,['MATHEMATICS I','MATHEMATICS II','SCIENCE I','SCIENCE II','HISTORY','GEOGRAPHY'], 20,7,0,0,0,0);
        }
        foreach (['TERM 1','TERM 2'] as $et) {
            $push('10TH',$et,['ENGLISH','SANSKRIT','MARATHI'], 80,28,20,7,0,0);
            $push('10TH',$et,['MATHEMATICS I','MATHEMATICS II','SCIENCE I','SCIENCE II','HISTORY','GEOGRAPHY'], 40,14,10,4,0,0, 50, 18);
        }

        /* ================= 11SCI / 12SCI ================= */
        foreach (['11SCI','12SCI'] as $std) {
            foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
                $push($std,$et,['MARATHI','ENGLISH','MATHEMATICS','GEOGRAPHY'], 25,8,0,0,0,0);
                $push($std,$et,['PHYSICS','CHEMISTRY','BIOLOGY'], 25,8,0,0,0,0);
                $push($std,$et,['INFORMATION TECHNOLOGY'], 25,8,0,0,0,0);
            }
            $push($std,'TERM 1',['MARATHI','ENGLISH','MATHEMATICS','GEOGRAPHY'], 50,16,25,8,0,0, 100, 35);
            $push($std,'TERM 1',['PHYSICS','CHEMISTRY','BIOLOGY'],                  50,16,25,8,0,0, 100, 35);
            $push($std,'TERM 1',['INFORMATION TECHNOLOGY'],                         50,16,0,0,50,17, 100, 35);
            $push($std,'TERM 2',['MARATHI','ENGLISH','MATHEMATICS','GEOGRAPHY'], 80,28,20,7,0,0, 100, 35);
            $push($std,'TERM 2',['PHYSICS','CHEMISTRY','BIOLOGY'],                  70,25,0,0,30,11, 100, 35);
            $push($std,'TERM 2',['INFORMATION TECHNOLOGY'],                         50,18,0,0,50,17, 100, 35);
        }

        /* ================= 11COM / 12COM ================= */
        $com = ['MARATHI','ENGLISH','ECONOMICS','ACCOUNTS',
                'ORGANIZATION OF COMMERCE AND MANAGEMENT','SECRETARIAL PRACTICE'];
        foreach (['11COM','12COM'] as $std) {
            foreach (['UNIT TEST 1','UNIT TEST 2'] as $et) {
                $push($std,$et,$com, 25,8,0,0,0,0);
            }
            $push($std,'TERM 1',$com, 50,16,25,8,0,0, 100, 35);
            $push($std,'TERM 2',$com, 80,28,20,7,0,0, 100, 35);
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            ExamStructure::insert($chunk);
        }

        $this->command->info('Inserted ' . count($rows) . ' exam-structure rows.');
    }
}