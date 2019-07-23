<?php

namespace LaravelJira\Responses;


use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\Issue\Version;
use Khill\Duration\Duration;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function withTicketInformation(int $verbosityLevel = OutputInterface::VERBOSITY_NORMAL)
    {
        $issueService = new IssueService();

        $this->filteredVersions->transform(function ($version) use ($issueService, $verbosityLevel) {
            $searchResult = $issueService->search('fixVersion = '.$version->id, 0, 500);

            if ($verbosityLevel >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                if ($version->archived) {
                    Log::debug("- Skipping ticket informations for milestone {$version->name} #$version->id");

                    return $version;
                }

                Log::debug("- Updating milestone {$version->name} #$version->id");
            }

            //dd($searchResult);

            $version->totalIssues = 0;
            $version->remainingOpenIssues = 0;
            $version->estimatedEffortInHours = 0;
            $version->remainingEffortInHours = 0;
            $version->issuesNotAssigned = 0;
            $version->issuesNotEstimated = 0;

            foreach ($searchResult->getIssues() as $issue) {
                $version->totalIssues++;

                $version->issuesNotAssigned += $issue->fields->assignee ? 0 : 1;
                $version->issuesNotEstimated += $issue->fields->aggregatetimeoriginalestimate ? 0 : 1;

                $hoursEstimated = 0;
                if ($issue->fields->aggregatetimeoriginalestimate) {
                    $hoursEstimated = $issue->fields->aggregatetimeoriginalestimate / 60 / 60;
                }
                $version->estimatedEffortInHours = round($version->estimatedEffortInHours + $hoursEstimated, 4);

                if (!$issue->fields->resolutiondate) {

                    $version->remainingOpenIssues++;

                    $hoursLeft = 0;

                    if ($issue->fields->timeestimate) {
                        $hoursLeft = $issue->fields->timeestimate / 60 / 60;
                    }

                    $version->remainingEffortInHours = round($version->remainingEffortInHours + $hoursLeft, 4);
                }

                $version->issues[$issue->key] = [
                    'key'                                 => $issue->key,
                    'reporter'                            => $issue->fields->reporter ? [
                        'name'         => $issue->fields->reporter->name,
                        'display_name' => $issue->fields->reporter->displayName,
                        'avatar_url'   => $issue->fields->reporter->avatarUrls['48x48'],
                    ] : null,
                    'created'                             => $issue->fields->created ? Carbon::instance($issue->fields->created) : null,
                    'updated'                             => $issue->fields->updated ? Carbon::instance($issue->fields->updated) : null,
                    'description'                         => $issue->fields->description,
                    'priority'                            => $issue->fields->priority ? $issue->fields->priority->name : null,
                    'assignee'                            => $issue->fields->assignee ? [
                        'name'         => $issue->fields->assignee->name,
                        'display_name' => $issue->fields->assignee->displayName,
                        'avatar_url'   => $issue->fields->assignee->avatarUrls['48x48'],
                    ] : null,
                    'duedate'                             => $issue->fields->duedate ? new Carbon($issue->fields->duedate) : null,
                    'resolutiondate'                      => $issue->fields->resolutiondate ? new Carbon($issue->fields->resolutiondate) : null,
                    'effort_estimated'                    => (int)$issue->fields->aggregatetimeoriginalestimate,
                    'effort_estimated_remaining'          => (int)$issue->fields->timeestimate,
                    'effort_spent'                        => (int)$issue->fields->aggregatetimespent,
                    'effort_estimated_readable'           => (new Duration((int)$issue->fields->aggregatetimeoriginalestimate,
                        8))->humanize(),
                    'effort_estimated_remaining_readable' => (new Duration((int)$issue->fields->timeestimate,
                        8))->humanize(),
                    'effort_spent_readable'               => (new Duration((int)$issue->fields->aggregatetimespent,
                        8))->humanize(),
                    'lastViewed'                          => $issue->fields->lastViewed ? new Carbon($issue->fields->lastViewed->scalar) : null,
                ];
            }

            $version->estimatedEffortInHoursReadable = (new Duration((int)$version->estimatedEffortInHours * 60 * 60,
                8))->humanize();
            $version->remainingEffortInHoursReadable = (new Duration((int)$version->remainingEffortInHours * 60 * 60,
                8))->humanize();

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