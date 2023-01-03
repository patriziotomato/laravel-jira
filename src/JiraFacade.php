<?php

namespace LaravelJira;

use Illuminate\Support\Facades\Facade;

class JiraFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Jira::class;
    }
}