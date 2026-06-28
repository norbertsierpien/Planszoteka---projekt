<?php
include 'db.php';


// Bezpieczne przypisanie: jeśli zalogowany, pobierz ID, jeśli nie - przypisz 0 lub null
$uzytkownik_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Logika sprawdzania ulubionych (wykona się tylko dla zalogowanego)
$is_fav = false;
if ($uzytkownik_id > 0) {
    $gra_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $check_fav = $conn->query("SELECT id FROM ulubione WHERE uzytkownik_id = $uzytkownik_id AND gra_id = $gra_id");
    if ($check_fav && $check_fav->num_rows > 0) {
        $is_fav = true;
    }
}
$gra_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. OBSŁUGA DODAWANIA RECENZJI
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dodaj_recenzje'])) {
    $ocena = intval($_POST['ocena']);
    $tresc = trim($_POST['tresc']);
    
    if ($ocena >= 1 && $ocena <= 5 && !empty($tresc)) {
        $stmt_rev = $conn->prepare("INSERT INTO recenzje (gra_id, uzytkownik_id, ocena, tresc) VALUES (?, ?, ?, ?)");
        $stmt_rev->bind_param("iiis", $gra_id, $uzytkownik_id, $ocena, $tresc);
        $stmt_rev->execute();
        header("Location: szczegoly.php?id=" . $gra_id);
        exit();
    }
}

// 2. POBRANIE DANYCH O GRZE
$stmt = $conn->prepare("SELECT gry.*, kategorie.nazwa AS kat_nazwa, uzytkownicy.login AS autor 
                        FROM gry 
                        JOIN kategorie ON gry.kategoria_id = kategorie.id 
                        JOIN uzytkownicy ON gry.uzytkownik_id = uzytkownicy.id 
                        WHERE gry.id = ?");
$stmt->bind_param("i", $gra_id);
$stmt->execute();
$gra = $stmt->get_result()->fetch_assoc();

if (!$gra) {
    header("Location: index.php");
    exit();
}

// 3. SPRAWDZENIE CZY JEST W ULUBIONYCH
$check_fav = $conn->prepare("SELECT id FROM ulubione WHERE uzytkownik_id = ? AND gra_id = ?");
$check_fav->bind_param("ii", $uzytkownik_id, $gra_id);
$check_fav->execute();
$is_fav = $check_fav->get_result()->num_rows > 0;

// 4. POBRANIE ŚREDNIEJ OCENY
$avg_res = $conn->query("SELECT AVG(ocena) as srednia FROM recenzje WHERE gra_id = $gra_id")->fetch_assoc();
$srednia_ocena = $avg_res['srednia'] ? round($avg_res['srednia'], 1) : "Brak ocen";

// 5. POBRANIE LISTY RECENZJI
// Stara linia (powodująca błąd):
$recenzje_query = $conn->query("SELECT recenzje.*, uzytkownicy.login FROM recenzje 
                                JOIN uzytkownicy ON recenzje.uzytkownik_id = uzytkownicy.id 
                                WHERE recenzje.gra_id = $gra_id ORDER BY recenzje.data_dodania DESC");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($gra['tytul']); ?> - Szczegóły</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo"> Planszo<b>Teka</b></div>
        <nav class="nav-menu">
    <a href="index.php"> Sklep</a>
    <a href="koszyk.php"> Koszyk (<?php echo isset($_SESSION['koszyk']) ? array_sum($_SESSION['koszyk']) : 0; ?>)</a>
    
    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['login'])): ?>
        <span class="user-badge"> <?php echo htmlspecialchars($_SESSION['login']); ?></span>
        <?php if (isset($_SESSION['rola']) && $_SESSION['rola'] === 'admin'): ?>
            <a href="admin.php"> Panel Admina</a>
        <?php endif; ?>
        <a href="logout.php" class="logout-btn">Wyloguj się</a>
    <?php else: ?>
        <a href="login.php"> Zaloguj się</a>
        <a href="rejestracja.php"> Rejestracja</a>
    <?php endif; ?>
</nav>
    </div>
</header>

