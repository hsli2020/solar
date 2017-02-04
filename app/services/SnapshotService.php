<?php

namespace App\Service;

use Phalcon\Di\Injectable;

use App\Models\Snapshot;

class SnapshotService extends Injectable
{
    public function load()
    {
        return $this->db->fetchAll("SELECT * FROM snapshot");
    }

    public function generate()
    {
    }
}
