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
        full_name,
        resume_path
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

    <title>Upload Résumé</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <div class="resume-upload-container">

        <a href="../index.php">
            &larr; Back to Home
        </a>

        <h1>Upload Résumé</h1>

        <p>
            Upload a PDF version of your résumé so that Employers
            can view and download it.
        </p>

        <?php if (count($jobSeekers) > 0): ?>

            <form
                method="POST"
                action=""
                enctype="multipart/form-data"
                class="resume-upload-form"
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

                <div class="form-group">

                    <label for="resume">
                        Résumé File *
                    </label>

                    <input
                        type="file"
                        id="resume"
                        name="resume"
                        accept=".pdf,application/pdf"
                        required
                    >

                    <small class="form-help">
                        Only PDF files are allowed. Maximum file size:
                        5 MB.
                    </small>

                </div>

                <div class="resume-information">

                    <h2>Upload Requirements</h2>

                    <ul>
                        <li>The résumé must be in PDF format.</li>
                        <li>The file must not exceed 5 MB.</li>
                        <li>
                            Uploading a new résumé will replace the
                            existing résumé.
                        </li>
                    </ul>

                </div>

                <button
                    type="submit"
                    class="register-button"
                    disabled
                >
                    Upload Résumé
                </button>

                <p class="development-note">
                    Résumé upload processing will be enabled after
                    PDF validation is implemented.
                </p>

            </form>

        <?php else: ?>

            <div class="no-results">

                <p>
                    No Job Seeker profiles were found. Create a
                    Job Seeker profile before uploading a résumé.
                </p>

            </div>

        <?php endif; ?>

    </div>

</body>

</html>