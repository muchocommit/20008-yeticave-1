<?php
session_start();
require 'defaults/config.php';
require 'defaults/var.php';
require 'resource/functions.php';

require_once 'init.php';
require 'db/db.php';

require 'markup/markup.php';
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    http_response_code(403);
    exit('Вы не авторизованы ' . http_response_code() . '');
}
$user_id = $_SESSION['user']['id'];

$lot_id = $_POST['lot_id'] ?? null;

$bet_value = $_POST['bet_value'] ?? null;

$lot_value = $_POST['lot_value'] ?? null;
$lot_step = $_POST['lot_step'] ?? null;
$lot_min = $lot_value + $lot_step;

$validate = '';
$_POST = [];

if (!isset($bet_value)) {
    $index = false;
    $title = 'Мои ставки';

    $my_bets = select_data_assoc($link, $my_bets_sql, [$user_id]);
    $nav = includeTemplate('templates/nav.php',
        [
            'categories' => $categories
        ]);

    $content = includeTemplate('templates/my-bets.php',
        [
            'my_bets' => $my_bets
        ]
    );
    $markup = new Markup(
        'templates/layout.php', array_merge_recursive(
            $layout,
            [
                'index' => $index, 'title' => $title,
                'nav' => $nav, 'content' => $content, 'search' => $search
            ]
        )
    );
    $markup->get_layout();
}

if (isset($bet_value)) {
    $validate = validateBetValue($bet_value, $lot_min);

    if (!empty($validate)) {
        $_SESSION['user'][$user_id]['bet_error'] = $validate;
        header('Location: lot.php?lot_id=' . $lot_id);
    }

    $lot_update_sql = "UPDATE lots SET value=? WHERE id=?";

    mysqli_query($link, 'START TRANSACTION');
    $bet_id_res = insert_data($link, 'bets',
        [
            'lot_id' => $lot_id, 'value' => $bet_value,
            'date_add' => $date_current->format('Y.m.d H:i:s'),
            'user_id' => $user_id
        ]
    );

    $lot_update_res = update_data($link, $lot_update_sql,
        [
            'value' => $bet_value,
            'id' => $lot_id
        ]
    );

    if ($bet_id_res && $lot_update_res) {
        mysqli_query($link, "COMMIT");
        header('Location: lot.php?lot_id=' . $lot_id);
    } else {
        mysqli_query($link, "ROLLBACK");
    }
}



