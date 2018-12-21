<?php

namespace App\Sensor;

use \App\AbstractSensor;

/**
 * Description of MemInfo
 *
 * @author tibo
 */
class Ifconfig extends AbstractSensor {

    public function report() {

        $interfaces = $this->parseIfconfig($this->getLastRecord("ifconfig"));
        return view("agent.ifconfig", [
            "server" => $this->getServer(),
            "interfaces" => $interfaces]);
    }

    /*
    public function cachedMemoryPoints() {
        $records = $this->getLastRecords("ifconfig", 288);

        $points = [];
        foreach ($records as $record) {
            $interfaces = $this->parseIfconfig($record->memory);
            $points[] = new Point(
                    $record->time * 1000, $interface->cached / 1000);
        }

        return $points;
    }*/

    public function status() {
        return self::STATUS_OK;
    }

    const IFNAME = "/^(\\S+)\\s+Link encap:/m";
    const IPV4 = '/^\\s+inet addr:(\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3})/m';
    const RXTX = '/^\\s+RX bytes:(\\d+) .*TX bytes:(\\d+)/m';

    /**
     * Parse the result of the ifconfig command.
     * @param type $string
     * @return \App\Sensor\NetworkInterface[]
     */
    public function parseIfconfig($string) {
        if ($string == null) {
            return [];
        }

        $interfaces = [];
        $lines = explode("\n", $string);
        $if = null;
        foreach ($lines as $line) {
            $name = $this->pregMatchOne(self::IFNAME, $line);
            if ($name !== false) {
                // Starting the section of a new interface
                $if = new NetworkInterface();
                $interfaces[] = $if;
                $if->name = $name;
                continue;
            }

            $ip = $this->pregMatchOne(self::IPV4, $line);
            if ($ip !== false) {
                $if->address = $ip;
                continue;
            }

            $matches = array();
            if (preg_match(self::RXTX, $line, $matches) === 1) {
                $if->rx = $matches[1];
                $if->tx = $matches[2];
                continue;
            }

        }
        return $interfaces;
    }

    public function pregMatchOne($pattern, $string) {
        $matches = array();
        if (preg_match($pattern, $string, $matches) === 1) {
            return $matches[1];
        }

        return false;
    }
}

class NetworkInterface {
    public $name;
    public $address;
    public $rx;
    public $tx;
}
