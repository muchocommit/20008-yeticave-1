<?php
session_start();
require 'defaults/config.php';
require 'defaults/var.php';
require 'util/functions.php';

require_once 'init.php';
require 'data/data.php';

require 'markup/markup.php';

$lot_estimate = intval($_POST['cost']) ?? null;
$lot_value = intval($_POST['lot_value']) ?? null;

$lot_id = intval($_POST['lot_id']) ?? null;
$bet_value = $lot_estimate - $lot_value;
$user_id = intval($_POST['user_id']) ?? null;

$lot_update_sql = "UPDATE lots SET lot_value=? WHERE lot_id=?";

mysqli_query($link, 'START TRANSACTION');
$bet_id_res = insert_data($link, 'bets',
    [
        'lot_id' => $lot_id,
        'bet_value' => $bet_value, 'user_id' => $user_id
    ]
);

$lot_update_res = update_data($link, $lot_update_sql,
    [
        'lot_estimate' => $lot_estimate,
        'lot_id' => $lot_id
    ]
);

if ($bet_id_res && $lot_update_res) {
    mysqli_query($link, "COMMIT");
} else {
    mysqli_query($link, "ROLLBACK");
}


