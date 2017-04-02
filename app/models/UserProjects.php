<?php

namespace App\Models;

use Phalcon\Mvc\Model;

/**
 * App\Models\UserProjects
 */
class UserProjects extends Model
{
    public $userId;
    public $projects;

    public function initialize()
    {
        $this->setSource('user_projects');
    }

    public function columnMap()
    {
        // Keys are the real names in the table and
        // the values their names in the application

        return array(
            'user_id'  => 'userId',
            'projects' => 'projects',
        );
    }
}
