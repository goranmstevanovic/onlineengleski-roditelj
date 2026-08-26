<?php

class dokument
{
    private $conn;
    private $table_name = "dokumenti";

    public $id;
    public $naziv;
    public $opis;

    public $originalni_naziv;
    public $sacuvan_naziv;
    public $putanja;
    public $ekstenzija;
    public $mime_type;
    public $velicina;
    public $datum_vazenja_do;

    public $fk_jezik = 0;
    public $fk_nivo_znanja = 0;
    public $fk_uzrast = 0;

    public $uploadovao_id;
    public $uploadovao_uloga;

    public $vidljiv_profesorima = 1;
    public $aktivan = 1;

    public $datum_pocetka_prikaza;
    public $datum_kraja_prikaza;

    public $created_at;
    public $updated_at;

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
                naziv = :naziv,
                opis = :opis,
                originalni_naziv = :originalni_naziv,
                sacuvan_naziv = :sacuvan_naziv,
                putanja = :putanja,
                ekstenzija = :ekstenzija,
                mime_type = :mime_type,
                velicina = :velicina,
                fk_jezik = :fk_jezik,
                fk_nivo_znanja = :fk_nivo_znanja,
                fk_uzrast = :fk_uzrast,
                datum_vazenja_do = :datum_vazenja_do,
                uploadovao_id = :uploadovao_id,
                uploadovao_uloga = :uploadovao_uloga,
                vidljiv_profesorima = :vidljiv_profesorima,
                aktivan = :aktivan,
                datum_pocetka_prikaza = :datum_pocetka_prikaza,
                datum_kraja_prikaza = :datum_kraja_prikaza,
                created_at = :created_at";

        $stmt = $this->conn->prepare($query);

        $this->naziv = htmlspecialchars(strip_tags($this->naziv));
        $this->opis = htmlspecialchars(strip_tags($this->opis));
        $this->originalni_naziv = htmlspecialchars(strip_tags($this->originalni_naziv));
        $this->sacuvan_naziv = htmlspecialchars(strip_tags($this->sacuvan_naziv));
        $this->putanja = htmlspecialchars(strip_tags($this->putanja));
        $this->ekstenzija = htmlspecialchars(strip_tags($this->ekstenzija));
        $this->mime_type = htmlspecialchars(strip_tags($this->mime_type));
        $this->uploadovao_uloga = htmlspecialchars(strip_tags($this->uploadovao_uloga));

