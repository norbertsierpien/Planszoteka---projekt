<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$gry_w_koszyku = [];
$suma_calkowita = 0;

// Przykładowa stała cena dla gier, jeśli nie masz kolumny 'cena' w bazie (np. 129.99 zł)
// Jeśli masz kolumnę cena w tabeli gry, zamień poniższe na realne pobieranie z bazy
$cena_domyslna = 129.90; 

if (!empty($_SESSION['koszyk'])) {
    // Pobieramy identyfikatory gier z koszyka
    $ids = implode(',', array_keys($_SESSION['koszyk']));
    // Pobieramy z bazy tylko te gry, które są w koszyku
    $result = $conn->query("SELECT * FROM gry WHERE id IN ($ids)");
    
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $ilosc = $_SESSION['koszyk'][$id];
        $cena = isset($row['cena']) ? $row['cena'] : $cena_domyslna;
        $wartosc = $cena * $ilosc;
        $suma_calkowita += $wartosc;
        
        $row['ilosc'] = $ilosc;
        $row['cena'] = $cena;
        $row['wartosc'] = $wartosc;
        $gry_w_koszyku[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Twój Koszyk - Sklep z Grami</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo"> Gra<b>Sklep</b></div>
        <nav class="nav-menu">
            <a href="index.php"> Sklep</a>
            <a href="koszyk.php" class="active"> Koszyk (<?php echo isset($_SESSION['koszyk']) ? array_sum($_SESSION['koszyk']) : 0; ?>)</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="user-badge"> <?php echo htmlspecialchars($_SESSION['login']); ?></span>
                <a href="logout.php" class="logout-btn">Wyloguj</a>
            <?php else: ?>
                <a href="login.php"> Zaloguj się</a>
                <a href="rejestracja.php"> Rejestracja</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">
    <h2> Twój Koszyk zakupowy</h2>
    
    <?php if (empty($gry_w_koszyku)): ?>
        <div style="text-align: center; padding: 50px 0;">
            <p style="color: var(--text-muted); font-size: 1.2rem;">Twój koszyk jest pusty.</p>
            <a href="index.php" class="btn-filter" style="margin-top: 20px;">Powrót do zakupów</a>
        </div>
    <?php else: ?>
        <div class="admin-box-modern" style="margin-top: 20px;">
            <table class="admin-table-modern" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Gra</th>
                        <th>Cena</th>
                        <th>Ilość</th>
                        <th>Wartość</th>
                        <th style="text-align: right;">Akcja</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gry_w_koszyku as $produkt): ?>
                    <tr>
                        <td><b><?php echo htmlspecialchars($produkt['tytul']); ?></b></td>
                        <td><?php echo number_format($produkt['cena'], 2); ?> zł</td>
                        <td><?php echo $produkt['ilosc']; ?> szt.</td>
                        <td><?php echo number_format($produkt['wartosc'], 2); ?> zł</td>
                        <td style="text-align: right;">
                            <a href="koszyk_akcja.php?action=remove&id=<?php echo $produkt['id']; ?>" class="btn-review-delete"> Usuń</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <h3>Suma do zapłaty: <span style="color: var(--accent-color);"><?php echo number_format($suma_calkowita, 2); ?> zł</span></h3>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="zamowienie_sukces.php" class="btn-filter" style="background: #2ecc71; color: #fff;"> Przejdź do płatności (Kupuję)</a>
                <?php else: ?>
                    <a href="login.php?redirect=koszyk.php" class="btn-filter" style="background: #f1c40f; color: #121824;"> Zaloguj się, aby kupić</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

</body>
</html>