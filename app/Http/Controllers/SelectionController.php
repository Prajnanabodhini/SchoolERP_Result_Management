<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubStudentMst;

class SelectionController extends Controller
{
    private $sectionNames = [

        // 2017-2018
        1 => 'PRIMARY SECTION',
        2 => 'PRE - PRIMARY SECTION',

        // 2018-2019
        4 => 'PRIMARY SECTION',
        5 => 'PRE - PRIMARY SECTION',
        6 => 'SECONDARY SECTION',

        // 2019-2020
        7 => 'PRE - PRIMARY SECTION',
        8 => 'PRIMARY SECTION',
        9 => 'SECONDARY SECTION',

        // 2020-2021
        10 => 'PRE - PRIMARY SECTION',
        11 => 'PRIMARY SECTION',
        12 => 'SECONDARY SECTION',

        // 2021-2022
        13 => 'PRE - PRIMARY SECTION',
        14 => 'PRIMARY SECTION',
        15 => 'SECONDARY SECTION',

        // 2022-2023
        1013 => 'PRE - PRIMARY SECTION',
        1014 => 'PRIMARY SECTION',
        1015 => 'SECONDARY SECTION',
        2013 => 'PRE - PRIMARY SECTION',

        // 2023-2024
        1016 => 'PRE - PRIMARY SECTION',
        1017 => 'PRIMARY SECTION',
        1018 => 'SECONDARY SECTION',

        // 2024-2025
        1019 => 'PRE - PRIMARY SECTION',
        1020 => 'PRIMARY SECTION',
        1021 => 'SECONDARY SECTION',

        // 2025-2026
        1022 => 'PRE - PRIMARY SECTION',
        1023 => 'PRIMARY SECTION',
        1024 => 'SECONDARY SECTION',
        1025 => 'HIGHER SECONDARY SECTION',

        // 2026-2027
        1026 => 'PRE - PRIMARY SECTION',
        1027 => 'PRIMARY SECTION',
        1028 => 'SECONDARY SECTION',
        1029 => 'HIGHER SECONDARY SECTION',
    ];

    public function index()
    {
        $sections = $this->sectionNames;

        $records = SubStudentMst::select('yearid', 'sectionid')
            ->distinct()
            ->orderByDesc('yearid')
            ->orderBy('sectionid')
            ->get();

        return view(
            'selection.index',
            compact('records', 'sections')
        );
    }

    public function select($yearid, $sectionid)
    {
        session([
            'yearid'       => $yearid,
            'sectionid'    => $sectionid,
            'year_name'    => $yearid . '-' . ($yearid + 1),
            'section_name' => $this->sectionNames[$sectionid] ?? 'Unknown Section',
        ]);

        return redirect('/dashboard');
    }
}

