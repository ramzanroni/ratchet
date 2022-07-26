<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ratchet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <?php
                session_start();
                if (!isset($_SESSION['user'])) {
                    header("Location: index.php");
                }
                require("db/users.php");
                require("db/chatrooms.php");
                $objChatroom = new chatrooms;
                $chatrooms = $objChatroom->getAllChatRooms();

                $objUser = new users;
                $users = $objUser->getAllUsers();
                ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <td>
                                <?php
                                foreach ($_SESSION['user'] as $key => $user) {
                                    $userID = $key;
                                ?>

                                    <input type="hidden" id="userId" value="<?php echo $key; ?>">
                                    <div><?php echo $user['name']; ?></div>
                                    <div><?php echo $user['email']; ?></div>
                                <?php
                                }
                                ?>
                            </td>
                            <td>
                                <input type="button" class="btn btn-warning" id="leave-chat" name="leave-chat" value="Leave">
                            </td>
                        </tr>
                        <tr>
                            <th colspan="3">Users</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($users as $key => $user) {
                            $color = "color: red";
                            if ($user['login_status'] == 1) {
                                $color = "color: green";
                            }
                            if (!isset($_SESSION['user'][$user['id']])) {
                        ?>
                                <tr>
                                    <td><?php echo $user['name']; ?></td>
                                    <td><span class="fa-solid fa-baseball" style="<?php echo $color; ?>"></span></td>
                                    <td><?php echo $user['last_login']; ?></td>
                                </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-8">
                <div id="messages">
                    <table id="chats" class="table table-striped">
                        <thead>
                            <tr>
                                <th colspan="4" scope="col"><strong>Chat Room</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($chatrooms as $key => $chatroom) {
                                if ($userID == $chatroom['userId']) {
                                    $from = "Me";
                                } else {
                                    $from = $chatroom['name'];
                                }
                            ?>
                                <tr>
                                    <td valign="top">
                                        <div><strong><?php echo $from; ?></strong></div>
                                        <div><?php echo $chatroom['msg']; ?></div>
                                    <td align="right" valign="top"><?php echo date("d-m-Y h:i:s A", strtotime($chatroom['created_on'])); ?> </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <form id="chat-room-frm" method="post" action="">
                    <div class="form-group">
                        <textarea class="form-control" id="msg" name="msg" placeholder="Enter Message"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="button" value="Send" class="btn btn-success btn-block" id="send" name="send">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            var conn = new WebSocket('ws://localhost:8080');
            conn.onopen = function(e) {
                console.log("Connection established!");
            };

            conn.onmessage = function(e) {
                console.log(e.data);
                var data = JSON.parse(e.data);
                var row = '<tr><td valing="top"><div><strong>' + data.from + '</strong></div><div>' + data.msg + '</div></td><td align="right" valign="top">' + data.dt + '</td></tr>';
                $("#chats > tbody").prepend(row);
            };
            conn.onclose = function(e) {
                console.log("Connection Closed");
            }
            $("#send").click(function() {
                var msg = $("#msg").val();
                var userId = $("#userId").val();
                var data = {
                    userId: userId,
                    msg: msg
                }
                conn.send(JSON.stringify(data));
                $("#msg").val('');
            });

            $("#leave-chat").click(function() {
                var userId = $("#userId").val();
                // console.log(userId);

                $.ajax({
                    url: "action.php",
                    method: "post",
                    data: {
                        userId: userId,
                        action: "leave"
                    },
                }).done(function(result) {
                    var data = JSON.parse(result);
                    if (data.status == 1) {
                        conn.close();
                        location = "index.php";
                    } else {
                        console.log(data.msg);
                    }


                });

            })
        });
    </script>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>

</html>