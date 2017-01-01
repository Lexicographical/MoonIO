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
    <title>MoonIO</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href = "lib/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="index.css">
    <script src="lib/js/jquery.js"></script>
    <script src="lib/js/bootstrap.js"></script>
</head>
<body>
    <header class="container-fluid" onclick = "window.location='index.html';">
        <img src="lib/pics/icon.png" id="headerIcon" class="pull-left hidden-xs hidden-sm">
        <span id="headerText text">MoonIO</span>
        <span id="ip" class="pull-right  hidden-xs hidden-sm">localhost</span>
    </header>
    <section class = "row container-fluid">

        <div class="col-sm-3 panel  panel-success category">
            <div class="panel-heading">                
                <h1 class="panel-title">Upload</h1>
            </div>
            <div class="panel-body" id="fileUploads">
                <form action="index.php" method="POST" enctype="multipart/form-data" id="uploadform">
                    <input type="file" name="f1" id="f1">
                    <input type="button" id="add" value="More..." onclick="addFileInput()"><br>
                    <input type="submit" value="Submit">
                    <input type="hidden" name= "counter" id="counter" value=1>
                </form>
            </div>
        </div>

        <div class="col-sm-3 panel panel-info category">
            <div class="panel-heading">                
                <h1 class="panel-title">Chat</h1>
            </div>
            <div class="panel-body" id="chatBody">
                <div id="registerPane" class = "row">
                    <input type="text" maxlength = "10" id="inputName" placeholder = "Username">
                    <input type="button" onclick = "registerUser()" id = "register" value="Register">
                    <p id="displayName">Username</p>
                </div>
                <div id="messagesBox" class = "row">
        <!--             <div class="row chatrow">
                        <div class="user col-sm-2">Bolt</div>
                        <div class="content col-sm-7">Sample Text</div>
                        <div class="time col-sm-3">1:00</div>
                    </div>

                    <div class="row chatrow0">
                        <div class="user col-sm-2">Other</div>
                        <div class="content col-sm-7">Lorem ipsum dolor sit amet</div>
                        <div class="time col-sm-3">1:10</div>
                    </div> -->
                </div>
                <div id="inputBox" class="row">
                    <textarea id="input" rows="1" maxlength = "1024" placeholder="Enter a message" class="col-sm-9"></textarea>
                    <input type="button" onclick="submitData()" id="enter" value="Submit" class = "col-sm-3">
                </div>
            </div>
        </div>

        <div class="col-sm-3 panel panel-danger category">
            <div class="panel-heading">                
                <h1 class="panel-title">Download</h1>
            </div>
            <div class="panel-body container-fluid" id = "dlTable">
                <div class = "row" id = "headerRow">
                    <div class = "col-sm-8 dlHeader">
                        File Name
                    </div>
                    <div class = "col-sm-4 dlHeader">
                        File Size
                    </div>
                </div>
            </div>
        </div>
    </section>
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
               var last_id = 0;
        var keepAliveInterval = 10;
        $.post("chatsql.php", {
            action: "retrieveConfig"
        }, function(data, status) {
            keepAliveInterval = data;
        });
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
            var iconLink = getFileIcon(getFileExtension(fn));
            var unit = units[ind];
            var table = document.getElementById("dlTable");
            var row = document.createElement("div");
            row.setAttribute("class", "row");
            var fnContainer = document.createElement("div");
            fnContainer.setAttribute("class", "col-xs-12 col-md-9 fnEntry");
            var fsContainer = document.createElement("div");
            fsContainer.setAttribute("class", "col-xs-12 col-md-3 fsEntry");
            var fileLink = document.createElement("a");
            fileLink.setAttribute("href", "files/" + fn);
            fileLink.setAttribute("target", "_blank");
            fileLink.setAttribute("class", "fileLink")
            fileLink.innerHTML = `<img src="${iconLink}" class="fileIcon"> ${fn}`;
            fnContainer.appendChild(fileLink);
            fsContainer.innerHTML = "<span class='fileSize'>" + fs + "</span> " + "<span class = 'fsUnit'>" + unit + "</span>";
            row.appendChild(fnContainer);
            row.appendChild(fsContainer);
            table.appendChild(row);
        }
        function getFileExtension(s) {
            return s.split(".").pop();
        }
        function getFileIcon(ext) {
            ext = ext.toLowerCase();
            var path = "lib/pics/fileIcons/";
            var file = "file.png";
            switch(ext) {
                case "doc":
                case "docx":
                file =  "word.png";
                break;

                case "xls":
                case "xlsx":
                file = "excel.png";
                break;

                case "ppt":
                case "pptx":
                file =  "powerpoint.png";
                break;

                case "pdf":
                file = "pdf.png";
                break;

                case "zip":
                case "7zip":
                case "gz":
                file = "zip.png";
                break;

                case "mp4":
                case "mkv":
                case "mov":
                file = "video.png";
                break;

                case "mp3":
                case "mid":
                case "midi":
                case "wav":
                case "ogg":
                file = "audio.png";
                break;
            }
            return path + file;
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
                    //set last_id
                    $.post("chatsql.php", {
                        action: "getLastId",
                    }, function(data, status) {
                        try {
//                         console.log(data);
var json = JSON.parse(data);
last_id=json;
} catch (e) {
    console.log(e.message);
    console.log("Data:\n" + data + "\nLength: " + data.length);
}
});
                    //last_id set
                    $("#inputName").remove();
                    $("#register").remove();
                    $("#displayName").html(name);
                    $("#displayName").css("display", "block");
                    username = name;
                    setInterval(retrieveData, 50);
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
//                    console.log("Problem: " + data);
//                    console.log(data.length);
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
                $("#messagesBox").append(`
                 <div class="row chatrow">
                    <div class="user col-sm-2">${name}</div>
                    <div class="content col-sm-7">${msg}</div>
                    <div class="time col-sm-3">${datestr}</div>
                </div>
                `);
            } else if (type == 1) {
                $("#messagesBox").append(`
                 <div class="row chatrow0">
                    <div class="user col-sm-2">${name}</div>
                    <div class="content col-sm-7">${msg}</div>
                    <div class="time col-sm-3">${datestr}</div>
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
            if (txt.trim() == "") {
                return;
            }
            addChatEntry(username, txt, new Date().getTime(), 0);
            $("#input").val("");
            $.post("chatsql.php", {
                action: "submitData",
                name: username,
                msg: txt,
            }, function(data, status) {
                try {
                    last_id = data;
                } catch (e) {
                    console.log(e.message);
                    console.log("Data:\n" + data + "\nLength: " + data.length);
                }
            });
        }
        function retrieveData() {
            $.post("chatsql.php", {
                action: "retrieveData",
                               id: last_id,
                name: username
            }, function(data, status) {
                try {
//                    console.log(data);
var json = JSON.parse(data);
for (var i in json) {
                        var tid = json[i][0];
                        last_id = Math.max(last_id, tid);
 var name = json[i][1];
 var msg = json[i][2];
 var time = json[i][3];
 addChatEntry(name, msg, time, 1);
}
} catch (e) {
    console.log(e.message);
    console.log("Data:\n" + data + "\nLength: " + data.length);
}
});
        }
    </script>
    </html>