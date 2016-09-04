<?php

namespace App\Service;

use Phalcon\Di\Injectable;

class DataService extends Injectable
{
    public function ping()
    {
        fpr(__METHOD__);
    }
}