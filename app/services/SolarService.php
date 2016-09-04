<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class SolarService extends Injectable
{
    public function ping()
    {
        fpr(__METHOD__);
    }
}