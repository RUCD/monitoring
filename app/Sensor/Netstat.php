<?php

namespace App\Sensor;

use \App\AbstractSensor;

/**
 * Parse netstat
 *
 * @author tibo
 */
class Netstat extends AbstractSensor
{

    public function report()
    {
        return view("agent.netstat", [
            "server" => $this->getServer()]);
    }

    public function points() : array
    {
        $records = $this->getLastRecords("netstat-statistics", 289);

        if (count($records) == 0) {
            return [];
        }

        $reports = [];
        foreach ($records as $record) {
            $report = $this->parse($record['netstat-statistics']);
            $report->time = $record->time;
            $reports[] = $report;
        }

        $dataset = ["name" => "Retransmitted TCP segments [%]", "points" => []];
        $previous_report = $reports[0];
        foreach ($reports as $report) {
            $sent_segments = $report->tcp_segments_sent - $previous_report->tcp_segments_sent;
            $retransmitted_segments =
                    $report->tcp_segments_retransmitted - $previous_report->tcp_segments_retransmitted;
            $ratio = 0;
            if ($sent_segments != 0) {
                $ratio = $retransmitted_segments / $sent_segments * 100;
            }
            // point time is in miliseconds :-(
            $dataset["points"][] = new Point($report->time * 1000, $ratio);
            $previous_report = $report;
        }

        return [$dataset];
    }

    public function status()
    {
        return self::STATUS_OK;
    }


    public function parseRecord($record)
    {
        $interfaces = $this->parseIfconfig($record->ifconfig);
        foreach ($interfaces as $interface) {
            $interface->time = $record->time;
        }

        return $interfaces;
    }

    const TCP_SENT = '/^    (\d+) segments sent out/m';
    const TCP_RETRANSMITTED = '/^    (\d+) segments retransmitted$/m';

    public function parse(string $string) : NetstatReport
    {
        $report = new NetstatReport;
        $report->tcp_segments_retransmitted =
                $this->pregMatchOne(self::TCP_RETRANSMITTED, $string, 0);
        $report->tcp_segments_sent =
                $this->pregMatchOne(self::TCP_SENT, $string, 0);

        return $report;
    }

    public function pregMatchOne($pattern, $string, $default = null)
    {
        $matches = [];
        if (preg_match($pattern, $string, $matches) === 1) {
            return $matches[1];
        }

        return $default;
    }
}
