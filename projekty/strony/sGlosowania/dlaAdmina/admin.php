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

// Sprawdź, czy użytkownik jest zalogowany i ma uprawnienia admina
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $_SESSION['uprawnienia'] === 'admin') {
    // Admin jest zalogowany, wykonaj operacje

    // 1. Pełna Kontrola nad Uprawnieniami
    // (Tutaj dodaj funkcje do zmiany uprawnień dla wszystkich użytkowników)

    // 2. Zarządzanie Konfiguracją Systemu
    // (Tutaj dodaj funkcje do dostosowywania ustawień systemu)

    // 3. Monitoring Bezpieczeństwa
    // (Tutaj dodaj funkcje do monitorowania logów bezpieczeństwa)

    // 4. Zarządzanie Archiwum i Backupami
    // (Tutaj dodaj funkcje związane z archiwizacją danych)

    // 5. Tworzenie i Edytowanie Kont Adminów
    // (Tutaj dodaj funkcje do tworzenia i edycji innych administratorów)

    // 6. Zarządzanie Modułami i Rozszerzeniami
    // (Tutaj dodaj funkcje do zarządzania modułami i rozszerzeniami)

    // 7. Rozwiązywanie Problemów Technicznych
    // (Tutaj dodaj funkcje do zaawansowanego rozwiązywania problemów technicznych)

    // 8. Ustawienia Bezpieczeństwa Systemu
    // (Tutaj dodaj funkcje do zarządzania ustawieniami bezpieczeństwa)

} else {
    // Użytkownik nie jest zalogowany lub nie ma uprawnień admina
    header("Location: /projekty/strony/sGlosowania/logowanie/logowanie.php");
    exit();
}

$conn->close();
?>