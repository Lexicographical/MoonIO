<?php
$dir = "files/";
if (isset($_POST["ip"])) {
    echo gethostbyname(gethostname());
    return;
} else if (file_exists($dir)) {
    if (isset($_POST["counter"])) {
        for ($i = 1; $i <= intval($_POST["counter"]); $i++) {
            $fname = $_FILES["f".$i]["name"];
            $fsize = $_FILES["f".$i]["size"];
            $ftmp = $_FILES["f".$i]["tmp_name"];

            $res = move_uploaded_file($ftmp, $dir.$fname);

            if ($res) {
                echo "<script>console.log('Uploaded $fname ($fsize B)');</script>";
            } else {
                echo "<script>console.log($res);</script>";
            }
        }
    }
    $files = scandir($dir);
    $script = "<script>window.onload = function(){\n";
    foreach ($files as $file) {
        if ($file == "." || $file == ".." || $file == "index.html") {
            continue;
        }
        $fs = filesize($dir.$file);
        $script .= "addFileDownload('$file', $fs);\n";
    }
    $script .= "};</script>";
    echo $script;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Moon IO</title>
        <link rel = "stylesheet" type = "text/css" href = "index.css">
        <script src="lib/jquery.js"></script>
        <script>
//            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(navigator.userAgent)) {
//                window.location="/m";
//            }
        </script>
    </head>
    <body>
        <header onclick = "window.location=''">
            <img src="lib/icon.png" id="icon"><span id="home">Moon IO</span><span id="ip">localhost</span>
        </header>
        <div id="wrapper">
            <fieldset>
                <legend>Upload</legend>
                <form action="index.php" method="POST" enctype="multipart/form-data" id="uploadform">
                    <input type="file" name="f1" id="f1">
                    <input type="button" id="add" value="More..." onclick="addFileInput()">
                    <input type="submit" value="Submit">
                    <input type="hidden" name= "counter" id="counter" value=1>
                </form>
            </fieldset>
            <fieldset>
                <legend>Chat</legend>
                <div id="namebox">
                    <input type="text" maxlength="10" name="name" id="inputName" placeholder="Username">
                    <button onclick="registerUser()" id="register">Register</button>
                    <p id="displayName">Placeholder</p>
                </div>
                <div id="chatbox">
<!--
                    <div class="chatrow">
                        <div class="user">Bolt</div>
                        <div class="content">Sample Text</div>
                        <div class="time">1:00</div>
                    </div>
                    <div class="chatrow">
                        <div class="user">Bolt</div>
                        <div class="content">Lorem ipsum dolor sit amet</div>
                        <div class="time">1:10</div>
                    </div>
-->
                </div>
                <div id="inputbox">
                    <textarea id="input" rows="1" placeholder="Enter a message"></textarea>
                    <input type="button" onclick="submitData()" id="enter" value="Submit">
                </div>
            </fieldset>
            <fieldset>
                <legend>Download</legend>
                <table id="dltable">
                    <tr>
                        <td>
                            <strong class="title">File Name</strong>
                        </td>
                        <td>
                            <strong class="title">File Size</strong>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </div>
    </body>
    <script>
        n=1
        // AJAX
        $.post("index.php", {
            ip: true
        },
        function(data, status) {
            $("#ip").html(data);
        });
        $("#input").keypress(function(e) {
           if (e.keyCode == 13 && !e.ctrlKey) {
               submitData();
               e.preventDefault();
           } 
            return true;
        });
        $("#inputName").keypress(function(e) {
           if (e.keyCode == 13 && !e.ctrlKey) {
               registerUser();
               e.preventDefault();
           } 
            return true;
        });
        var lastDate = new Date();
        var keepAliveInterval = 10;
        $.post("chatsql.php", {
            action: "retrieveConfig"
        }, function(data, status) {
            var json = JSON.parse(data);
            keepAliveInterval = parseInt(json);
        });
        setInterval(retrieveData(), 50);
        var username;
        function addFileInput() {
            var child1 = document.getElementById("f" + n);
            var child2 = document.createElement("input");
            child2.setAttribute("type", "file");
            n++;
            child2.setAttribute("name", "f" + n);
            child2.setAttribute("id", "f" + n);
            document.getElementById("counter").setAttribute("value", n);
            child1.parentNode.insertBefore(child2, child1.nextSibling);
        }
        function addFileDownload(fn, fs) {
            var units = ["B", "KB", "MB", "GB", "TB", "PB"];
            var ind = 0;
            while (fs > 1024) {
                fs /= 1024;
                ind++;
            }
            fs = Math.round(fs);
            var unit = units[ind];
            var table = document.getElementById("dltable");
            var row = document.createElement("tr");
            var td = document.createElement("td");
            var tdsize = document.createElement("td");
            var file = document.createElement("a");
            file.setAttribute("href", "/files/" + fn);
            file.setAttribute("target", "_blank");
            file.innerHTML = fn;
            td.appendChild(file);
            tdsize.innerHTML = "<span class='size'>" + fs + "</span> " + unit;
            row.appendChild(td);
            row.appendChild(tdsize);
            table.appendChild(row);
        }
        function registerUser() {
            var name = $("#inputName").val();
            var auth = false;
            $.post("chatsql.php", {
                action: "register",
                name: name 
            }, function(data, status) {
                auth = data == 1;
                if (auth) {
                    $("#inputName").remove();
                    $("#register").remove();
                    $("#displayName").html(name);
                    $("#displayName").css("display", "block");
                    username=name;
                    setInterval(function() {
                       $.post("chatsql.php", {
                            action: "keepAlive",
                            name: name
                       }, function(data, status) {
//                           console.log(data);
                       });
                    }, keepAliveInterval*1000);
                } else {
//                    ** UNCOMMENT THIS LATER ON **
                    console.log("Problem: " + data);
                    console.log(data.length);
                    alert("That username is already taken! Please try another.");
                }
            });
        }
        function formatTime(t) {
            if (t < 10) {
                return '0' + t;
            }
            return t;
        }
        /**
        type: 0 - own message
        1 - received message
        **/
        function addChatEntry(name, msg, time, type) {
            var date = new Date(time);
            var datestr = `${formatTime(date.getDate())}/${formatTime(date.getMonth())}/${date.getYear()} ${formatTime(date.getHours()%12)}:${formatTime(date.getMinutes())}:${formatTime(date.getSeconds())}${date.getHours()/12 > 0 ? 'PM':'AM'}`;
            if (type == 0) {
                $("#chatbox").append(`
                                     <div class='chatrow0'>
                                        <div class='user'>${name}</div>
                                        <div class='content'>${msg}</div>
                                        <div class='time'>${datestr}</div>
                                     </div>
                                     `);
            } else if (type == 1) {
                $("#chatbox").append(`
                                     <div class='chatrow'>
                                        <div class='user'>${name}</div>
                                        <div class='content'>${msg}</div>
                                        <div class='time'>${datestr}</div>
                                     </div>
                                     `);
            }
        }
        function submitData() {
            if (username == null) {
                alert("Please register a username first!");
                return;
            }
            var txt = $("#input").val();
            lastDate = new Date().getTime();
            addChatEntry(username, txt, lastDate, 0);
            $("#input").val("");
            $.post("chatsql.php", {
                action: "submitData",
                name: username,
                msg: txt,
                time: lastDate
            }, function(data, status) {
                try {
                    var json = JSON.parse(data);
                } catch (e) {
                    console.log(e.message);
                    console.log("Data:\n" + data + "\nLength: " + data.length);
                }
            });
        }
        function retrieveData() {
            var date = lastDate;
            $.post("chatsql.php", {
                action: "retrieveData",
                time: date.getTime()
            }, function(data, status) {
                try {
                    var json = JSON.parse(data);
                } catch (e) {
                    console.log(e.message);
                    console.log("Data:\n" + data + "\nLength: " + data.length);
                }
            });
        }
    </script>
</html>