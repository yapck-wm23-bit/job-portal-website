<?php

require_once __DIR__ . "/../config/database.php";

$jobSeekers = [];

$selectedJobSeekerId = filter_input(
    INPUT_GET,
    "job_seeker_id",
    FILTER_VALIDATE_INT
);

// Retrieve Job Seeker profiles for demonstration
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


        <?php if ($selectedJobSeekerId): ?>

            <div class="application-placeholder">

                <h2>Submitted Applications</h2>

                <p>
                    Your submitted applications will be displayed
                    here after database retrieval is implemented.
                </p>

            </div>

        <?php endif; ?>

    </div>

</body>

</html>