<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: text/html; charset=utf-8');

$hostname = "aps.h.filess.io";
$database = "systemGlosowania_ropewiseto";
$port = "3306";
$username = "systemGlosowania_ropewiseto";
$password = "3fa85b505316139f726ea34a83d958895d0cb23e";

$conn = new mysqli($hostname, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$uprawnienia = '';
// Sprawdź, czy użytkownik jest zalogowany i ma uprawnienia admina
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $_SESSION['uprawnienia'] === 'admin') {
        $username = $_SESSION['username'];
        $uprawnienia = $_SESSION['uprawnienia'];
    
        echo 'Witaj, ' . $username . '!';
        echo '<div class="navigation">';
    
        echo '<div class="common-navigation">';
        echo '<button onclick="location.href=\'/projekty/strony/sGlosowania/logowanie/wyloguj.php\'">Wyloguj się tutaj</button>';
        echo '</div>';

        // Nawigacja dla admina
        if ($uprawnienia === 'admin') {
            echo '<div class="admin-navigation">';
            echo '<button onclick="location.href=\'/projekty/strony/sGlosowania/dlaSekretarza/sekretarz.php\'">Panel Sekretarza</button>';
            echo '</div>';
        }
    
        // Nawigacja dla sekretarza
        if ($uprawnienia === 'sekretarz' || $uprawnienia === 'admin') {
            echo '<div class="sekretarz-navigation">';
            echo '<button onclick="location.href=\'/projekty/strony/sGlosowania/dlaUsera/glosowanie.php\'">Panel Głosowań</button>';
            echo '</div>';
        }
    
        echo '</div>';  // Zamknij div nawigacji
    } else {
    // Użytkownik nie jest zalogowany lub nie ma uprawnień admina
    header("Location: /projekty/strony/sGlosowania/logowanie/logowanie.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admina</title>
    <link rel="stylesheet" type="text/css" href="\projekty\strony\sGlosowania\style.css">
    <!-- style dodatkowe -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>
    <!-- Górna nawigacja -->
    <div class="navigation">
        <?php
        // Nawigacja dla admina
        if ($uprawnienia === 'admin') {
            echo '<select class="function-select" onchange="toggleFunction(this.value)">';
            echo '<option value="" disabled selected>Wybierz funkcję...</option>';
            echo '<option value="create-user">Utwórz Użytkownika</option>';
            echo '<option value="generator">Generator wyników</option>';
            echo '<option value="remove-user">Usuń Użytkownika</option>';
            echo '<option value="edit-user">Edytuj Użytkownika</option>';
            echo '</select>';
        }
        ?>
    </div>

    <!-- 1. Utwórz user -->
    <div class="function-container" id="create-user">
        <div class="container">
            <form method="post" action="obslugaAdmin.php">
                <label for="nazwa_uzytkownika">Nazwa Uzytkownika</label><br>
                <input type="text" name="nazwa_uzytkownika" required><br>

                <label for="haslo">Haslo</label>
                <input type="text" name="haslo" required><br>

                <label for="typ_uprawnienia">Typ uprawnienia</label>
                <select name="uprawnienia" required>
                    <option value="user">Uzytkownik</option>
                    <option value="sekretarz">Sekretarz</option>
                    <option value="admin">Admin</option>
                </select>

                <input type="submit" name="utworz_uzytkownika" value="Utwórz Uzytkownika">
            </form>
        </div>
    </div>

    <!-- 2. usun user -->
    <div class="function-container" id="remove-user">
        <div class="container">
            <form method="post" action="obslugaAdmin.php">
                <label for="nazwa_uzytkownika">Nazwa Uzytkownika</label><br>
                <input type="text" name="nazwa_uzytkownika" required><br>

                <input type="submit" name="usun_uzytkownika" value="Usuń Uzytkownika">
            </form>
        </div>
    </div>

    <div class="function-container" id="edit-user">
        <div class="container">
            <form method="post" action="obslugaAdmin.php">
                <label for="nazwa_uzytkownika">Aktualna Nazwa Uzytkownika</label><br>
                <input type="text" name="nazwa_uzytkownika" required><br>

                <label for="nowa_nazwa">Nowa Nazwa Uzytkownika</label><br>
                <input type="text" name="nowa_nazwa" required><br>

                <label for="nowe_haslo">Nowe Haslo</label><br>
                <input type="text" name="nowe_haslo" required><br>

                <label for="nowe_uprawnienia">Nowy Typ uprawnienia</label>
                <select name="nowe_uprawnienia" required>
                    <option value="user">Uzytkownik</option>
                    <option value="sekretarz">Sekretarz</option>
                    <option value="admin">Admin</option>
                </select>

                <input type="submit" name="edytuj_uzytkownika" value="Edytuj Uzytkownika">
            </form>
        </div>
    </div>


    <!-- 4. Generator losowych wynikow -->
    <div class="function-container" id="generator">
        <div class="container">
            <form method="post" action="obslugaSekretarz.php">
            <button type="submit" name="generator_wynikow" class="gnrt">Wygeneruj przypadkowe wyniki</button>
            </form>
        </div>
    </div>

<!-- Skrypt JavaScript do obsługi widoczności kontenerów funkcji -->
    <script>
        function toggleFunction(functionName) {
            var containers = document.querySelectorAll('.function-container');

            containers.forEach(function(item) {
                if (item.id !== functionName) {
                    item.style.display = 'none';
                } else {
                    item.style.display = 'block';
                }
            });
        }
    </script>
</body>
</html>