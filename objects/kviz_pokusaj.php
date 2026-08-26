<?php

class kviz_pokusaj
{
    private $conn;
    private $table_name = "kviz_pokusaji";

    public $id;
    public $fk_kviz;
    public $fk_djak;
    public $started_at;
    public $finished_at;
    public $bodovi;
    public $max_bodovi;
    public $procenat;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function showError($stmt)
    {
        echo "<pre>";
        print_r($stmt->errorInfo());
        echo "</pre>";
    }

    // Pokrece novi pokusaj resavanja kviza
    public function create()
    {
        $this->started_at = date('Y-m-d H:i:s');

        $query = "INSERT INTO " . $this->table_name . "
            SET
                fk_kviz = :fk_kviz,
                fk_djak = :fk_djak,
                started_at = :started_at";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':fk_kviz', $this->fk_kviz);
        $stmt->bindParam(':fk_djak', $this->fk_djak);
        $stmt->bindParam(':started_at', $this->started_at);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    // Zatvara pokusaj i upisuje konacan rezultat
    public function finish($id, $bodovi, $max_bodovi, $procenat)
    {
        $query = "UPDATE " . $this->table_name . "
            SET
                finished_at = :finished_at,
                bodovi = :bodovi,
                max_bodovi = :max_bodovi,
                procenat = :procenat
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $finished_at = date('Y-m-d H:i:s');

        $stmt->bindParam(':finished_at', $finished_at);
        $stmt->bindParam(':bodovi', $bodovi);
        $stmt->bindParam(':max_bodovi', $max_bodovi);
        $stmt->bindParam(':procenat', $procenat);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    public function read_one($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt;
    }

    // Svi pokusaji jednog djaka za odredjeni kviz - provera broj_pokusaja i prikaz istorije
    public function read_by_kviz_djak($fk_kviz, $fk_djak)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE fk_kviz = :fk_kviz AND fk_djak = :fk_djak
                  ORDER BY started_at DESC, id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_kviz', $fk_kviz);
        $stmt->bindParam(':fk_djak', $fk_djak);
        $stmt->execute();

        return $stmt;
    }

    // Pokusaj koji je u toku (finished_at je NULL), ako postoji
    public function read_in_progress($fk_kviz, $fk_djak)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE fk_kviz = :fk_kviz AND fk_djak = :fk_djak AND finished_at IS NULL
                  ORDER BY started_at DESC
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_kviz', $fk_kviz);
        $stmt->bindParam(':fk_djak', $fk_djak);
        $stmt->execute();

        return $stmt;
    }

    // Svi zavrseni i nezavrseni pokusaji za kviz, sa imenom djaka - za admin pregled rezultata
    public function read_all_by_kviz($fk_kviz)
    {
        $query = "SELECT kp.*, d.firstname, d.lastname
                  FROM " . $this->table_name . " kp
                  INNER JOIN djaci d ON d.id = kp.fk_djak
                  WHERE kp.fk_kviz = :fk_kviz
                  ORDER BY kp.finished_at IS NULL, kp.finished_at DESC, kp.started_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_kviz', $fk_kviz);
        $stmt->execute();

        return $stmt;
    }
}
