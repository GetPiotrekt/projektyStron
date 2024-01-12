<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test";

// Utwórz połączenie
$conn = new mysqli($servername, $username, $password, $dbname);

// Sprawdź połączenie
if ($conn->connect_error) {
  die("Błąd połączenia: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $email = $_POST['email'];
    $telefon = $_POST['telefon'];
    $data = $_POST['data'];
    $godzina = $_POST['godzina'];
    $uwagi = $_POST['uwagi'];
    
    $sql = "INSERT INTO dane (imie, nazwisko, email, telefon, data, godzina, uwagi)
    VALUES ('$imie', '$nazwisko', '$email', '$telefon', '$data', '$godzina', '$uwagi')";

    if ($conn->query($sql) === TRUE) {
      echo "Nowe zgłoszenie zostało pomyślnie dodane";
    } else {
      echo "Błąd: " . $sql . "<br>" . $conn->error;
    }

}

$conn->close();
?>