<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inventory:sync')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/inventory_sync.log'))
            ->onSuccess(function () {
                \Log::info('✅ inventory:sync SUCCESS at ' . now());
            })
            ->onFailure(function () {
                \Log::error('❌ inventory:sync FAILED at ' . now());
            });

        $schedule->command('inventory-item:sync')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/inventory_item_sync.log'))
            ->onSuccess(function () {
                \Log::info('✅ inventory-item:sync SUCCESS at ' . now());
            })
            ->onFailure(function () {
                \Log::error('❌ inventory-item:sync FAILED at ' . now());
            });

        $schedule->command('order-items:sync')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/order_items_sync.log'))
            ->onSuccess(function () {
                \Log::info('✅ order-items:sync SUCCESS at ' . now());
            })
            ->onFailure(function () {
                \Log::error('❌ order-items:sync FAILED at ' . now());
            });
    }


    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
