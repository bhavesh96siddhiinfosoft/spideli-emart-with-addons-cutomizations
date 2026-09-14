<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MultivendorScheduledOrderNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:multivendor-scheduled-order-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes the multivendorScheduledOrderNotification.js file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $node_path = env('NODE_PATH', '');

        if (! empty($node_path)) {

            // Run the JS file using Node.js
            $command = $node_path . ' --no-experimental-fetch --max-old-space-size=1024 ' . storage_path('app/firebase/multivendorScheduledOrderNotification.js');
            \Log::info("Running command: " . $command);
            $output = shell_exec($command . " /dev/null 2>&1");

            \Log::info('Multivendor Scheduled Order Notification Output: ' . $output);

            $this->info('Multivendor Scheduled Order Notification Process executed.');
        } else {

            // Log the output
            \Log::info('Multivendor Scheduled Order Notification Output: Node path is not defined');
        }
    }
}
