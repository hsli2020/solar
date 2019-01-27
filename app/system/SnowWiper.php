<?php

namespace App\System;

class SnowWiper
{
    private $ip = 'http://10.206.2.241/state.xml';

    public function __construct()
    {
        // $this->ip = $ip;
    }

    public function getState()
    {
        /*
          <?xml version="1.0" encoding="utf-8"?>
          <datavalues>
            <relaystate>0</relaystate>
            <inputstate>0</inputstate>
            <rebootstate>0</rebootstate>
            <totalreboots>0</totalreboots>
          </datavalues>
        */
        $url = $this->ip;
        return $this->send($url);
    }

    // Turn the relay ‘off’:
    public function turnOff()
    {
        /*
          <?xml version="1.0" encoding="utf-8"?>
          <datavalues>
            <relaystate>0</relaystate>
            <inputstate>0</inputstate>
            <rebootstate>0</rebootstate>
            <totalreboots>0</totalreboots>
          </datavalues>
        */
        $url = $this->ip . '?relayState=0';
        return $this->send($url);
    }

    // Turn the relay ‘On’:
    public function turnOn()
    {
        /*
          <?xml version="1.0" encoding="utf-8"?>
          <datavalues>
            <relaystate>1</relaystate>
            <inputstate>0</inputstate>
            <rebootstate>0</rebootstate>
            <totalreboots>0</totalreboots>
          </datavalues>
        */
        $url = $this->ip . '?relayState=1';
        return $this->send($url);
    }

    public function pulse($time = 5)
    {
        /*
          <?xml version="1.0" encoding="utf-8"?>
          <datavalues>
            <relaystate>1</relaystate>
            <inputstate>0</inputstate>
            <rebootstate>0</rebootstate>
            <totalreboots>0</totalreboots>
          </datavalues>
        */
        $url = $this->ip . "?relayState=2&pulseTime=$time";
        return $this->send($url);
    }

    protected function send($url)
    {
        $res = $this->httpGet($url);
        return simplexml_load_string($res);
    }

    protected function httpGet($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
       #curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $output = curl_exec($ch);
       #$error = curl_error($ch);
       #$info = curl_getinfo($ch);
       #$errno = curl_errno($ch);
        curl_close($ch);

        return $output;
    }
}

//$wiper = new SnowWiper();
//print_r($wiper->getState());
//print_r($wiper->turnOn());
//print_r($wiper->turnOff());
//print_r($wiper->pulse());
