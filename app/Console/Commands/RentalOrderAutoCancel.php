<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RentalOrderAutoCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:rental-order-auto-cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes the rentalOrderAutoCancel.js file to cancel rental orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $node_path = env('NODE_PATH', '');

        if (! empty($node_path)) {

            // Run the JS file using Node.js
            $command = $node_path . ' --no-experimental-fetch --max-old-space-size=1024 ' . storage_path('app/firebase/rentalOrderAutoCancel.js');
            \Log::info("Running command: " . $command);
            $output = shell_exec($command . " /dev/null 2>&1");

            \Log::info('Rental Order Auto Cancel Output: ' . $output);

            $this->info('Rental Order Auto Cancel process executed.');
        } else {

            // Log the output
            \Log::info('Rental Order Auto Cancel Output: Node path is not defined');
        }
    }
}
