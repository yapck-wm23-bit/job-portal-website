<?php

require_once __DIR__ . "/../config/database.php";

$employers = [];
$jobs = [];

$selectedEmployerId = filter_input(
    INPUT_GET,
    "employer_id",
    FILTER_VALIDATE_INT
);

$selectedJobId = filter_input(
    INPUT_GET,
    "job_id",
    FILTER_VALIDATE_INT
);

// Retrieve employers for demonstration
$employerResult = $conn->query(
    "SELECT
        employer_id,
        company_name
     FROM employers
     ORDER BY company_name ASC"
);

if ($employerResult) {
    while ($row = $employerResult->fetch_assoc()) {
        $employers[] = $row;
    }
}

// Retrieve jobs belonging to the selected employer
if ($selectedEmployerId) {
    $jobQuery = $conn->prepare(
        "SELECT
            job_id,
            job_title
         FROM jobs
         WHERE employer_id = ?
         ORDER BY created_at DESC"
    );

    $jobQuery->bind_param(
        "i",
        $selectedEmployerId
    );

    $jobQuery->execute();

    $jobResult = $jobQuery->get_result();

    while ($row = $jobResult->fetch_assoc()) {
        $jobs[] = $row;
    }

    $jobQuery->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>View Applicants</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >
</head>

<body>

    <div class="applicants-container">

        <a href="../index.php">
            &larr; Back to Home
        </a>

        <h1>View Job Applicants</h1>

        <p>
            Select an Employer and one of their job postings to view
            the applicants.
        </p>

        <form
            method="GET"
            action=""
            class="applicant-filter-form"
        >

            <div class="form-group">

                <label for="employer_id">
                    Employer *
                </label>

                <select
                    id="employer_id"
                    name="employer_id"
                    required
                    onchange="this.form.submit()"
                >
                    <option value="">
                        Select an Employer
                    </option>

                    <?php foreach ($employers as $employer): ?>

                        <option
                            value="<?=
                                (int) $employer["employer_id"]
                            ?>"
                            <?php if (
                                $selectedEmployerId ===
                                (int) $employer["employer_id"]
                            ): ?>
                                selected
                            <?php endif; ?>
                        >
                            <?= htmlspecialchars(
                                $employer["company_name"]
                            ) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="form-group">

                <label for="job_id">
                    Job Posting *
                </label>

                <select
                    id="job_id"
                    name="job_id"
                    required
                    <?= !$selectedEmployerId ? "disabled" : "" ?>
                >
                    <option value="">
                        Select a Job
                    </option>

                    <?php foreach ($jobs as $job): ?>

                        <option
                            value="<?= (int) $job["job_id"] ?>"
                            <?php if (
                                $selectedJobId ===
                                (int) $job["job_id"]
                            ): ?>
                                selected
                            <?php endif; ?>
                        >
                            <?= htmlspecialchars(
                                $job["job_title"]
                            ) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <button
                type="submit"
                class="register-button"
                <?= !$selectedEmployerId ? "disabled" : "" ?>
            >
                View Applicants
            </button>

        </form>

        <?php if ($selectedJobId): ?>

            <div class="applicant-placeholder">

                <h2>Applicant List</h2>

                <p>
                    Applicant information will be displayed here after
                    database retrieval is implemented.
                </p>

            </div>

        <?php endif; ?>

    </div>

</body>

</html>