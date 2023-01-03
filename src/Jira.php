<?php

namespace LaravelJira;


class Jira
{
    use Services\Project;
    use Services\User;

    public function __construct(
        public string $url,
//        public string $user,
        public string $accessToken
    ) {
    }
}