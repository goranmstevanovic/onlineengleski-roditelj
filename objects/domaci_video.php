<?php

class domaci_video
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read_visible_for_djak($djak_id)
    {
        $query = "SELECT DISTINCT dvz.id, dvz.naziv, dvz.video_url, dvz.opis, dvz.rok, dvz.created_at, dvz.zakljucan,
                         (SELECT MAX(g.created_at) FROM domaci_video_grupe g WHERE g.fk_domaci = dvz.id) AS podeljeno_at,
                         dvo.tekst AS odgovor_tekst,
                         dvo.komentar_html,
                         dvo.poslato_at,
                         dvo.ocena_poeni,
                         dvo.ocena_max,
                         dvo.ocena_komentar
                  FROM domaci_video_zadaci dvz
                  INNER JOIN domaci_video_grupe dvg ON dvg.fk_domaci = dvz.id
                  INNER JOIN povezivanje p ON p.fk_grupa = dvg.fk_grupa
                  LEFT JOIN domaci_video_odgovori dvo
                         ON dvo.fk_domaci = dvz.id AND dvo.fk_djak = :djak_id1
                  WHERE p.fk_djak = :djak_id2
                    AND p.status  = 1
                    AND dvz.aktivan = 1
                  ORDER BY dvz.rok IS NULL, dvz.rok ASC, dvz.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':djak_id1', $djak_id);
        $stmt->bindParam(':djak_id2', $djak_id);
        $stmt->execute();
        return $stmt;
    }

    public function je_zakljucan($domaci_id)
    {
        $query = "SELECT zakljucan FROM domaci_video_zadaci WHERE id = :id AND aktivan = 1 LIMIT 1";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $domaci_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['zakljucan'];
    }

    public function djak_ima_pristup($domaci_id, $djak_id)
    {
        $query = "SELECT 1
                  FROM domaci_video_zadaci dvz
                  INNER JOIN domaci_video_grupe dvg ON dvg.fk_domaci = dvz.id
                  INNER JOIN povezivanje p ON p.fk_grupa = dvg.fk_grupa
                  WHERE dvz.id = :domaci_id
                    AND p.fk_djak = :djak_id
                    AND p.status = 1
                    AND dvz.aktivan = 1
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':domaci_id', $domaci_id);
        $stmt->bindParam(':djak_id',   $djak_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function sacuvaj_odgovor($domaci_id, $djak_id, $tekst)
    {
        $query = "INSERT INTO domaci_video_odgovori (fk_domaci, fk_djak, tekst, poslato_at)
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
