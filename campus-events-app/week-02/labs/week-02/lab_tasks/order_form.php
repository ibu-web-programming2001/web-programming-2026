<?php
declare(strict_types=1);
require_once 'functions.php';
$menu  = require 'data.php';
$zones = ['center', 'suburb', 'outside'];

$values  = ['name' => '', 'email' => '', 'dish' => '', 'qty' => '1',
            'zone' => 'center'];
$errors  = [];
$summary = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO 1: trimmed values from $_POST
    foreach ($values as $field => $default) {
        $values[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    // TODO 2: validation
    if ($values['name'] === '') {
        $errors['name'] = 'Name is required';
    } elseif (mb_strlen($values['name']) > 50) {
        $errors['name'] = 'Name can have at most 50 characters';
    }

    if (filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Enter a valid email address';
    }

    $dish   = null;
    $dishId = filter_var($values['dish'], FILTER_VALIDATE_INT);
    if ($dishId !== false) {
        $dish = findDish($menu, $dishId);
    }
    if ($dish === null) {
        $errors['dish'] = 'Choose a dish from the menu';
    }

    $qty = filter_var($values['qty'], FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1, 'max_range' => 20]]);
    if ($qty === false) {
        $errors['qty'] = 'Quantity must be a whole number from 1 to 20';
    }

    if (!in_array($values['zone'], $zones, true)) {
        $errors['zone'] = 'Choose a valid zone';
    }

    // TODO 3: summary
    if (empty($errors)) {
        $subtotal = $dish['price'] * $qty;
        $fee      = deliveryFee($subtotal, $values['zone']);
        $summary  = [
            'dish'     => $dish['name'],
            'qty'      => $qty,
            'zone'     => $values['zone'],
            'subtotal' => $subtotal,
            'fee'      => $fee,
            'total'    => $subtotal + $fee,
        ];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order</title>
    <style>
        label { display: block; margin-top: 10px; }
        .error { color: red; }
    </style>
</head>
<body>
<form method="POST">
    <label>Name
        <input name="name" value="<?= htmlspecialchars($values['name']) ?>">
    </label>
    <span class="error"><?= $errors['name'] ?? '' ?></span>

    <label>Email
        <input type="email" name="email" value="<?= htmlspecialchars($values['email']) ?>">
    </label>
    <span class="error"><?= $errors['email'] ?? '' ?></span>

    <label>Dish
        <select name="dish">
            <option value="">-- choose --</option>
            <?php foreach ($menu as $item): ?>
            <option value="<?= $item['id'] ?>"<?= $values['dish'] === (string) $item['id'] ? ' selected' : '' ?>>
                <?= htmlspecialchars($item['name']) ?> (<?= number_format($item['price'], 2) ?> KM)
            </option>
            <?php endforeach; ?>
        </select>
    </label>
    <span class="error"><?= $errors['dish'] ?? '' ?></span>

    <label>Quantity
        <input type="number" name="qty" value="<?= htmlspecialchars($values['qty']) ?>">
    </label>
    <span class="error"><?= $errors['qty'] ?? '' ?></span>

    <label>Zone
        <select name="zone">
            <?php foreach ($zones as $zone): ?>
            <option value="<?= $zone ?>"<?= $values['zone'] === $zone ? ' selected' : '' ?>><?= $zone ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <span class="error"><?= $errors['zone'] ?? '' ?></span>

    <br><br>
    <button>Order</button>
</form>

<?php if ($summary !== null): ?>
<h2>Order summary</h2>
<p>
    <?= htmlspecialchars($summary['dish']) ?> &times; <?= $summary['qty'] ?>
    to <?= htmlspecialchars($summary['zone']) ?><br>
    Subtotal: <?= number_format($summary['subtotal'], 2) ?> KM<br>
    Delivery: <?= number_format($summary['fee'], 2) ?> KM<br>
    Total: <?= number_format($summary['total'], 2) ?> KM
</p>
<?php endif; ?>
</body>
</html>
