<?php
//include_once("admin.php");
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
    // 1. Dodawanie
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['utworz_uzytkownika'])) {
        // Pobieranie danych z formularza
        $nazwaUzytkownika = $_POST['nazwa_uzytkownika']; // Corrected the name attribute
        $haslo = $_POST['haslo'];
        $uprawnienia = $_POST['uprawnienia'];

        // Tutaj wykonaj SQL INSERT, aby dodać nowego użytkownika do bazy danych
        $stmt = $conn->prepare("INSERT INTO uzytkownicy (nazwaUzytkownika, haslo, uprawnienia) VALUES (?, ?, ?)");
        
        $stmt->bind_param("sss", $nazwaUzytkownika, $haslo, $uprawnienia); // Corrected the number of placeholders

        $stmt->execute();
        $stmt->close();
        echo 'Użytkownik został dodany do bazy danych'; 
    }

    // 2. Usuwanie
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usun_uzytkownika'])) {
            // Pobierz nazwę użytkownika z formularza
            $nazwa_uzytkownika = $_POST['nazwa_uzytkownika'];
    
            // Przygotuj zapytanie do sprawdzenia czy użytkownik istnieje
            $zapytanie_sprawdz = $conn->prepare("SELECT * FROM uzytkownicy WHERE nazwaUzytkownika = ?");
            $zapytanie_sprawdz->bind_param("s", $nazwa_uzytkownika);
            $zapytanie_sprawdz->execute();
            $wynik = $zapytanie_sprawdz->get_result();
    
            // Jeśli użytkownik istnieje, usuń go z bazy danych
            if ($wynik->num_rows > 0) {
                $zapytanie_usun = $conn->prepare("DELETE FROM uzytkownicy WHERE nazwaUzytkownika = ?");
                $zapytanie_usun->bind_param("s", $nazwa_uzytkownika);
                $zapytanie_usun->execute();
                echo "Użytkownik został pomyślnie usunięty.";
            } else {
                echo "Nie znaleziono użytkownika o podanej nazwie.";
            }
    
            // Zamknij połączenie z bazą danych
            $conn->close();
        }


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edytuj_uzytkownika'])) {
        // Obsługa edycji danych użytkownika
        $nazwa_uzytkownika = $_POST['nazwa_uzytkownika'];
        $nowa_nazwa = $_POST['nowa_nazwa'];
        $nowe_haslo = $_POST['nowe_haslo'];
        $nowe_uprawnienia = $_POST['nowe_uprawnienia'];

        // Przygotuj zapytanie do sprawdzenia czy użytkownik istnieje
        $zapytanie_sprawdz = $conn->prepare("SELECT * FROM uzytkownicy WHERE nazwaUzytkownika = ?");
        $zapytanie_sprawdz->bind_param("s", $nazwa_uzytkownika);
        $zapytanie_sprawdz->execute();
        $wynik = $zapytanie_sprawdz->get_result();

        // Jeśli użytkownik istnieje, zaktualizuj jego dane
        if ($wynik->num_rows > 0) {
            $zapytanie_edytuj = $conn->prepare("UPDATE uzytkownicy SET nazwaUzytkownika = ?, haslo = ?, uprawnienia = ? WHERE nazwaUzytkownika = ?");
            $zapytanie_edytuj->bind_param("ssss", $nowa_nazwa, $nowe_haslo, $nowe_uprawnienia, $nazwa_uzytkownika);
            $zapytanie_edytuj->execute();
            echo "Dane użytkownika zostały pomyślnie zaktualizowane.";
        } else {
            echo "Nie znaleziono użytkownika o podanej nazwie.";
        }
    }

    // 4. Generator Głosowania
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generator_wynikow'])) {
        // Pobierz wszystkich użytkowników
        $resultUsers = $conn->query("SELECT idUzytkownika FROM uzytkownicy");
        $users = array();
    
        while ($rowUser = $resultUsers->fetch_assoc()) {
            $users[] = $rowUser['idUzytkownika'];
        }
    
        // Pobierz wszystkie głosowania
        $resultVotes = $conn->query("SELECT idGlosowania FROM glosowanie");
        $votes = array();
    
        while ($rowVote = $resultVotes->fetch_assoc()) {
            $votes[] = $rowVote['idGlosowania'];
        }
    
        // Dla każdego użytkownika
        foreach ($users as $userId) {
            // Dla każdego głosowania
            foreach ($votes as $voteId) {
                // Pobierz informacje o głosowaniu
                $resultVoteInfo = $conn->query("SELECT opcja1, opcja2, opcja3, opcja4, opcja5 FROM glosowanie WHERE idGlosowania = $voteId");
    
                if ($resultVoteInfo->num_rows > 0) {
                    $rowVoteInfo = $resultVoteInfo->fetch_assoc();
    
                    // Sprawdź, czy użytkownik bierze udział w głosowaniu
                    $participateInQuorum = (rand(1, 100) <= 75); // Symulacja losowego uczestnictwa
    
                    if ($participateInQuorum) {
                        // Wybierz tylko dostępne opcje
                        $availableOptions = array_filter($rowVoteInfo, function ($value) {
                            return $value !== "";
                        });
    
                        if (!empty($availableOptions)) {
                            // Wybierz losową opcję spośród dostępnych
                            $randomOptionKey = array_rand($availableOptions);
    
                            // Wstaw informację o wybranej opcji do bazy danych
                            $stmtInsertVote = $conn->prepare("INSERT INTO glosUzytkownika (idUzytkownika, idGlosowania, wybor) VALUES (?, ?, ?)");
                            $stmtInsertVote->bind_param("iis", $userId, $voteId, $randomOptionKey);
                            $stmtInsertVote->execute();
                            $stmtInsertVote->close();
                        }
                    }
                }
            }
        }
    
        echo '<p>Wygenerowano przypadkowe wyniki dla użytkowników.</p>';
    }
?>