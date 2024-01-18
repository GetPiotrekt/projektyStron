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

// 5. Generowanie Raportów
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generuj_raport'])) {
    // Pobierz daty z formularza
    $data_od = $_POST['data_od'];
    $data_do = $_POST['data_do'];

    // Pobierz identyfikatory głosowań z określonego przedziału czasowego
    $query = "SELECT idGlosowania FROM glosowanie WHERE dataRozpoczecia >= ? AND dataZakonczenia <= ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $data_od, $data_do);
    $stmt->execute();
    $result = $stmt->get_result();

    // Sprawdź, czy znaleziono jakieś głosowania w danym okresie
        if ($result->num_rows > 0) {
            // Utwórz obiekt PDF
            require(__DIR__ . '/../../vendor/tcpdf/tcpdf.php');
            $pdf = new TCPDF();
            $pdf->SetAutoPageBreak(true, 10);
        
            // Wyczyść bufor wyjściowy
            ob_clean();
        
            // Dodaj treść do pliku PDF
            $pdf->AddPage();
            $pdf->SetFont('times', 'N', 12);
        
            // Iteruj przez identyfikatory i generuj raporty
            while ($row = $result->fetch_assoc()) {
                generujRaport($conn, $pdf, $row['idGlosowania']);
            }
        
            // Zapisz plik PDF
            $pdf->Output('raport_glosowan.pdf', 'D'); // 'D' - pobranie pliku
    } else {
        echo 'Nie znaleziono głosowań w podanym przedziale czasowym.';
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['raport_glosowanie'])) {
    $idGlosowania = $_POST['glosowanie_do_raportu'];

    // Pobierz dane z bazy danych
    $query = "SELECT * FROM glosowanie WHERE idGlosowania = $idGlosowania";
    $result = $conn->query($query);

    if ($result !== false && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Pobierz informacje z tabeli glosUzytkownika
        $glosyQuery = "SELECT wybor, COUNT(*) as liczbaGlosow FROM glosUzytkownika WHERE idGlosowania = $idGlosowania GROUP BY wybor";
        $glosyResult = $conn->query($glosyQuery);

        // Utwórz obiekt PDF
        require(__DIR__ . '/../../vendor/tcpdf/tcpdf.php');
        $pdf = new TCPDF();
        $pdf->SetAutoPageBreak(true, 10);

        // Wyczyść bufor wyjściowy
        ob_clean();

        // Dodaj treść do pliku PDF
        $pdf->AddPage();
        $pdf->SetFont('times', 'N', 12);

        // Dodaj informacje o głosowaniu
        $pdf->Cell(0, 10, 'ID Glosowania: ' . $row['idGlosowania'], 0, 1);
        $pdf->Cell(0, 10, 'Tytul: ' . $row['tytul'], 0, 1);
        $pdf->Cell(0, 10, 'Tresc: ' . $row['tresc'], 0, 1);
        $pdf->Cell(0, 10, 'Opcja 1: ' . $row['opcja1'], 0, 1);
        $pdf->Cell(0, 10, 'Opcja 2: ' . $row['opcja2'], 0, 1);
        $pdf->Cell(0, 10, 'Opcja 3: ' . $row['opcja3'], 0, 1);
        $pdf->Cell(0, 10, 'Opcja 4: ' . $row['opcja4'], 0, 1);
        $pdf->Cell(0, 10, 'Opcja 5: ' . $row['opcja5'], 0, 1);
        $pdf->Cell(0, 10, 'Kworum (w %):' . $row['kworum'], 0, 1);
        $pdf->Cell(0, 10, 'Data utworzenia: ' . $row['dataRozpoczecia'], 0, 1);
        $pdf->Cell(0, 10, 'Data zakonczenia: ' . $row['dataZakonczenia'], 0, 1);
        $pdf->Cell(0, 10, 'Godzina zakonczenia: ' . $row['godzZakonczenia'], 0, 1);
        $pdf->Cell(0, 10, 'Rodzaj glosowania: ' . $row['rodzajWyniku'], 0, 1);

        // Dodaj sekcję z wynikami głosowania
        $pdf->Ln(10);
        $pdf->Cell(0, 10, 'Wyniki Glosowania:', 0, 1);

        while ($glosyRow = $glosyResult->fetch_assoc()) {
            $pdf->Cell(0, 10, $glosyRow['wybor'] . ': ' . $glosyRow['liczbaGlosow'] . ' glosow', 0, 1);
        }

        generujRaport($conn, $row['idGlosowania']);
        // Zapisz plik PDF
        $pdf->Output('raport_glosowania.pdf', 'D'); // 'D' - pobranie pliku

    } else {
        echo 'Nie znaleziono danych dla wybranego głosowania.';
    }
}

function generujRaport($conn, $pdf, $idGlosowania) {
    // Pobierz dane z bazy danych
    $query = "SELECT * FROM glosowanie WHERE idGlosowania = $idGlosowania";
    $result = $conn->query($query);

    if ($result !== false && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Utwórz nową stronę dla każdego głosowania
        $pdf->SetFont('times', 'N', 12);

        // Dodaj informacje o głosowaniu
        $pdf->Cell(0, 10, 'ID Glosowania: ' . $row['idGlosowania'], 0, 1);
        $pdf->Cell(0, 10, 'Tytul: ' . $row['tytul'], 0, 1);
        $pdf->Cell(0, 10, 'Tresc: ' . $row['tresc'], 0, 1);
        $pdf->Cell(0, 10, 'Opcja 1: ' . $row['opcja1'], 0, 1);
        $pdf->Cell(0, 10, 'Opcja 2: ' . $row['opcja2'], 0, 1);
        $pdf->Cell(0, 10, 'Opcja 3: ' . $row['opcja3'], 0, 1);
        $pdf->Cell(0, 10, 'Opcja 4: ' . $row['opcja4'], 0, 1);
        $pdf->Cell(0, 10, 'Opcja 5: ' . $row['opcja5'], 0, 1);

        // Pobierz informacje z tabeli glosUzytkownika
        $glosyQuery = "SELECT wybor, COUNT(*) as liczbaGlosow FROM glosUzytkownika WHERE idGlosowania = $idGlosowania GROUP BY wybor";
        $glosyResult = $conn->query($glosyQuery);

        // Dodaj sekcję z wynikami głosowania
        $pdf->Cell(0, 10, 'Wyniki Glosowania:', 0, 1);

        while ($glosyRow = $glosyResult->fetch_assoc()) {
            $pdf->Cell(0, 10, $glosyRow['wybor'] . ': ' . $glosyRow['liczbaGlosow'] . ' glosow', 0, 1);
        }
        $pdf->Ln(10);
    } else {
        echo 'Nie znaleziono danych dla wybranego głosowania.';
    }
}

?>
