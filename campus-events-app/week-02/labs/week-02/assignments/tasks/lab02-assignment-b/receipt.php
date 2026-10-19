<?php
declare(strict_types=1);

/*
 *
 * TASK
 *   A customer ordered the dishes in $order below (dish id => quantity).
 *   Print a receipt for that order.
 *
 *   TODO 1  For every line of the order print the quantity, the dish name
 *           and the line total (price x quantity), like this:
 *               4 x Sirnica = 16.00 KM
 *           Take the name and the price from $menu. Do not type them in.
 *
 *   TODO 2  After the lines print the subtotal, the delivery fee and the
 *           amount to pay. Get the fee by calling
 *           deliveryFee($subtotal, $zone).
 *
 *   TODO 3  If the zone is unknown, deliveryFee() throws an exception.
 *           Catch it and print "Order failed: " followed by the exception
 *           message instead of the Delivery and To pay lines.
 *
 * RULES FOR THE OUTPUT
 *   - every amount has 2 decimals: number_format($amount, 2)
 *   - escape dish names with htmlspecialchars()
 *
 * EXPECTED OUTPUT
 *   receipt.php                    receipt.php?zone=outside
 *     1 x Pepperoni = 11.00 KM       (the same three lines)
 *     4 x Sirnica = 16.00 KM         Subtotal: 47.00 KM
 *     2 x Ćevapi (10) = 20.00 KM     Delivery to outside: 7.00 KM
 *     Subtotal: 47.00 KM             To pay: 54.00 KM
 *     Delivery to suburb: 4.00 KM
 *     To pay: 51.00 KM
 *
 *   receipt.php?zone=moon
 *     (the same three lines)
 *     Subtotal: 47.00 KM
 *     Order failed: Unknown zone: moon
 */

require_once 'functions.php';
$menu = require 'data.php';

$order = [2 => 1, 4 => 4, 5 => 2];        // dish id => quantity
$zone  = $_GET['zone'] ?? 'suburb';

$subtotal = 0.0;

foreach ($order as $dishId => $qty) {
    // TODO 1: find the dish with this id in $menu (a loop),
    //         print its line and add the line total to $subtotal
}

// TODO 2: print "Subtotal: 47.00 KM"
//         then "Delivery to suburb: 4.00 KM" and "To pay: 51.00 KM"

// TODO 3: wrap the call of deliveryFee() in try/catch
//         and print "Order failed: ..." when it throws
