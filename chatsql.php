<?php
$cfgdata = parse_ini_file("sqldb.cfg");
$host = $cfgdata["host"];
$db = $cfgdata["database"];
$user = $cfgdata["user"];
$pw = $cfgdata["password"];
$mysqli = initDB();

if (isset($_POST["date"])) {
    // SQL Query
    $date = date("Y-m-d G:i:s", $_POST["date"]);
    $arr = array();
    if ($result = $mysqli->query("SELECT * FROM MoonChat WHERE Time > $date")) {
        while ($row = $result->fetch_row()) {
            $arr[] = $row;
        }
    }
    echo json_encode($arr);
    
} else if (isset($_POST["user"])) {
    // Username authentication
    $user = $_POST["user"];
    if ($result = $mysqli->query("SELECT * FROM MoonChatUsers WHERE User = $user")) {
        echo json_encode(false);
    }
    echo json_encode(true);
}

function initDB() {
    global $host, $db, $user, $pw;
    $sql = "CREATE TABLE IF NOT EXISTS MoonChat(
        User varchar(64) NOT NULL,
        Message varchar(1024) NOT NULL,
        Time timestamp NOT NULL DEFAULT(GETDATE())
    )";
    $sql1 = "CREATE TABLE IF NOT EXISTS MoonChatUsers(
        User varchar(64) PRIMARY KEY
    )";
    $mysqli = new mysqli($host, $user, $pw);
    if ($mysqli->connect_error) {
        die("Failed to connect to chat server.");
    } else {
        $mysqli->select_db($db);
        $mysqli->query($sql);
        $mysqli->query($sql1);
        return $mysqli;
    }
}