<?php
include 'db.php';

// Jeśli użytkownik jest zalogowany, nie ma potrzeby, by się rejestrował
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$komunikat = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rejestracja'])) {
    $login = trim($_POST['login']);
    $haslo = $_POST['haslo'];

    if (empty($login) || empty($haslo)) {
        $komunikat = "<p class='alert danger' style='background-color:rgba(231, 76, 60, 0.1); color:var(--danger-color); padding:10px; border-radius:4px; margin-bottom:15px; border:1px solid rgba(231, 76, 60, 0.2); font-size:0.9rem;'>Wypełnij wszystkie pola!</p>";
    } else {
        // Sprawdzenie, czy użytkownik o takim loginie już istnieje
        $check = $conn->prepare("SELECT id FROM uzytkownicy WHERE login = ?");
        $check->bind_param("s", $login);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $komunikat = "<p class='alert danger' style='background-color:rgba(231, 76, 60, 0.1); color:var(--danger-color); padding:10px; border-radius:4px; margin-bottom:15px; border:1px solid rgba(231, 76, 60, 0.2); font-size:0.9rem;'>Ten login jest już zajęty!</p>";
        } else {
            // Bezpieczne haszowanie hasła (BCRYPT) i zapis do bazy z domyślną rolą 'user'
            $hashed_password = password_hash($haslo, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO uzytkownicy (login, haslo, rola) VALUES (?, ?, 'user')");
            $stmt->bind_param("ss", $login, $hashed_password);
            
            if ($stmt->execute()) {
                $komunikat = "<p class='alert success' style='background-color:rgba(46, 204, 113, 0.1); color:var(--success-color); padding:10px; border-radius:4px; margin-bottom:15px; border:1px solid rgba(46, 204, 113, 0.2); font-size:0.9rem;'>Konto utworzone pomyślnie! Możesz się zalogować.</p>";
            } else {
                $komunikat = "<p class='alert danger' style='background-color:rgba(231, 76, 60, 0.1); color:var(--danger-color); padding:10px; border-radius:4px; margin-bottom:15px; border:1px solid rgba(231, 76, 60, 0.2); font-size:0.9rem;'>Wystąpił błąd systemu. Spróbuj później.</p>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Załóż konto - Planszoteka</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<div class="login-container">
    <h2> Rejestracja</h2>
    <p style="text-align: center; color: var(--text-muted); margin-bottom: 20px;">Utwórz nowe konto użytkownika</p>
    
    <?php echo $komunikat; ?>
    
    <form method="POST">
        <input type="text" name="login" placeholder="Wymyśl login" required>
        <input type="password" name="haslo" placeholder="Wymyśl bezpieczne hasło" required>
        <button type="submit" name="rejestracja" style="background-color: var(--success-color); color: #121824;">Zarejestruj się</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
        Masz już konto? <a href="login.php" style="color: var(--accent-color); font-weight: bold;">Zaloguj się tutaj</a>
    </p>
</div>

</body>
</html>