<?php

namespace App;

/**
 * Description of AbstractSensor
 *
 * @author tibo
 */
abstract class AbstractSensor implements Sensor {
    /**
     *
     * @var \App\Server
     */
    private $server;

    public function __construct(\App\Server $server) {
        $this->server = $server;
    }

    protected function getServer() {
        return $this->server;
    }

    function getLastRecord($field) {
        return $this->server->lastRecordContaining($field);
    }

    function getLastRecords($field, $count) {
        return \Mongo::get()->monitoring->records->find(
                [   "server_id" => $this->server->id,
                    $field => ['$ne' => null]],
                ["limit" => $count, "sort" => ["_id" => -1]]);
    }
}
