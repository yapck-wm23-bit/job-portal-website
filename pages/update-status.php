<?php

require_once __DIR__ . "/../config/database.php";

$applicationId = filter_input(
    INPUT_GET,
    "application_id",
    FILTER_VALIDATE_INT
);

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
            Select a new recruitment status for the applicant.
        </p>

        <?php if ($applicationId): ?>

            <div class="application-summary">

                <h2>Application Information</h2>

                <p>
                    <strong>Application ID:</strong>
                    <?= (int) $applicationId ?>
                </p>

                <p>
                    Applicant and job information will be displayed
                    here after database retrieval is implemented.
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
                    value="<?= (int) $applicationId ?>"
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

                        <option value="Pending">
                            Pending
                        </option>

                        <option value="Shortlisted">
                            Shortlisted
                        </option>

                        <option value="Rejected">
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
                    database processing is implemented.
                </p>

            </form>

        <?php else: ?>

            <div class="error-message">

                Invalid application selection.

            </div>

        <?php endif; ?>

    </div>

</body>

</html>