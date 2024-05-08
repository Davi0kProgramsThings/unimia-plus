<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

$connection_string = "
    host=$POSTGRESQL_HOST
    port=$POSTGRESQL_PORT
    user=$POSTGRESQL_USER
    password=$POSTGRESQL_PASSWORD
    dbname=$POSTGRESQL_DBNAME
";

$connection = pg_pconnect($connection_string);

function execute_query($query, $params = [], $stmtname = '') {
    global $connection;

    $result = pg_prepare($connection, $stmtname, $query);

    $result = pg_execute($connection, $stmtname, $params);

    if (!$result)
        return [ null, pg_last_error($connection) ];

    return [ pg_fetch_all($result), null ];
}

function parse_error_message($err, $errors) {
    foreach ($errors as $constraint => $error)
        if (str_contains($err, $constraint))
            return $error;

    return false;
}
