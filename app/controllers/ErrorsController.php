<?php

namespace App\Controllers;

class ErrorsController extends ControllerBase
{
    public function show401Action()
    {
        $this->response->setHeader(401, 'Unauthorized');
    }

    public function show403Action()
    {
        $this->response->setHeader(403, 'Forbidden');
    }

    public function show404Action()
    {
        $this->response->setHeader(404, 'Not Found');
    }

    public function show500Action()
    {
        $this->response->setHeader(500, 'Internal Server Error');
    }
}
