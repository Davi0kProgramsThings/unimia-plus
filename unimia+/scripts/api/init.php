<?php

require($_SERVER['DOCUMENT_ROOT'] . '/scripts/init.php');

header('Content-Type: application/json');

function send_error($status_code, $error) {
    http_response_code($status_code);

    echo json_encode([
        "error" => $error
    ]);

    exit();
}