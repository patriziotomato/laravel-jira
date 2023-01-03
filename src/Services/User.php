<?php

namespace LaravelJira\Services;

use JiraCloud\User\UserService;
use LaravelJira\Responses\Users;

trait User
{
    /**
     * @return Users
     */
    public function users(): Users
    {
        $userService = new UserService();

        return new Users(users: $userService->findUsers([
            'accountId' => '.', // get all users.
            'startAt' => 0,
            'maxResults' => 1000,
            'includeInactive' => false,
            //'property' => '*',
        ]));
    }
}