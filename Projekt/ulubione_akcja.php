<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$uzytkownik_id = $_SESSION['user_id'];
$gra_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($gra_id > 0) {
    if ($action === 'add') {
        // IGNORE zabezpiecza przed błędem, jeśli ktoś kliknąłby dwa razy
        $stmt = $conn->prepare("INSERT IGNORE INTO ulubione (uzytkownik_id, gra_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $uzytkownik_id, $gra_id);
        $stmt->execute();
    } elseif ($action === 'remove') {
        $stmt = $conn->prepare("DELETE FROM ulubione WHERE uzytkownik_id = ? AND gra_id = ?");
        $stmt->bind_param("ii", $uzytkownik_id, $gra_id);
        $stmt->execute();
    }
}

// Powrót na stronę szczegółów tej konkretnej gry
header("Location: szczegoly.php?id=" . $gra_id);
exit();
?>