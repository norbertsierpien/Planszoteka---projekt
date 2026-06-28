<?php
include 'db.php'; 

// Filtrowanie i wyszukiwanie (tylko po tytule i opisie)
$where = [];
if (!empty($_GET['szukaj'])) {
    $szukaj = $conn->real_escape_string($_GET['szukaj']);
    $where[] = "(tytul LIKE '%$szukaj%' OR opis LIKE '%$szukaj%')";
}

$sql = "SELECT * FROM gry";
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$Gry = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlanszoTeka - Sklep z Grami Planszowymi</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo">Planszo<b>Teka</b></div>
        <nav class="nav-menu">
              <a href="index.php" class="active"> Sklep</a>
    
        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['rola']) && $_SESSION['rola'] === 'admin'): ?>
        <a href="admin.php" class="admin-link"> Panel Admina</a>
        <a href="dodaj.php" class="admin-link"> Dodaj Grę</a>
       <?php endif; ?>
       <?php if (isset($_SESSION['user_id'])): ?>
        <a href="ulubione.php"> Ulubione</a>
       <?php endif; ?>
       <a href="koszyk.php"> Koszyk (<?php echo isset($_SESSION['koszyk']) ? array_sum($_SESSION['koszyk']) : 0; ?>)</a>
       <?php if (isset($_SESSION['user_id']) && isset($_SESSION['login'])): ?>
        <span class="user-badge"> <?php echo htmlspecialchars($_SESSION['login']); ?></span>
        <a href="logout.php" class="logout-btn">Wyloguj się</a>
       <?php else: ?>
        <a href="login.php"> Zaloguj się</a>
        <a href="rejestracja.php"> Rejestracja</a>
      <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">
    
    <section class="filters" style="margin-bottom: 30px;">
        <form method="GET" action="index.php">
            <input type="text" name="szukaj" placeholder=" Szukaj gry po tytule lub opisie..." value="<?php echo isset($_GET['szukaj']) ? htmlspecialchars($_GET['szukaj']) : ''; ?>" style="flex: 1;">
            <button type="submit" class="btn-filter">Szukaj</button>
        </form>
    </section>

    <section class="grid-container">
        <?php if ($Gry && $Gry->num_rows > 0): ?>
            <?php while($gra = $Gry->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-img-wrapper">
                        <img src="uploads/<?php echo !empty($gra['zdjecie']) ? $gra['zdjecie'] : 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($gra['tytul']); ?>">
                    </div>
                    <div class="card-content">
                        <h3><?php echo htmlspecialchars($gra['tytul']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($gra['opis'], 0, 80)) . '...'; ?></p>
                        
                        <div class="actions" style="margin-top: auto; padding-top: 15px; display: flex; flex-direction: column; gap: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 5px;">
                                <span style="font-weight: bold; color: var(--success-color); font-size: 1.1rem;">
                                    <?php echo isset($gra['cena']) ? number_format($gra['cena'], 2) . ' zł' : '129.90 zł'; ?>
                                </span>
                                
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <a href="szczegoly.php?id=<?php echo $gra['id']; ?>" style="font-size: 0.9rem; color: var(--accent-color);"> Szczegóły</a>
                                    
                                    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['rola']) && $_SESSION['rola'] === 'admin'): ?>
                                        <a href="usun.php?id=<?php echo $gra['id']; ?>" 
                                           style="font-size: 0.9rem; color: var(--danger-color); font-weight: bold;" 
                                           onclick="return confirm('Czy na pewno chcesz usunąć tę grę?');">
                                            Usuń
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div style="display: flex; gap: 8px; width: 100%;">
                                <?php if (isset($_SESSION['user_id'])): 
                                    $uzytkownik_id = $_SESSION['user_id'];
                                    $gra_id = $gra['id'];
                                    $check_fav = $conn->query("SELECT id FROM ulubione WHERE uzytkownik_id = $uzytkownik_id AND gra_id = $gra_id");
                                    $is_fav = ($check_fav && $check_fav->num_rows > 0);
                                ?>
                                    <a href="koszyk_akcja.php?action=add&id=<?php echo $gra['id']; ?>" class="btn-add-cart" style="flex: 1; text-align: center;"> Koszyk</a>
                                    
                                    <?php if ($is_fav): ?>
                                        <a href="ulubione_akcja.php?action=remove&id=<?php echo $gra['id']; ?>" class="btn-fav-active" style="padding: 8px 12px;">❤️</a>
                                    <?php else: ?>
                                        <a href="ulubione_akcja.php?action=add&id=<?php echo $gra['id']; ?>" class="btn-fav-inactive" style="padding: 8px 12px;">🤍</a>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <a href="login.php" class="btn-add-cart" style="flex: 1; text-align: center; background-color: #7f8c8d;">🛒 Koszyk</a>
                                    <a href="login.php" class="btn-fav-inactive" style="padding: 8px 12px;">🤍</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-favorites-box">
                Nie znaleziono żadnych gier planszowych spełniających kryteria.
            </div>
        <?php endif; ?>
    </section>
</main>

<footer class="main-footer">
    <p>&copy; 2026 - PlanszoTeka. Wszelkie prawa zastrzeżone.</p>
</footer>

</body>
</html>