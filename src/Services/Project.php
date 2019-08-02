<?php

namespace LaravelJira\Services;

use JiraRestApi\Project\ProjectService;
use LaravelJira\Responses\Versions;

trait Project
{
    /**
     * @param $projectName
     *
     * @return Versions
     */
    public function projectVersions($projectName)
    {
        $proj = new ProjectService();

        return new Versions($proj->getVersions($projectName));
    }
}