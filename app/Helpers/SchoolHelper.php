<?php

if (!function_exists('getOldStandardId'))
{
    function getOldStandardId($standardName)
    {
        $map = [

            'NURSERY' => 3130,
            'JR KG' => 3131,
            'SR KG' => 3132,

            'FIRST' => 3133,
            'SECOND' => 3134,
            'THIRD' => 3135,
            'FOURTH' => 3136,
            'FIFTH' => 3137,
            'SIXTH' => 3138,
            'SEVENTH' => 3139,
            'EIGHTH' => 3141,
            'NINTH' => 3142,
            'TENTH' => 3143,

            'ELEVENTH SCIENCE' => 3145,
            'TWELFTH SCIENCE'  => 3145,

            'ELEVENTH COMMERCE' => 3144,
            'TWELFTH COMMERCE'  => 3144,

            'ELEVENTH ARTS' => 3144,
            'TWELFTH ARTS'  => 3144,
        ];

        return $map[strtoupper(trim($standardName))] ?? null;
    }
}