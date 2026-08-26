<?php

class domaci_esej
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read_visible_for_djak($djak_id)
    {
        $query = "SELECT DISTINCT dez.id, dez.naziv, dez.opis, dez.rok, dez.created_at, dez.zakljucan,
                         (SELECT MAX(g.created_at) FROM domaci_esej_grupe g WHERE g.fk_domaci = dez.id) AS podeljeno_at,
                         deo.tekst AS odgovor_tekst,
                         deo.komentar_html,
                         deo.poslato_at,
                         deo.ocena_poeni,
                         deo.ocena_max,
                         deo.ocena_komentar
                  FROM domaci_esej_zadaci dez
                  INNER JOIN domaci_esej_grupe deg ON deg.fk_domaci = dez.id
                  INNER JOIN povezivanje p ON p.fk_grupa = deg.fk_grupa
                  LEFT JOIN domaci_esej_odgovori deo
                         ON deo.fk_domaci = dez.id AND deo.fk_djak = :djak_id1
                  WHERE p.fk_djak = :djak_id2
                    AND p.status  = 1
                    AND dez.aktivan = 1
                  ORDER BY dez.rok IS NULL, dez.rok ASC, dez.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':djak_id1', $djak_id);
        $stmt->bindParam(':djak_id2', $djak_id);
        $stmt->execute();
        return $stmt;
    }

    public function je_zakljucan($domaci_id)
    {
        $query = "SELECT zakljucan FROM domaci_esej_zadaci WHERE id = :id AND aktivan = 1 LIMIT 1";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $domaci_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['zakljucan'];
    }

    public function djak_ima_pristup($domaci_id, $djak_id)
    {
        $query = "SELECT 1
                  FROM domaci_esej_zadaci dez
                  INNER JOIN domaci_esej_grupe deg ON deg.fk_domaci = dez.id
                  INNER JOIN povezivanje p ON p.fk_grupa = deg.fk_grupa
                  WHERE dez.id = :domaci_id
                    AND p.fk_djak = :djak_id
                    AND p.status = 1
                    AND dez.aktivan = 1
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':domaci_id', $domaci_id);
        $stmt->bindParam(':djak_id',   $djak_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function sacuvaj_odgovor($domaci_id, $djak_id, $tekst)
    {
        $query = "INSERT INTO domaci_esej_odgovori (fk_domaci, fk_djak, tekst, poslato_at)
                  VALUES (:fk_domaci, :fk_djak, :tekst, NOW())
                  ON DUPLICATE KEY UPDATE
                      tekst      = VALUES(tekst),
                      poslato_at = NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_domaci', $domaci_id);
        $stmt->bindParam(':fk_djak',   $djak_id);
        $stmt->bindParam(':tekst',     $tekst);
        return $stmt->execute();
    }
}
