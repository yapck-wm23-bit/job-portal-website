<?php

function hasSubmittedApplications(array $applications): bool
{
    return count($applications) > 0;
}

function getApplicationCountMessage(int $count): string
{
    if ($count === 0) {
        return "0 submitted applications found.";
    }

    if ($count === 1) {
        return "1 submitted application found.";
    }

    return $count . " submitted applications found.";
}

function isValidJobSeekerSelection(int $jobSeekerId): bool
{
    return $jobSeekerId > 0;
}