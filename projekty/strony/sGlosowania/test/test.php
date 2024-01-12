<?php
$hostname = "aps.h.filess.io";
$database = "systemGlosowania_ropewiseto";
$port = "3306";
$username = "systemGlosowania_ropewiseto";
$password = "3fa85b505316139f726ea34a83d958895d0cb23e";

// Tworzenie połączenia z bazą danych
$conn = new mysqli($hostname, $username, $password, $database, $port);

// Sprawdzenie połączenia
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Zapytanie SQL
$sql = "SELECT id, nazwaUzytkownika FROM uzytkownicy";

// Wykonanie zapytania
$result = $conn->query($sql);

// Sprawdzenie, czy są wyniki
if ($result->num_rows > 0) {
    // Wyświetlenie wyników
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"] . ", Nazwa użytkownika: " . $row["nazwaUzytkownika"] . "<br>";
    }
} else {
    echo "Brak użytkowników w bazie danych.";
}

// Zamknięcie połączenia
$conn->close();
?>
