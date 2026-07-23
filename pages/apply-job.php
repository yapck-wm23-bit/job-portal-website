<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . "/../config/database.php";

$job = null;
$jobSeekers = [];
$error = "";
$success = "";
$selectedJobSeekerId = 0;

// Get job ID from either the form or the URL
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $jobId = filter_input(
        INPUT_POST,
        "job_id",
        FILTER_VALIDATE_INT
    );
} else {
    $jobId = filter_input(
        INPUT_GET,
        "job_id",
        FILTER_VALIDATE_INT
    );
}

// Retrieve selected job
if (!$jobId) {
    $error = "Please select a valid job listing.";
} else {
    $jobQuery = $conn->prepare(
        "SELECT
            jobs.job_id,
            jobs.job_title,
            jobs.job_description,
            jobs.job_requirements,
            jobs.location,
            jobs.salary_min,
            jobs.salary_max,
            jobs.job_type,
            employers.company_name
         FROM jobs
         INNER JOIN employers
            ON jobs.employer_id = employers.employer_id
         WHERE jobs.job_id = ?"
    );

    $jobQuery->bind_param("i", $jobId);
    $jobQuery->execute();

    $jobResult = $jobQuery->get_result();
    $job = $jobResult->fetch_assoc();

    $jobQuery->close();

    if (!$job) {
        $error = "The selected job listing was not found.";
    }
}

// Retrieve Job Seeker profiles
$jobSeekerResult = $conn->query(
    "SELECT
        job_seeker_id,
        full_name
     FROM job_seekers
     ORDER BY full_name ASC"
);

while ($row = $jobSeekerResult->fetch_assoc()) {
    $jobSeekers[] = $row;
}

// Process the application form
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    $job !== null
) {
    $jobSeekerId = filter_input(
        INPUT_POST,
        "job_seeker_id",
        FILTER_VALIDATE_INT
    );

    $selectedJobSeekerId = (int) $jobSeekerId;

    if (!$jobSeekerId) {
        $error = "Please select a valid Job Seeker profile.";
    } else {
        // Confirm that the Job Seeker exists
        $jobSeekerQuery = $conn->prepare(
            "SELECT job_seeker_id
             FROM job_seekers
             WHERE job_seeker_id = ?"
        );

        $jobSeekerQuery->bind_param(
            "i",
            $jobSeekerId
        );

        $jobSeekerQuery->execute();

        $jobSeekerResult = $jobSeekerQuery->get_result();

        if ($jobSeekerResult->num_rows === 0) {
            $error = "The selected Job Seeker profile was not found.";
        } else {
            try {
                $status = "Pending";

                $insertApplication = $conn->prepare(
                    "INSERT INTO applications
                    (
                        job_id,
                        job_seeker_id,
                        status
                    )
                    VALUES (?, ?, ?)"
                );

                $insertApplication->bind_param(
                    "iis",
                    $jobId,
                    $jobSeekerId,
                    $status
                );

                $insertApplication->execute();
                $insertApplication->close();

                $success = "Application submitted successfully.";
                $selectedJobSeekerId = 0;

            } catch (mysqli_sql_exception $exception) {
                $error = "The application could not be submitted.";
            }
        }

        $jobSeekerQuery->close();
    }
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

    <title>Apply for Job</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >
</head>

<body>

    <div class="registration-container">

        <a href="job-search.php">
            &larr; Back to Job Search
        </a>

        <h1>Apply for Job</h1>

        <p>
            Review the job information and select your Job Seeker
            profile.
        </p>

        <?php if ($success !== ""): ?>

            <div class="success-message">
                <?= htmlspecialchars($success) ?>
            </div>

        <?php endif; ?>

        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <?php if ($job): ?>

            <div class="job-card">

                <h2>
                    <?= htmlspecialchars($job["job_title"]) ?>
                </h2>

                <h3>
                    <?= htmlspecialchars($job["company_name"]) ?>
                </h3>

                <p>
                    <strong>Location:</strong>
                    <?= htmlspecialchars(
                        $job["location"] ?: "Not specified"
                    ) ?>
                </p>

                <p>
                    <strong>Job Type:</strong>
                    <?= htmlspecialchars($job["job_type"]) ?>
                </p>

                <p>
                    <strong>Salary:</strong>

                    <?php if (
                        $job["salary_min"] !== null &&
                        $job["salary_max"] !== null
                    ): ?>

                        RM <?= number_format(
                            (float) $job["salary_min"],
                            2
                        ) ?>
                        -
                        RM <?= number_format(
                            (float) $job["salary_max"],
                            2
                        ) ?>

                    <?php else: ?>

                        Not specified

                    <?php endif; ?>
                </p>

                <p>
                    <strong>Description:</strong><br>

                    <?= nl2br(
                        htmlspecialchars($job["job_description"])
                    ) ?>
                </p>

                <p>
                    <strong>Requirements:</strong><br>

                    <?= nl2br(
                        htmlspecialchars($job["job_requirements"])
                    ) ?>
                </p>

            </div>

            <form method="POST" action="">

                <input
                    type="hidden"
                    name="job_id"
                    value="<?= (int) $job["job_id"] ?>"
                >

                <div class="form-group">

                    <label for="job_seeker_id">
                        Job Seeker Profile *
                    </label>

                    <select
                        id="job_seeker_id"
                        name="job_seeker_id"
                        required
                    >
                        <option value="">
                            Select a Job Seeker
                        </option>

                        <?php foreach ($jobSeekers as $jobSeeker): ?>

                            <option
                                value="<?= (int) $jobSeeker["job_seeker_id"] ?>"
                                <?php if (
                                    $selectedJobSeekerId ===
                                    (int) $jobSeeker["job_seeker_id"]
                                ): ?>
                                    selected
                                <?php endif; ?>
                            >
                                <?= htmlspecialchars($jobSeeker["full_name"]) ?>
                            </option>

                        <?php endforeach; ?>
                    </select>

                </div>

                <button
                    type="submit"
                    class="register-button"
                >
                    Submit Application
                </button>

            </form>

        <?php endif; ?>

    </div>

</body>

</html>