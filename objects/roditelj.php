<?php
/**
 * Roditelj/staratelj djaka - nezavisan login nalog (isti auth obrazac kao djak.php:
 * email je unique username, password hash, email verifikacija preko tokena).
 * Osnova za buduci avalon-roditelj portal (roditelj vidi svu svoju decu preko
 * objects/roditelj_djak.php::read_all_djaca_za_roditelja()).
 */

class roditelj
{
    private $conn;
    private $table_name = "roditelji";

    // object properties
    public $id;
    public $firstname;
    public $lastname;
    public $contact_number;
    public $email;
    public $password;
    public $role;
    public $email_verified_at;
    public $verification_token;
    public $fk_porodica;
    public $active;
    public $created;
    public $modified;

    public function __construct($db){
        $this->conn = $db;
    }

    public function showError($stmt){
        echo "<pre>";
        print_r($stmt->errorInfo());
        echo "</pre>";
    }

    // da li email vec postoji (jedinstven je - koristi se kao username)
    function emailExists()
    {
        $query = "SELECT id, firstname, lastname, password, active, email_verified_at
            FROM " . $this->table_name . "
            WHERE email = :email
            LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->firstname = $row['firstname'];
            $this->lastname = $row['lastname'];
            $this->password = $row['password'];
            $this->active = $row['active'];
            $this->email_verified_at = $row['email_verified_at'];
            return true;
        }

        return false;
    }

    // novi roditelj - password se NE zadaje ovde, samo se generise
    // verification_token; roditelj postavlja lozinku preko "podesi lozinku"
    // linka sa mejla (vidi set_password())
    function create()
    {
        if(empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            die("Email nije validan.");
        }

        $this->verification_token = bin2hex(random_bytes(32));
        $email_verified_at = null;

        // nasumican, neupotrebljiv hash dok roditelj sam ne postavi lozinku
        $temp_password_hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);

        $this->created = date('Y-m-d H:i:s');

        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                firstname = :firstname,
                lastname = :lastname,
                contact_number = :contact_number,
                email = :email,
                password = :password,
                role = 'roditelj',
                verification_token = :verification_token,
                email_verified_at = :email_verified_at,
                fk_porodica = :fk_porodica,
                active = :active,
                created = :created";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->contact_number = htmlspecialchars(strip_tags($this->contact_number));
        $this->email = htmlspecialchars(strip_tags($this->email));

        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':contact_number', $this->contact_number);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $temp_password_hash);
        $stmt->bindParam(':verification_token', $this->verification_token);
        $stmt->bindParam(':email_verified_at', $email_verified_at);

        if(empty($this->fk_porodica)){
            $stmt->bindValue(':fk_porodica', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':fk_porodica', $this->fk_porodica);
        }

        $this->active = isset($this->active) ? $this->active : 1;
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':created', $this->created);

        if($stmt->execute()){
            return $this->conn->lastInsertId();
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    function read_one($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt;
    }

    function read_all()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE active = 1 ORDER BY firstname ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // svi aktivni roditelji koji JOS NISU povezani (aktivnom vezom) sa datim djakom -
    // za "poveži postojećeg roditelja" na kartici djaka (izbegava ponudu duplikata)
    function read_all_dostupni_za_djaka($fk_djak)
    {
        $query = "SELECT r.* FROM " . $this->table_name . " r
                WHERE r.active = 1
                AND r.id NOT IN (
                    SELECT fk_roditelj FROM roditelji_djaci
                    WHERE fk_djak = :fk_djak AND active = 1
                )
                ORDER BY r.firstname ASC, r.lastname ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_djak', $fk_djak);
        $stmt->execute();

        return $stmt;
    }

    function update($id)
    {
        $stmt_old = $this->conn->prepare("SELECT email FROM " . $this->table_name . " WHERE id = :id");
        $stmt_old->bindParam(':id', $id);
        $stmt_old->execute();
        $row_old = $stmt_old->fetch(PDO::FETCH_ASSOC);
        $old_email = $row_old['email'] ?? null;

        if(!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            die("Email nije validan.");
        }

        $set_verification_sql = "";
        $this->verification_token = null;

        if($this->email != $old_email){
            // email promenjen -> nov token, ponovna verifikacija
            $this->verification_token = bin2hex(random_bytes(32));
            $set_verification_sql = ", verification_token = :verification_token, email_verified_at = NULL";
        }

        $query = "UPDATE
                " . $this->table_name . "
            SET
                firstname = :firstname,
                lastname = :lastname,
                contact_number = :contact_number,
                email = :email,
                fk_porodica = :fk_porodica,
                active = :active
                $set_verification_sql
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->contact_number = htmlspecialchars(strip_tags($this->contact_number));
        $this->email = htmlspecialchars(strip_tags($this->email));

        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':contact_number', $this->contact_number);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':id', $id);

        if(empty($this->fk_porodica)){
            $stmt->bindValue(':fk_porodica', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':fk_porodica', $this->fk_porodica);
        }

        if($this->verification_token){
            $stmt->bindParam(':verification_token', $this->verification_token);
        }

        if($stmt->execute()){
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    // roditelj (ili admin u njegovo ime) postavlja lozinku - koristi se i pri
    // prvom podesavanju naloga (preko verification_token linka) i pri reset-u
    function set_password($id, $new_password_plain)
    {
        $password_hash = password_hash($new_password_plain, PASSWORD_BCRYPT);
        $email_verified_at = date('Y-m-d H:i:s');

        $query = "UPDATE " . $this->table_name . "
            SET password = :password,
                email_verified_at = COALESCE(email_verified_at, :email_verified_at),
                verification_token = NULL
            WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':email_verified_at', $email_verified_at);
        $stmt->bindParam(':id', $id);

        if($stmt->execute()){
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    // rucna verifikacija email-a od strane admina
    function verify_email_manual($id)
    {
        $email_verified_at = date('Y-m-d H:i:s');
        $query = "UPDATE " . $this->table_name . " SET email_verified_at = :email_verified_at WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email_verified_at', $email_verified_at);
        $stmt->bindParam(':id', $id);

        if($stmt->execute()){
            return $email_verified_at;
        }

        return false;
    }

}
