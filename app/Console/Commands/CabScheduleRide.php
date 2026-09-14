<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CabScheduleRide extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cab-schedule-ride';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes the cabScheduleRide.js file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $node_path = env('NODE_PATH','');

        if(!empty($node_path)){

            // Run the JS file using Node.js
            $command = $node_path.' '.storage_path('app/firebase/cabScheduleRide.js');
        
            $output = shell_exec($command." /dev/null 2>&1");

            // Log the output
            \Log::info('Cab Schedule Ride Output: ' . $output);

            $this->info('Cab Schedule Ride Process executed.');

        }else{

            // Log the output
            \Log::info('Cab Schedule Ride Output: Node path is not defined');
        }
    }
}
