<?php
include 'admin_guard.php'; // WYWOŁANIE STRAŻNIKA NA SAMEJ GÓRZE PLIKU!

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nowa_kategoria'])) {
    $nowa_kat = trim($_POST['nowa_kategoria']);
    
    if (!empty($nowa_kat)) {
        // Tutaj Twój dotychczasowy kod wykonujący zapytanie INSERT do bazy danych
        $stmt = $conn->prepare("INSERT INTO kategorie (nazwa) VALUES (?)");
        $stmt->bind_param("s", $nowa_kat);
        $stmt->execute();
        
        // Przeładuj stronę, aby wyczyścić formularz i odświeżyć widok
        header("Location: admin.php");
        exit();
    }
}

$total_users = $conn->query("SELECT COUNT(*) FROM uzytkownicy")->fetch_row()[0];
$total_games = $conn->query("SELECT COUNT(*) FROM gry")->fetch_row()[0];
$wszystkie_gry = $conn->query("SELECT gry.id, gry.tytul, uzytkownicy.login FROM gry JOIN uzytkownicy ON gry.uzytkownik_id = uzytkownicy.id");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administratora</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<header class="main-header">
    <div class="header-container">
        <div class="logo"> Planszo<b>Teka</b></div>
        <nav class="nav-menu">
            <a href="index.php"> Główna</a>
            <a href="dodaj.php"> Dodaj grę</a>
            <a href="admin.php" class="admin-link"> Panel Admina</a>
            <span class="user-badge"> <?php echo htmlspecialchars($_SESSION['login']); ?></span>
            <a href="logout.php" class="logout-btn">Wyloguj</a>
        </nav>
    </div>
</header>

<main class="container admin-panel">
    <div class="admin-header-block">
        <h2> Panel Zarządzania Systemem</h2>
        <p>Witaj w centrum dowodzenia. Zarządzaj zawartością bazy danych, użytkownikami oraz kategoriami gier.</p>
    </div>

    <section class="stats-grid">
        <div class="stat-card card-games">
            <div class="stat-icon"></div>
            <div class="stat-info">
                <h3>Wszystkie Gry</h3>
                <div class="stat-number">
                    <?php echo $conn->query("SELECT COUNT(*) FROM gry")->fetch_row()[0]; ?>
                </div>
            </div>
        </div>

        <div class="stat-card card-users">
            <div class="stat-icon"></div>
            <div class="stat-info">
                <h3>Użytkownicy</h3>
                <div class="stat-number">
                    <?php echo $conn->query("SELECT COUNT(*) FROM uzytkownicy")->fetch_row()[0]; ?>
                </div>
            </div>
        </div>

        <div class="stat-card card-reviews">
            <div class="stat-icon"></div>
            <div class="stat-info">
                <h3>Recenzje</h3>
                <div class="stat-number">
                    <?php echo $conn->query("SELECT COUNT(*) FROM recenzje")->fetch_row()[0]; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="admin-management-grid">
        
        <div class="admin-box-modern">
            <h3> Nowa Kategoria</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 15px;">Dodaj nową grupę, do której gracze będą przypisywać planszówki.</p>
            
            <form method="POST" action="admin.php">
                <label>Nazwa kategorii</label>
                <input type="text" name="nowa_kategoria" placeholder="np. Strategiczne, Imprezowe" required>
                <button type="submit" class="btn-filter" style="width: 100%; margin-top: 15px;">Utwórz kategorię</button>
            </form>
        </div>

        <div class="admin-box-modern">
            <h3> Rejestr Użytkowników</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 15px;">Lista zarejestrowanych kont z możliwością zarządzania uprawnieniami.</p>
            
            <div class="table-wrapper">
                <table class="admin-table-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Użytkownik</th>
                            <th>Rola</th>
                            <th style="text-align: right;">Akcja</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $uzytkownicy = $conn->query("SELECT * FROM uzytkownicy ORDER BY id ASC");
                        while($user = $uzytkownicy->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><span class="id-tag">#<?php echo $user['id']; ?></span></td>
                            <td><b><?php echo htmlspecialchars($user['login']); ?></b></td>
                            <td>
                                <span class="role-badge <?php echo $user['rola'] === 'admin' ? 'role-admin' : 'role-user'; ?>">
                                    <?php echo strtoupper($user['rola']); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="zmien_role.php?id=<?php echo $user['id']; ?>" class="btn-action-panel">🔄 Zmień rolę</a>
                                <?php else: ?>
                                    <span style="font-size: 0.8rem; color: var(--text-muted); italic">Ty</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<footer class="main-footer">
    <p>&copy; 2026 - Projekt Indywidualny: Podstawy Technologii WWW</p>
</footer>

</body>
</html>