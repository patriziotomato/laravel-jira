<?php

namespace LaravelJira;


class JiraService
{
    public $url;
    private $username;
    private $password;

    public function __construct($url, $username, $password)
    {
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
    }
}