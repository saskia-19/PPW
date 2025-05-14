
    <?php
        // echo "Hai! Berikut adalah Tabel Basis data dari Final Project Web + Basis data Semester 2";
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "FP";

        $conn = mysql_connect($servername, $username, $password, $dbname);
        if (!$conn) {
            die("Koneksi gagal : " . mysql_connect_error());
        }
        echo "connected";

        
        mysql_close($conn)
    ?>
