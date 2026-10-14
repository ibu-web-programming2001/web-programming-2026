<?php
/*
 * Slide 9 – Your first PHP file: tags, echo and comments
 * Run:  php 01_first_file.php
 */
declare(strict_types=1);
header("Content-Type: text/plain; charset=utf-8");

// single-line comment
# also a single-line comment
/* multi-line
   comment */

/** PHPDoc: your editor reads it */
$title = "Hackathon 2026";
echo "<h1>$title</h1>\n";

// A file with only PHP: leave out the closing tag at the end.
// (a stray newline after the closing tag would already be sent to the browser –
//  and a closing tag inside a comment ends the PHP block too: try adding one here!)
