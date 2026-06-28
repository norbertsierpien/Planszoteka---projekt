<?php
include 'db.php'; // Tutaj uruchamia się session_start()

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$gra_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Jeśli koszyk jeszcze nie istnieje w sesji, utwórz go jako pustą tablicę
if (!isset($_SESSION['koszyk'])) {
    $_SESSION['koszyk'] = [];
}

if ($gra_id > 0) {
    if ($action === 'add') {
        // Jeśli gra jest już w koszyku, zwiększ ilość, jeśli nie - dodaj z ilością 1
        if (isset($_SESSION['koszyk'][$gra_id])) {
            $_SESSION['koszyk'][$gra_id]++;
        } else {
            $_SESSION['koszyk'][$gra_id] = 1;
        }
        header("Location: index.php?status=dodano_do_koszyka");
        exit();
    }
    
    if ($action === 'remove') {
        // Usuń grę całkowicie z koszyka
        if (isset($_SESSION['koszyk'][$gra_id])) {
            unset($_SESSION['koszyk'][$gra_id]);
        }
        header("Location: koszyk.php");
        exit();
    }
}

header("Location: index.php");
exit();
?>