<?php

namespace App\Commands;

use App\Jobs\PortUsageJob;
use App\PortCount;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use InfluxDB\Client\Exception;

class PortUsageReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'port-counts {--B|backfill}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate port usage data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     * @throws \InfluxDB\Exception
     */
    public function handle(): int
    {
        // lets see if we've built data for this month - and if not, we will build it.
        if (!PortCount::query()->where('created_at', now()->startOfMonth())->limit(1)->exists()) {
            if ($this->option('backfill')) {
                $dates = CarbonPeriod::create('2017-06-01', '1 month', now()->format('Y-m-d'));
                $this->withProgressBar($dates, fn($date) => (new PortUsageJob($date))->handle());
            } else {
                (new PortUsageJob(now()))->handle();
            }
        } else {
            $this->error('ERROR: Port counts have been completed for ' . now()->startOfMonth());
        }

        return 0;
    }

    /**
     * Define the command's schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        $schedule->command(static::class)->everyThreeHours();
    }
}
