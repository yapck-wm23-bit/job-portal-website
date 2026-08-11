<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__
    . "/../includes/notification_helper.php";

$jobSeekers = [];
$notifications = [];
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

// Retrieve notifications for selected Job Seeker
if ($selectedJobSeekerId) {

    // Confirm Job Seeker exists
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

    $selectedResult =
        $jobSeekerQuery->get_result();

    $selectedJobSeeker =
        $selectedResult->fetch_assoc();

    $jobSeekerQuery->close();

    if (!$selectedJobSeeker) {

        $error =
            "The selected Job Seeker profile was not found.";

    } else {

        $selectedJobSeekerName =
            $selectedJobSeeker["full_name"];

        $notificationQuery = $conn->prepare(
            "SELECT
                notifications.notification_id,
                notifications.message,
                notifications.is_read,
                notifications.created_at,
                applications.application_id,
                jobs.job_id,
                jobs.job_title,
                employers.company_name
             FROM notifications
             INNER JOIN applications
                ON notifications.application_id =
                   applications.application_id
             INNER JOIN jobs
                ON applications.job_id =
                   jobs.job_id
             INNER JOIN employers
                ON jobs.employer_id =
                   employers.employer_id
             WHERE notifications.job_seeker_id = ?
             ORDER BY notifications.created_at DESC"
        );

        $notificationQuery->bind_param(
            "i",
            $selectedJobSeekerId
        );

        $notificationQuery->execute();

        $notificationResult =
            $notificationQuery->get_result();

        while (
            $row = $notificationResult->fetch_assoc()
        ) {
            $notifications[] = $row;
        }

        $notificationQuery->close();
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

    <title>Notifications</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <div class="notifications-container">

        <a href="../index.php">
            &larr; Back to Home
        </a>

        <h1>Notifications</h1>

        <p>
            View notifications about changes to your
            job applications.
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
                class="notification-filter-form"
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
                    View Notifications
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

    <section class="notification-list">

        <h2>Application Notifications</h2>

        <p class="notification-owner">
            Job Seeker:
            <strong>
                <?= htmlspecialchars(
                    $selectedJobSeekerName
                ) ?>
            </strong>
        </p>

        <p class="notification-count">

            <?= htmlspecialchars(
                getNotificationCountMessage(
                    count($notifications)
                )
            ) ?>

        </p>

        <?php if (count($notifications) > 0): ?>

            <?php foreach (
                $notifications as $notification
            ): ?>

                <article
                    class="notification-card <?=
                        isUnreadNotification(
                            (int) $notification["is_read"]
                        )
                            ? "notification-unread"
                            : "notification-read"
                    ?>"
                >

                    <div class="notification-header">

                        <h3>
                            <?= htmlspecialchars(
                                $notification["job_title"]
                            ) ?>
                        </h3>

                        <?php if (
                            isUnreadNotification(
                                (int) $notification["is_read"]
                            )
                        ): ?>

                            <span class="unread-badge">
                                New
                            </span>

                        <?php endif; ?>

                    </div>

                    <p>
                        <strong>Company:</strong>

                        <?= htmlspecialchars(
                            $notification["company_name"]
                        ) ?>
                    </p>

                    <p class="notification-message">
                        <?= htmlspecialchars(
                            $notification["message"]
                        ) ?>
                    </p>

                    <p>
                        <strong>Received:</strong>

                        <?= date(
                            "d M Y, h:i A",
                            strtotime(
                                $notification["created_at"]
                            )
                        ) ?>
                    </p>

                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="no-results">

                <h3>No Notifications</h3>

                <p>
                    There are no application status
                    notifications for this Job Seeker.
                </p>

            </div>

        <?php endif; ?>

    </section>

<?php endif; ?>

    </div>

</body>

</html>