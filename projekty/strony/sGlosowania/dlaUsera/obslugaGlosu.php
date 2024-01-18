<?php
session_start();
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

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Sprawdzamy, czy zostały przesłane dane z formularza
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['idGlosowania']) && isset($_POST['opcje'])) {
        $idGlosowania = $_POST['idGlosowania'];
        $opcja = $_POST['opcje'];

        // Sprawdzamy, czy glosowanie jest jeszcze aktywne
        $sqlGlosowanie = "SELECT * FROM glosowanie WHERE idGlosowania = $idGlosowania";
        $resultGlosowanie = $conn->query($sqlGlosowanie);

        if ($resultGlosowanie->num_rows > 0) {
            $rowGlosowanie = $resultGlosowanie->fetch_assoc();
            $currentDate = date("Y-m-d H:i:s");
            $endDate = $rowGlosowanie['dataZakonczenia'] . ' ' . $rowGlosowanie['godzZakonczenia'];
            $isExpired = strtotime($currentDate) > strtotime($endDate);

            if (!$isExpired) {
                // Dodajemy wybór użytkownika do bazy danych glosUzytkownika
                if (isset($_SESSION['idUzytkownika'])) {
                    $idUzytkownika = $_SESSION['idUzytkownika'];
                    $sqlSprawdzGlos = "SELECT * FROM glosUzytkownika WHERE idUzytkownika = '$idUzytkownika' AND idGlosowania = '$idGlosowania'";
                    $resultSprawdzGlos = $conn->query($sqlSprawdzGlos);
            
                    if ($resultSprawdzGlos->num_rows > 0) {
                        // Użytkownik już brał udział w tym głosowaniu, więc usuwamy poprzednią decyzję
                        $sqlUsunPoprzedniGlos = "DELETE FROM glosUzytkownika WHERE idUzytkownika = '$idUzytkownika' AND idGlosowania = '$idGlosowania'";
                        if ($conn->query($sqlUsunPoprzedniGlos) !== TRUE) {
                            echo "Błąd podczas usuwania poprzedniego wyboru: " . $conn->error;
                        }
                    }
            
                    // Dodajemy nowy wybór użytkownika
                    $sqlDodajGlos = "INSERT INTO glosUzytkownika (idUzytkownika, idGlosowania, wybor) VALUES ('$idUzytkownika', '$idGlosowania', '$opcja')";
            
                    if ($conn->query($sqlDodajGlos) === TRUE) {
                        // Wyskakujący komunikat JavaScript z przekierowaniem do poprzedniej strony
                        echo '<script>window.history.go(-1);</script>';
                    } else {
                        // Wyskakujący komunikat JavaScript z błędem i przekierowaniem do poprzedniej strony
                        echo '<script>alert("Błąd podczas dodawania wyboru: ' . $conn->error . '"); window.history.go(-1);</script>';
                    }
                } else {
                    // Wyskakujący komunikat JavaScript o błędzie sesji i przekierowaniem do poprzedniej strony
                    echo '<script>alert("Błąd: Sesja \'idUzytkownika\' nie jest ustawiona."); window.history.go(-1);</script>';
                }
            } else {
                echo "Głosowanie jest już zakończone.";
            }
        } else {
            echo "Nieprawidłowe ID głosowania.";
        }
    } else {
        echo "Błędne dane przesłane z formularza.";
    }
} else {
    echo "Użytkownik niezalogowany.";
}

$conn->close();
?>