<main class="container">
    <div class="details-container">
        <div class="details-image-wrapper">
            <img src="uploads/<?php echo $gra['zdjecie']; ?>" alt="Okładka">
        </div>
        <div class="details-info">
            <div>
                <h2 class="details-title"><?php echo htmlspecialchars($gra['tytul']); ?></h2>
                <div class="details-meta">
                    <span class="user-tag">Kategoria: <?php echo htmlspecialchars($gra['kat_nazwa']); ?></span>
                    <span class="rating-badge">⭐ Średnia ocena: <?php echo $srednia_ocena; ?></span>
                </div>
                
                <h4 class="details-subtitle">Opis gry:</h4>
                <div class="details-description"><?php echo htmlspecialchars($gra['opis']); ?></div>
                
                <p class="details-author">Wpis dodany przez: <b><?php echo htmlspecialchars($gra['autor']); ?></b></p>
            </div>

          <div class="details-actions" style="display: flex; gap: 15px; margin-top: 20px; align-items: center;">
    <a href="koszyk_akcja.php?action=add&id=<?php echo $gra['id']; ?>" class="btn-add-cart" style="padding: 12px 24px; font-size: 1rem;"> Dodaj do koszyka</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($is_fav): ?>
            <a href="ulubione_akcja.php?action=remove&id=<?php echo $gra['id']; ?>" class="btn-favorite-remove">💔 Usuń z ulubionych</a>
        <?php else: ?>
            <a href="ulubione_akcja.php?action=add&id=<?php echo $gra['id']; ?>" class="btn-favorite-add">⭐ Dodaj do ulubionych</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="login.php" class="btn-favorite-add" style="background: rgba(255,255,255,0.05); color: var(--text-muted); border: 1px dashed rgba(255,255,255,0.2);"> Zaloguj się, by dodać do ulubionych</a>
    <?php endif; ?>

    <a href="index.php" class="btn-back-link">⬅ Powrót do sklepu</a>
</div>
        </div>
    </div>

    <div class="reviews-section">
        <div class="review-box-form">
            <h3> Dodaj swoją recenzję</h3>
            <form method="POST">
                <label>Twoja ocena (1-5):</label>
                <select name="ocena" required style="margin-bottom: 15px;">
                    <option value="5">⭐⭐⭐⭐⭐ (5/5) - Genialna</option>
                    <option value="4">⭐⭐⭐⭐ (4/5) - Bardzo dobra</option>
                    <option value="3">⭐⭐⭐ (3/5) - Przeciętna</option>
                    <option value="2">⭐⭐ (2/5) - Słaba</option>
                    <option value="1">⭐ (1/5) - Tragedia</option>
                </select>
                
                <label>Treść opinii:</label>
                <textarea name="tresc" rows="4" placeholder="Napisz co myślisz o tym tytule..." required></textarea>
                
                <button type="submit" name="dodaj_recenzje">Wyślij recenzję</button>
                <?php if (isset($_SESSION['user_id'])): ?>
    <?php else: ?>
    <p style="color: var(--text-muted); text-align: center; padding: 15px; background: rgba(255,255,255,0.02); border-radius: 6px;">
        💬 Chcesz ocenić grę? <a href="login.php" style="color: var(--accent-color);">Zaloguj się</a>, aby dodać recenzję.
    </p>
<?php endif; ?>
            </form>
        </div>

        <div class="reviews-list-container">
            <h3> Recenzje graczy (<?php echo $recenzje_query->num_rows; ?>)</h3>
            <?php if ($recenzje_query->num_rows > 0): ?>
                <?php while($rev = $recenzje_query->fetch_assoc()): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <span class="review-user"> <?php echo htmlspecialchars($rev['login']); ?></span>
                            <span class="review-stars"><?php echo str_repeat('⭐', $rev['ocena']); ?></span>
                        </div>
                        <p class="review-text"><?php echo htmlspecialchars($rev['tresc']); ?></p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                            <?php if ($_SESSION['user_id'] == $rev['uzytkownik_id'] || (isset($_SESSION['rola']) && $_SESSION['rola'] === 'admin')): ?>
                                <a href="usun_recenzje.php?id=<?php echo $rev['id']; ?>&gra_id=<?php echo $gra_id; ?>" 
                                   class="btn-review-delete" 
                                   onclick="return confirm('Czy na pewno chcesz usunąć tę recenzję?');">
                                    Usuń
                                </a>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>
                            
                            <span class="review-date"><?php echo date("d.m.Y H:i", strtotime($rev['data_dodania'])); ?></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: var(--text-muted); padding: 20px 0; text-align: center;">Brak recenzji. Bądź pierwszym, który oceni tę grę!</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer class="main-footer">
    <p>&copy; 2026 - Projekt Indywidualny: Podstawy Technologii WWW</p>
</footer>

</body>
</html>