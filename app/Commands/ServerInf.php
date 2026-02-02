<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ServerInf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'serverInf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        echo "Hello, World!" . PHP_EOL;
        $this->info("serverInf springs to life!");
        /*
    render(<<<'HTML'
        <div>
            <div class="px-1 bg-green-600 py-10 w-full">
                <span> Mondev </span>
            </div>
            <em class="ml-1 py-10 text-red-500 font-bold">
                The Developer's Newsletter 0_1
            </em>
        </div>
    HTML);
    */
        }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
