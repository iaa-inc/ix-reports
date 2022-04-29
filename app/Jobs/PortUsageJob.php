<?php

namespace App\Jobs;

use App\PortCount;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use InfluxDB\Client;
use InfluxDB\Exception;

/**
 * The theory behind this is that we need to pick a time in the month and "count" the number of *up* ports as noted by
 * grafana for that month. We'll them create a record in the database for this - as well as a record for "additional things
 * which will allow us to generate really nice port usage graphs and things...
 */
class PortUsageJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public Client $client;

    /**
     * @throws Client\Exception
     */
    public function __construct(public Carbon $date)
    {
        $this->client = Client::fromDSN(config('services.influx.dsn'))->getClient();
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        /**
         * We want to pick a time in this month, and get the count of all ports from each site
         *
         */

        $switches = collect();
        $switch_query = $this->client->query('metrics', 'show tag values FROM "ifmib-net" with key="device" where device =~ /pe.\./');
        foreach ($switch_query->getPoints() as $point) {
            $switches->add($point['value']);
        }


        $speeds = collect([100, 1000, 10000, 40000, 100000, 400000]);
        $switches->unique()->each(function ($switch) use ($speeds) {
            $results = $speeds->map(function ($speed) use ($switch) {
                $query = sprintf(
                    'SELECT count(ifOperStatus) FROM "ifmib-net" WHERE ("device" =~ /^%s$/) AND ("ifAlias" =~ /AS.*/) AND (ifOperStatus = 1)AND(ifHighSpeed=%d) AND  time >= %s and time <= %s GROUP BY time(1s) fill(previous)',
                    $switch,
                    $speed,
                    $this->date->startOfMonth()->getTimestampMs() . "000000",
                    $this->date->copy()->startOfMonth()->addMinute()->getTimestampMs() . "000000",
                );

                $result = $this->client->query('metrics', $query);

                $count = 0;
                if ($points = $result->getPoints()) {
                    //TODO: Filter 0, and return the average of non zero counts.
                    $count = end($points)['count'];
                }

                return [
                    'speed' => $speed,
                    'count' => $count
                ];
            });

            $portCount = new PortCount;
            $portCount->ix = get_ix_from_switch_name($switch);
            $portCount->site = preg_replace('/pe.\./', '', $switch);
            $portCount->switch = $switch;

            // TODO: Build Cross Connect Reporting
            $portCount->total_cross_connects = 0;
            $portCount->free_cross_connects = 0;
            $portCount->used_cross_connects = 0;

            $portCount->created_at = $this->date->startOfMonth();

            foreach ($results as $item) {
                $portCount->setAttribute("count_" . $item['speed'], $item['count']);
            }

            $portCount->save();
        });

    }
}
