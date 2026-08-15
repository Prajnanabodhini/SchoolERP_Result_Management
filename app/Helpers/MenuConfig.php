<?php

return [

    'Dashboard' => [
        [
            'name'  => 'Dashboard',
            'route' => 'dashboard'
        ]
    ],

    'Masters' => [

        [
            'name'  => 'Academic Year Master',
            'route' => 'academic-years.index'
        ],

        [
            'name'  => 'Section Master',
            'route' => 'sections.index'
        ],

        [
            'name'  => 'Standard Master',
            'route' => 'standards.index'
        ],

        [
            'name'  => 'Division Master',
            'route' => 'divisions.index'
        ],

        [
            'name'  => 'Subject Type Master',
            'route' => 'subject-types.index'
        ],

        [
            'name'  => 'Subject Master',
            'route' => 'subjects.index'
        ],

        [
            'name'  => 'Standard Subject Allocation',
            'route' => 'standard-subject-allocation.index'
        ],

        [
            'name'  => 'Student Master',
            'route' => 'students.index'
        ]
    ],

    'Students' => [

        [
            'name'  => 'Student Promotion',
            'route' => '#'
        ],

        [
            'name'  => 'Student Attendance',
            'route' => '#'
        ]
    ],

    'Examination' => [

    [
        'name'  => 'Exam Progress Dashboard',
        'route' => 'exam-progress.index'
    ],

    [
        'name'  => 'Exam Master',
        'route' => 'exam-masters.index'
    ],

    // [
    //     'name'  => 'Exam Pattern',
    //     'route' => 'exam-patterns.index'
    // ],

    // [
    //     'name'  => 'Exam Pattern Subject Allocation',
    //     'route' => 'exam-pattern-details.index'
    // ],

// [
//     'name'  => 'Exam Subject Configuration',
//     'route' => 'exam-subjects.index'
// ],
//     [
//         'name'  => 'Teacher Class Allocation',
//         'route' => 'teacher-class-allocation.index'
//     ],

//     [
//         'name'  => 'Teacher Subject Allocation',
//         'route' => 'teacher-subject-allocation.index'
//     ],

[
    'name'  => 'Teacher Bulk Allocation',
    'route' => 'teacher-bulk-allocation.index'
],

// [
//     'name'  => 'Teacher Bulk Allocation',
//     'route' => 'teacher-bulk-allocation.create'
// ],
    [
        'name'  => 'Student Skill Subject Allocation',
        'route' => 'student-skill-subject-allocation.index'
    ],

    [
        'name'  => 'Marks Entry',
        'route' => 'marks-entry.index'
    ]
],

    'Reports' => [

        [
            'name'  => 'Edit Examination Marks',
            'route' => 'result-generation.admin-marks.index'
        ],

        [
            'name'  => 'Result Generation',
            'route' => 'administrator.result-generation.index'
        ],

        [
            'name'  => 'Result Sheet',
            'route' => 'result-sheet.index'
        ],

        [
        'name'  => 'Result Analytics',
        'route' => 'analytics.index'
        ],

        [
            'name'  => 'Report Card',
            'route' => 'report-card.index'
        ],

        [
            'name'  => 'Student Profile',
            'route' => 'student-profile.index'
        ]
    ],

    'Administration' => [

        [
            'name'  => 'User Master',
            'route' => 'users.index'
        ],

        [
            'name'  => 'Role Master',
            'route' => 'roles.index'
        ],

        [
            'name'  => 'Role Permission Master',
            'route' => 'role-permissions.index'
        ]
    ]
];