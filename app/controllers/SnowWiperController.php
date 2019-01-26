<?php

namespace App\Controllers;

use App\System\SnowWiper;

class SnowWiperController extends ControllerBase
{
    public function getStateAction()
    {
        $wiper = new SnowWiper();
        return json_encode($wiper->getState());
    }

    public function turnOnAction()
    {
        $wiper = new SnowWiper();
        return json_encode($wiper->turnOn());
    }

    public function turnOffAction()
    {
        $wiper = new SnowWiper();
        return json_encode($wiper->turnOff());
    }

    public function pulseAction()
    {
        $wiper = new SnowWiper();
        return json_encode($wiper->pulse());
    }

    public function autoPulseAction($state = 0)
    {
        //$wiper = new SnowWiper();
        //return json_encode($wiper->pulse());
    }
}
