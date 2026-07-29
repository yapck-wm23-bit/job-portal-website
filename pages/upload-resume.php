<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . "/../config/database.php";

$jobSeekers = [];
$error = "";
$success = "";

$maximumFileSize = 5 * 1024 * 1024;

$selectedJobSeekerId = 0;

function getResumeUrl(?string $resumePath): ?string
{
    if (empty($resumePath)) {
        return null;
    }

    $fileName = basename($resumePath);

    $absolutePath =
        __DIR__
        . "/../uploads/resumes/"
        . $fileName;

    if (!is_file($absolutePath)) {
        return null;
    }

    return "../uploads/resumes/"
        . rawurlencode($fileName);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $selectedJobSeekerId = (int) filter_input(
        INPUT_POST,
        "job_seeker_id",
        FILTER_VALIDATE_INT
    );
} else {
    $selectedJobSeekerId = (int) filter_input(
        INPUT_GET,
        "job_seeker_id",
        FILTER_VALIDATE_INT
    );
}

// Process résumé upload
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($selectedJobSeekerId <= 0) {

        $error = "Please select a valid Job Seeker profile.";

    } else {

        // Confirm that the Job Seeker exists
        $jobSeekerQuery = $conn->prepare(
            "SELECT
                job_seeker_id,
                full_name,
                resume_path
            FROM job_seekers
            WHERE job_seeker_id = ?"
        );

        $jobSeekerQuery->bind_param(
            "i",
            $selectedJobSeekerId
        );

        $jobSeekerQuery->execute();

        $jobSeekerResult = $jobSeekerQuery->get_result();
        $selectedJobSeeker = $jobSeekerResult->fetch_assoc();

        $jobSeekerQuery->close();

        if (!$selectedJobSeeker) {

            $error = "The selected Job Seeker profile was not found.";

        } elseif (
            !isset($_FILES["resume"]) ||
            !is_array($_FILES["resume"])
        ) {

            $error = "Please select a résumé file.";

        } else {

            $uploadedFile = $_FILES["resume"];
            $uploadError = $uploadedFile["error"];

            if ($uploadError === UPLOAD_ERR_NO_FILE) {

                $error = "Please select a résumé file.";

            } elseif ($uploadError === UPLOAD_ERR_INI_SIZE) {

                $error = "The résumé exceeds the server upload limit.";

            } elseif ($uploadError === UPLOAD_ERR_FORM_SIZE) {

                $error = "The résumé file is too large.";

            } elseif ($uploadError !== UPLOAD_ERR_OK) {

                $error = "The résumé could not be uploaded.";

            } elseif ($uploadedFile["size"] <= 0) {

                $error = "The selected résumé file is empty.";

            } elseif ($uploadedFile["size"] > $maximumFileSize) {

                $error = "The résumé must not exceed 5 MB.";

            } elseif (
                !is_uploaded_file(
                    $uploadedFile["tmp_name"]
                )
            ) {

                $error = "The uploaded résumé file is invalid.";

            } else {

                $originalFileName = $uploadedFile["name"];

                $fileExtension = strtolower(
                    pathinfo(
                        $originalFileName,
                        PATHINFO_EXTENSION
                    )
                );

                $fileInfo = new finfo(FILEINFO_MIME_TYPE);

                $mimeType = $fileInfo->file(
                    $uploadedFile["tmp_name"]
                );

                $allowedMimeTypes = [
                    "application/pdf",
                    "application/x-pdf"
                ];

                if ($fileExtension !== "pdf") {

                    $error = "Only PDF résumé files are allowed.";

                } elseif (
                    !in_array(
                        $mimeType,
                        $allowedMimeTypes,
                        true
                    )
                ) {

                    $error = "The selected file is not a valid PDF.";

                } else {

                    // Check the PDF file signature
                    $fileHandle = fopen(
                        $uploadedFile["tmp_name"],
                        "rb"
                    );

                    $fileSignature = "";

                    if ($fileHandle !== false) {
                        $fileSignature = fread($fileHandle, 5);
                        fclose($fileHandle);
                    }

                    if ($fileSignature !== "%PDF-") {

                        $error = "The selected file is not a valid PDF.";

                    } else {

                        $uploadDirectory =
                            __DIR__ . "/../uploads/resumes/";

                        if (!is_dir($uploadDirectory)) {

                            $directoryCreated = mkdir(
                                $uploadDirectory,
                                0755,
                                true
                            );

                            if (!$directoryCreated) {
                                $error =
                                    "The résumé upload folder "
                                    . "could not be created.";
                            }
                        }

                        if (
                            $error === "" &&
                            !is_writable($uploadDirectory)
                        ) {

                            $error =
                                "The résumé upload folder is "
                                . "not writable.";
                        }

                        if ($error === "") {

                            try {

                                $newFileName =
                                    "resume_"
                                    . $selectedJobSeekerId
                                    . "_"
                                    . bin2hex(random_bytes(8))
                                    . ".pdf";

                                $destinationPath =
                                    $uploadDirectory
                                    . $newFileName;

                                $fileMoved = move_uploaded_file(
                                    $uploadedFile["tmp_name"],
                                    $destinationPath
                                );

                                if (!$fileMoved) {

                                    $error =
                                        "The résumé file could not "
                                        . "be saved.";

                                } else {

                                    try {

                                        $updateQuery = $conn->prepare(
                                            "UPDATE job_seekers
                                             SET resume_path = ?
                                             WHERE job_seeker_id = ?"
                                        );

                                        $updateQuery->bind_param(
                                            "si",
                                            $newFileName,
                                            $selectedJobSeekerId
                                        );

                                        $updateQuery->execute();
                                        $updateQuery->close();

                                        // Delete the previous résumé only after the
                                        // new file and database record are successful
                                        $previousResume =
                                            $selectedJobSeeker["resume_path"] ?? null;

                                        if (!empty($previousResume)) {

                                            $previousFileName = basename(
                                                $previousResume
                                            );

                                            $previousFilePath =
                                                $uploadDirectory
                                                . $previousFileName;

                                            if (
                                                $previousFileName !== $newFileName &&
                                                is_file($previousFilePath)
                                            ) {
                                                unlink($previousFilePath);
                                            }
                                        }

                                        $selectedJobSeeker["resume_path"] =
                                            $newFileName;

                                        $success =
                                            empty($previousResume)
                                                ? "Résumé uploaded successfully."
                                                : "Résumé replaced successfully.";

                                    } catch (
                                        mysqli_sql_exception $exception
                                    ) {

                                        // Remove the uploaded file when
                                        // the database update fails
                                        if (is_file($destinationPath)) {
                                            unlink($destinationPath);
                                        }

                                        $error =
                                            "The résumé information "
                                            . "could not be saved.";
                                    }
                                }

                            } catch (Exception $exception) {

                                $error =
                                    "A secure résumé filename "
                                    . "could not be generated.";
                            }
                        }
                    }
                }
            }
        }
    }
}

