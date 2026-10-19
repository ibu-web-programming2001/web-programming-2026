<?php
declare(strict_types=1);

/*
 * SETUP
 *   Unzip the folder into htdocs/ so that you have htdocs/lab02-assignment/
 *   and open http://localhost/lab02-assignment/receipt.php
 *   Two files are already finished for you. Read them, do not change them:
 *     - functions.php  deliveryFee($total, $zone) from Task 2
 *     - data.php       the menu from Task 3
 *   You write code only in this file, at the TODO comments.
 *
 * TASK
 *   A customer ordered the dishes in $order below (dish id => quantity).
 *   Print a receipt for that order.
 *
 *   TODO 1  For every line of the order print the dish name, the quantity
 *           and the line total (price x quantity), like this:
 *               Margherita x 2 = 19.00 KM
 *           Take the name and the price from $menu. Do not type them in.
 *
 *   TODO 2  After the lines print the subtotal, the delivery fee and the
 *           total. Get the fee by calling deliveryFee($subtotal, $zone).
 *
 *   TODO 3  If the zone is unknown, deliveryFee() throws an exception.
 *           Catch it and print "Error: " followed by the exception message
 *           instead of the Delivery and Total lines.
 *
 * RULES FOR THE OUTPUT
 *   - every amount has 2 decimals: number_format($amount, 2)
 *   - every line ends with <br>
 *   - escape dish names with htmlspecialchars()
 *
 * EXPECTED OUTPUT
 *   receipt.php                    receipt.php?zone=suburb
 *     Margherita x 2 = 19.00 KM      (the same three lines)
 *     Burek x 3 = 13.50 KM           Subtotal: 40.00 KM
 *     Greek salad x 1 = 7.50 KM      Delivery (suburb): 4.00 KM
 *     Subtotal: 40.00 KM             Total: 44.00 KM
 *     Delivery (center): 2.00 KM
 *     Total: 42.00 KM
 *
 *   receipt.php?zone=mars
 *     (the same three lines)
 *     Subtotal: 40.00 KM
 *     Error: Unknown zone: mars
 *
 * SUBMIT
 *   Put the finished receipt.php in lab02/assignment/ in your lab
 *   repository, commit and push before you leave.
 */

require_once 'functions.php';
$menu = require 'data.php';

$order = [1 => 2, 3 => 3, 6 => 1];        // dish id => quantity
$zone  = $_GET['zone'] ?? 'center';

$subtotal = 0.0;

foreach ($order as $dishId => $qty) {
    // TODO 1: find the dish with this id in $menu (a loop),
    //         print its line and add the line total to $subtotal
}

// TODO 2: print "Subtotal: 40.00 KM"
//         then "Delivery (center): 2.00 KM" and "Total: 42.00 KM"

// TODO 3: wrap the call of deliveryFee() in try/catch
//         and print "Error: ..." when it throws
