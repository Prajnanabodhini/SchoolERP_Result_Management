<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 12th Science Standard IDs
    |--------------------------------------------------------------------------
    | Standard IDs that represent the 12th Science stream.
    | From the SQL log, your Twelfth Science is standard_id = 20.
    */
    'science_12_standard_ids' => [20],

    /*
    |--------------------------------------------------------------------------
    | 12th Science Structure
    |--------------------------------------------------------------------------
    | compulsory_count : number of compulsory subjects (English, Physics, Chemistry)
    | optional_slots   : how many optionals a student actually takes
    |                    (there may be 4 optional columns shown, but each student
    |                     takes only 3, so total subjects = 3 + 3 = 6)
    */
    'science_12_compulsory_count' => 3,
    'science_12_optional_slots'   => 3,
];