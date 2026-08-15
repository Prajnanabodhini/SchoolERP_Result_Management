<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('getAcademicYearIdFromSession'))
{
    function getAcademicYearIdFromSession()
    {
        $year = session('yearid');

        if (!$year)
        {
            return null;
        }

        return DB::table('academic_years')
            ->where(
                'year_name',
                $year . '-' . ($year + 1)
            )
            ->value('id');
    }
}

if (!function_exists('getSessionYearName'))
{
    function getSessionYearName()
    {
        $year = session('yearid');

        return $year
            ? $year . '-' . ($year + 1)
            : '';
    }
}