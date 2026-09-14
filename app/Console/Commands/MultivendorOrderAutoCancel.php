<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MultivendorOrderAutoCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:multivendor-order-auto-cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes the multivendorOrderAutoCancel.js file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $node_path = env('NODE_PATH', '');

        if (! empty($node_path)) {

            // Run the JS file using Node.js
            $command = $node_path . ' --no-experimental-fetch --max-old-space-size=1024 ' . storage_path('app/firebase/multivendorOrderAutoCancel.js');
            \Log::info("Running command: " . $command);
            $output = shell_exec($command . " /dev/null 2>&1");

            \Log::info('Multivendor Order Auto Cancel Output: ' . $output);

            $this->info('Multivendor Order Auto Cancel process executed.');
        } else {

            // Log the output
            \Log::info('Multivendor Order Auto Cancel Output: Node path is not defined');
        }
    }
}
