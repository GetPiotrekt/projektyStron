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

// Sprawdź, czy użytkownik jest zalogowany
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && ($_SESSION['uprawnienia'] === 'sekretarz' || $_SESSION['uprawnienia'] === 'admin')) {
    $username = $_SESSION['username'];
    $uprawnienia = $_SESSION['uprawnienia'];

    echo 'Witaj, ' . $username . '!';
    echo '<div class="navigation">';

    echo '<div class="common-navigation">';
    echo '<button onclick="location.href=\'/projekty/strony/sGlosowania/logowanie/wyloguj.php\'">Wyloguj się tutaj</button>';
    echo '</div>';

    // Nawigacja dla sekretarza
    if ($uprawnienia === 'sekretarz' || $uprawnienia === 'admin') {
        echo '<div class="sekretarz-navigation">';
        echo '<button onclick="location.href=\'/projekty/strony/sGlosowania/dlaUsera/glosowanie.php\'">Panel glosowań</button>';
        echo '</div>';
    }

    // Nawigacja dla admina
    if ($uprawnienia === 'admin') {
        echo '<div class="admin-navigation">';
        echo '<button onclick="location.href=\'/projekty/strony/sGlosowania/dlaAdmina/admin.php\'">Panel Admina</button>';
        echo '</div>';
    }

    echo '</div>';  // Zamknij div nawigacji
} else {
    header("Location: /projekty/strony/sGlosowania/logowanie/logowanie.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Sekretarza</title>
    <link rel="stylesheet" type="text/css" href="\projekty\strony\sGlosowania\style.css">
    <!-- style dodatkowe -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>
    <!-- Górna nawigacja -->
    <div class="navigation">
        <?php
        // Nawigacja dla sekretarza i admina
        if ($uprawnienia === 'sekretarz' || $uprawnienia === 'admin') {
            echo '<select class="function-select" onchange="toggleFunction(this.value)">';
            echo '<option value="" disabled selected>Wybierz funkcję...</option>';
            echo '<option value="create-vote">Utwórz Głosowanie</option>';
            echo '<option value="start-vote">Wznów Głosowanie</option>';
            echo '<option value="stop-vote">Zatrzymaj Głosowanie</option>';
            echo '<option value="generate-report">Generuj Raport</option>';
            echo '<option value="browse-results">Przeglądaj Wyniki</option>';
            echo '<option value="access-archive">Dostęp do Archiwum</option>';
            echo '</select>';
        }
        ?>
    </div>

    <!-- 1. stwórz głosowanie -->
    <div class="function-container" id="create-vote">
        <div class="container">
            <form method="post" action="obslugaSekretarz.php">
                <label for="tytul">Tytuł:</label>
                <input type="text" name="tytul" required><br>

                <label for="tresc">Treść:</label><br>
                <textarea name="tresc" required></textarea><br><br>

                <label for="data_zakonczenia">Data zakończenia:</label>
                <input type="date" name="data_zakonczenia" required><br>

                <label for="godz_zakonczenia">Godzina zakończenia:</label>
                <input type="time" name="godz_zakonczenia" required><br><br>

                <label for="kworum">Kworum:</label>
                <select name="kworum" required>
                    <option value="0">Brak kworum</option>
                    <option value="10.00">10% (1/10)</option>
                    <option value="25.00">25% (1/4)</option>
                    <option value="33.34">33% (1/3)</option>
                    <option value="50.00">50% (1/2)</option>
                    <option value="66.67">66% (2/3)</option>
                    <option value="75.00">75% (3/4)</option>
                    <option value="90.00">90% (9/10)</option>
                    <option value="100.00">100%</option>
                </select>

                <label for="rodzaj_wyniku">Rodzaj Wyniku:</label>
                <select name="rodzaj_wyniku" required>
                    <option value="wiekszosc_wzgledna">Większość Względna</option>
                    <option value="wiekszosc_bezwzgledna">Większość Bezwzględna</option>
                </select>

                <label for="opcja1">Opcja 1:</label>
                <input type="text" name="opcja1" placeholder="Opcja 1" required><br>

                <label for="opcja2">Opcja 2:</label>
                <input type="text" name="opcja2" placeholder="Opcja 2" required><br>

                <label for="opcja3">Opcja 3:</label>
                <input type="text" name="opcja3" placeholder="Opcja 3"><br>

                <label for="opcja4">Opcja 4:</label>
                <input type="text" name="opcja4" placeholder="Opcja 4"><br>

                <label for="opcja5">Opcja 5:</label>
                <input type="text" name="opcja5" placeholder="Opcja 5"><br>

                <input type="submit" name="utworz_glosowanie" value="Utwórz Głosowanie">
            </form>
        </div>
    </div>

    <!-- 2. wznów głosowanie -->
    <div class="function-container" id="start-vote">
        <div class="container">
            <?php
            $result = $conn->query("SELECT idGlosowania, tytul, dataRozpoczecia FROM glosowanie WHERE czyAktywne = 0");
            $glosowania = array();
            $znalezionoOgloszenia = false;

            if ($result->num_rows > 0) {
                echo '<form method="post" action="obslugaSekretarz.php">';
                echo '<select name="glosowanie_do_wznowienia" required>';
                echo '<option value="" disabled selected hidden>Wybierz ogłoszenie do wznowienia</option>';

                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['idGlosowania']}'>{$row['tytul']} - {$row['dataRozpoczecia']}</option>";
                    $znalezionoOgloszenia = true;
                }

                echo '</select>';
                echo '<button type="submit" name="wznow_glosowanie" class="wznowbtn">Wznów Głosowanie</button>';
                echo '</form>';
            } else {
                echo '<p>Brak dostępnych ogłoszeń do wznowienia.</p>';
            }
            ?>
        </div>
    </div>

    <!-- 3. zatrzymaj głosowanie -->
    <div class="function-container" id="stop-vote">
        <div class="container">
            <?php
            // Pobierz wszystkie glosowania, ktore maja czyAktywne ustawione na 1
            $result = $conn->query("SELECT idGlosowania, tytul, dataRozpoczecia FROM glosowanie WHERE czyAktywne = 1");

            // Zmienna do śledzenia, czy znaleziono ogłoszenia
            $znalezionoOgloszenia = false;

            if ($result->num_rows > 0) {
                echo '<form method="post" action="obslugaSekretarz.php">';
                echo '<select name="glosowanie_do_zatrzymania" required>';
                echo '<option value="" disabled selected hidden>Wybierz ogłoszenie do zatrzymania</option>';

                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['idGlosowania']}'>{$row['tytul']} - {$row['dataRozpoczecia']}</option>";
                    $znalezionoOgloszenia = true;
                }

                echo '</select>';
                echo '<button type="submit" name="zatrzymaj_glosowanie" class="wznowbtn">Zatrzymaj Głosowanie</button>';
                echo '</form>';
            }

            // Wyświetl informację o braku głosowań do zatrzymania
            if (!$znalezionoOgloszenia) {
                echo '<p>Brak dostępnych głosowań do zatrzymania.</p>';
            }
            ?>
        </div>
    </div>

    <!-- 4.  Wyniki głosowania -->
    <div class="function-container" id="browse-results">
        <div class="container">
            <div id="wyniki-container">
                <?php include("obslugaSekretarz.php"); ?>
            </div>
        </div>
    </div>

    <!-- 5.  Generowanie raportow -->
    <div class="function-container" id="generate-report">
        <div class="container">
            <?php
                $result = $conn->query("SELECT idGlosowania, tytul, dataRozpoczecia FROM glosowanie");
                $znalezionoGlosowania = false;
                
                if ($result->num_rows > 0) {
                    echo '<form method="POST" action="obslugaPDF.php">';
                    echo '<select name="glosowanie_do_raportu" required>';
                    echo '<option value="" disabled selected hidden>Wybierz głosowanie</option>';

                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['idGlosowania']}'>{$row['tytul']} - {$row['dataRozpoczecia']}</option>";
                        $znalezionoGlosowania = true;
                    }

                    echo '</select>';
                    echo '<button type="submit" name="raport_glosowanie" class="raportbtn">Generuj szczegółowy raport</button>';
                    echo '</form>';
                } else {
                    echo '<p>Brak dostępnych ogłoszeń do raportu.</p>';
                }
                ?>
                <br><br>
                
            <form method="POST" action="obslugaPDF.php">
                <label for="data_od">Data od:</label>
                <input type="date" name="data_od" id="data_od" required>

                <label for="data_do">Data do:</label>
                <input type="date" name="data_do" id="data_do" required>
                <br><br>
                <button type="submit" name="generuj_raport">Generuj raport za dany okres</button>
            </form>
        </div>
    </div>

    <!-- 6.  Archiwum -->
    <div class="function-container" id="access-archive">
        <div class="container">
            <form method="post" action="obslugaSekretarz.php">
            <button type="submit" name="dostep_archiwum">Generuj zahasłowany plik archiwum</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Wywołaj funkcję przegladajWyniki po kliknięciu przycisku
            $("#przycisk-przegladaj").click(przegladajWyniki);
            
            // Funkcja do pobierania i wyświetlania wyników
            function przegladajWyniki() {
                $.ajax({
                    type: "POST",
                    url: "obslugaSekretarz.php",
                    data: "przegladaj_wyniki=1",
                    success: function(response) {
                        $("#wyniki-container").html(response);
                    }
                });
            }
            
            // Wywołaj funkcję przy załadowaniu strony
            przegladajWyniki();
        });
    </script>

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
