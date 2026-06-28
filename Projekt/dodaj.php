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
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tytul = trim($_POST['tytul']);
    $opis = trim($_POST['opis']);
    $kategoria_id = intval($_POST['kategoria_id']);
    $uzytkownik_id = $_SESSION['user_id'];
    
    $filename = "default.jpg";
    if (isset($_FILES['zdjecie']) && $_FILES['zdziecie']['error'] == 0) {
        $filename = time() . "_" . $_FILES['zdjecie']['name'];
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        move_uploaded_file($_FILES['zdjecie']['tmp_name'], "uploads/" . $filename);
    }

    $stmt = $conn->prepare("INSERT INTO gry (tytul, opis, zdjecie, kategoria_id, uzytkownik_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $tytul, $opis, $filename, $kategoria_id, $uzytkownik_id);
    if($stmt->execute()) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj Grę - Planszoteka</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo"> Planszo<b>Teka</b></div>
        <nav class="nav-menu">
            <a href="index.php"> Główna</a>
            <a href="dodaj.php"> Dodaj grę</a>
            <?php if (isset($_SESSION['rola']) && $_SESSION['rola'] === 'admin'): ?>
                <a href="admin.php" class="admin-link"> Panel Admina</a>
            <?php endif; ?>
            <span class="user-badge"> <?php echo htmlspecialchars($_SESSION['login']); ?></span>
            <a href="logout.php" class="logout-btn">Wyloguj</a>
        </nav>
    </div>
</header>

<main class="container">
    <div class="admin-box" style="max-width: 600px; margin: 0 auto;">
        <h3 style="margin-bottom: 20px;">Dodaj nową grę do swojej kolekcji</h3>
        <form method="POST" enctype="multipart/form-data">
            <label>Tytuł gry:</label>
            <input type="text" name="tytul" placeholder="np. Carcassonne" required>
            
            <label>Opis gry:</label>
            <textarea name="opis" rows="5" placeholder="Krótki opis gry, mechaniki itp."></textarea>
            
            <label>Kategoria:</label>
            <select name="kategoria_id" style="margin-bottom: 15px;">
                <?php while($kat = $query_kat->fetch_assoc()): ?>
                    <option value="<?php echo $kat['id']; ?>"><?php echo htmlspecialchars($kat['nazwa']); ?></option>
                <?php endwhile; ?>
            </select>
            
            <label>Zdjęcie okładki (opcjonalnie):</label>
            <input type="file" name="zdjecie" style="margin-bottom: 20px;">
            
            <button type="submit">Zapisz grę w kolekcji</button>
        </form>
    </div>
</main>

<footer class="main-footer">
    <p>&copy; 2026 - Projekt Indywidualny: Podstawy Technologii WWW</p>
</footer>

</body>
</html>