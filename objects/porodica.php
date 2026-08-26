<?php


class porodica
{
    private $conn;
    private $table_name = "porodice";

    // object properties
    public $id;
    public $ime;
    public $opis;
    public $adresa;
    public $email;
    public $pib;
    public $mb;
    public $posta_mesto;
    public $racun;
    public $active;
    public $odgovoran;
    public $broj_clanova; 
    public $created;

    public function __construct($db){
        $this->conn = $db;
    }

     function create()
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');

        // insert query
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                ime = :ime, 
                opis = :opis,
                adresa = :adresa,
                email = :email,
                pib = :pib,
                mb = :mb,
                posta_mesto = :posta_mesto,
                racun = :racun,
                odgovoran = :odgovoran,
                active = :active,
                broj_clanova = :broj_clanova,
                created = :created
                
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->ime = htmlspecialchars(strip_tags($this->ime));
        $this->opis = htmlspecialchars(strip_tags($this->opis));

        $this->adresa = htmlspecialchars(strip_tags($this->adresa));
        $this->posta_mesto = htmlspecialchars(strip_tags($this->posta_mesto));

        $this->odgovoran = htmlspecialchars(strip_tags($this->odgovoran));
         // bind the values
        $stmt->bindParam(':ime', $this->ime);
        $stmt->bindParam(':opis', $this->opis);

        $stmt->bindParam(':adresa', $this->adresa);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':pib', $this->pib);
        $stmt->bindParam(':mb', $this->mb);
        $stmt->bindParam(':posta_mesto', $this->posta_mesto);
        $stmt->bindParam(':racun', $this->racun);

        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':odgovoran', $this->odgovoran);
        $stmt->bindParam(':broj_clanova', $this->broj_clanova);
        $stmt->bindParam(':created', $this->created);
        

        // execute the query, also check if query was successful
        if ($stmt->execute()) {
           // return true;
           $lastid=$this->conn->lastInsertId();
           // return true;
           return $lastid;
        } else {
            echo "\nPDO::errorInfo():\n";
            print_r($stmt->errorInfo());
          //  $this->showError($stmt);
            return false;
        }
    }

    function read_all()
    {
        //select all data
        $query = "SELECT
                    " . $this->table_name . ".*
                FROM
                " . $this->table_name . "
               
                WHERE " . $this->table_name . ".active=1 ORDER BY " . $this->table_name . ".id ASC
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_all()
    {
        //select all data
        $query = "SELECT
                    p.*
                FROM
                " . $this->table_name . " p
               
            
                ORDER BY p.id ASC
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

   
    function read_one($idd)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `id` = '$idd'
                ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function update($idd)
    {
      //  $this->procenat_za_platu = $procenat_za_platu;

        // to get time stamp for 'created' field
       // $this->created=date('Y-m-d H:i:s');
                // update query
        $query = "UPDATE
                " . $this->table_name . "
            SET
                ime = :ime,
                opis = :opis,
                adresa = :adresa,
                email = :email,
                pib = :pib,
                mb = :mb,
                posta_mesto = :posta_mesto,
                racun = :racun,
                odgovoran = :odgovoran,
                active = :active,
                broj_clanova = :broj_clanova
                
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->ime = htmlspecialchars(strip_tags($this->ime));
        $this->opis = htmlspecialchars(strip_tags($this->opis));

        $this->adresa = htmlspecialchars(strip_tags($this->adresa));
        $this->posta_mesto = htmlspecialchars(strip_tags($this->posta_mesto));

        $this->odgovoran = htmlspecialchars(strip_tags($this->odgovoran));
        


        // bind the values
        $stmt->bindParam(':ime', $this->ime);
        $stmt->bindParam(':opis', $this->opis);

        $stmt->bindParam(':adresa', $this->adresa);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':pib', $this->pib);
        $stmt->bindParam(':mb', $this->mb);
        $stmt->bindParam(':posta_mesto', $this->posta_mesto);
        $stmt->bindParam(':racun', $this->racun);

        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':odgovoran', $this->odgovoran);
        $stmt->bindParam(':broj_clanova', $this->broj_clanova);
        
        $stmt->bindParam(':idd', $idd);
        // hash the password before saving to database

        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            echo "\nPDO::errorInfo():\n";
            print_r($stmt->errorInfo());
            
            return false;
        }

    }


}