<?php
/**
 * M:N veza roditelj <-> djak. Jedan djak moze imati vise roditelja/staratelja,
 * jedan roditelj moze imati vise dece. Vrsta veze (otac/majka/staratelj) je
 * vezana za ovu vezu (fk_vrsta_roditelja), ne za samog roditelja - isti
 * roditelj teoretski moze imati razlicitu ulogu po detetu.
 */

class roditelj_djak
{
    private $conn;
    private $table_name = "roditelji_djaci";

    public $id;
    public $fk_roditelj;
    public $fk_djak;
    public $fk_vrsta_roditelja;
    public $active;
    public $created;

    public function __construct($db){
        $this->conn = $db;
    }

    public function showError($stmt)
    {
        echo "<pre>";
        print_r($stmt->errorInfo());
        echo "</pre>";
    }

    function create()
    {
        $this->created = date('Y-m-d H:i:s');

        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                fk_roditelj = :fk_roditelj,
                fk_djak = :fk_djak,
                fk_vrsta_roditelja = :fk_vrsta_roditelja,
                active = :active,
                created = :created";

        $stmt = $this->conn->prepare($query);

        $this->fk_roditelj = htmlspecialchars(strip_tags($this->fk_roditelj));
        $this->fk_djak = htmlspecialchars(strip_tags($this->fk_djak));
        $this->active = isset($this->active) ? $this->active : 1;

        $stmt->bindParam(':fk_roditelj', $this->fk_roditelj);
        $stmt->bindParam(':fk_djak', $this->fk_djak);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':active', $this->active);

        if(empty($this->fk_vrsta_roditelja)){
            $stmt->bindValue(':fk_vrsta_roditelja', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':fk_vrsta_roditelja', $this->fk_vrsta_roditelja);
        }

        if($stmt->execute()){
            return $this->conn->lastInsertId();
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    function update($id)
    {
        $query = "UPDATE
                " . $this->table_name . "
            SET
                fk_vrsta_roditelja = :fk_vrsta_roditelja,
                active = :active
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':id', $id);

        if(empty($this->fk_vrsta_roditelja)){
            $stmt->bindValue(':fk_vrsta_roditelja', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':fk_vrsta_roditelja', $this->fk_vrsta_roditelja);
        }

        if($stmt->execute()){
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    // deaktivira vezu (soft-delete) - koristi se za "ukloni roditelja sa dosijea djaka"
    function deactivate($id)
    {
        $query = "UPDATE " . $this->table_name . " SET active = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if($stmt->execute()){
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    // svi roditelji/staratelji za jednog djaka - za tab "Roditelji" na kartici djaka
    function read_all_za_djaka($fk_djak)
    {
        $query = "SELECT rd.*, r.firstname, r.lastname, r.email, r.contact_number,
                    r.email_verified_at, r.active AS roditelj_active, vr.ime AS vrsta_ime
                FROM " . $this->table_name . " rd
                INNER JOIN roditelji r ON r.id = rd.fk_roditelj
                LEFT JOIN vrste_roditelja vr ON vr.id = rd.fk_vrsta_roditelja
                WHERE rd.active = 1 AND rd.fk_djak = :fk_djak
                ORDER BY r.lastname ASC, r.firstname ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_djak', $fk_djak);
        $stmt->execute();

        return $stmt;
    }

    // sva deca za jednog roditelja - osnova za avalon-roditelj ("moja deca u skoli")
    // $bez_kandidata = true: bez djaka sa statusom 2 (kandidat iz lida) - roditeljska app ih ne prikazuje
    function read_all_djaci_za_roditelja($fk_roditelj, $bez_kandidata = false)
    {
        $query = "SELECT rd.*, dj.firstname, dj.lastname, dj.status AS djak_status,
                    dj.photo, vr.ime AS vrsta_ime
                FROM " . $this->table_name . " rd
                INNER JOIN djaci dj ON dj.id = rd.fk_djak
                LEFT JOIN vrste_roditelja vr ON vr.id = rd.fk_vrsta_roditelja
                WHERE rd.active = 1 AND rd.fk_roditelj = :fk_roditelj
                " . ($bez_kandidata ? "AND dj.status != 2" : "") . "
                ORDER BY dj.firstname ASC, dj.lastname ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_roditelj', $fk_roditelj);
        $stmt->execute();

        return $stmt;
    }

    // provera da li veza vec postoji (spreci duplo dodavanje istog roditelja istom djaku)
    function read_one($fk_roditelj, $fk_djak)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE active = 1 AND fk_roditelj = :fk_roditelj AND fk_djak = :fk_djak
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_roditelj', $fk_roditelj);
        $stmt->bindParam(':fk_djak', $fk_djak);
        $stmt->execute();

        return $stmt;
    }

}
