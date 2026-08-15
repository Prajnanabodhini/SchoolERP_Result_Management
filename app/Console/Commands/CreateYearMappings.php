<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateYearMappings extends Command
{

    protected $signature = 'erp:create-mappings';

    protected $description = 'Create standard and division year mappings';


    public function handle()
    {

        /*
        |--------------------------------------------------------------------------
        | STANDARD MAPPING
        |--------------------------------------------------------------------------
        */


        $standards = DB::connection('sqlsrv_olderp')
            ->table('StandardMst')
            ->select(
                'yearid',
                'standardid',
                'standard'
            )
            ->get();



        foreach($standards as $row)
        {


            $laravelStandard = DB::table('standards')
                ->where('standard_name','LIKE',
                    '%'.$row->standard.'%'
                )
                ->first();


            if($laravelStandard)
            {

                DB::table('standard_year_mappings')
                ->updateOrInsert(

                    [
                        'academic_year_id'=>$row->yearid,
                        'standard_id'=>$laravelStandard->id
                    ],

                    [

                    'old_standard_id'=>$row->standardid,

                    'created_at'=>now(),

                    'updated_at'=>now()

                    ]

                );

            }

        }



        /*
        |--------------------------------------------------------------------------
        | DIVISION MAPPING
        |--------------------------------------------------------------------------
        */


        $divisions = DB::connection('sqlsrv_olderp')
            ->table('DivisionMst')
            ->select(
                'yearid',
                'standardid',
                'divisionid',
                'division'
            )
            ->get();



        foreach($divisions as $row)
        {


            $standardMap =
                DB::table('standard_year_mappings')
                ->where('academic_year_id',$row->yearid)
                ->where(
                    'old_standard_id',
                    $row->standardid
                )
                ->first();



            if(!$standardMap)
                continue;



            $division =
                DB::table('divisions')
                ->where(
                    'division_name',
                    $row->division
                )
                ->first();



            if($division)
            {


                DB::table('division_year_mappings')
                ->updateOrInsert(

                    [

                    'academic_year_id'=>$row->yearid,

                    'division_id'=>$division->id,

                    'old_standard_id'=>$row->standardid

                    ],

                    [

                    'old_division_id'=>$row->divisionid,

                    'created_at'=>now(),

                    'updated_at'=>now()

                    ]

                );


            }

        }


        $this->info('Mapping completed');

    }

}