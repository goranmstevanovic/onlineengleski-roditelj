<?php

class dokument_log
{
    private $conn;
    private $table_name = "dokument_log";

    public $id;
    public $fk_dokument;
    public $korisnik_id;
    public $korisnik_uloga; // admin | profesor | djak
    public $akcija; // pregled | download
    public $created_at;

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

    public function create()
    {
        $this->created_at = date('Y-m-d H:i:s');

        $query = "INSERT INTO " . $this->table_name . "
            SET
                fk_dokument = :fk_dokument,
                korisnik_id = :korisnik_id,
                korisnik_uloga = :korisnik_uloga,
                akcija = :akcija,
                created_at = :created_at";

        $stmt = $this->conn->prepare($query);

        $this->korisnik_uloga = htmlspecialchars(strip_tags($this->korisnik_uloga));
        $this->akcija = htmlspecialchars(strip_tags($this->akcija));

        $stmt->bindParam(':fk_dokument', $this->fk_dokument);
        $stmt->bindParam(':korisnik_id', $this->korisnik_id);
        $stmt->bindParam(':korisnik_uloga', $this->korisnik_uloga);
        $stmt->bindParam(':akcija', $this->akcija);
        $stmt->bindParam(':created_at', $this->created_at);

        if ($stmt->execute()) {
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    public function read_by_dokument($fk_dokument)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE fk_dokument = :fk_dokument
                  ORDER BY created_at DESC, id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_dokument', $fk_dokument);
        $stmt->execute();

        return $stmt;
    }

    public function read_by_user($korisnik_id, $korisnik_uloga = null)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE korisnik_id = :korisnik_id";

        if (!empty($korisnik_uloga)) {
            $query .= " AND korisnik_uloga = :korisnik_uloga";
        }

        $query .= " ORDER BY created_at DESC, id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':korisnik_id', $korisnik_id);

        if (!empty($korisnik_uloga)) {
            $stmt->bindParam(':korisnik_uloga', $korisnik_uloga);
        }

        $stmt->execute();

        return $stmt;
    }
}