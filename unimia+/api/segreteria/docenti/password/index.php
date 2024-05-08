<?php

require('../../../../scripts/api/init.php');

require_role('secretary');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_GET['email'];

    $password = $_POST['password'];

    $query = 'UPDATE professor SET password=MD5($1) WHERE email=$2';

    execute_query($query, [ $password, $email ]);

    // Return '204: No Content'
    http_response_code(204);
}
