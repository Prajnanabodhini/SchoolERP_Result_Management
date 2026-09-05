<?php

namespace App\Helpers;

class ResultHelper
{
    /*
    |--------------------------------------------------------------------------
    | PASSING PERCENTAGE BY STANDARD ID
    |--------------------------------------------------------------------------
    |
    | 35%:
    |   9  = NINTH
    |   10 = TENTH
    |   13 = JR KG
    |   14 = SR KG
    |   15 = NURSERY
    |   19 = ELEVENTH SCIENCE
    |   20 = TWELFTH SCIENCE
    |   21 = ELEVENTH COMMERCE
    |   22 = TWELFTH COMMERCE
    |   23 = ELEVENTH ARTS
    |   24 = TWELFTH ARTS
    |
    | 40%:
    |   1 to 8
    |
    */

    private const PASSING_35_STANDARD_IDS = [
        9,   // NINTH
        10,  // TENTH

        13,  // JR KG
        14,  // SR KG
        15,  // NURSERY

        19,  // ELEVENTH SCIENCE
        20,  // TWELFTH SCIENCE
        21,  // ELEVENTH COMMERCE
        22,  // TWELFTH COMMERCE
        23,  // ELEVENTH ARTS
        24,  // TWELFTH ARTS
    ];

    private const PASSING_40_STANDARD_IDS = [
        1, // FIRST
        2, // SECOND
        3, // THIRD
        4, // FOURTH
        5, // FIFTH
        6, // SIXTH
        7, // SEVENTH
        8, // EIGHTH
    ];


    /**
     * Get passing percentage based ONLY on standard ID.
     *
     * 35%:
     * Nursery, Jr KG, Sr KG,
     * 9th, 10th,
     * 11th/12th Science, Commerce, Arts
     *
     * 40%:
     * 1st to 8th
     *
     * @param int|null $standardId
     * @return int
     */
    public static function getPassingPercentage($standardId): int
    {
        if (
            $standardId === null ||
            $standardId === ''
        ) {
            // Safe default
            return 40;
        }

        $standardId = (int) $standardId;

        if (
            in_array(
                $standardId,
                self::PASSING_35_STANDARD_IDS,
                true
            )
        ) {
            return 35;
        }

        if (
            in_array(
                $standardId,
                self::PASSING_40_STANDARD_IDS,
                true
            )
        ) {
            return 40;
        }

        /*
        |--------------------------------------------------------------------------
        | Unknown Standard ID
        |--------------------------------------------------------------------------
        |
        | If a new standard is added later and its ID is not configured above,
        | use 40% as the safe default.
        |
        */

        return 40;
    }


    /**
     * Get passing marks based on:
     *
     *   Standard ID
     *   Maximum Marks
     *
     * Passing marks are rounded UP using ceil().
     *
     * Examples:
     *
     * 25 × 35% = 8.75 → 9
     * 25 × 40% = 10   → 10
     * 50 × 35% = 17.5 → 18
     * 50 × 40% = 20   → 20
     * 100 × 35% = 35  → 35
     * 100 × 40% = 40  → 40
     *
     * @param int|null $standardId
     * @param int|float|null $maxMarks
     * @return int
     */
    public static function getPassingMarks(
        $standardId,
        $maxMarks
    ): int {
        $maxMarks = (float) $maxMarks;

        if ($maxMarks <= 0) {
            return 0;
        }

        $passingPercentage = self::getPassingPercentage(
            $standardId
        );

        $passingMarks = (
            $maxMarks * $passingPercentage
        ) / 100;

        return (int) ceil($passingMarks);
    }


    /**
     * Check whether obtained marks are passing.
     *
     * @param int|null $standardId
     * @param int|float|string|null $marks
     * @param int|float|null $maxMarks
     * @return bool
     */
    public static function isPassing(
        $standardId,
        $marks,
        $maxMarks
    ): bool {
        /*
        |--------------------------------------------------------------------------
        | Empty marks
        |--------------------------------------------------------------------------
        |
        | Empty marks should not be treated as PASS.
        |
        */

        if (
            $marks === null ||
            $marks === ''
        ) {
            return false;
        }

        $passingMarks = self::getPassingMarks(
            $standardId,
            $maxMarks
        );

        return (float) $marks >= (float) $passingMarks;
    }


    /**
     * Get all standard IDs which use 35%.
     *
     * Useful if another controller needs the list.
     *
     * @return array
     */
    public static function get35PercentStandardIds(): array
    {
        return self::PASSING_35_STANDARD_IDS;
    }


    /**
     * Get all standard IDs which use 40%.
     *
     * Useful if another controller needs the list.
     *
     * @return array
     */
    public static function get40PercentStandardIds(): array
    {
        return self::PASSING_40_STANDARD_IDS;
    }
}