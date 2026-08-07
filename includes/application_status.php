<?php

function getAllowedApplicationStatuses(): array
{
    return [
        "Pending",
        "Shortlisted",
        "Rejected"
    ];
}

function isValidApplicationStatus(string $status): bool
{
    return in_array(
        $status,
        getAllowedApplicationStatuses(),
        true
    );
}

function canChangeApplicationStatus(
    string $currentStatus,
    string $newStatus
): bool {
    return isValidApplicationStatus($newStatus)
        && $currentStatus !== $newStatus;
}