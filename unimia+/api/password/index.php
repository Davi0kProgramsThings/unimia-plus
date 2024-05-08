<?php

require('../../scripts/api/init.php');

require_authentication();

$role = $_SESSION['role'];

$email = $_SESSION['user']['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];

    $password = $_POST['password'];

    [ $rows, $_ ] = execute_query("SELECT * FROM $role WHERE email=$1 AND password=MD5($2)", 
        [ $email, $old_password ]);

    if (count($rows) === 0) {
        send_error(400, 'La password inserita non corrisponde a quella del tuo account.');
    }

    execute_query("UPDATE $role SET password=MD5($1) WHERE email=$2", 
        [ $password, $email ]);

    // Return '204: No Content'
    http_response_code(204);
}