<?php

namespace App\Sensor;

use \App\AbstractSensor;

/**
 * Description of LoadAvg
 *
 * @author tibo
 */
class LoadAvg extends AbstractSensor
{

    public function report(array $records) : string
    {
        $record = $this->getServer()->lastRecord();
        if (! isset($record['loadavg'])) {
            return "<p>No data available...</p>";
        }
        $field = $record->loadavg;
        $current_load = $this->parse($field);


        return view("agent.loadavg", [
            "current_load" => $current_load]);
    }

    public function loadPoints(array $records)
    {
        $points = [];
        foreach ($records as $record) {
            $points[] = new Point(
                $record->time * 1000,
                $this->parse($record->loadavg)
            );
        }
        return $points;
    }

    public function status(array $records) : int
    {
        return self::STATUS_OK;
    }

    public function parse($string)
    {
        return current(explode(" ", $string));
    }
}
