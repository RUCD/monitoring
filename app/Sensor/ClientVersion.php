<?php

namespace App\Sensor;

use GuzzleHttp\Client;

/**
 * Description of Reboot
 *
 * @author tibo
 */
class ClientVersion extends \App\AbstractSensor
{

    const MANIFEST = "https://download.cylab.be/monitor-php-client/manifest.json";
    
    public function manifest()
    {
        $client = new Client([
            'timeout'  => 5.0,
        ]);

        try {
            $json = $client->get(self::MANIFEST)->getBody();
        } catch (\Exception $ex) {
            return "";
        }

        return json_decode($json)[0];
    }
        

    public function latestVersion()
    {
        return $this->manifest()->version;
    }
    
    public function latestUrl()
    {
        return $this->manifest()->url;
    }

    //put your code here
    public function report()
    {
        return "<p>Installed version: " . $this->getServer()->clientVersion() . "</p>"
        . "<p>Latest client version: " . $this->latestVersion() . "</p>";
    }

    public function status()
    {
        if ($this->getServer()->clientVersion() === $this->latestVersion()) {
            return self::STATUS_OK;
        }

        return self::STATUS_WARNING;
    }
}