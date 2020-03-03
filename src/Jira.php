<?php

namespace LaravelJira;


class Jira
{
    use Services\Project;
    use Services\User;

    private $url;
    private $username;
    private $password;

    public function __construct($url, $username, $password)
    {
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
    }
}