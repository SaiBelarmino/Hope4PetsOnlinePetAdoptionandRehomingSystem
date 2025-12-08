
<?php
$host = 'localhost';
$dbname = 'hope4pets';
$username = 'root';
$password = '';


// Legacy global connection for BaseController compatibility
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function get_db_connection() {
    global $host, $username, $password, $dbname;
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
