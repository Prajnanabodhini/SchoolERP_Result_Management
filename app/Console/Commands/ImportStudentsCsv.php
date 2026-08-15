<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:import-students-csv')]
#[Description('Command description')]
class ImportStudentsCsv extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
