<?php
include 'db.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$recenzja_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$gra_id = isset($_GET['gra_id']) ? intval($_GET['gra_id']) : 0;
$uzytkownik_id = $_SESSION['user_id'];
$rola = isset($_SESSION['rola']) ? $_SESSION['rola'] : 'user';

if ($recenzja_id > 0 && $gra_id > 0) {
    if ($rola === 'admin') {
        // Administrator może usunąć każdą recenzję
        $stmt = $conn->prepare("DELETE FROM recenzje WHERE id = ?");
        $stmt->bind_param("i", $recenzja_id);
        $stmt->execute();
    } else {
        // Zwykły użytkownik może usunąć tylko swoją własną recenzję
        $stmt = $conn->prepare("DELETE FROM recenzje WHERE id = ? AND uzytkownik_id = ?");
        $stmt->bind_param("ii", $recenzja_id, $uzytkownik_id);
        $stmt->execute();
    }
}

// Powrót na stronę szczegółów gry, z której wywołano usuwanie
header("Location: szczegoly.php?id=" . $gra_id);
exit();
?>