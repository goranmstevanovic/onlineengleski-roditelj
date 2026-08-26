<?php

class odgovor
{
    private $conn;
    private $table_name = "odgovori";

    public $id;
    public $fk_pitanje;
    public $tekst_odgovora;
    public $tacan = 0;
    public $poeni; // koristi se za 'dopuna' i 'spajanje' pitanja - poeni za ovu konkretnu dopunu/stavku
    public $tacan_broj; // koristi se samo za 'spajanje' pitanja - broj praznine (1-5) koju ova stavka popunjava, NULL = mamac
    public $redosled = 0;

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
        $query = "INSERT INTO " . $this->table_name . "
            SET
                fk_pitanje = :fk_pitanje,
                tekst_odgovora = :tekst_odgovora,
                tacan = :tacan,
                poeni = :poeni,
                tacan_broj = :tacan_broj,
                redosled = :redosled";

        $stmt = $this->conn->prepare($query);

        $this->tekst_odgovora = htmlspecialchars(strip_tags($this->tekst_odgovora));

        $stmt->bindParam(':fk_pitanje', $this->fk_pitanje);
        $stmt->bindParam(':tekst_odgovora', $this->tekst_odgovora);
        $stmt->bindParam(':tacan', $this->tacan);
        $stmt->bindParam(':poeni', $this->poeni);
        $stmt->bindParam(':tacan_broj', $this->tacan_broj);
        $stmt->bindParam(':redosled', $this->redosled);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    // Svi ponudjeni odgovori za pitanje, po zadatom redosledu prikazivanja
    public function read_by_pitanje($fk_pitanje)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE fk_pitanje = :fk_pitanje
                  ORDER BY redosled ASC, id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_pitanje', $fk_pitanje);
        $stmt->execute();

        return $stmt;
    }
}
