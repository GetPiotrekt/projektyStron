<?php
//include_once("sekretarz.php");
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

    // 1. Tworzenie Głosowania
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['utworz_glosowanie'])) {
        // Pobieranie danych z formularza
        $tytul = $_POST['tytul'];
        $tresc = $_POST['tresc'];
        $dataRozpoczecia = date("Y-m-d"); // Aktualna data
        $dataZakonczenia = $_POST['data_zakonczenia'];
        $godzZakonczenia = $_POST['godz_zakonczenia'];
        $kworum = $_POST['kworum'];
        $rodzajWyniku = $_POST['rodzaj_wyniku'];
        $opcja1 = $_POST['opcja1'];
        $opcja2 = $_POST['opcja2'];
        $opcja3 = $_POST['opcja3'];
        $opcja4 = $_POST['opcja4'];
        $opcja5 = $_POST['opcja5'];

        // Tutaj wykonaj SQL INSERT, aby dodać nowe głosowanie do bazy danych
        $stmt = $conn->prepare("INSERT INTO glosowanie (tytul, tresc, dataRozpoczecia, 
        dataZakonczenia, godzZakonczenia, kworum, opcja1, opcja2, opcja3, opcja4, opcja5, rodzajWyniku) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssssssssss", $tytul, $tresc, $dataRozpoczecia, 
        $dataZakonczenia, $godzZakonczenia, $kworum, $opcja1, $opcja2, $opcja3, $opcja4, $opcja5, $rodzajWyniku);

        $stmt->execute();
        $stmt->close();

    }

    // 2. Uruchamianie Głosowania
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['wznow_glosowanie'])) {
        $idGlosowaniaWznow = $_POST['glosowanie_do_wznowienia'];
    
        $stmt = $conn->prepare("UPDATE glosowanie SET czyAktywne = 1 WHERE idGlosowania = ?");
        $stmt->bind_param("i", $idGlosowaniaWznow);
        $stmt->execute();
        $stmt->close();
    }
    // 2.1 Zatrzymywanie Głosowania
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['zatrzymaj_glosowanie'])) {
        $idGlosowaniaWznow = $_POST['glosowanie_do_zatrzymania'];
    
        $stmt = $conn->prepare("UPDATE glosowanie SET czyAktywne = 0 WHERE idGlosowania = ?");
        $stmt->bind_param("i", $idGlosowaniaWznow);
        $stmt->execute();
        $stmt->close();
    }

    // 4. Przeglądanie Wyników Głosowań
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['przegladaj_wyniki'])) {
        // Pobierz wszystkie głosowania
        $resultVotes = $conn->query("SELECT idGlosowania, tytul, kworum, rodzajWyniku FROM glosowanie");
        $votesData = array();
    
        while ($rowVote = $resultVotes->fetch_assoc()) {
            $voteId = $rowVote['idGlosowania'];
    
            // Pobierz liczbę użytkowników
            $resultUsersCount = $conn->query("SELECT COUNT(*) AS count FROM uzytkownicy");
            $rowUsersCount = $resultUsersCount->fetch_assoc();
            $liczbaUzytkownikow = $rowUsersCount['count'];
    
            // Pobierz liczbę oddanych głosów dla konkretnego głosowania
            $resultVotesCount = $conn->query("SELECT COUNT(*) AS count FROM glosUzytkownika WHERE idGlosowania = $voteId");
            $rowVotesCount = $resultVotesCount->fetch_assoc();
            $glosyOddane = $rowVotesCount['count'];
    
            // Oblicz procent oddanych głosów
            $procentGlosow = ($liczbaUzytkownikow > 0) ? round(($glosyOddane / $liczbaUzytkownikow) * 100, 2) : 0;
    
            // Dodaj logikę obliczania wyniku
            if ($procentGlosow >= $rowVote['kworum']) {
                // Głosowanie jest ważne, znajdź najpopularniejszą odpowiedź
                $resultOptions = $conn->query("SELECT DISTINCT wybor FROM glosUzytkownika WHERE idGlosowania = $voteId");
                $availableOptions = array();
                while ($rowOption = $resultOptions->fetch_assoc()) {
                    $availableOptions[] = $rowOption['wybor'];
                }
    
                if (!empty($availableOptions)) {
                    $randomOption = $availableOptions[array_rand($availableOptions)];
    
                    // Znajdź kolumnę w tabeli glosowanie, odpowiadającą wybranej opcji
                    $kolumnaOpcji = "opcja" . substr($randomOption, -1);
                    $resultInfo = $conn->query("SELECT $kolumnaOpcji FROM glosowanie WHERE idGlosowania = $voteId");
                    $rowInfo = $resultInfo->fetch_assoc();
                    $infoOpcji = $rowInfo[$kolumnaOpcji];
    
                    $wynik = $infoOpcji;
                } else {
                    // Brak dostępnych opcji, co oznacza, że coś poszło nie tak
                    $wynik = 'Błąd';
                }
            } else {
                // Głosowanie jest nieważne
                $wynik = 'Głosowanie nieważne';
            }
    
            // Połącz kolumny "Głosy oddane" i "Liczba użytkowników" jako procent
            $glosyProcent = $procentGlosow . '%';
    
            $votesData[] = array(
                'idGlosowania' => $voteId,
                'tytul' => $rowVote['tytul'],
                'kworum' => $rowVote['kworum'],
                'glosy_procent' => $glosyProcent,
                'wynik' => $wynik
            );
        }
    
        // Zbierz wyniki do zmiennej $output
        $output = '<table border="1"><tbody><tr><th>Tytuł</th><th>Kworum (%)</th><th>Oddane głosy</th><th>Wynik</th></tr>';
        foreach ($votesData as $result) {
            $output .= '<tr>';
            $output .= '<td>' . $result['tytul'] . '</td>';
            $output .= '<td>' . $result['kworum'] . '</td>';
            $output .= '<td>' . $result['glosy_procent'] . '</td>';
            $output .= '<td>' . $result['wynik'] . '</td>';
            $output .= '</tr>';
        }
        $output .= '</tbody></table>';
    
        // Wyślij wyniki do klienta
        echo $output;
    
        // Zakończ działanie skryptu
        exit;
    }

    // 5. Dostęp do Archiwum
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dostep_archiwum'])) {
        // Pobierz dane z tablicy glosowanie
        $glosowanieQuery = "SELECT * FROM glosowanie";
        $glosowanieResult = $conn->query($glosowanieQuery);
    
        // Sprawdzenie, czy zapytanie zwróciło wyniki
        if ($glosowanieResult) {
            // Pobranie danych z wyników zapytania do tablicy
            $glosowanieData = [];
            while ($row = $glosowanieResult->fetch_assoc()) {
                $glosowanieData[] = $row;
            }
        } else {
            echo "Error: " . $mysqli->error;
        }
    
        // Pobierz dane z tablicy glosUzytkownika
        $glosUzytkownikaQuery = "SELECT * FROM glosUzytkownika";
        $glosUzytkownikaResult = $conn->query($glosUzytkownikaQuery);
    
        // Sprawdzenie, czy zapytanie zwróciło wyniki
        if ($glosUzytkownikaResult) {
            // Pobranie danych z wyników zapytania do tablicy
            $glosUzytkownikaData = [];
            while ($row = $glosUzytkownikaResult->fetch_assoc()) {
                $glosUzytkownikaData[] = $row;
            }
        } else {
            echo "Error: " . $mysqli->error;
        }
    
        // Stwórz zahasłowany plik zip
        $hasloArchiwum = date("dmY");
        $zipFileName = "archiwum.zip";
    
        // Komenda do zabezpieczenia pliku hasłem za pomocą 7-Zip
        $command = "\"C:\\Program Files\\7-Zip\\7z.exe\" a -p$hasloArchiwum $zipFileName glosowanie.txt glosUzytkownika.txt";
        exec($command);
    
        // Inicjalizuj obiekt ZipArchive
        $zip = new ZipArchive();
    
        // Sprawdź, czy archiwum zostało utworzone
        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Dodaj plik z danymi z tablicy glosowanie
            $zip->addFromString("glosowanie.txt", json_encode($glosowanieData));
    
            // Dodaj plik z danymi z tablicy glosUzytkownika
            $zip->addFromString("glosUzytkownika.txt", json_encode($glosUzytkownikaData));
    
            $zip->setPassword($hasloArchiwum); // Ustaw hasło
            $zip->close();
    
            // Pobierz plik archiwum zabezpieczonego hasłem
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
            readfile($zipFileName);
    
            // Usuń plik tymczasowy
            unlink($zipFileName);
    
            exit();
        } else {
            // Jeżeli coś poszło nie tak
            echo "Błąd podczas tworzenia archiwum.";
        }
    }
    ?>