<?php

error_reporting(E_ERROR | E_PARSE);

require_once($_SERVER['DOCUMENT_ROOT'] . '/scripts/database.php');

require_once($_SERVER['DOCUMENT_ROOT'] . '/scripts/redirect.php');

session_start();

function require_authentication() {
    if (!isset($_SESSION['user'])) {
        redirect('/');
    }
}

function require_role($role) {
    if ($_SESSION['role'] !== $role) {
        redirect('/');
    }
}
