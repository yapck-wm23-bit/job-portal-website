<?php

require_once __DIR__ . "/../config/database.php";

$employers = [];
$jobs = [];
$applicants = [];

$error = "";
$selectedJobTitle = "";

function getResumeLink(?string $resumePath): ?string
{
    if (empty($resumePath)) {
        return null;
    }

    // Only use the file name to prevent unsafe paths
    $fileName = basename($resumePath);

    $filePath = __DIR__
        . "/../uploads/resumes/"
        . $fileName;

    if (!is_file($filePath)) {
        return null;
    }

    return "../uploads/resumes/"
        . rawurlencode($fileName);
}

$resumeColumnAvailable = false;

// Check whether JPW-7 has added the resume_path column
$resumeColumnResult = $conn->query(
    "SHOW COLUMNS FROM job_seekers LIKE 'resume_path'"
);

if (
    $resumeColumnResult &&
    $resumeColumnResult->num_rows > 0
) {
    $resumeColumnAvailable = true;
}

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

    $resumeSelect = $resumeColumnAvailable
    ? ", job_seekers.resume_path"
    : ", NULL AS resume_path";

    if (!$selectedJob) {
        $error = "The selected job does not belong to this employer.";
    } else {
        $selectedJobTitle = $selectedJob["job_title"];

        // Retrieve applicants for selected job
        $applicantSql = "
            SELECT
                applications.application_id,
                applications.status,
                applications.applied_at,
                job_seekers.job_seeker_id,
                job_seekers.full_name,
                job_seekers.phone,
                job_seekers.skills,
                job_seekers.education,
                job_seekers.experience_summary,
                users.email
                $resumeSelect
            FROM applications
            INNER JOIN job_seekers
                ON applications.job_seeker_id =
                job_seekers.job_seeker_id
            INNER JOIN users
                ON job_seekers.user_id = users.user_id
            WHERE applications.job_id = ?
            ORDER BY applications.applied_at DESC
        ";

        $applicantQuery = $conn->prepare($applicantSql);

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
            count($jobs) === 0
        ): ?>

            <div class="no-results">

                <p>
                    No job postings were found for this Employer.
                </p>

            </div>

        <?php endif; ?>

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

                <p class="applicant-count">

                    <?php if (count($applicants) === 1): ?>

                        1 applicant found.

                    <?php else: ?>

                        <?= count($applicants) ?> applicants found.

                    <?php endif; ?>

                </p>

                <?php if (count($applicants) > 0): ?>

                    <?php foreach ($applicants as $applicant): ?>

                        <article class="applicant-card">

                            <h3>
                                <?= htmlspecialchars(
                                    $applicant["full_name"]
                                ) ?>
                            </h3>

                            <p>
                                <strong>Email:</strong>

                                <?= htmlspecialchars(
                                    $applicant["email"]
                                ) ?>
                            </p>

                            <p>
                                <strong>Phone:</strong>

                                <?= htmlspecialchars(
                                    !empty($applicant["phone"])
                                        ? $applicant["phone"]
                                        : "Not provided"
                                ) ?>
                            </p>

                            <?php
                                $statusClass = strtolower(
                                    preg_replace(
                                        "/[^a-zA-Z]/",
                                        "",
                                        $applicant["status"]
                                    )
                                );
                                ?>

                                <p>
                                    <strong>Status:</strong>

                                    <span
                                        class="status-badge status-<?= htmlspecialchars(
                                            $statusClass
                                        ) ?>"
                                    >
                                        <?= htmlspecialchars(
                                            $applicant["status"]
                                        ) ?>
                                    </span>
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
                                        !empty($applicant["skills"])
                                            ? $applicant["skills"]
                                            : "Not provided"
                                    )
                                ) ?>
                            </p>

                            <p>
                                <strong>Education:</strong><br>

                                <?= nl2br(
                                    htmlspecialchars(
                                        !empty($applicant["education"])
                                            ? $applicant["education"]
                                            : "Not provided"
                                    )
                                ) ?>
                            </p>

                            <p>
                                <strong>Experience:</strong><br>

                                <?= nl2br(
                                    htmlspecialchars(
                                        !empty(
                                            $applicant["experience_summary"]
                                        )
                                            ? $applicant["experience_summary"]
                                            : "Not provided"
                                    )
                                ) ?>
                            </p>

                            <?php
                                $resumeLink = getResumeLink(
                                    $applicant["resume_path"] ?? null
                                );
                                ?>

                                <div class="resume-section">

                                    <strong>Résumé:</strong>

                                    <?php if ($resumeLink !== null): ?>

                                        <div class="resume-actions">

                                            <a
                                                href="<?= htmlspecialchars(
                                                    $resumeLink,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ) ?>"
                                                class="resume-button"
                                                target="_blank"
                                                rel="noopener"
                                            >
                                                View Résumé
                                            </a>

                                            <a
                                                href="<?= htmlspecialchars(
                                                    $resumeLink,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ) ?>"
                                                class="resume-button secondary-button"
                                                download
                                            >
                                                Download Résumé
                                            </a>

                                        </div>

                                    <?php else: ?>

                                        <span class="resume-unavailable">
                                            No résumé uploaded
                                        </span>

                                    <?php endif; ?>

                                </div>

                            <div class="status-action">

                            <a
                                href="update-status.php?application_id=<?=
                                    (int) $applicant["application_id"]
                                ?>"
                                class="status-update-button"
                            >
                                Update Status
                            </a>

                            </div>
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