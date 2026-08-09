<?php

function createApplicationStatusNotification(
    string $jobTitle,
    string $newStatus
): string {
    return "Your application for "
        . $jobTitle
        . " has been changed to "
        . $newStatus
        . ".";
}

function isUnreadNotification(int $isRead): bool
{
    return $isRead === 0;
}

function getNotificationCountMessage(int $count): string
{
    if ($count === 0) {
        return "0 notifications found.";
    }

    if ($count === 1) {
        return "1 notification found.";
    }

    return $count . " notifications found.";
}