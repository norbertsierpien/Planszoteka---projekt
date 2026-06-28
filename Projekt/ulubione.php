<?php
include 'db.php'; // Zawiera sesję i połączenie $conn

// Jeśli użytkownik nie jest zalogowany, przekieruj do logowania
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uzytkownik_id = $_SESSION['user_id'];

// Pobranie tylko tych gier, które zalogowany użytkownik dodał do ulubionych
$sql = "SELECT g.* FROM gry g 
        INNER JOIN ulubione u ON g.id = u.gra_id 
        WHERE u.uzytkownik_id = $uzytkownik_id";
$GryUlubione = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twoje Ulubione Gry - PlanszoTeka</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo">Planszo<b>Teka</b></div>
        <nav class="nav-menu">
            <a href="index.php"> Sklep</a>
            <a href="ulubione.php" class="active"> Ulubione</a>
            <a href="koszyk.php"> Koszyk (<?php echo isset($_SESSION['koszyk']) ? array_sum($_SESSION['koszyk']) : 0; ?>)</a>
            
            <span class="user-badge"> <?php echo htmlspecialchars($_SESSION['login']); ?></span>
            <a href="logout.php" class="logout-btn">Wyloguj się</a>
        </nav>
    </div>
</header>

<main class="container">
    <h2 style="margin-bottom: 20px; color: var(--primary-color);"> Twoje ulubione gry</h2>

    <section class="grid-container">
        <?php if ($GryUlubione && $GryUlubione->num_rows > 0): ?>
            <?php while($gra = $GryUlubione->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-img-wrapper">
                        <img src="uploads/<?php echo !empty($gra['zdjecie']) ? $gra['zdjecie'] : 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($gra['tytul']); ?>">
                    </div>
                    <div class="card-content">
                        <h3><?php echo htmlspecialchars($gra['tytul']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($gra['opis'], 0, 80)) . '...'; ?></p>
                        
                        <div class="actions" style="margin-top: auto; padding-top: 15px; display: flex; flex-direction: column; gap: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                <span style="font-weight: bold; color: var(--success-color);">
                                    <?php echo isset($gra['cena']) ? number_format($gra['cena'], 2) . ' zł' : '129.90 zł'; ?>
                                </span>
                                <a href="szczegoly.php?id=<?php echo $gra['id']; ?>" style="font-size: 0.9rem; color: var(--accent-color);"> Szczegóły</a>
                            </div>

                            <div style="display: flex; gap: 8px; width: 100%;">
                                <a href="koszyk_akcja.php?action=add&id=<?php echo $gra['id']; ?>" class="btn-add-cart" style="flex: 1; text-align: center;"> Koszyk</a>
                                <a href="ulubione_akcja.php?action=remove&id=<?php echo $gra['id']; ?>" class="btn-fav-active" style="padding: 8px 12px;">❤️</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-favorites-box" style="grid-column: 1/-1; text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px;">
                <p style="font-size: 1.2rem; color: #7f8c8d;">Nie dodałeś jeszcze żadnej gry do ulubionych.</p>
                <a href="index.php" class="btn-filter" style="display: inline-block; margin-top: 15px; text-decoration: none;">Przeglądaj sklep</a>
            </div>
        <?php endif; ?>
    </section>
</main>

<footer class="main-footer">
    <p>&copy; 2026 - PlanszoTeka. Wszelkie prawa zastrzeżone.</p>
</footer>

</body>
</html>