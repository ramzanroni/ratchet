<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ratchet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
</head>

<body>
    <?php
    if (isset($_POST['submit'])) {
        session_start();
        require("db/users.php");
        $objUser = new users;
        $objUser->setEmail($_POST['email']);
        $objUser->setName($_POST['uname']);
        $objUser->setLoginStatus(1);
        $objUser->setLastLogin(date('Y-m-d h:i:s'));
        $userData = $objUser->getUserByEmail();
        if (is_array($userData) && count($userData) > 0) {
            $objUser->setId($userData['id']);
            if ($objUser->updateLoginStatus()) {
                echo "user login";
                $_SESSION['user'][$userData['id']] = $userData;
                header("location: chatroom.php");
            } else {
                echo "login failed.";
            }
        } else {
            if ($objUser->save()) {
                $lastId = $objUser->conn->lastInsertId();
                $objUser->setId($lastId);
                $_SESSION['user'][$userData['id']] = (array) $objUser;
                echo "user registred.";
                header("location: chatroom.php");
            } else {
                echo "failed";
            }
        }
    }
    ?>
    <div class="p-4 m-4 col-md-6 justify-content-center">
        <form action="" method="POST">
            <div class="form-floating mb-3">
                <input type="text" name="uname" class="form-control" id="uname" placeholder="name@example.com">
                <label for="floatingInput">Username</label>
            </div>
            <div class="form-floating">
                <input type="email" name="email" class="form-control" id="email" placeholder="Password">
                <label for="floatingPassword">Email</label>
            </div>
            <button type="submit" name="submit" class="btn btn-primary m-2">Login</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
</body>

</html>