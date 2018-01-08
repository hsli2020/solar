<?php

return [
    // ErrorsController
    '/error/401' => array(
        'params' => array(
            'controller' => 'error',
            'action'     => 'error401',
        ),
        'name' => 'error-401',
    ),
    '/error/403' => array(
        'params' => array(
            'controller' => 'error',
            'action'     => 'error403',
        ),
        'name' => 'error-403',
    ),
    '/error/404' => array(
        'params' => array(
            'controller' => 'error',
            'action'     => 'error404',
        ),
        'name' => 'error-404',
    ),
    '/error/500' => array(
        'params' => array(
            'controller' => 'error',
            'action'     => 'error500',
        ),
        'name' => 'error-500',
    ),
];
