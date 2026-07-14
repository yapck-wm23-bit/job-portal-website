<?php

require_once "../config/database.php";

$error = "";
$success = "";

// Check whether the registration form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get form values
    $fullName = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";
    $phone = trim($_POST["phone"] ?? "");
    $skills = trim($_POST["skills"] ?? "");
    $education = trim($_POST["education"] ?? "");
    $experienceSummary = trim($_POST["experience_summary"] ?? "");

    // Validate required fields
    if ($fullName === "" || $email === "" || $password === "" || $confirmPassword === "") {
        $error = "Please fill in all required fields.";
    }

    // Validate email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }

    // Check whether passwords match
    elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    }

    // Check password length
    elseif (strlen($password) < 8) {
        $error = "Password must contain at least 8 characters.";
    }

    else {

        // Check whether the email already exists
        $checkEmail = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );

        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();

        $result = $checkEmail->get_result();

        if ($result->num_rows > 0) {
            $error = "This email address is already registered.";
        } else {

            // Hash the password before storing it
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $role = "job_seeker";

            // Start transaction
            $conn->begin_transaction();

            try {

                // Insert account into users table
                $insertUser = $conn->prepare(
                    "INSERT INTO users (email, password, role)
                     VALUES (?, ?, ?)"
                );

                $insertUser->bind_param(
                    "sss",
                    $email,
                    $hashedPassword,
                    $role
                );

                $insertUser->execute();

                // Get the new user's ID
                $userId = $conn->insert_id;

                // Insert professional profile
                $insertProfile = $conn->prepare(
                    "INSERT INTO job_seekers
                    (
                        user_id,
                        full_name,
                        phone,
                        skills,
                        education,
                        experience_summary
                    )
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                $insertProfile->bind_param(
                    "isssss",
                    $userId,
                    $fullName,
                    $phone,
                    $skills,
                    $education,
                    $experienceSummary
                );

                $insertProfile->execute();

                // Save both database changes
                $conn->commit();

                $success = "Registration successful!";

                // Clear form values after successful registration
                $fullName = "";
                $email = "";
                $phone = "";
                $skills = "";
                $education = "";
                $experienceSummary = "";

            } catch (Exception $e) {

                // Cancel all changes if an error occurs
                $conn->rollback();

                $error = "Registration failed. Please try again.";
            }
        }

        $checkEmail->close();
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

    <title>Job Seeker Registration</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >
</head>

<body>

    <div class="registration-container">

        <h1>Job Seeker Registration</h1>

        <p>
            Create your account and professional profile.
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


        <form
            method="POST"
            action=""
        >

            <!-- Full Name -->
            <div class="form-group">

                <label for="full_name">
                    Full Name *
                </label>

                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    value="<?php
                        echo htmlspecialchars(
                            $fullName ?? ""
                        );
                    ?>"
                    required
                >

            </div>


            <!-- Email -->
            <div class="form-group">

                <label for="email">
                    Email Address *
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php
                        echo htmlspecialchars(
                            $email ?? ""
                        );
                    ?>"
                    required
                >

            </div>


            <!-- Password -->
            <div class="form-group">

                <label for="password">
                    Password *
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >

            </div>


            <!-- Confirm Password -->
            <div class="form-group">

                <label for="confirm_password">
                    Confirm Password *
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    required
                >

            </div>


            <!-- Phone -->
            <div class="form-group">

                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="text"
                    id="phone"
                    name="phone"
                    value="<?php
                        echo htmlspecialchars(
                            $phone ?? ""
                        );
                    ?>"
                >

            </div>


            <!-- Skills -->
            <div class="form-group">

                <label for="skills">
                    Skills
                </label>

                <textarea
                    id="skills"
                    name="skills"
                    rows="4"
                ><?php
                    echo htmlspecialchars(
                        $skills ?? ""
                    );
                ?></textarea>

            </div>


            <!-- Education -->
            <div class="form-group">

                <label for="education">
                    Education
                </label>

                <textarea
                    id="education"
                    name="education"
                    rows="4"
                ><?php
                    echo htmlspecialchars(
                        $education ?? ""
                    );
                ?></textarea>

            </div>


            <!-- Experience -->
            <div class="form-group">

                <label for="experience_summary">
                    Experience Summary
                </label>

                <textarea
                    id="experience_summary"
                    name="experience_summary"
                    rows="5"
                ><?php
                    echo htmlspecialchars(
                        $experienceSummary ?? ""
                    );
                ?></textarea>

            </div>


            <button
                type="submit"
                class="register-button"
            >
                Register
            </button>

        </form>

    </div>

</body>

</html>