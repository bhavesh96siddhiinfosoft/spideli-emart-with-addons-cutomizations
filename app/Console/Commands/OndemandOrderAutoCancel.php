<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OndemandOrderAutoCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ondemand-order-auto-cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes the onDemandOrderAutoCancel.js file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $node_path = env('NODE_PATH','');

        if(!empty($node_path)){

            // Run the JS file using Node.js
            $command = $node_path.' '.storage_path('app/firebase/onDemandOrderAutoCancel.js');
        
            $output = shell_exec($command." /dev/null 2>&1");

            // Log the output
            \Log::info('On demand Order AutoCancel Output: ' . $output);

            $this->info('On demand Order AutoCancel process executed.');

        }else{

            // Log the output
            \Log::info('On demand Order AutoCancel Output: Node path is not defined');
        }
    }
}
