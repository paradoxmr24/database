<?php
// Rename this file to config.php to use it with database library
// Set the database cridentials Here
if($_SERVER['SERVER_NAME'] == 'localhost') {
    // Set cridentials for localhost
    $username = 'root'; //username for the local database user
    $password = ''; //password for the local database user
    $database = 'test'; //Name of the local database
    $location = 'localhost'; //Location of the local database
} else {
    // Set Cridentials for Live server
    $username = 'root'; //username for the live database user
    $password = ''; //password for the live database user
    $database = 'test'; //Name of the live database
    $location = 'localhost'; //Location of the live database
}
?>