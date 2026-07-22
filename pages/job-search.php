<?php

require_once "../config/database.php";

$keyword = trim($_GET["keyword"] ?? "");
$jobs = null;

if ($keyword !== "") {

    $searchKeyword = "%" . $keyword . "%";

    $searchQuery = $conn->prepare(
        "SELECT
            jobs.job_id,
            jobs.job_title,
            jobs.job_description,
            jobs.job_requirements,
            jobs.location,
            jobs.salary_min,
            jobs.salary_max,
            jobs.job_type,
            jobs.created_at,
            employers.company_name
        FROM jobs
        INNER JOIN employers
            ON jobs.employer_id = employers.employer_id
        WHERE
            jobs.job_title LIKE ?
            OR jobs.job_description LIKE ?
            OR jobs.job_requirements LIKE ?
        ORDER BY jobs.created_at DESC"
    );

    $searchQuery->bind_param(
        "sss",
        $searchKeyword,
        $searchKeyword,
        $searchKeyword
    );

    $searchQuery->execute();

    $jobs = $searchQuery->get_result();

} else {

    // Display all jobs when no keyword is entered
    $jobs = $conn->query(
        "SELECT
            jobs.job_id,
            jobs.job_title,
            jobs.job_description,
            jobs.job_requirements,
            jobs.location,
            jobs.salary_min,
            jobs.salary_max,
            jobs.job_type,
            jobs.created_at,
            employers.company_name
        FROM jobs
        INNER JOIN employers
            ON jobs.employer_id = employers.employer_id
        ORDER BY jobs.created_at DESC"
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

    <title>Search Jobs</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <div class="job-search-container">

        <h1>Search Jobs</h1>

        <p>
            Search for available job opportunities by keyword or job title.
        </p>

        <!-- Search Form -->
        <form
            method="GET"
            action=""
            class="search-form"
        >

            <input
                type="text"
                name="keyword"
                placeholder="Enter job title or keyword"
                value="<?php echo htmlspecialchars($keyword); ?>"
            >

            <button type="submit">
                Search
            </button>

        </form>


        <!-- Search Information -->
        <?php if ($keyword !== ""): ?>

            <h2>
                Search Results for:
                "<?php echo htmlspecialchars($keyword); ?>"
            </h2>

        <?php else: ?>

            <h2>
                Available Jobs
            </h2>

        <?php endif; ?>


        <!-- Job Results -->
        <div class="job-results">

            <?php if ($jobs && $jobs->num_rows > 0): ?>

                <?php while ($job = $jobs->fetch_assoc()): ?>

                    <div class="job-card">

                        <h2>
                            <?php
                                echo htmlspecialchars(
                                    $job["job_title"]
                                );
                            ?>
                        </h2>

                        <h3>
                            <?php
                                echo htmlspecialchars(
                                    $job["company_name"]
                                );
                            ?>
                        </h3>


                        <p>
                            <strong>Location:</strong>

                            <?php
                                echo htmlspecialchars(
                                    $job["location"] !== ""
                                        ? $job["location"]
                                        : "Not specified"
                                );
                            ?>
                        </p>


                        <p>
                            <strong>Job Type:</strong>

                            <?php
                                echo htmlspecialchars(
                                    $job["job_type"]
                                );
                            ?>
                        </p>


                        <p>
                            <strong>Salary:</strong>

                            <?php

                            if (
                                $job["salary_min"] !== null &&
                                $job["salary_max"] !== null
                            ) {

                                echo "RM "
                                    . number_format(
                                        $job["salary_min"],
                                        2
                                    )
                                    . " - RM "
                                    . number_format(
                                        $job["salary_max"],
                                        2
                                    );

                            } elseif (
                                $job["salary_min"] !== null
                            ) {

                                echo "From RM "
                                    . number_format(
                                        $job["salary_min"],
                                        2
                                    );

                            } elseif (
                                $job["salary_max"] !== null
                            ) {

                                echo "Up to RM "
                                    . number_format(
                                        $job["salary_max"],
                                        2
                                    );

                            } else {

                                echo "Not specified";
                            }

                            ?>

                        </p>


                        <p>
                            <strong>Description:</strong>
                        </p>

                        <p>
                            <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $job["job_description"]
                                    )
                                );
                            ?>
                        </p>


                        <p>
                            <strong>Requirements:</strong>
                        </p>

                        <p>
                            <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $job["job_requirements"]
                                    )
                                );
                            ?>
                        </p>


                        <p>
                            <small>
                                Posted on:
                                <?php
                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $job["created_at"]
                                        )
                                    );
                                ?>
                            </small>
                        </p>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="no-results">

                    <p>
                        No job listings found.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</body>

</html>