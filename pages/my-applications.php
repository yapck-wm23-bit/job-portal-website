<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__
    . "/../includes/application_history.php";

$jobSeekers = [];
$applications = [];
$error = "";
$selectedJobSeekerName = "";

$selectedJobSeekerId = filter_input(
    INPUT_GET,
    "job_seeker_id",
    FILTER_VALIDATE_INT
);

// Retrieve Job Seeker profiles
$jobSeekerResult = $conn->query(
    "SELECT
        job_seeker_id,
        full_name
     FROM job_seekers
     ORDER BY full_name ASC"
);

if ($jobSeekerResult) {
    while ($row = $jobSeekerResult->fetch_assoc()) {
        $jobSeekers[] = $row;
    }
}

// Retrieve applications belonging to the selected Job Seeker
if ($selectedJobSeekerId) {

    // Confirm that the Job Seeker exists
    $jobSeekerQuery = $conn->prepare(
        "SELECT
            job_seeker_id,
            full_name
         FROM job_seekers
         WHERE job_seeker_id = ?"
    );

    $jobSeekerQuery->bind_param(
        "i",
        $selectedJobSeekerId
    );

    $jobSeekerQuery->execute();

    $selectedResult = $jobSeekerQuery->get_result();
    $selectedJobSeeker = $selectedResult->fetch_assoc();

    $jobSeekerQuery->close();

    if (!$selectedJobSeeker) {

        $error = "The selected Job Seeker profile was not found.";

    } else {

        $selectedJobSeekerName =
            $selectedJobSeeker["full_name"];

        $applicationQuery = $conn->prepare(
            "SELECT
                applications.application_id,
                applications.applied_at,
                jobs.job_id,
                jobs.job_title,
                jobs.location,
                jobs.job_type,
                employers.company_name
             FROM applications
             INNER JOIN jobs
                ON applications.job_id = jobs.job_id
             INNER JOIN employers
                ON jobs.employer_id = employers.employer_id
             WHERE applications.job_seeker_id = ?
             ORDER BY applications.applied_at DESC"
        );

        $applicationQuery->bind_param(
            "i",
            $selectedJobSeekerId
        );

        $applicationQuery->execute();

        $applicationResult =
            $applicationQuery->get_result();

        while ($row = $applicationResult->fetch_assoc()) {
            $applications[] = $row;
        }

        $applicationQuery->close();
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

    <title>My Applications</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <div class="my-applications-container">

        <a href="../index.php">
            &larr; Back to Home
        </a>

        <h1>My Applications</h1>

        <p>
            View the jobs that you have previously applied for.
        </p>

        <?php if ($error !== ""): ?>

        <div class="error-message">
            <?= htmlspecialchars($error) ?>
        </div>

        <?php endif; ?>

        <?php if (count($jobSeekers) > 0): ?>

            <form
                method="GET"
                action=""
                class="application-filter-form"
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
                                value="<?=
                                    (int) $jobSeeker["job_seeker_id"]
                                ?>"
                                <?php if (
                                    $selectedJobSeekerId ===
                                    (int) $jobSeeker["job_seeker_id"]
                                ): ?>
                                    selected
                                <?php endif; ?>
                            >
                                <?= htmlspecialchars(
                                    $jobSeeker["full_name"]
                                ) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <button
                    type="submit"
                    class="register-button"
                >
                    View My Applications
                </button>

            </form>

        <?php else: ?>

            <div class="no-results">

                <p>
                    No Job Seeker profiles were found.
                </p>

            </div>

        <?php endif; ?>


        <?php if (
            $selectedJobSeekerId &&
            $error === ""
        ): ?>

    <section class="submitted-applications">

        <h2>
            Submitted Applications
        </h2>

        <p class="application-owner">
            Job Seeker:
            <strong>
                <?= htmlspecialchars(
                    $selectedJobSeekerName
                ) ?>
            </strong>
        </p>

        <p class="application-count">

            <?= htmlspecialchars(
                getApplicationCountMessage(
                    count($applications)
                )
            ) ?>

        </p>

        <?php if (
            hasSubmittedApplications($applications)
        ): ?>

            <?php foreach (
                $applications as $index => $application
            ): ?>

                <article class="my-application-card">

                <p class="application-number">
                    Application #<?= $index + 1 ?>
                </p>

                    <h3>
                        <?= htmlspecialchars(
                            $application["job_title"]
                        ) ?>
                    </h3>

                    <p>
                        <strong>Application ID:</strong>
                        <?= (int) $application["application_id"] ?>
                    </p>

                    <p>
                        <strong>Company:</strong>

                        <?= htmlspecialchars(
                            $application["company_name"]
                        ) ?>
                    </p>

                    <p>
                        <strong>Location:</strong>

                        <?= htmlspecialchars(
                            !empty($application["location"])
                                ? $application["location"]
                                : "Not specified"
                        ) ?>
                    </p>

                    <p>
                        <strong>Job Type:</strong>

                        <?= htmlspecialchars(
                            $application["job_type"]
                        ) ?>
                    </p>

                    <p>
                        <strong>Applied On:</strong>

                        <?= date(
                            "d M Y, h:i A",
                            strtotime(
                                $application["applied_at"]
                            )
                        ) ?>
                    </p>

                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="no-results">

                <h3>No Applications Yet</h3>

                <p>
                    This Job Seeker has not submitted any
                    job applications.
                </p>

                <a
                    href="job-search.php"
                    class="apply-button"
                >
                    Search Jobs
                </a>

            </div>

        <?php endif; ?>

    </section>

<?php endif; ?>

    </div>

</body>

</html>