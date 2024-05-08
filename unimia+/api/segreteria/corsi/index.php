<?php

require('../../../scripts/api/init.php');

require_role('secretary');

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $code = $_GET['code'];

    $query = 'DELETE FROM course WHERE code=$1';

    [ $_, $err ] = execute_query($query, [ $code ]);

    if (str_contains($err, 'fkey')) {
        send_error(400, 'Non è possibile cancellare questo corso di laurea: altre risorse ne dipendono.');
    }

    // Return '204: No Content'
    http_response_code(204);
}