<?php

require_once __DIR__ . "/../config/database.php";

$application = null;
$error = "";

$applicationId = filter_input(
    INPUT_GET,
    "application_id",
    FILTER_VALIDATE_INT
);

// Retrieve selected application
if (!$applicationId) {

    $error = "Invalid application selection.";

} else {

    $applicationQuery = $conn->prepare(
        "SELECT
            applications.application_id,
            applications.status,
            applications.applied_at,
            job_seekers.job_seeker_id,
            job_seekers.full_name,
            jobs.job_id,
            jobs.job_title,
            employers.employer_id,
            employers.company_name
         FROM applications
         INNER JOIN job_seekers
            ON applications.job_seeker_id =
               job_seekers.job_seeker_id
         INNER JOIN jobs
            ON applications.job_id =
               jobs.job_id
         INNER JOIN employers
            ON jobs.employer_id =
               employers.employer_id
         WHERE applications.application_id = ?"
    );

    $applicationQuery->bind_param(
        "i",
        $applicationId
    );

    $applicationQuery->execute();

    $applicationResult =
        $applicationQuery->get_result();

    $application =
        $applicationResult->fetch_assoc();

    $applicationQuery->close();

    if (!$application) {
        $error = "Application not found.";
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

    <title>Update Application Status</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <div class="status-update-container">

        <a href="view-applicants.php">
            &larr; Back to Applicants
        </a>

        <h1>Update Application Status</h1>

        <p>
            Review the application information and select
            a new recruitment status.
        </p>

        <?php if ($error !== ""): ?>

            <div class="error-message">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <?php if ($application !== null): ?>

            <div class="application-summary">

                <h2>Application Information</h2>

                <p>
                    <strong>Applicant:</strong>

                    <?= htmlspecialchars(
                        $application["full_name"]
                    ) ?>
                </p>

                <p>
                    <strong>Job:</strong>

                    <?= htmlspecialchars(
                        $application["job_title"]
                    ) ?>
                </p>

                <p>
                    <strong>Company:</strong>

                    <?= htmlspecialchars(
                        $application["company_name"]
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

                <p>
                    <strong>Current Status:</strong>

                    <span
                        class="status-badge status-<?=
                            strtolower(
                                htmlspecialchars(
                                    $application["status"]
                                )
                            )
                        ?>"
                    >
                        <?= htmlspecialchars(
                            $application["status"]
                        ) ?>
                    </span>

                </p>

            </div>


            <form
                method="POST"
                action=""
                class="status-update-form"
            >

                <input
                    type="hidden"
                    name="application_id"
                    value="<?=
                        (int) $application[
                            "application_id"
                        ]
                    ?>"
                >

                <div class="form-group">

                    <label for="status">
                        New Application Status *
                    </label>

                    <select
                        id="status"
                        name="status"
                        required
                    >

                        <option value="">
                            Select a Status
                        </option>

                        <option
                            value="Pending"
                            <?php if (
                                $application["status"] ===
                                "Pending"
                            ): ?>
                                selected
                            <?php endif; ?>
                        >
                            Pending
                        </option>

                        <option
                            value="Shortlisted"
                            <?php if (
                                $application["status"] ===
                                "Shortlisted"
                            ): ?>
                                selected
                            <?php endif; ?>
                        >
                            Shortlisted
                        </option>

                        <option
                            value="Rejected"
                            <?php if (
                                $application["status"] ===
                                "Rejected"
                            ): ?>
                                selected
                            <?php endif; ?>
                        >
                            Rejected
                        </option>

                    </select>

                </div>

                <button
                    type="submit"
                    class="register-button"
                    disabled
                >
                    Update Status
                </button>

                <p class="development-note">
                    Status updating will be enabled after
                    database update processing is implemented.
                </p>

            </form>

        <?php endif; ?>

    </div>

</body>

</html>