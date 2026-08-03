<?php
// db.php allows for a centralised database connection (can be reused across pages).
$host = "YOUR_HOST_NAME";   
$database = "YOUR_DB_NAME"; // Database name
$username = "YOUR_USERNAME";
$password = "YOUR_DB_PASSWORD";

try
{
    /*
    Create a PDO connection (allows for more compatibility, and prepared statements which prevent SQL injections) 
    Uses utf8mb4 for support in a wider set of characters/symbols and keeps text consistent between PHP and Database.
    */ 
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", 
    $username, 
    $password);

    // Attribute to throw an exception on database errors (easier to debug and prevents failing silently).
    $pdo ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    /*
    Attribute to return results as an associative array e.g. $row["user_id"]
    Allows for readability and fewer mistakes handling data.
    */
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}

catch (PDOException $e)
{
    echo "The system is temporarily unavailable. Please try again later.";
    exit;
}
?>
