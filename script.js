        n = 1
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
            switch (ext) {
                case "doc":
                case "docx":
                    file = "word.png";
                    break;

                case "xls":
                case "xlsx":
                    file = "excel.png";
                    break;

                case "ppt":
                case "pptx":
                    file = "powerpoint.png";
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

                    $.post("chatsql.php", {
                        action: "getLastId",
                    }, function(data, status) {

                        try {
                            last_id = parseInt(data);
                            var p = document.createElement("p");
                            p.setAttribute("style", "display: none");
                            p.innerHTML = last_id.toString();
                            document.body.appendChild(p);
                        } catch (e) {
                            console.log(e.message);
                            console.log("Data:\n" + data + "\nLength: " + data.length);
                        }

                    });
                    $("#inputName").remove();
                    $("#register").remove();
                    $("#displayName").html(name);
                    $("#displayName").css("display", "block");
                    username = name;
                    setInterval(retrieveData, 100);
                    setInterval(function() {
                        $.post("chatsql.php", {
                            action: "keepAlive",
                            name: name
                        }, function(data, status) {});
                    }, keepAliveInterval * 1000);

                } else {
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