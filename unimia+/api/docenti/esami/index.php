<?php

require('../../../scripts/api/init.php');

require_role('professor');

$email = $_SESSION['user']['email'];

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $query = "
        DELETE FROM exam 
            WHERE course=$1 AND
                  identifier=$2 AND
                  date=$3 AND
                  $4 IN (
                      SELECT professor FROM teaching
                          WHERE course=$1 AND
                                identifier=$2
                  )
    ";

    [ $_, $err ] = execute_query($query, [ 
        $_GET['course'], 
        $_GET['identifier'], 
        $_GET['date'], 
        $email 
    ]);

    if (str_contains($err, 'fkey')) {
        send_error(400, 'Non è possibile cancellare questo esame: altre risorse ne dipendono.');
    }

    // Return '204: No Content'
    http_response_code(204);
}