        $stmt->bindParam(':naziv', $this->naziv);
        $stmt->bindParam(':opis', $this->opis);
        $stmt->bindParam(':originalni_naziv', $this->originalni_naziv);
        $stmt->bindParam(':sacuvan_naziv', $this->sacuvan_naziv);
        $stmt->bindParam(':putanja', $this->putanja);
        $stmt->bindParam(':ekstenzija', $this->ekstenzija);
        $stmt->bindParam(':mime_type', $this->mime_type);
        $stmt->bindParam(':velicina', $this->velicina);
        $stmt->bindParam(':fk_jezik', $this->fk_jezik);
        $stmt->bindParam(':fk_nivo_znanja', $this->fk_nivo_znanja);
        $stmt->bindParam(':fk_uzrast', $this->fk_uzrast);
        $stmt->bindParam(':uploadovao_id', $this->uploadovao_id);
        $stmt->bindParam(':uploadovao_uloga', $this->uploadovao_uloga);
        $stmt->bindParam(':vidljiv_profesorima', $this->vidljiv_profesorima);
        $stmt->bindParam(':aktivan', $this->aktivan);
        $stmt->bindParam(':datum_pocetka_prikaza', $this->datum_pocetka_prikaza);
        $stmt->bindParam(':datum_kraja_prikaza', $this->datum_kraja_prikaza);
        $stmt->bindParam(':created_at', $this->created_at);
        $stmt->bindParam(':datum_vazenja_do', $this->datum_vazenja_do);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    public function update($id)
    {
        $query = "UPDATE " . $this->table_name . "
            SET
                naziv = :naziv,
                opis = :opis,
                fk_jezik = :fk_jezik,
                fk_nivo_znanja = :fk_nivo_znanja,
                fk_uzrast = :fk_uzrast,
                vidljiv_profesorima = :vidljiv_profesorima,
                aktivan = :aktivan,
                datum_vazenja_do = :datum_vazenja_do,
                datum_pocetka_prikaza = :datum_pocetka_prikaza,
                datum_kraja_prikaza = :datum_kraja_prikaza
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->naziv = htmlspecialchars(strip_tags($this->naziv));
        $this->opis = htmlspecialchars(strip_tags($this->opis));

        $stmt->bindParam(':naziv', $this->naziv);
        $stmt->bindParam(':opis', $this->opis);
        $stmt->bindParam(':fk_jezik', $this->fk_jezik);
        $stmt->bindParam(':fk_nivo_znanja', $this->fk_nivo_znanja);
        $stmt->bindParam(':fk_uzrast', $this->fk_uzrast);
        $stmt->bindParam(':vidljiv_profesorima', $this->vidljiv_profesorima);
        $stmt->bindParam(':aktivan', $this->aktivan);
        $stmt->bindParam(':datum_pocetka_prikaza', $this->datum_pocetka_prikaza);
        $stmt->bindParam(':datum_kraja_prikaza', $this->datum_kraja_prikaza);
        $stmt->bindParam(':datum_vazenja_do', $this->datum_vazenja_do);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    public function update_file($id)
    {
        $query = "UPDATE " . $this->table_name . "
            SET
                originalni_naziv = :originalni_naziv,
                sacuvan_naziv = :sacuvan_naziv,
                putanja = :putanja,
                ekstenzija = :ekstenzija,
                mime_type = :mime_type,
                velicina = :velicina
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->originalni_naziv = htmlspecialchars(strip_tags($this->originalni_naziv));
        $this->sacuvan_naziv = htmlspecialchars(strip_tags($this->sacuvan_naziv));
        $this->putanja = htmlspecialchars(strip_tags($this->putanja));
        $this->ekstenzija = htmlspecialchars(strip_tags($this->ekstenzija));
        $this->mime_type = htmlspecialchars(strip_tags($this->mime_type));

        $stmt->bindParam(':originalni_naziv', $this->originalni_naziv);
        $stmt->bindParam(':sacuvan_naziv', $this->sacuvan_naziv);
        $stmt->bindParam(':putanja', $this->putanja);
        $stmt->bindParam(':ekstenzija', $this->ekstenzija);
        $stmt->bindParam(':mime_type', $this->mime_type);
        $stmt->bindParam(':velicina', $this->velicina);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    public function delete($id)
    {
        $query = "UPDATE " . $this->table_name . "
            SET aktivan = 0
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);
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

    public function read_all()
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE aktivan = 1
                  ORDER BY created_at DESC, id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function read_all_by_uploader($uploadovao_id, $uploadovao_uloga = null)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE aktivan = 1
                  AND uploadovao_id = :uploadovao_id";

        if (!empty($uploadovao_uloga)) {
            $query .= " AND uploadovao_uloga = :uploadovao_uloga";
        }

        $query .= " ORDER BY created_at DESC, id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uploadovao_id', $uploadovao_id);

        if (!empty($uploadovao_uloga)) {
            $stmt->bindParam(':uploadovao_uloga', $uploadovao_uloga);
        }

        $stmt->execute();

        return $stmt;
    }

    public function read_filtered($fk_jezik = null, $fk_nivo_znanja = null, $fk_uzrast = null)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE aktivan = 1";

        $params = array();

        if ($fk_jezik !== null) {
            $query .= " AND (fk_jezik = 0 OR fk_jezik = :fk_jezik)";
            $params[':fk_jezik'] = $fk_jezik;
        }

        if ($fk_nivo_znanja !== null) {
            $query .= " AND (fk_nivo_znanja = 0 OR fk_nivo_znanja = :fk_nivo_znanja)";
            $params[':fk_nivo_znanja'] = $fk_nivo_znanja;
        }

        if ($fk_uzrast !== null) {
            $query .= " AND (fk_uzrast = 0 OR fk_uzrast = :fk_uzrast)";
            $params[':fk_uzrast'] = $fk_uzrast;
        }

        $query .= " ORDER BY created_at DESC, id DESC";

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt;
    }
}