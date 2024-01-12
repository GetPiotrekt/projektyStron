<?php
session_start();

function loginUser() {
    $hostname = "aps.h.filess.io";
    $database = "systemGlosowania_ropewiseto";
    $port = "3306";
    $username = "systemGlosowania_ropewiseto";
    $password = "3fa85b505316139f726ea34a83d958895d0cb23e";

    $conn = new mysqli($hostname, $username, $password, $database, $port);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $message = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
        $username = $_POST["username"];
        $password = $_POST["psw"];

        $sql = "SELECT * FROM uzytkownicy WHERE nazwaUzytkownika=? AND haslo=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['logged_in'] = true;
            $_SESSION['idUzytkownika'] = $row['idUzytkownika'];
            $_SESSION['username'] = $username;
            $_SESSION['uprawnienia'] = $row['uprawnienia'];
            header("Location: /projekty/strony/sGlosowania/dlaUsera/glosowanie.php");
            exit();
        } else {
            $message = "Nieprawidłowa nazwa użytkownika lub hasło. Spróbuj ponownie";
            echo '<script>alert("'.$message.'");</script>';
        }

        $stmt->close();
    }

    $conn->close();
}

if (isset($_POST['login']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    loginUser();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie</title>
    <link rel="stylesheet" type="text/css" href="\projekty\strony\sGlosowania\style.css">
</head>
<body>
    <div class="container">
        <h1>ZALOGUJ SIĘ</h1>
        <form method="post" action="logowanie.php">
            <label for="username"><b>Nazwa użytkownika</b></label>
            <input type="text" placeholder="Wprowadź nazwę użytkownika" name="username" required>
    
            <label for="psw"><b>Hasło</b></label>
            <input type="password" placeholder="Wprowadź hasło" name="psw" required>
    
            <button type="submit" class="loginbtn" name="login">Zaloguj</button>
            <span class="srodek">W przypadku braku konta, lub zapomnienia hasła,
            proszę wysłać wiadomość na adres email:<br> <b>pomoc@glosowanie.pl</span>
        </form>
    </div>
</body>
</html>