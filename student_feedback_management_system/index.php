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
<link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="containerlogin">
<div class="cardlogin">
<h1> Welcome to the Feedback System! </h1>
<h3> Please login using your credentials. </h3>

<p class="p_error"> <?php echo $error; ?> </p>

<form method="post">
<label for="username"> Username </label> <br>
<input type="text" id="username" name="username" required> 

<label for="password"> Password </label> <br>
<input type="password" id="password" name="password" required> 

<button class="btn_login" type="submit"> Submit </button>
</form>
</div>
</div>
</body>
</html>