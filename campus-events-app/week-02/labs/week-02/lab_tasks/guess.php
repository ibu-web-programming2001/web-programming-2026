<?php
declare(strict_types=1);
session_start();

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_game'])) {
    unset($_SESSION['secret'], $_SESSION['attempts']);
}

// the secret lives in the session; without it, it would change on every guess
$_SESSION['secret']   ??= random_int(1, 10);
$_SESSION['attempts'] ??= 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guess'])) {
    $guess = filter_var(trim($_POST['guess']), FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 10]]);

    if ($guess === false) {
        $error = 'Enter a whole number from 1 to 10';
    } else {
        $_SESSION['attempts']++;

        if ($guess > $_SESSION['secret']) {
            $message = 'Too high!';
        } elseif ($guess < $_SESSION['secret']) {
            $message = 'Too low!';
        } else {
            $message = 'Correct in ' . $_SESSION['attempts'] . ' attempts!';
            // the game is over: the next request picks a new secret
            unset($_SESSION['secret'], $_SESSION['attempts']);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Guess the number</title></head>
<body>
    <h1>Guess the number (1-10)</h1>

    <form method="POST">
        <input type="number" name="guess" min="1" max="10" autofocus>
        <button>Guess</button>
        <span style="color: red"><?= $error ?></span>
    </form>

    <p><?= $message ?></p>

    <form method="POST">
        <button name="new_game" value="1">New game</button>
    </form>
</body>
</html>
