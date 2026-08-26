<?php
/**
 * Genericki auth guard za studentq AJAX/download endpointe.
 * Dozvoljava prijavljene studente (djake).
 * Ako korisnik nije prijavljen, vraca HTTP 403 i izlazi.
 *
 * Upotreba na vrhu endpoint fajla:
 *   include_once __DIR__ . '/config/require_login.php';
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['logged_in'])
    || empty($_SESSION['user_id'])
    || empty($_SESSION['role'])) {

    http_response_code(403);
    echo 'NEAUTORIZOVAN';
    exit;
}
