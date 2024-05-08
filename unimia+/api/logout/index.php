<?php

require('../../scripts/api/init.php');

require_authentication();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    session_destroy();

    // Return '204: No Content'
    http_response_code(204);
}
