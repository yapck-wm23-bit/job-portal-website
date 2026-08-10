<?php

require_once __DIR__
    . "/../includes/application_status.php";

$testsPassed = 0;
$testsFailed = 0;

function runTest(
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
runTest(
    "Pending is a valid status",
    isValidApplicationStatus("Pending")
);


// Test 2
runTest(
    "Shortlisted is a valid status",
    isValidApplicationStatus("Shortlisted")
);


// Test 3
runTest(
    "Rejected is a valid status",
    isValidApplicationStatus("Rejected")
);


// Test 4
runTest(
    "Unknown status is rejected",
    !isValidApplicationStatus("Approved")
);


// Test 5
runTest(
    "Pending can change to Shortlisted",
    canChangeApplicationStatus(
        "Pending",
        "Shortlisted"
    )
);


// Test 6
runTest(
    "Pending can change to Rejected",
    canChangeApplicationStatus(
        "Pending",
        "Rejected"
    )
);


// Test 7
runTest(
    "Same status is not considered a change",
    !canChangeApplicationStatus(
        "Pending",
        "Pending"
    )
);


// Test 8
runTest(
    "Invalid new status cannot be used",
    !canChangeApplicationStatus(
        "Pending",
        "Hired123"
    )
);


echo PHP_EOL;
echo "Tests passed: "
    . $testsPassed
    . PHP_EOL;

echo "Tests failed: "
    . $testsFailed
    . PHP_EOL;


// Return failure code to CI
if ($testsFailed > 0) {
    exit(1);
}

exit(0);