<?php

namespace LaravelJira\Responses;


use Carbon\Carbon;
use Illuminate\Support\Collection;
use JiraRestApi\Issue\Version;

/**
 * Use this class to filter through Versions fluently
 *
 * ie.
 * - <code>Jira::projectVersions('TEST')->notArchived()->released()->overdue()->get();</code> for filtered versions
 * - <code>Jira::projectVersions('TEST')->versions;</code> for all Versions
 *
 * @package LaravelJira\Responses
 */
class Versions
{
    public $versions;

    /** @var Collection */
    private $filteredVersions;

    public function __construct($versions)
    {
        $this->versions = $versions;
        $this->filteredVersions = collect($versions);
    }

    public function get()
    {
        return $this->filteredVersions;
    }

    public function orderByReleaseDate()
    {
        $this->filteredVersions = $this->filteredVersions->sort(function(Version $versionA, Version $versionB) {
            if (!$versionA->releaseDate) {
                return 1;
            }

            if (!$versionB->releaseDate) {
                return -1;
            }

            return Carbon::instance($versionA->releaseDate)->gt(Carbon::instance($versionB->releaseDate)) ? 1 : -1;
        })->values();

        return $this;
    }

    public function released()
    {
        $this->filterVersions(true, null, null);

        return $this;
    }

    public function unreleased()
    {
        $this->filterVersions(false, null, null);

        return $this;
    }

    public function archived()
    {
        $this->filterVersions(null, true, null);

        return $this;
    }

    public function unarchived()
    {
        $this->filterVersions(null, false, null);

        return $this;
    }

    public function overdue()
    {
        $this->filterVersions(null, true, true);

        return $this;
    }

    public function notOverdue()
    {
        $this->filterVersions(null, false, false);

        return $this;
    }

    private function filterVersions($released, $archived, $overdue)
    {
        $this->filteredVersions = $this->filteredVersions->reject(function (Version $version) use (
            $released,
            $archived,
            $overdue
        ) {
            if (!is_null($released) && $version->released != $released) {
                return true;
            }

            if (!is_null($archived) && $version->archived != $archived) {
                return true;
            }

            if (!is_null($overdue) && $version->overdue != $overdue) {
                return true;
            }

//            if (!$version->releaseDate) {
//                return true;
//            }
//
//            return Carbon::now()->gt(Carbon::instance($version->releaseDate));
        });
    }
}