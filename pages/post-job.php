<?php

require_once "../config/database.php";

$error = "";
$success = "";

$employerId = "";
$jobTitle = "";
$jobDescription = "";
$jobRequirements = "";
$location = "";
$salaryMin = "";
$salaryMax = "";
$jobType = "";

// Get all employers for the dropdown
$employers = $conn->query(
    "SELECT employer_id, company_name
     FROM employers
     ORDER BY company_name ASC"
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employerId = $_POST["employer_id"] ?? "";
    $jobTitle = trim($_POST["job_title"] ?? "");
    $jobDescription = trim($_POST["job_description"] ?? "");
    $jobRequirements = trim($_POST["job_requirements"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $salaryMin = trim($_POST["salary_min"] ?? "");
    $salaryMax = trim($_POST["salary_max"] ?? "");
    $jobType = $_POST["job_type"] ?? "";

    if (
        $employerId === "" ||
        $jobTitle === "" ||
        $jobDescription === "" ||
        $jobRequirements === "" ||
        $jobType === ""
    ) {
        $error = "Please fill in all required fields.";
    }

    elseif (
        $salaryMin !== "" &&
        !is_numeric($salaryMin)
    ) {
        $error = "Minimum salary must be a valid number.";
    }

    elseif (
        $salaryMax !== "" &&
        !is_numeric($salaryMax)
    ) {
        $error = "Maximum salary must be a valid number.";
    }

    elseif (
        $salaryMin !== "" &&
        $salaryMax !== "" &&
        (float) $salaryMin > (float) $salaryMax
    ) {
        $error = "Minimum salary cannot be higher than maximum salary.";
    }

    else {

        // Convert empty salary values to NULL
        $salaryMinValue =
            $salaryMin === "" ? null : (float) $salaryMin;

        $salaryMaxValue =
            $salaryMax === "" ? null : (float) $salaryMax;

        $insertJob = $conn->prepare(
            "INSERT INTO jobs
            (
                employer_id,
                job_title,
                job_description,
                job_requirements,
                location,
                salary_min,
                salary_max,
                job_type
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $insertJob->bind_param(
            "issssdds",
            $employerId,
            $jobTitle,
            $jobDescription,
            $jobRequirements,
            $location,
            $salaryMinValue,
            $salaryMaxValue,
            $jobType
        );

        if ($insertJob->execute()) {

            $success = "Job listing posted successfully!";

            $employerId = "";
            $jobTitle = "";
            $jobDescription = "";
            $jobRequirements = "";
            $location = "";
            $salaryMin = "";
            $salaryMax = "";
            $jobType = "";

        } else {

            $error = "Unable to post the job listing.";
        }

        $insertJob->close();
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

    <title>Post a Job</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <div class="registration-container">

        <h1>Post a New Job</h1>

        <p>
            Enter the details of the job vacancy.
        </p>

        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <?php if ($success !== ""): ?>

            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>

        <?php endif; ?>


        <form method="POST" action="">

            <!-- Employer -->
            <div class="form-group">

                <label for="employer_id">
                    Employer *
                </label>

                <select
                    id="employer_id"
                    name="employer_id"
                    required
                >

                    <option value="">
                        Select Employer
                    </option>

                    <?php while ($employer = $employers->fetch_assoc()): ?>

                        <option
                            value="<?php
                                echo $employer["employer_id"];
                            ?>"
                            <?php
                                if (
                                    $employerId ==
                                    $employer["employer_id"]
                                ) {
                                    echo "selected";
                                }
                            ?>
                        >
                            <?php
                                echo htmlspecialchars(
                                    $employer["company_name"]
                                );
                            ?>
                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- Job Title -->
            <div class="form-group">

                <label for="job_title">
                    Job Title *
                </label>

                <input
                    type="text"
                    id="job_title"
                    name="job_title"
                    value="<?php
                        echo htmlspecialchars($jobTitle);
                    ?>"
                    required
                >

            </div>


            <!-- Job Description -->
            <div class="form-group">

                <label for="job_description">
                    Job Description *
                </label>

                <textarea
                    id="job_description"
                    name="job_description"
                    rows="6"
                    required
                ><?php
                    echo htmlspecialchars($jobDescription);
                ?></textarea>

            </div>


            <!-- Job Requirements -->
            <div class="form-group">

                <label for="job_requirements">
                    Job Requirements *
                </label>

                <textarea
                    id="job_requirements"
                    name="job_requirements"
                    rows="5"
                    required
                ><?php
                    echo htmlspecialchars($jobRequirements);
                ?></textarea>

            </div>


            <!-- Location -->
            <div class="form-group">

                <label for="location">
                    Location
                </label>

                <input
                    type="text"
                    id="location"
                    name="location"
                    value="<?php
                        echo htmlspecialchars($location);
                    ?>"
                >

            </div>


            <!-- Minimum Salary -->
            <div class="form-group">

                <label for="salary_min">
                    Minimum Salary
                </label>

                <input
                    type="number"
                    id="salary_min"
                    name="salary_min"
                    min="0"
                    step="0.01"
                    value="<?php
                        echo htmlspecialchars($salaryMin);
                    ?>"
                >

            </div>


            <!-- Maximum Salary -->
            <div class="form-group">

                <label for="salary_max">
                    Maximum Salary
                </label>

                <input
                    type="number"
                    id="salary_max"
                    name="salary_max"
                    min="0"
                    step="0.01"
                    value="<?php
                        echo htmlspecialchars($salaryMax);
                    ?>"
                >

            </div>


            <!-- Job Type -->
            <div class="form-group">

                <label for="job_type">
                    Job Type *
                </label>

                <select
                    id="job_type"
                    name="job_type"
                    required
                >

                    <option value="">
                        Select Job Type
                    </option>

                    <option
                        value="Full-time"
                        <?php
                            if ($jobType === "Full-time") {
                                echo "selected";
                            }
                        ?>
                    >
                        Full-time
                    </option>

                    <option
                        value="Part-time"
                        <?php
                            if ($jobType === "Part-time") {
                                echo "selected";
                            }
                        ?>
                    >
                        Part-time
                    </option>

                    <option
                        value="Internship"
                        <?php
                            if ($jobType === "Internship") {
                                echo "selected";
                            }
                        ?>
                    >
                        Internship
                    </option>

                    <option
                        value="Contract"
                        <?php
                            if ($jobType === "Contract") {
                                echo "selected";
                            }
                        ?>
                    >
                        Contract
                    </option>

                </select>

            </div>


            <button
                type="submit"
                class="register-button"
            >
                Post Job
            </button>

        </form>

    </div>

</body>

</html>