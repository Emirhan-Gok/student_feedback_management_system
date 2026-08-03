<?php
session_start();
$_SESSION = []; // Clear session variables e.g. user ID, username, and role.
session_destroy(); // Destroy the session to fully log out the user.
header("Location: index.php");
exit;
?>