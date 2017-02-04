<?php

return [
    '/dashboard' => array(
        'params' => array(
            'controller' => 'index',
            'action'     => 'dashboard',
        ),
        'name' => 'dashboard',
    ),

    // ErrorsController
    '/error/401' => array(
        'params' => array(
            'controller' => 'errors',
            'action'     => 'show401',
        ),
        'name' => 'error-401',
    ),
    '/error/403' => array(
        'params' => array(
            'controller' => 'errors',
            'action'     => 'show403',
        ),
        'name' => 'error-403',
    ),
    '/error/404' => array(
        'params' => array(
            'controller' => 'errors',
            'action'     => 'show404',
        ),
        'name' => 'error-404',
    ),
    '/error/500' => array(
        'params' => array(
            'controller' => 'errors',
            'action'     => 'show500',
        ),
        'name' => 'error-500',
    ),
];
