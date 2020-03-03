<?php

namespace LaravelJira\Responses;

use Illuminate\Support\Collection;

class Users
{
    public $users;

    /** @var Collection */
    private $filteredVersions;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function get()
    {
        return $this->users;
    }

}
