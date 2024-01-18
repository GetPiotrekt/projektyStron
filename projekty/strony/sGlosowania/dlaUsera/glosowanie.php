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

// Sprawdź, czy użytkownik jest zalogowany
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
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
        echo '<button onclick="location.href=\'/projekty/strony/sGlosowania/dlaSekretarza/sekretarz.php\'">Dla Sekretarza</button>';
        echo '</div>';
    }

    // Nawigacja dla admina
    if ($uprawnienia === 'admin') {
        echo '<div class="admin-navigation">';
        echo '<button onclick="location.href=\'/projekty/strony/sGlosowania/dlaAdmina/admin.php\'">Dla Admina</button>';
        echo '</div>';
    }

    echo '</div>';  // Zamknij div nawigacji
} else {
    header("Location: /projekty/strony/sGlosowania/logowanie/logowanie.php");
    exit();
}

$sql = "SELECT * FROM glosowanie WHERE czyAktywne = 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $currentDate = date("Y-m-d H:i:s");
        $endDate = $row['dataZakonczenia'] . ' ' . $row['godzZakonczenia'];
        $isExpired = strtotime($currentDate) > strtotime($endDate);
        $disabled = $isExpired ? 'disabled' : '';

        $idGlosowania = $row['idGlosowania'];
        $sqlZaznaczenia = "SELECT wybor FROM glosUzytkownika WHERE idUzytkownika = ".$_SESSION['idUzytkownika']." AND idGlosowania = $idGlosowania";
        $resultZaznaczenia = $conn->query($sqlZaznaczenia);
        $wyborUzytkownika = '';

        if ($resultZaznaczenia->num_rows > 0) {
            $rowZaznaczenia = $resultZaznaczenia->fetch_assoc();
            $wyborUzytkownika = $rowZaznaczenia['wybor'];
        }

        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Wybór Kandydata</title>
            <link rel="stylesheet" type="text/css" href="\projekty\strony\sGlosowania\style.css">
            <style>
                .disabled-btn {
                    background-color: #ccc; /* Kolor dla wyłączonego przycisku */
                }
            </style>
            <script>
                function validateSelection<?php echo $row['idGlosowania']; ?>() {
                    var selectedOption = document.getElementById("opcje<?php echo $row['idGlosowania']; ?>").value;
                    var submitButton = document.getElementById("submitBtn<?php echo $row['idGlosowania']; ?>");

                    if (selectedOption === "") {
                        submitButton.disabled = true;
                        submitButton.classList.add("disabled-btn"); // Dodaj klasę dla stylu wyłączonego przycisku
                    } else {
                        submitButton.disabled = false;
                        submitButton.classList.remove("disabled-btn"); // Usuń klasę dla stylu wyłączonego przycisku
                    }
                }
            </script>
        </head>
        <body>
            <div class="container">
                <span>Data utworzenia: <?php echo $row['dataRozpoczecia']; ?></span><br>
                <h2><?php echo mb_substr($row['tytul'], 0, mb_strlen($row['tytul'])); ?></h2>
                <span><?php echo $isExpired ? 'Nieaktualne od: ' . $endDate : 'Aktywne do: ' . $endDate; ?></span><br>
                <span><?php echo mb_substr($row['tresc'], 0, mb_strlen($row['tresc'])); ?></span><br>

                <form action="obslugaGlosu.php" method="post">
                    <input type="hidden" name="idGlosowania" value="<?php echo $row['idGlosowania']; ?>">
                    <?php
                    $opcje = array(
                        'opcja1' => $row['opcja1'],
                        'opcja2' => $row['opcja2'],
                        'opcja3' => $row['opcja3'],
                        'opcja4' => $row['opcja4'],
                        'opcja5' => $row['opcja5']
                    );

                    if (count(array_filter($opcje, function($value) { return $value !== ''; })) > 0) {
                        echo '<select name="opcje" id="opcje' . $row['idGlosowania'] . '" onchange="validateSelection' . $row['idGlosowania'] . '()" ' . $disabled . '>';
                        echo '<option value="" disabled selected>Wybierz opcję</option>';

                        foreach ($opcje as $value => $opcja) {
                            if ($opcja !== '') {
                                echo "<option value='$value' " . ($wyborUzytkownika === $value ? 'selected' : '') . ">$opcja</option>";
                            }
                        }

                        echo '</select>';
                        echo '<button type="submit" id="submitBtn' . $row['idGlosowania'] . '" class="votebtn ' . ($disabled ? 'disabled-btn' : '') . '" ' . $disabled . ' disabled>Zatwierdź</button>';
                    } else {
                        echo '<p>Brak dostępnych opcji do wyboru.</p>';
                    }
                    ?>
                </form>
            </div>
        </body>
        </html>

        <?php
    }
} else {
    echo "Brak dostępnych wydarzeń.";
}

$conn->close();
?>
