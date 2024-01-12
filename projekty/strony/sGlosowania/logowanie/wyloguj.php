<?php
session_start();

// Zniszcz sesję
session_destroy();

// Przekieruj na stronę logowania
header("Location: /projekty/strony/sGlosowania/logowanie/logowanie.php");
exit();
?>