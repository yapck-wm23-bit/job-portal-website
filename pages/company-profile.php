<?php

require_once "../config/database.php";

$error = "";
$success = "";

$companyName = "";
$email = "";
$companyDescription = "";
$websiteLink = "";
$location = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $companyName = trim($_POST["company_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";
    $companyDescription = trim($_POST["company_description"] ?? "");
    $websiteLink = trim($_POST["website_link"] ?? "");
    $location = trim($_POST["location"] ?? "");

    if (
        $companyName === "" ||
        $email === "" ||
        $password === "" ||
        $confirmPassword === ""
    ) {
        $error = "Please fill in all required fields.";
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }

    elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    }

    elseif (strlen($password) < 8) {
        $error = "Password must contain at least 8 characters.";
    }

    elseif (
        $websiteLink !== "" &&
        !filter_var($websiteLink, FILTER_VALIDATE_URL)
    ) {
        $error = "Please enter a valid website URL.";
    }

    else {

        // Check whether email already exists
        $checkEmail = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );

        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();

        $result = $checkEmail->get_result();

        if ($result->num_rows > 0) {

            $error = "This email address is already registered.";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $role = "employer";

            $conn->begin_transaction();

            try {

                // Create employer user account
                $insertUser = $conn->prepare(
                    "INSERT INTO users
                    (email, password, role)
                    VALUES (?, ?, ?)"
                );

                $insertUser->bind_param(
                    "sss",
                    $email,
                    $hashedPassword,
                    $role
                );

                $insertUser->execute();

                $userId = $conn->insert_id;

                // Create company profile
                $insertEmployer = $conn->prepare(
                    "INSERT INTO employers
                    (
                        user_id,
                        company_name,
                        company_description,
                        website_link,
                        location
                    )
                    VALUES (?, ?, ?, ?, ?)"
                );

                $insertEmployer->bind_param(
                    "issss",
                    $userId,
                    $companyName,
                    $companyDescription,
                    $websiteLink,
                    $location
                );

                $insertEmployer->execute();

                $conn->commit();

                $success = "Company profile created successfully!";

                $companyName = "";
                $email = "";
                $companyDescription = "";
                $websiteLink = "";
                $location = "";

            } catch (Exception $e) {

                $conn->rollback();

                $error = "Unable to create company profile. Please try again.";
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

    <title>Employer Company Profile</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <div class="registration-container">

        <h1>Create Company Profile</h1>

        <p>
            Register your employer account and provide your company information.
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

            <div class="form-group">

                <label for="company_name">
                    Company Name *
                </label>

                <input
                    type="text"
                    id="company_name"
                    name="company_name"
                    value="<?php echo htmlspecialchars($companyName); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="email">
                    Company Email *
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($email); ?>"
                    required
                >

            </div>


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


            <div class="form-group">

                <label for="company_description">
                    Company Description
                </label>

                <textarea
                    id="company_description"
                    name="company_description"
                    rows="5"
                ><?php echo htmlspecialchars($companyDescription); ?></textarea>

            </div>


            <div class="form-group">

                <label for="website_link">
                    Website Link
                </label>

                <input
                    type="url"
                    id="website_link"
                    name="website_link"
                    placeholder="https://example.com"
                    value="<?php echo htmlspecialchars($websiteLink); ?>"
                >

            </div>


            <div class="form-group">

                <label for="location">
                    Company Location
                </label>

                <input
                    type="text"
                    id="location"
                    name="location"
                    value="<?php echo htmlspecialchars($location); ?>"
                >

            </div>


            <button
                type="submit"
                class="register-button"
            >
                Create Company Profile
            </button>

        </form>

    </div>

</body>

</html>