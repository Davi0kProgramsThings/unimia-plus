<?php

require('../../../scripts/api/init.php');

require_role('secretary');

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $course = $_GET['course'];

    $identifier = $_GET['identifier'];

    $query = 'DELETE FROM teaching WHERE course=$1 AND identifier=$2';

    [ $_, $err ] = execute_query($query, [ $course, $identifier ]);

    if (str_contains($err, 'fkey')) {
        send_error(400, 'Non è possibile cancellare questo insegnamento: altre risorse ne dipendono.');
    }

    // Return '204: No Content'
    http_response_code(204);
}