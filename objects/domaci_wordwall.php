<?php

class domaci_wordwall
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read_visible_for_djak($djak_id)
    {
        $query = "SELECT DISTINCT dwz.id, dwz.naziv, dwz.wordwall_url, dwz.opis, dwz.rok, dwz.created_at, dwz.zakljucan,
                         (SELECT MAX(g.created_at) FROM domaci_wordwall_grupe g WHERE g.fk_domaci = dwz.id) AS podeljeno_at,
                         dwo.otvoreno_at,
                         dwo.ocena_poeni,
                         dwo.ocena_max,
                         dwo.ocena_komentar
                  FROM domaci_wordwall_zadaci dwz
                  INNER JOIN domaci_wordwall_grupe dwg ON dwg.fk_domaci = dwz.id
                  INNER JOIN povezivanje p ON p.fk_grupa = dwg.fk_grupa
                  LEFT JOIN domaci_wordwall_odgovori dwo
                         ON dwo.fk_domaci = dwz.id AND dwo.fk_djak = :djak_id1
                  WHERE p.fk_djak = :djak_id2
                    AND p.status  = 1
                    AND dwz.aktivan = 1
                  ORDER BY dwz.rok IS NULL, dwz.rok ASC, dwz.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':djak_id1', $djak_id);
        $stmt->bindParam(':djak_id2', $djak_id);
        $stmt->execute();
        return $stmt;
    }

    public function djak_ima_pristup($domaci_id, $djak_id)
    {
        $query = "SELECT 1
                  FROM domaci_wordwall_zadaci dwz
                  INNER JOIN domaci_wordwall_grupe dwg ON dwg.fk_domaci = dwz.id
                  INNER JOIN povezivanje p ON p.fk_grupa = dwg.fk_grupa
                  WHERE dwz.id = :domaci_id
                    AND p.fk_djak = :djak_id
                    AND p.status = 1
                    AND dwz.aktivan = 1
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':domaci_id', $domaci_id);
        $stmt->bindParam(':djak_id',   $djak_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Evidencija da je djak kliknuo link (prvi klik; ne garantuje da je i zavrsio aktivnost na Wordwall-u).
    // Koristi COALESCE da ne pregazi otvoreno_at ako je vec postavljen, i da ne dira ocenu ako profesor
    // vec ima red u tabeli (npr. uneo ocenu pre nego sto je djak kliknuo).
    public function oznaci_otvoreno($domaci_id, $djak_id)
    {
        $query = "INSERT INTO domaci_wordwall_odgovori (fk_domaci, fk_djak, otvoreno_at)
                  VALUES (:fk_domaci, :fk_djak, NOW())
                  ON DUPLICATE KEY UPDATE otvoreno_at = COALESCE(otvoreno_at, VALUES(otvoreno_at))";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_domaci', $domaci_id);
        $stmt->bindParam(':fk_djak',   $djak_id);
        return $stmt->execute();
    }
}
