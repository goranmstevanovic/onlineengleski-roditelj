<?php

class kviz_odgovor
{
    private $conn;
    private $table_name = "kviz_odgovori";

    public $id;
    public $fk_pokusaj;
    public $fk_pitanje;
    public $fk_odgovor;
    public $unet_tekst; // koristi se samo za 'dopuna' pitanja - tekst koji je djak uneo
    public $tacan;

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

    // Upisuje odgovor djaka na jedno pitanje u okviru pokusaja
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
            SET
                fk_pokusaj = :fk_pokusaj,
                fk_pitanje = :fk_pitanje,
                fk_odgovor = :fk_odgovor,
                unet_tekst = :unet_tekst,
                tacan = :tacan";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':fk_pokusaj', $this->fk_pokusaj);
        $stmt->bindParam(':fk_pitanje', $this->fk_pitanje);
        $stmt->bindParam(':fk_odgovor', $this->fk_odgovor);
        $stmt->bindParam(':unet_tekst', $this->unet_tekst);
        $stmt->bindParam(':tacan', $this->tacan);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    // Svi odgovori djaka u okviru jednog pokusaja, sa tekstom pitanja i izabranog odgovora
    // - koristi se za prikaz rezultata (show_answers) i admin pregled
    public function read_by_pokusaj($fk_pokusaj)
    {
        $query = "SELECT ko.*, p.tekst_pitanja, p.tip_pitanja, p.poeni, o.tekst_odgovora, o.poeni AS odgovor_poeni, o.tacan_broj AS odgovor_tacan_broj, o.redosled AS odgovor_redosled
                  FROM " . $this->table_name . " ko
                  INNER JOIN pitanja p ON p.id = ko.fk_pitanje
                  LEFT JOIN odgovori o ON o.id = ko.fk_odgovor
                  WHERE ko.fk_pokusaj = :fk_pokusaj
                  ORDER BY p.redosled ASC, p.id ASC, o.redosled ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_pokusaj', $fk_pokusaj);
        $stmt->execute();

        return $stmt;
    }

    // Statistika za admin pregled po pitanju: koliko puta je svaki odgovor izabran
    public function read_stats_by_pitanje($fk_pitanje)
    {
        $query = "SELECT fk_odgovor, COUNT(*) AS broj
                  FROM " . $this->table_name . "
                  WHERE fk_pitanje = :fk_pitanje
                  GROUP BY fk_odgovor";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_pitanje', $fk_pitanje);
        $stmt->execute();

        return $stmt;
    }
}
