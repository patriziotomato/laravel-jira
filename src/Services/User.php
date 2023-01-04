<?php

namespace LaravelJira\Services;

use JiraCloud\JiraException;
use JiraCloud\User\UserService;
use JsonMapper_Exception;
use LaravelJira\Responses\Users;

trait User
{
    /**
     * @throws JsonMapper_Exception
     * @throws JiraException
     */
    public function users(): Users
    {
        $userService = new UserService();

        return new Users(users: $userService->getUsers([
            'startAt' => 0,
            'maxResults' => 1000,
            //'property' => '*',
        ]));
    }
}