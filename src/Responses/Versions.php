<?php

namespace LaravelJira\Responses;


use Carbon\Carbon;
use Illuminate\Support\Collection;
use JiraRestApi\Issue\IssueService;
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
        $this->filteredVersions = $this->filteredVersions->sort(function (Version $versionA, Version $versionB) {
            if (!$versionA->releaseDate) {
                return 1;
            }

            if (!$versionB->releaseDate) {
                return -1;
            }

            $dateA = new Carbon($versionA->releaseDate);
            $dateB = new Carbon($versionB->releaseDate);

            return $dateA->gt($dateB) ? 1 : -1;
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

    public function withTicketInformation()
    {
        $issueService = new IssueService();

        $this->filteredVersions->transform(function ($version) use ($issueService) {
            $searchResult = $issueService->search(
                'resolution = Unresolved AND fixVersion = ' . $version->id
            );

            $version->remainingOpenIssues = $searchResult->total ?? 0;
            $version->remainingEffortInHours = 0;

            foreach ($searchResult->getIssues() as $issue) {
                $hours = 0;
                if ($issue->fields->aggregatetimeoriginalestimate) {
                    $hours = $issue->fields->aggregatetimeoriginalestimate / 60 / 60;
                }
                $version->remainingEffortInHours = round($version->remainingEffortInHours + $hours, 1);
            }

            return $version;
        });

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