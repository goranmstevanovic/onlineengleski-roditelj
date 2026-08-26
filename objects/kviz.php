<?php

class kviz
{
    private $conn;
    private $table_name = "kvizovi";

    public $id;
    public $naziv;
    public $opis;
    public $vreme_ogranicenje_min;
    public $broj_pokusaja = 1;
    public $prikazi_odgovore = 0;
    public $rok;
    public $aktivan = 1;
    public $fk_profesor;
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
                vreme_ogranicenje_min = :vreme_ogranicenje_min,
                broj_pokusaja = :broj_pokusaja,
                prikazi_odgovore = :prikazi_odgovore,
                rok = :rok,
                aktivan = :aktivan,
                fk_profesor = :fk_profesor,
                created_at = :created_at";

        $stmt = $this->conn->prepare($query);

        $this->naziv = htmlspecialchars(strip_tags($this->naziv));
        $this->opis = htmlspecialchars(strip_tags($this->opis));

        $stmt->bindParam(':naziv', $this->naziv);
        $stmt->bindParam(':opis', $this->opis);
        $stmt->bindParam(':vreme_ogranicenje_min', $this->vreme_ogranicenje_min);
        $stmt->bindParam(':broj_pokusaja', $this->broj_pokusaja);
        $stmt->bindParam(':prikazi_odgovore', $this->prikazi_odgovore);
        $stmt->bindParam(':rok', $this->rok);
        $stmt->bindParam(':aktivan', $this->aktivan);
        $stmt->bindParam(':fk_profesor', $this->fk_profesor);
        $stmt->bindParam(':created_at', $this->created_at);

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
                vreme_ogranicenje_min = :vreme_ogranicenje_min,
                broj_pokusaja = :broj_pokusaja,
                prikazi_odgovore = :prikazi_odgovore,
                rok = :rok,
                aktivan = :aktivan
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->naziv = htmlspecialchars(strip_tags($this->naziv));
        $this->opis = htmlspecialchars(strip_tags($this->opis));

