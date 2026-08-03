<?php
if (session_status() === PHP_SESSION_NONE) // Check the session status and if none exists ensure it is started before proceeding.
{
    session_start();
}

function redirect_and_exit(string $path): void  // This function is used to redirect the user to the path specified (e.g. the login page).
{
    header("Location: " . $path);
    exit;
}

function require_login(string $redirectTo = "../index.php"): void
{
    if (!isset($_SESSION["user_id"])) // Check the user id is set if not, redirect user to previously mentioned location.
    {
        redirect_and_exit($redirectTo);
    }

    if (!isset($_SESSION["role"])) 
    {
        redirect_and_exit($redirectTo);
    }
}

function require_role(string $role, string $redirectTo = "../index.php"): void
{
    require_login($redirectTo); // Check to see if the user is logged in.

    if ($_SESSION["role"] !== $role) // Check the exact role such as "teacher" or "student", this variable is checked when calling require_role.
    {
        redirect_and_exit($redirectTo);
    }
}


function require_get_int(string $key, string $redirectTo, int $min = 1): int 
{
    if (!isset($_GET[$key])) 
    {
        redirect_and_exit($redirectTo);
    }

    // Ensure value is numeric (checks if characters are only digits), and is at least a minimum value. Used to check the submission ID.
    if (!ctype_digit($_GET[$key])) 
    {
        redirect_and_exit($redirectTo);
    }

    $value = (int)$_GET[$key];

    if ($value < $min) // if the value is less than 1, redirect and exit to the index (login) page. 
    {
        redirect_and_exit($redirectTo);
    }

    return $value;
}


function current_user_id(): int
{
    if (isset($_SESSION["user_id"])) // Checking to see if the user ID is set.
    {
        return (int)$_SESSION["user_id"]; // Returns the user id as an integer for consistency throughout the pages.
    }
    return 0;
}