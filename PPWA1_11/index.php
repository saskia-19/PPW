<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FP</title>
</head>
<body>
    <h2>Tabel Final Project Flashcards Semester 2</h1>
    <table border="1">
    <tr>
        <th>user_id : </th>
        <th>Username : </th>
        <th>Email : </th>
        <th>Password : </th>
        <th>Dibuat pada : </th>
    </tr>
    <?php
        include'connection.php';
        $user = mysqli_query($conn, "select * from users");
        $user_id = "1";
        foreach ($user as $value){
            echo"
            <tr>
                <td>$user_id</td>
                <td>$username</td>
                <td>$email</td>
                <td>$password_hash</td>
                <td>$created_at</td>
            </tr>
            ";
            $user_id ++;
        }
    ?>
    </table>
</body>
</html>