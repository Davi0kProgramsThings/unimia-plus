<?php

require('../../../scripts/api/init.php');

require_role('secretary');

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $email = $_GET['email'];

    $query = 'DELETE FROM student WHERE email=$1';

    execute_query($query, [ $email ]);

    // Return '204: No Content'
    http_response_code(204);
}
