<?php
declare(strict_types=1);

$name = $_GET['name'] ?? 'PHP World';
?>
<!DOCTYPE html>
<html>
<head><title>Lab 02</title></head>
<body>
    <h1>Hello, <?= htmlspecialchars($name) ?>!</h1>
    <p>Server time: <?= date('H:i:s') ?></p>
</body>
</html>
