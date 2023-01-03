<?php

namespace LaravelJira\Services;

use JiraCloud\Project\ProjectService;
use LaravelJira\Responses\Versions;

trait Project
{
    /**
     * @param $projectName
     *
     * @return Versions
     */
    public function projectVersions($projectName): Versions
    {
        $proj = new ProjectService();

        return new Versions($proj->getVersions($projectName));
    }
}