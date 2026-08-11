<?php

require_once __DIR__
    . "/../includes/notification_helper.php";

$testsPassed = 0;
$testsFailed = 0;

function runNotificationTest(
    string $name,
    bool $condition
): void {
    global $testsPassed;
    global $testsFailed;

    if ($condition) {
        echo "[PASS] " . $name . PHP_EOL;
        $testsPassed++;
    } else {
        echo "[FAIL] " . $name . PHP_EOL;
        $testsFailed++;
    }
}


// Test 1
runNotificationTest(
    "Shortlisted notification message is correct",
    createApplicationStatusNotification(
        "Software Developer",
        "Shortlisted"
    ) ===
    "Your application for Software Developer has been changed to Shortlisted."
);


// Test 2
runNotificationTest(
    "Rejected notification message is correct",
    createApplicationStatusNotification(
        "Web Developer",
        "Rejected"
    ) ===
    "Your application for Web Developer has been changed to Rejected."
);


// Test 3
runNotificationTest(
    "Pending notification message is correct",
    createApplicationStatusNotification(
        "Software Engineer",
        "Pending"
    ) ===
    "Your application for Software Engineer has been changed to Pending."
);


// Test 4
runNotificationTest(
    "Unread notification is recognised",
    isUnreadNotification(0)
);


// Test 5
runNotificationTest(
    "Read notification is not recognised as unread",
    !isUnreadNotification(1)
);


// Test 6
runNotificationTest(
    "Zero notification count message is correct",
    getNotificationCountMessage(0) ===
        "0 notifications found."
);


// Test 7
runNotificationTest(
    "Single notification count message is correct",
    getNotificationCountMessage(1) ===
        "1 notification found."
);


// Test 8
runNotificationTest(
    "Multiple notification count message is correct",
    getNotificationCountMessage(3) ===
        "3 notifications found."
);


echo PHP_EOL;

echo "Tests passed: "
    . $testsPassed
    . PHP_EOL;

echo "Tests failed: "
    . $testsFailed
    . PHP_EOL;


if ($testsFailed > 0) {
    exit(1);
}

exit(0);