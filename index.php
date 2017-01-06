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
    <header class="container-fluid" onclick = "window.location='#';">
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
                    <input type="text" maxlength = "10" id="inputName" placeholder = "Username" value = "" autocomplete = "off">
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
                    <textarea id="input" rows="1" maxlength = "1024" placeholder="Enter a message" class="col-sm-9" value = "" autocomplete = "off"></textarea>
                    <input type="button" onclick="submitData()" id="enter" value="Submit" class = "col-sm-3 pull-right">
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
<script src="script.js"></script>
</html>