        $stmt->bindParam(':naziv', $this->naziv);
        $stmt->bindParam(':opis', $this->opis);
        $stmt->bindParam(':vreme_ogranicenje_min', $this->vreme_ogranicenje_min);
        $stmt->bindParam(':broj_pokusaja', $this->broj_pokusaja);
        $stmt->bindParam(':prikazi_odgovore', $this->prikazi_odgovore);
        $stmt->bindParam(':rok', $this->rok);
        $stmt->bindParam(':aktivan', $this->aktivan);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    // Soft delete - kviz prestaje da bude vidljiv djacima, ali istorija pokusaja ostaje
    public function delete($id)
    {
        $query = "UPDATE " . $this->table_name . " SET aktivan = 0 WHERE id = :id";
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

    // Svi aktivni kvizovi za admin pregled, najnoviji prvi
    public function read_all()
    {
        $query = "SELECT k.*, u.firstname, u.lastname
                  FROM " . $this->table_name . " k
                  LEFT JOIN users u ON u.id = k.fk_profesor
                  WHERE k.aktivan = 1
                  ORDER BY k.created_at DESC, k.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Aktivni kvizovi koje je kreirao odredjeni profesor
    public function read_all_by_profesor($fk_profesor)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE fk_profesor = :fk_profesor
                    AND aktivan = 1
                  ORDER BY created_at DESC, id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_profesor', $fk_profesor);
        $stmt->execute();

        return $stmt;
    }

    // Kvizovi vidljivi djaku - preko grupa kojima je kviz dodeljen i kojima djak pripada
    public function read_visible_for_djak($fk_djak)
    {
        $query = "SELECT DISTINCT k.*,
                         (SELECT MAX(g.created_at) FROM kviz_grupe g WHERE g.fk_kviz = k.id) AS podeljeno_at
                  FROM " . $this->table_name . " k
                  INNER JOIN kviz_grupe kg ON kg.fk_kviz = k.id
                  INNER JOIN povezivanje p ON p.fk_grupa = kg.fk_grupa
                  WHERE p.fk_djak = :fk_djak
                    AND p.status = 1
                    AND k.aktivan = 1
                  ORDER BY k.id DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_djak', $fk_djak);
        $stmt->execute();

        return $stmt;
    }

    // Zamenjuje listu grupa kojima je kviz dodeljen (brise stare veze, upisuje nove)
    public function set_grupe($kviz_id, array $grupa_ids)
    {
        $delete = $this->conn->prepare("DELETE FROM kviz_grupe WHERE fk_kviz = :fk_kviz");
        $delete->bindParam(':fk_kviz', $kviz_id);
        $delete->execute();

        if (empty($grupa_ids)) {
            return true;
        }

        $insert = $this->conn->prepare("INSERT INTO kviz_grupe (fk_kviz, fk_grupa) VALUES (:fk_kviz, :fk_grupa)");

        foreach ($grupa_ids as $fk_grupa) {
            $insert->bindParam(':fk_kviz', $kviz_id);
            $insert->bindParam(':fk_grupa', $fk_grupa);
            if (!$insert->execute()) {
                $this->showError($insert);
                return false;
            }
        }

        return true;
    }

    // Grupe dodeljene kvizu - za prikaz/editovanje u admin formi
    public function read_grupe($kviz_id)
    {
        $query = "SELECT g.id, g.alias
                  FROM kviz_grupe kg
                  INNER JOIN grupe g ON g.id = kg.fk_grupa
                  WHERE kg.fk_kviz = :fk_kviz
                  ORDER BY g.alias";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_kviz', $kviz_id);
        $stmt->execute();

        return $stmt;
    }

    // Grupe dodeljene kvizu, sa detaljima (jezik/nivo/uzrast/profesor) - za prikaz badge-ova u "Deljenje" stranici
    public function read_grupe_detaljno($kviz_id)
    {
        $query = "SELECT g.id, g.alias, j.ime AS jezik_ime, nz.ime AS nivo_ime, uz.ime AS uzrast_ime,
                         us.color_prof, us.firstname, us.lastname
                  FROM kviz_grupe kg
                  INNER JOIN grupe g ON g.id = kg.fk_grupa
                  LEFT JOIN jezik j ON j.id = g.fk_jezik
                  LEFT JOIN nivo_znanja nz ON nz.id = g.nivo
                  LEFT JOIN uzrasti uz ON uz.id = g.uzrast
                  LEFT JOIN users us ON us.id = g.fk_profesor
                  WHERE kg.fk_kviz = :fk_kviz
                  ORDER BY j.ime, nz.ime, uz.ime, g.alias";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_kviz', $kviz_id);
        $stmt->execute();

        return $stmt;
    }

    // Dodaje jednu grupu kvizu (koristi se za trenutno cekiranje u "Deljenje" stranici)
    public function add_grupa($kviz_id, $grupa_id)
    {
        $query = "INSERT IGNORE INTO kviz_grupe (fk_kviz, fk_grupa) VALUES (:fk_kviz, :fk_grupa)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_kviz', $kviz_id);
        $stmt->bindParam(':fk_grupa', $grupa_id);

        return $stmt->execute();
    }

    // Uklanja jednu grupu od kviza (koristi se za trenutno otkacivanje u "Deljenje" stranici)
    public function remove_grupa($kviz_id, $grupa_id)
    {
        $query = "DELETE FROM kviz_grupe WHERE fk_kviz = :fk_kviz AND fk_grupa = :fk_grupa";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_kviz', $kviz_id);
        $stmt->bindParam(':fk_grupa', $grupa_id);

        return $stmt->execute();
    }

    // Svi djaci kojima je kviz dostupan (preko grupa kojima je kviz dodeljen) - za admin statistiku
    public function read_djaci_za_kviz($kviz_id)
    {
        $query = "SELECT DISTINCT d.id, d.firstname, d.lastname
                  FROM kviz_grupe kg
                  INNER JOIN povezivanje p ON p.fk_grupa = kg.fk_grupa
                  INNER JOIN djaci d ON d.id = p.fk_djak
                  WHERE kg.fk_kviz = :fk_kviz
                    AND p.status = 1
                  ORDER BY d.firstname, d.lastname";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_kviz', $kviz_id);
        $stmt->execute();

        return $stmt;
    }
}
