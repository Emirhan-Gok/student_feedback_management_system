<?php
// start a session so that login state can be used across pages.
session_start();
// Load the PDO database connection ($pdo).
require_once __DIR__ . "/include/db.php";

$error = "";
    // Check if a session already exists.
if (isset($_SESSION["user_id"]) && isset($_SESSION["role"]))
{
    if ($_SESSION["role"] === "teacher")
    {
    header("Location: teacher/teacher_dashboard.php");
    exit;
    }
    else if ($_SESSION["role"] === "student")
    {
    header("Location: student/student_dashboard.php");
    exit;
    }
}

    // Login process starts here, used default values to avoid undeclared variable warnings.
    $username = "";
    $password = "";

    if($_SERVER["REQUEST_METHOD"] === "POST")
    {
        if(isset($_POST["username"])) 
        {   // trim removes accidental spaces that can cause a valid username to fail.
            $username = trim($_POST["username"]);
        }
         if(isset($_POST["password"])) 
        {
            $password = $_POST["password"];
        }

        if($username == "" || $password == "")
        {
            $error = "Please enter your username and password.";
        }
        else
        {
            // A prepared statementwith named placeholders is used so input values are handled safely and the query stays readable.
            // LIMIT 1 is to ensure that only a single matching row is read.
            $statement = $pdo->prepare("SELECT user_id, username, password_hash, role 
            FROM user
            WHERE username = :username
            LIMIT 1 
            ");
            $statement ->execute([":username" => $username]); // Keeps SQL seperate from the actual username value.
            $user = $statement->fetch();

            // password_verify compares the typed password with the stored hash inside database.
            if($user && password_verify($password, $user["password_hash"]))
            {
                // Regenerate a session ID after login for security.
                session_regenerate_id(true);

                // Store the login state for role based access so that other pages can be checked.
                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];
                    
                // Redirect user to the correct dashboard based on their role.
                if($user["role"] === "teacher")
                {
                    header("Location: teacher/teacher_dashboard.php");
                    exit;
                }
                else
                {
                    header("Location: student/student_dashboard.php");
                    exit;
                }
            }
                else
                {
                $error = "Invalid username or password please double check.";
                }
        }
    }
?>

<!DOCTYPE html>
<html>
<title>Feedback System</title>
<head>
<link rel="stylesheet" href="css/assets/bootstrap.min.css">
<link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-light">
    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="card shadow-sm border-0 p-4 " style="max-width: 450px; width: 100%;">

            <h1 class="text-center mb-2 header_index"> Welcome to the Feedback System! </h1>
            <p class="text-muted mb-4"> Please login using your credentials. </p>

            <?php if ($error !== ""): ?>
                <div class="alert alert-danger mb-4"> <?php echo htmlspecialchars($error); ?> 
            
            </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                <label for="username" class="form-label"> Username </label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label"> Password </label>
                <input type="password" class="form-control" id="password" name="password" required> 
            </div>
                <button type="submit" class="btn btn-primary w-100 btn_login"> Submit </button>
            </form>
        </div>

</body>
</html>