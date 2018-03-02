<?php

namespace LaravelJira\Services;

use JiraRestApi\JiraException;
use JiraRestApi\Project\ProjectService;

trait Project
{
    /**
     * @param $projectName
     * @throws JiraException
     */
    public function projectInfo($projectName)
    {
        $proj = new ProjectService();

        $p = $proj->get($projectName);

        dd($p);
    }
}