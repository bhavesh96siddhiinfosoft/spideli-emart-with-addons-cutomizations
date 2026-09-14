<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ParcelOrderAutoCancel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parcel-order-auto-cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes the parcelOrderAutoCancel.js file to cancel parcel orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $node_path = env('NODE_PATH', '');

        if (! empty($node_path)) {

            // Run the JS file using Node.js
            $command = $node_path . ' --no-experimental-fetch --max-old-space-size=1024 ' . storage_path('app/firebase/parcelOrderAutoCancel.js');
            \Log::info("Running command: " . $command);
            $output = shell_exec($command . " /dev/null 2>&1");

            \Log::info('Parcel Order Auto Cancel Output: ' . $output);

            $this->info('Parcel Order Auto Cancel process executed.');
        } else {

            // Log the output
            \Log::info('Parcel Order Auto Cancel Output: Node path is not defined');
        }
    }
}