// Retrieve Job Seeker profiles
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

$selectedProfile = null;

foreach ($jobSeekers as $jobSeeker) {

    if (
        (int) $jobSeeker["job_seeker_id"] ===
        $selectedJobSeekerId
    ) {
        $selectedProfile = $jobSeeker;
        break;
    }
}

$currentResumeUrl = null;

if ($selectedProfile !== null) {
    $currentResumeUrl = getResumeUrl(
        $selectedProfile["resume_path"] ?? null
    );
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
                        onchange="
                            if (this.value !== '') {
                                window.location.href =
                                    'upload-resume.php?job_seeker_id='
                                    + encodeURIComponent(this.value);
                            }
                        "
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

                <?php if ($selectedProfile !== null): ?>

                <div class="current-resume-panel">

                    <h2>Current Résumé</h2>

                    <?php if ($currentResumeUrl !== null): ?>

                        <p>
                            A résumé has already been uploaded for
                            <?= htmlspecialchars(
                                $selectedProfile["full_name"]
                            ) ?>.
                        </p>

                        <div class="resume-actions">

                            <a
                                href="<?= htmlspecialchars(
                                    $currentResumeUrl,
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
                                    $currentResumeUrl,
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>"
                                class="resume-button secondary-button"
                                download
                            >
                                Download Résumé
                            </a>

                        </div>

                        <p class="replacement-warning">
                            Uploading another PDF will replace this résumé.
                        </p>

                    <?php else: ?>

                        <p>
                            No résumé has been uploaded for this profile.
                        </p>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

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
                            The file will be saved using a secure,
                            unique filename.
                        </li>
                    </ul>

                </div>

                <button
                    type="submit"
                    class="register-button"
                >
                    <?= $currentResumeUrl !== null
                        ? "Replace Résumé"
                        : "Upload Résumé" ?>
                </button>

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