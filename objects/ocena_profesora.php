<?php

class ocena_profesora
{
    private $conn;
    private $table_name = "ocene_profesora";

    public $id;
    public $fk_djak;
    public $fk_profesor;
    public $ocena;
    public $komentar;
    public $created;
    public $active;

    public function __construct($db){
        $this->conn = $db;
    }

    public function create(){
        $this->created = date('Y-m-d H:i:s');

        $query = "INSERT INTO " . $this->table_name . "
            SET
                fk_djak     = :fk_djak,
                fk_profesor = :fk_profesor,
                ocena       = :ocena,
                komentar    = :komentar,
                created     = :created,
                active      = 1";

        $stmt = $this->conn->prepare($query);

        $this->fk_djak     = htmlspecialchars(strip_tags($this->fk_djak));
        $this->fk_profesor = htmlspecialchars(strip_tags($this->fk_profesor));
        $this->ocena       = htmlspecialchars(strip_tags($this->ocena));
        $this->komentar    = htmlspecialchars(strip_tags($this->komentar));

        $stmt->bindParam(':fk_djak',     $this->fk_djak);
        $stmt->bindParam(':fk_profesor', $this->fk_profesor);
        $stmt->bindParam(':ocena',       $this->ocena);
        $stmt->bindParam(':komentar',    $this->komentar);
        $stmt->bindParam(':created',     $this->created);

        return $stmt->execute();
    }

    public function read_all_za_profesora($fk_profesor){
        $query = "SELECT op.*, d.firstname AS djak_ime, d.lastname AS djak_prezime
                  FROM " . $this->table_name . " op
                  LEFT JOIN djaci d ON d.id = op.fk_djak
                  WHERE op.fk_profesor = :fk_profesor AND op.active = 1
                  ORDER BY op.created DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_profesor', $fk_profesor);
        $stmt->execute();

        return $stmt;
    }

    public function already_rated_today($fk_djak, $fk_profesor){
        $query = "SELECT id FROM " . $this->table_name . "
                  WHERE fk_djak = :fk_djak AND fk_profesor = :fk_profesor
                    AND active = 1 AND DATE(created) = CURDATE()
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_djak',     $fk_djak);
        $stmt->bindParam(':fk_profesor', $fk_profesor);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function deactivate($id){
        $query = "UPDATE " . $this->table_name . " SET active = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
