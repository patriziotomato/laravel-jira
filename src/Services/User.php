<?php

namespace LaravelJira\Services;

use JiraRestApi\Project\ProjectService;
use JiraRestApi\User\UserService;
use LaravelJira\Responses\Users;
use LaravelJira\Responses\Versions;

trait User
{
    /**
     * @return Users
     */
    public function users()
    {
        $userService = new UserService();

        return new Users($userService->getUsers([
            'startAt' => 0,
            'maxResults' => 1000,
        ]));
    }
}