<?php
$cfgdata = parse_ini_file("sqldb.cfg");
$host = $cfgdata["host"];
$db = $cfgdata["database"];
$user = $cfgdata["user"];
$pw = $cfgdata["password"];
$interval = $cfgdata["keepAliveInterval"];
$tUsers = "moonchatusers";
$tChat = "moonchat";
$mysqli = initDB();
purgeOld();

switch($_POST["action"]) {
    case "register":
        $user = formatString($_POST["name"]);
        if (mysqli_num_rows($result = $mysqli->query("SELECT * FROM $tUsers WHERE user = '$user'")) > 0) {
            echo 0;
        } else {
            echo 1;
            $mysqli->query("INSERT INTO $tUsers (user) VALUES ('$user')");
        }
        break;
        
    case "keepAlive":
        $user = formatString($_POST["name"]);
        $mysqli->query("UPDATE $tUsers SET time = NOW() WHERE user = '$user'");
        echo "keepAlive: $user";
        break;
        
    case "submitData":
        $user = formatString($_POST["name"]);
        $msg = formatString($_POST["msg"]);
        if ($result = $mysqli->query("INSERT INTO $tChat (user, message) VALUES ('$user', '$msg')")) {
            echo getLastId();
        } else {
            echo "Error occured while submitting message.";
        }
        break;
        
    case "retrieveData":
        $id = $_POST["id"];
        $name = $_POST["name"];
        $arr = array();
        $sql = "SELECT * FROM $tChat WHERE id > $id and user != '$name'";
        $result = $mysqli->query($sql);
        if ($mysqli->query($sql) === FALSE){
            echo "Error: $sql<br>" . $mysqli->error;
        } else {
            if ($result->num_rows == 0) {
                //no results
            } else {
                while ($row = $result->fetch_row()) {
                    $arr[] = $row;
                }
            }
        }
        echo json_encode($arr);
        break;
        
    case "retrieveConfig":
        echo $interval;
        break;

    case "getLastId":
        echo getLastId();
        break;
                     
}

function getLastId() {
    global $tChat, $mysqli;  
    $last_id=0;
    $sql = "SELECT id FROM $tChat ORDER BY id DESC LIMIT 1";
    $result = $mysqli->query($sql);
    if ($mysqli->query($sql) === FALSE){
        echo "Error: $sql<br>" . $mysqli->error;
    } else {
        if ($result->num_rows == 0) {
            $last_id=0;
        } else {
            while ($row = $result->fetch_row()) {
                $last_id=$row[0];
            }
        }
    }
    return $last_id;
}

function purgeOld() {
    global $mysqli, $interval, $tUsers;
    $mysqli->query("DELETE FROM $tUsers WHERE time < NOW() - INTERVAL $interval SECOND");
}

function formatString($s) {
    return str_replace("'", "''", $s);
}

function initDB() {
    global $host, $db, $user, $pw, $tChat, $tUsers;
    $sql = "CREATE TABLE IF NOT EXISTS $tChat (
        id integer(10) AUTO_INCREMENT PRIMARY KEY,
        user varchar(64) NOT NULL,
        message varchar(1024) NOT NULL,
        time timestamp NOT NULL DEFAULT NOW()
    )";
    $sql1 = "CREATE TABLE IF NOT EXISTS $tUsers (
        id integer(10) AUTO_INCREMENT PRIMARY KEY,
        user varchar(64) NOT NULL UNIQUE,
        time timestamp NOT NULL DEFAULT NOW()
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