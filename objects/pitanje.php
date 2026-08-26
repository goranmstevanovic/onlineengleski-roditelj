<?php

class pitanje
{
    private $conn;
    private $table_name = "pitanja";

    public $id;
    public $fk_kviz;
    public $tekst_pitanja;
    public $tip_pitanja = 'multiple_choice';
    public $poeni = 1;
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
                fk_kviz = :fk_kviz,
                tekst_pitanja = :tekst_pitanja,
                tip_pitanja = :tip_pitanja,
                poeni = :poeni,
                redosled = :redosled";

        $stmt = $this->conn->prepare($query);

        $this->tekst_pitanja = htmlspecialchars(strip_tags($this->tekst_pitanja));

        $stmt->bindParam(':fk_kviz', $this->fk_kviz);
        $stmt->bindParam(':tekst_pitanja', $this->tekst_pitanja);
        $stmt->bindParam(':tip_pitanja', $this->tip_pitanja);
        $stmt->bindParam(':poeni', $this->poeni);
        $stmt->bindParam(':redosled', $this->redosled);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
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

    // Sva pitanja jednog kviza, po zadatom redosledu prikazivanja
    public function read_by_kviz($fk_kviz)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE fk_kviz = :fk_kviz
                  ORDER BY redosled ASC, id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_kviz', $fk_kviz);
        $stmt->execute();

        return $stmt;
    }

    // Brise sva pitanja kviza pre ponovnog upisa iz forme za izmenu
    // (ON DELETE CASCADE u bazi automatski brise i njihove odgovore)
    public function delete_by_kviz($fk_kviz)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE fk_kviz = :fk_kviz";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_kviz', $fk_kviz);

        return $stmt->execute();
    }
}
