<?php
if (!isset($domaci_zadaci_enabled)) {
    if (isset($db) && $db instanceof PDO) {
        $_opt_conn = $db;
    } else {
        require_once __DIR__ . '/database.php';
        $_opt_tmp = new Database();
        $_opt_conn = $_opt_tmp->getConnection();
        unset($_opt_tmp);
    }
    $_opt_stmt = $_opt_conn->query("SELECT domaci_zadaci_integracija FROM dodatne_opcije LIMIT 1");
    $_opt_row  = $_opt_stmt->fetch(PDO::FETCH_ASSOC);
    $domaci_zadaci_enabled = $_opt_row ? (bool)$_opt_row['domaci_zadaci_integracija'] : false;
    unset($_opt_conn, $_opt_stmt, $_opt_row);
}
