<?php
$cfgdata = parse_ini_file("sqldb.cfg");
$host = $cfgdata["host"];
$db = $cfgdata["database"];
$user = $cfgdata["user"];
$pw = $cfgdata["password"];
$interval = $cfgdata["keepAliveInterval"];
$mysqli = initDB();
purgeOld();

switch($_POST["action"]) {
    case "register":
        $user = formatString($_POST["name"]);
        if (mysqli_num_rows($result = $mysqli->query("SELECT * FROM MoonChatUsers WHERE User = '$user'")) > 0) {
            echo 0;
        } else {
            echo 1;
            $mysqli->query("INSERT INTO MoonChatUsers (User) VALUES ('$user')");
        }
        break;
        
    case "keepAlive":
        $user = formatString($_POST["name"]);
        $mysqli->query("UPDATE MoonChatUsers SET Time = NOW() WHERE User = '$user'");
//        $mysqli->query("INSERT INTO MoonChatUsers (User) VALUES ('$user') ON DUPLICATE KEY UPDATE Time = NOW() WHERE User = '$user'");
        echo "keepAlive: $user";
        break;
        
    case "submitData":
        $user = formatString($_POST["name"]);
        $msg = formatString($_POST["msg"]);
        if ($result = $mysqli->query("INSERT INTO MoonChat (User, Message) VALUES ('$user', '$msg')")) {
            echo json_encode($result);
        } else {
            echo "Error occured while submitting message.";
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
        
    case "retrieveConfig":
        $arr = array($interval);
        echo json_encode($arr);
        break;
                     
}

function purgeOld() {
    global $mysqli, $interval;
    $mysqli->query("DELETE FROM MoonChatUsers WHERE Time < NOW() - INTERVAL $interval SECOND");
}

function formatString($s) {
    return str_replace("'", "''", $s);
}

function initDB() {
    global $host, $db, $user, $pw;
    $sql = "CREATE TABLE IF NOT EXISTS MoonChat(
        Id integer(10) AUTO_INCREMENT PRIMARY KEY,
        User varchar(64) NOT NULL,
        Message varchar(1024) NOT NULL,
        Time timestamp NOT NULL DEFAULT NOW()
    )";
    $sql1 = "CREATE TABLE IF NOT EXISTS MoonChatUsers(
        Id integer(10) AUTO_INCREMENT PRIMARY KEY,
        User varchar(64) NOT NULL UNIQUE,
        Time timestamp NOT NULL DEFAULT NOW()
    )";
    $mysqli = new mysqli($host, $user, $pw, $db);
    if ($mysqli->connect_error) {
        die("Failed to connect to chat server.");
    } else {
        $mysqli->query($sql);
        $mysqli->query($sql1);
        return $mysqli;
    }
}