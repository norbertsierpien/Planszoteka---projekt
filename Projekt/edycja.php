<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

// 2. BLOKADA: Jeśli użytkownik nie jest adminem, wyrzuć go na stronę główną z komunikatem błędu
if (!isset($_SESSION['rola']) || $_SESSION['rola'] !== 'admin') {
    header("Location: index.php?error=brak_uprawnien");
    exit();
}
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Pobranie aktualnych danych gry
$stmt = $conn->prepare("SELECT * FROM gry WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$gra = $stmt->get_result()->fetch_assoc();

if (!$gra) {
    die("Nie znaleziono takiego elementu.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tytul = $_POST['tytul'];
    $opis = $_POST['opis'];
    $kategoria_id = $_POST['kategoria_id'];
    $filename = $gra['zdjecie']; // domyślnie zostaje stare zdjęcie

    // Jeśli przesłano nowe zdjęcie
    if (isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] == 0) {
        $filename = time() . "_" . $_FILES['zdziecie']['name'];
        move_uploaded_file($_FILES['zdjecie']['tmp_name'], "uploads/" . $filename);
    }

    $update = $conn->prepare("UPDATE gry SET tytul = ?, opis = ?, zdjecie = ?, kategoria_id = ? WHERE id = ?");
    $update->bind_param("sssii", $tytul, $opis, $filename, $kategoria_id, $id);
    if ($update->execute()) {
        header("Location: index.php");
        exit();
    }
}

$query_kat = $conn->query("SELECT * FROM kategorie");
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edytuj Grę</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <h2>Edycja: <?php echo htmlspecialchars($gra['tytul']); ?></h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="tytul" value="<?php echo htmlspecialchars($gra['tytul']); ?>" required><br><br>
        <textarea name="opis"><?php echo htmlspecialchars($gra['opis']); ?></textarea><br><br>
        
        <label>Kategoria:</label>
        <select name="kategoria_id">
            <?php while($kat = $query_kat->fetch_assoc()): ?>
                <option value="<?php echo $kat['id']; ?>" <?php if($kat['id'] == $gra['kategoria_id']) echo 'selected'; ?>>
                    <?php echo $kat['nazwa']; ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>
        
        <label>Aktualne zdjęcie: <?php echo $gra['zdjecie']; ?></label><br>
        <input type="file" name="zdjecie"><br><br>
        
        <button type="submit">Zapisz zmiany</button>
        <a href="index.php" style="margin-left: 10px;">Anuluj</a>
    </form>
</body>
</html>