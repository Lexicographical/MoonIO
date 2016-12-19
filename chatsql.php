<?php
$cfgdata = parse_ini_file("sqldb.cfg");
$host = $cfgdata["host"];
$db = $cfgdata["database"];
$user = $cfgdata["user"];
$pw = $cfgdata["password"];

if (isset($_POST["date"])) {
    $date = date("Y-m-d G:i:s", $_POST["date"]);
    $sql = "CREATE TABLE IF NOT EXISTS MoonChat(
        Id int(10) unsigned AUTO_INCREMENT PRIMARY KEY,
        User varchar(64) NOT NULL,
        Message varchar(1024) NOT NULL,
        Time timestamp NOT NULL DEFAULT(GETDATE())
    )";
    $mysqli = new mysqli($host, $user, $pw);
    if ($mysqli->connect_error) {
        echo "Failed to connect to chat server.";
    } else {
        $mysqli->query($sql);
        $mysqli->select_db($db);
        $arr = array();
        if ($result = $mysqli->query("select * from MoonChat WHERE Time > $date")) {
            while ($row = $result->fetch_row()) {
                $arr[] = $row;
            }
        }
        echo json_encode($arr);
    }
} else if (isset($_POST["users"])) {
    
}