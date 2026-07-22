<?php
declare(strict_types=1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Job Portal Website</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            color: #222;
        }

        header {
            background-color: #1f2937;
            color: white;
            padding: 20px 8%;
        }

        header h1 {
            margin-bottom: 5px;
        }

        main {
            width: 85%;
            max-width: 1100px;
            margin: 50px auto;
        }

        .hero {
            text-align: center;
            margin-bottom: 40px;
        }

        .hero h2 {
            font-size: 32px;
            margin-bottom: 12px;
        }

        .hero p {
            color: #555;
            line-height: 1.6;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .feature-card {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }

        .feature-card h3 {
            margin-bottom: 12px;
        }

        .feature-card p {
            color: #555;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .feature-card a {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 5px;
        }

        .feature-card a:hover {
            background-color: #1d4ed8;
        }

        footer {
            text-align: center;
            margin-top: 50px;
            padding: 20px;
            color: #666;
        }
    </style>
</head>

<body>

<header>
    <h1>Job Portal Website</h1>
    <p>Sprint 1 Product Increment</p>
</header>

<main>
    <section class="hero">
        <h2>Find Your Next Opportunity</h2>

        <p>
            The Job Portal Website connects Job Seekers with Employers
            through account registration, job posting and job searching.
        </p>
    </section>

    <section class="feature-grid">
        <div class="feature-card">
            <h3>Job Seeker Registration</h3>

            <p>
                Register a Job Seeker account and create a professional
                profile.
            </p>

            <a href="pages/register.php">
                Register
            </a>
        </div>

        <div class="feature-card">
            <h3>Post a Job</h3>

            <p>
                Employers can create job listings containing the job
                description, requirements and salary range.
            </p>

            <a href="pages/post-job.php">
                Post Job
            </a>
        </div>

        <div class="feature-card">
            <h3>Search Jobs</h3>

            <p>
                Search available job listings using keywords and job
                titles.
            </p>

            <a href="pages/job-search.php">
                Search Jobs
            </a>
        </div>
    </section>

    <footer>
        <p>&copy; <?= date('Y') ?> Job Portal Website</p>
    </footer>
</main>

</body>
</html>