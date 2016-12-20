<?php
$cfgdata = parse_ini_file("sqldb.cfg");
$host = $cfgdata["host"];
$db = $cfgdata["database"];
$user = $cfgdata["user"];
$pw = $cfgdata["password"];
$mysqli = initDB();
//if (isset($_POST["date"])) {
//    // SQL Query
//    $date = date("Y-m-d G:i:s", $_POST["date"]);
//    $arr = array();
//    if ($result = $mysqli->query("SELECT * FROM MoonChat WHERE Time > $date")) {
//        while ($row = $result->fetch_row()) {
//            $arr[] = $row;
//        }
//    }
//    echo json_encode($arr);
//    
//} else if (isset($_POST["name"])) {
//    // Username authentication
//    $user = $_POST["name"];
//    if ($result = $mysqli->query("SELECT * FROM MoonChatUsers WHERE User = $user")) {
//        echo json_encode(false);
//    }
//    echo json_encode(true);
//}

switch($_POST["action"]) {
    case "register":
        $user = formatString($_POST["name"]);
        if ($result = $mysqli->query("SELECT * FROM MoonChatUsers WHERE User = '$user'")) {
            echo false;
        } else {
            echo true;
            $mysqli->query("INSERT INTO MoonChatUsers (User) VALUES ('$user')");
        }
        break;
        
    case "keepAlive":
        $user = formatString($_POST["name"]);
//        $mysqli->query("UPDATE MoonChatUsers SET Time = NOW() WHERE User = '$user'");
        $mysqli->query("INSERT INTO MoonChatUsers (User) VALUES ('$user') ON DUPLICATE KEY UPDATE Time = NOW() WHERE User = '$user'");
        break;
        
    case "submitData":
        $user = formatString($_POST["name"]);
        $msg = formatString($_POST["msg"]);
        $time = formatString($_POST["time"]);
        if ($result = $mysqli->query("INERT INTO MoonChat (User, Message, Time) VALUES ('$user', '$msg', '$time')")) {
            
        }
        break;
        
    case "retrieveData":
        $date = date("Y-m-d G:i:s", formatString($_POST["time"]));
        $arr = array();
        if ($result = $mysqli->query("SELECT * FROM MoonChat WHERE Time > $date")) {
            while ($row = $result->fetch_row()) {
                $arr[] = $row;
            }
        }
        echo json_encode($arr);
        break;
                     
}

function purgeOld() {
    global $mysqli;
    $mysqli->query("DELETE FROM MoonChatUsers WHERE DATEDIFF(second, Time, GETDATE()) > 10");
}

function formatString($s) {
    return str_replace("'", "''", $s);
}

function initDB() {
    global $host, $db, $user, $pw;
    $sql = "CREATE TABLE IF NOT EXISTS MoonChat(
        User varchar(64) NOT NULL,
        Message varchar(1024) NOT NULL,
        Time timestamp NOT NULL DEFAULT(GETDATE())
    )";
    $sql1 = "CREATE TABLE IF NOT EXISTS MoonChatUsers(
        User varchar(64) PRIMARY KEY,
        Time timestamp NOT NULL DEFAULT(GETDATE())
    )";
    $mysqli = new mysqli($host, $user, $pw);
    if ($mysqli->connect_error) {
        die("Failed to connect to chat server.");
    } else {
        $res=$mysqli->select_db($db);
        $mysqli->query($sql);
        $mysqli->query($sql1);
        return $mysqli;
    }
}