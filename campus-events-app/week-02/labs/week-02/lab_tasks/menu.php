<?php
// menu.php
declare(strict_types=1);
$menu = require 'data.php';

// Step 4: optional filter menu.php?category=pita
$category = $_GET['category'] ?? '';

$dishes = [];
foreach ($menu as $dish) {
    if ($category === '' || $dish['category'] === $category) {
        $dishes[] = $dish;
    }
}

// Steps 2 and 3: one loop, no sorting functions
$count              = count($dishes);
$vegetarian         = 0;
$sum                = 0.0;
$mostExpensive      = null;
$cheapestVegetarian = null;

foreach ($dishes as $dish) {
    $sum += $dish['price'];

    if ($mostExpensive === null || $dish['price'] > $mostExpensive['price']) {
        $mostExpensive = $dish;
    }

    if ($dish['vegetarian']) {
        $vegetarian++;
        if ($cheapestVegetarian === null || $dish['price'] < $cheapestVegetarian['price']) {
            $cheapestVegetarian = $dish;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Menu</title></head>
<body>
<?php if ($count === 0): ?>
    <p>No dishes in category "<?= htmlspecialchars($category) ?>"</p>
<?php else: ?>
    <table border="1" cellpadding="5">
        <tr><th>Name</th><th>Category</th><th>Price</th></tr>
        <?php foreach ($dishes as $dish): ?>
        <tr>
            <td><?= htmlspecialchars($dish['name']) ?><?= $dish['vegetarian'] ? ' (V)' : '' ?></td>
            <td><?= htmlspecialchars($dish['category']) ?></td>
            <td><?= number_format($dish['price'], 2) ?> KM</td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p>Dishes: <?= $count ?></p>
    <p>Vegetarian: <?= $vegetarian ?></p>
    <p>Average price: <?= number_format($sum / $count, 2) ?> KM</p>
    <p>Most expensive: <?= htmlspecialchars($mostExpensive['name']) ?>
        (<?= number_format($mostExpensive['price'], 2) ?> KM)</p>
    <?php if ($cheapestVegetarian !== null): ?>
    <p>Cheapest vegetarian: <?= htmlspecialchars($cheapestVegetarian['name']) ?>
        (<?= number_format($cheapestVegetarian['price'], 2) ?> KM)</p>
    <?php else: ?>
    <p>Cheapest vegetarian: none</p>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
