<?php

class domaci_audio
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Audio zadaci vidljivi djaku (preko grupa), sa statusom slušanja i odgovora
    public function read_visible_for_djak($djak_id)
    {
        $query = "SELECT DISTINCT daz.id, daz.naziv, daz.opis, daz.rok, daz.created_at,
                         daz.audio_filename, daz.mime_type, daz.zakljucan,
                         (SELECT MAX(g.created_at) FROM domaci_audio_grupe g WHERE g.fk_domaci = daz.id) AS podeljeno_at,
                         dap.slusano_at,
                         dao.poslato_at,
                         dao.audio_filename AS odgovor_filename,
                         dao.mime_type      AS odgovor_mime,
                         dao.ocena_poeni,
                         dao.ocena_max,
                         dao.ocena_komentar
                  FROM domaci_audio_zadaci daz
                  INNER JOIN domaci_audio_grupe dag ON dag.fk_domaci = daz.id
                  INNER JOIN povezivanje p ON p.fk_grupa = dag.fk_grupa
                  LEFT JOIN domaci_audio_pregled dap
                         ON dap.fk_domaci = daz.id AND dap.fk_djak = :djak_id1
                  LEFT JOIN domaci_audio_odgovori dao
                         ON dao.fk_domaci = daz.id AND dao.fk_djak = :djak_id2
                  WHERE p.fk_djak = :djak_id3
                    AND p.status  = 1
                    AND daz.aktivan = 1
                  ORDER BY daz.rok IS NULL, daz.rok ASC, daz.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':djak_id1', $djak_id);
        $stmt->bindParam(':djak_id2', $djak_id);
        $stmt->bindParam(':djak_id3', $djak_id);
        $stmt->execute();
        return $stmt;
    }

    public function read_one($id)
    {
        $query = "SELECT * FROM domaci_audio_zadaci WHERE id = :id AND aktivan = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt;
    }

    // Upisuje evidenciju slušanja (ignoruje duplikat - UNIQUE KEY štiti)
    public function oznaci_slusano($domaci_id, $djak_id)
    {
        $query = "INSERT IGNORE INTO domaci_audio_pregled (fk_domaci, fk_djak) VALUES (:fk_domaci, :fk_djak)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_domaci', $domaci_id);
        $stmt->bindParam(':fk_djak', $djak_id);
        return $stmt->execute();
    }

    // Snima djakov audio odgovor (INSERT ... ON DUPLICATE KEY UPDATE - dozvoljava zamenu)
    public function sacuvaj_odgovor($domaci_id, $djak_id, $audio_filename, $mime_type)
    {
        $query = "INSERT INTO domaci_audio_odgovori (fk_domaci, fk_djak, audio_filename, mime_type, poslato_at)
                  VALUES (:fk_domaci, :fk_djak, :audio_filename, :mime_type, NOW())
                  ON DUPLICATE KEY UPDATE
                      audio_filename = VALUES(audio_filename),
                      mime_type      = VALUES(mime_type),
                      poslato_at     = NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_domaci', $domaci_id);
        $stmt->bindParam(':fk_djak', $djak_id);
        $stmt->bindParam(':audio_filename', $audio_filename);
        $stmt->bindParam(':mime_type', $mime_type);
        return $stmt->execute();
    }

    public function je_zakljucan($domaci_id)
    {
        $query = "SELECT zakljucan FROM domaci_audio_zadaci WHERE id = :id AND aktivan = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $domaci_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['zakljucan'];
    }

    // Proverava da li djak ima pravo na ovaj zadatak (sigurnosna provera pre servinga)
    public function djak_ima_pristup($domaci_id, $djak_id)
    {
        $query = "SELECT 1
                  FROM domaci_audio_zadaci daz
                  INNER JOIN domaci_audio_grupe dag ON dag.fk_domaci = daz.id
                  INNER JOIN povezivanje p ON p.fk_grupa = dag.fk_grupa
                  WHERE daz.id = :domaci_id
                    AND p.fk_djak = :djak_id
                    AND p.status = 1
                    AND daz.aktivan = 1
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':domaci_id', $domaci_id);
        $stmt->bindParam(':djak_id', $djak_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
