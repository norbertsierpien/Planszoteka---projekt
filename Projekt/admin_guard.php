<?php
include_once 'db.php';

// Jeśli użytkownik nie jest zalogowany LUB jego rola to nie admin
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'admin') {
    // Przekieruj na stronę główną z komunikatem błędu
    header("Location: index.php?error=brak_uprawnien");
    exit();
}
?>