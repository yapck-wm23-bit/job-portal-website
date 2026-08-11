<?php

require_once __DIR__
    . "/../includes/application_history.php";

$testsPassed = 0;
$testsFailed = 0;

function runApplicationHistoryTest(
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
runApplicationHistoryTest(
    "Application list with records is recognised",
    hasSubmittedApplications([
        ["application_id" => 1]
    ])
);


// Test 2
runApplicationHistoryTest(
    "Empty application list is recognised",
    !hasSubmittedApplications([])
);


// Test 3
runApplicationHistoryTest(
    "Zero application count message is correct",
    getApplicationCountMessage(0) ===
        "0 submitted applications found."
);


// Test 4
runApplicationHistoryTest(
    "Single application count message is correct",
    getApplicationCountMessage(1) ===
        "1 submitted application found."
);


// Test 5
runApplicationHistoryTest(
    "Multiple application count message is correct",
    getApplicationCountMessage(3) ===
        "3 submitted applications found."
);


// Test 6
runApplicationHistoryTest(
    "Positive Job Seeker ID is valid",
    isValidJobSeekerSelection(1)
);


// Test 7
runApplicationHistoryTest(
    "Zero Job Seeker ID is invalid",
    !isValidJobSeekerSelection(0)
);


// Test 8
runApplicationHistoryTest(
    "Negative Job Seeker ID is invalid",
    !isValidJobSeekerSelection(-1)
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