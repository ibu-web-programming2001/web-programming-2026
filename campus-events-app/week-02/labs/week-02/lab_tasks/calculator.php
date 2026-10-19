<?php
declare(strict_types=1);

function calculate(float $a, float $b, string $operation): float
{
    if ($operation === '/' && $b == 0) {
        throw new InvalidArgumentException('Cannot divide by zero');
    }

    return match ($operation) {
        '+' => $a + $b,
        '-' => $a - $b,
        '*' => $a * $b,
        '/' => $a / $b,
        default => throw new InvalidArgumentException("Unknown operation: $operation"),
    };
}

$operations = ['+', '-', '*', '/'];
$values     = ['number1' => '', 'number2' => '', 'operation' => '+'];
$errors     = [];
$result     = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['number1']   = trim($_POST['number1'] ?? '');
    $values['number2']   = trim($_POST['number2'] ?? '');
    $values['operation'] = $_POST['operation'] ?? '';

    $number1 = filter_var($values['number1'], FILTER_VALIDATE_FLOAT);
    $number2 = filter_var($values['number2'], FILTER_VALIDATE_FLOAT);

    if ($number1 === false) {
        $errors['number1'] = 'Enter a valid number';
    }
    if ($number2 === false) {
        $errors['number2'] = 'Enter a valid number';
    }

    if (empty($errors)) {
        try {
            $result = calculate($number1, $number2, $values['operation']);
        } catch (InvalidArgumentException $e) {
            $errors['result'] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Calculator</title>
    <style>.error { color: red; }</style>
</head>
<body>
<form method="POST">
    <input name="number1" value="<?= htmlspecialchars($values['number1']) ?>">
    <span class="error"><?= $errors['number1'] ?? '' ?></span>

    <select name="operation">
        <?php foreach ($operations as $operation): ?>
        <option<?= $values['operation'] === $operation ? ' selected' : '' ?>><?= $operation ?></option>
        <?php endforeach; ?>
    </select>

    <input name="number2" value="<?= htmlspecialchars($values['number2']) ?>">
    <span class="error"><?= $errors['number2'] ?? '' ?></span>

    <button>Calculate</button>
</form>

<?php if ($result !== null): ?>
<p>Result: <?= $result ?></p>
<?php endif; ?>
<p class="error"><?= htmlspecialchars($errors['result'] ?? '') ?></p>
</body>
</html>
