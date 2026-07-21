<?php

require_once __DIR__ . "/../config/database.php";

$employers = [];
$jobs = [];
$applicants = [];

$error = "";
$selectedJobTitle = "";

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

// Retrieve all employers
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

// Retrieve jobs belonging to selected employer
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

// Validate selected job and retrieve applicants
if ($selectedEmployerId && $selectedJobId) {

    // Confirm job belongs to selected employer
    $selectedJobQuery = $conn->prepare(
        "SELECT job_title
         FROM jobs
         WHERE job_id = ?
           AND employer_id = ?"
    );

    $selectedJobQuery->bind_param(
        "ii",
        $selectedJobId,
        $selectedEmployerId
    );

    $selectedJobQuery->execute();

    $selectedJobResult = $selectedJobQuery->get_result();
    $selectedJob = $selectedJobResult->fetch_assoc();

    $selectedJobQuery->close();

    if (!$selectedJob) {
        $error = "The selected job does not belong to this employer.";
    } else {
        $selectedJobTitle = $selectedJob["job_title"];

        // Retrieve applicants for selected job
        $applicantQuery = $conn->prepare(
            "SELECT
                applications.application_id,
                applications.status,
                applications.applied_at,
                job_seekers.job_seeker_id,
                job_seekers.full_name,
                job_seekers.phone,
                job_seekers.skills,
                job_seekers.education,
                job_seekers.experience_summary
             FROM applications
             INNER JOIN job_seekers
                ON applications.job_seeker_id =
                   job_seekers.job_seeker_id
             WHERE applications.job_id = ?
             ORDER BY applications.applied_at DESC"
        );

        $applicantQuery->bind_param(
            "i",
            $selectedJobId
        );

        $applicantQuery->execute();

        $applicantResult = $applicantQuery->get_result();

        while ($row = $applicantResult->fetch_assoc()) {
            $applicants[] = $row;
        }

        $applicantQuery->close();
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

        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

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
                    onchange="
                        document.getElementById('job_id').value = '';
                        this.form.submit();
                    "
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

        <?php if (
            $selectedEmployerId &&
            $selectedJobId &&
            $error === ""
        ): ?>

            <section class="applicant-list">

                <h2>
                    Applicants for:
                    <?= htmlspecialchars($selectedJobTitle) ?>
                </h2>

                <?php if (count($applicants) > 0): ?>

                    <?php foreach ($applicants as $applicant): ?>

                        <article class="applicant-card">

                            <h3>
                                <?= htmlspecialchars(
                                    $applicant["full_name"]
                                ) ?>
                            </h3>

                            <p>
                                <strong>Phone:</strong>

                                <?= htmlspecialchars(
                                    $applicant["phone"] !== ""
                                        ? $applicant["phone"]
                                        : "Not provided"
                                ) ?>
                            </p>

                            <p>
                                <strong>Status:</strong>

                                <?= htmlspecialchars(
                                    $applicant["status"]
                                ) ?>
                            </p>

                            <p>
                                <strong>Applied on:</strong>

                                <?= date(
                                    "d M Y, h:i A",
                                    strtotime(
                                        $applicant["applied_at"]
                                    )
                                ) ?>
                            </p>

                            <p>
                                <strong>Skills:</strong><br>

                                <?= nl2br(
                                    htmlspecialchars(
                                        $applicant["skills"] !== ""
                                            ? $applicant["skills"]
                                            : "Not provided"
                                    )
                                ) ?>
                            </p>

                            <p>
                                <strong>Education:</strong><br>

                                <?= nl2br(
                                    htmlspecialchars(
                                        $applicant["education"] !== ""
                                            ? $applicant["education"]
                                            : "Not provided"
                                    )
                                ) ?>
                            </p>

                            <p>
                                <strong>Experience:</strong><br>

                                <?= nl2br(
                                    htmlspecialchars(
                                        $applicant[
                                            "experience_summary"
                                        ] !== ""
                                            ? $applicant[
                                                "experience_summary"
                                            ]
                                            : "Not provided"
                                    )
                                ) ?>
                            </p>

                        </article>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="no-results">
                        <p>
                            No applicants found for this job.
                        </p>
                    </div>

                <?php endif; ?>

            </section>

        <?php endif; ?>

    </div>

</body>

</html>