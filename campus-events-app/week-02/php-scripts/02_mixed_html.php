<?php
/*
 * Slide 9 – Classic PHP: mixed into HTML (template style)
 * Open in the browser:  http://localhost:8000/02_mixed_html.php
 * (In CampusEvents the backend never prints HTML – it returns JSON. This is only to show the old style.)
 */
declare(strict_types=1);
header("Content-Type: text/plain; charset=utf-8");

$events = [
    ["id" => 1, "title" => "Quiz"],
    ["id" => 2, "title" => "Hackathon"],
    ["id" => 3, "title" => "<script>alert('xss')</script>"], // why htmlspecialchars() matters
];
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Classic PHP</title></head>
<body>
<h1>Upcoming events</h1>
<ul>
<?php foreach ($events as $e): ?>
  <li><?= htmlspecialchars($e["title"]) ?></li>
<?php endforeach; ?>
</ul>
</body>
</html>
