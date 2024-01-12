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

// Sprawdź, czy użytkownik jest zalogowany i ma uprawnienia sekretarza
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && $_SESSION['uprawnienia'] === 'sekretarz') {
    // Sekretarz jest zalogowany, wykonaj operacje

    // 1. Tworzenie Głosowania
    // (Tutaj dodaj formularz do utworzenia nowego głosowania)

    // 2. Uruchamianie i Zatrzymywanie Głosowania
    // (Tutaj dodaj funkcje do uruchamiania i zatrzymywania głosowania)

    // 3. Zarządzanie Użytkownikami
    // (Tutaj dodaj funkcje do dodawania, usuwania i edytowania użytkowników)

    // 4. Generowanie Raportów
    // (Tutaj dodaj funkcje do generowania raportów)

    // 5. Przeglądanie Wyników Głosowań
    // (Tutaj dodaj funkcje do przeglądania wyników głosowań)

    // 6. Dostęp do Archiwum
    // (Tutaj dodaj funkcje do dostępu do zahasłowanego pliku archiwum)

} else {
    // Użytkownik nie jest zalogowany lub nie ma uprawnień sekretarza
    header("Location: /projekty/strony/sGlosowania/logowanie/logowanie.php");
    exit();
}

$conn->close();
?>