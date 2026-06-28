<?php
include 'db.php';

// Jeśli użytkownik jest już zalogowany, przekieruj na stronę główną
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$komunikat = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logowanie'])) {
    $login = trim($_POST['login']);
    $haslo = $_POST['haslo'];

    $stmt = $conn->prepare("SELECT id, haslo, rola FROM uzytkownicy WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($haslo, $user['haslo'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login'] = $login;
            $_SESSION['rola'] = $user['rola']; // Kluczowe dla Admin Guard
            header("Location: index.php");
            exit();
        }
    }
    $komunikat = "<p class='alert danger' style='background-color:rgba(231, 76, 60, 0.1); color:var(--danger-color); padding:10px; border-radius:4px; margin-bottom:15px; border:1px solid rgba(231, 76, 60, 0.2); font-size:0.9rem;'>Błędny login lub hasło.</p>";
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zaloguj się - Planszoteka</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<div class="login-container">
    <h2> PlanszoTeka</h2>
    <p style="text-align: center; color: var(--text-muted); margin-bottom: 20px;">Zaloguj się do swojego konta</p>
    
    <?php echo $komunikat; ?>
    
    <form method="POST">
        <input type="text" name="login" placeholder="Twój login" required>
        <input type="password" name="haslo" placeholder="Twoje hasło" required>
        <button type="submit" name="logowanie">Zaloguj się</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
        Nie masz jeszcze konta? <a href="rejestracja.php" style="color: var(--accent-color); font-weight: bold;">Zarejestruj się</a>
    </p>
</div>

</body>
</html>