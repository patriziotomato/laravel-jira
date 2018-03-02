<?php

namespace LaravelJira\Services;

use Illuminate\Support\Facades\Log;
use JiraRestApi\JiraException;
use JiraRestApi\Project\ProjectService;
use LaravelJira\Responses\Versions;

trait Project
{
    /**
     * @param $projectName
     * @return Versions
     */
    public function projectVersions($projectName)
    {
        $proj = new ProjectService();

        try {
            return new Versions($proj->getVersions($projectName));
        } catch (JiraException $e) {
            Log::error("Could not get Versions of Project {$projectName}: " . $e->getMessage());
        }
    }
}