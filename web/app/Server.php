<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{

    protected $fillable = ["token"];

    static $sensors = [
        \App\Sensor\LoadAvg::class,
        \App\Sensor\Reboot::class,
        \App\Sensor\Updates::class,
        \App\Sensor\Disks::class
    ];

    public function __construct(array $attributes = array()) {
        $attributes["token"] = str_random(32);
        parent::__construct($attributes);
    }

    public function organization() {
        return $this->belongsTo("App\Organization");
    }

    public function lastRecord() {
        $collection = \Mongo::get()->monitoring->records;
        return $collection->findOne(
                ["server_id" => $this->id],
                ["sort" => ["_id" => -1]]);
    }

    /**
     *
     * @return \DateTimeZone
     */
    public function lastRecordTime() {
        $last_record = $this->lastRecord();
        if ($last_record === null) {
            return \Carbon\Carbon::createFromTimestamp(0);
        }

        return \Carbon\Carbon::createFromTimestamp($last_record->time);
    }

    public function clientVersion() {
        $last_record = $this->lastRecord();
        if ($last_record == null) {
            return "none";
        }

        return $last_record->version;
    }

    /**
     * Get integer status of server.
     * @return int
     */
    public function status() {
        $all_status = [];
        foreach ($this->getSensors() as $sensor) {
            $all_status[] = $sensor->status();
        }

        return max($all_status);
    }

    public function statusString() {
        switch ($this->status()) {
            case 0:
                return "OK";
            case 10:
                return "WARNING";
            case 20:
                return "ERROR";
            default:
                return "Unknown";
        }
    }

    public function getSensors() {

        $sensors = [];
        foreach (self::$sensors as $sensor) {
            $sensors[] = new $sensor($this);
        }
        return $sensors;
    }
}