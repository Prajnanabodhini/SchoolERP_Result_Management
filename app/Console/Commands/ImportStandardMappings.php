<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Standard;

class ImportStandardMappings extends Command
{
    protected $signature = 'erp:import-standard-mappings';

    protected $description = 'Import ERP standard mappings';

    public function handle()
    {

        $standards = Standard::all();


        $erpStandards = DB::connection('sqlsrv_olderp')
            ->table('StandardMst')
            ->whereIn('yearid',[2025,2026])
            ->get();


        foreach($erpStandards as $erp)
        {

            foreach($standards as $standard)
            {

                $match=false;


                if(
                    strtoupper($erp->name)
                    ==
                    strtoupper($standard->standard_name)
                ){
                    $match=true;
                }


                if(
                    $standard->standard_name=='JR KG'
                    &&
                    strtoupper($erp->standard)=='JR'
                ){
                    $match=true;
                }


                if(
                    $standard->standard_name=='SR KG'
                    &&
                    strtoupper($erp->standard)=='SR'
                ){
                    $match=true;
                }


                if(
                    $standard->standard_name=='NURSERY'
                    &&
                    strtoupper($erp->standard)=='NUR'
                ){
                    $match=true;
                }


                if($match)
                {

                    DB::table('standard_year_mappings')
                    ->updateOrInsert(
                        [
                            'academic_year_id'=>$erp->yearid,
                            'standard_id'=>$standard->id
                        ],
                        [
                            'old_standard_id'=>$erp->standardid,
                            'updated_at'=>now(),
                            'created_at'=>now()
                        ]
                    );

                }

            }

        }


        $this->info('Standard mapping completed');

    }
}