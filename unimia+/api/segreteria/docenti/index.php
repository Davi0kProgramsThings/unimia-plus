<?php

require('../../../scripts/api/init.php');

require_role('secretary');

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $email = $_GET['email'];

    $query = 'DELETE FROM professor WHERE email=$1';

    [ $_, $err ] = execute_query($query, [ $email ]);

    if (str_contains($err, 'fkey')) {
        send_error(400, 'Non è possibile cancellare questo professore: altre risorse ne dipendono.');
    }

    // Return '204: No Content'
    http_response_code(204);
}
