<?php


class program_rada
{
    private $conn;
    private $table_name = "programi_rada";

    // object properties
    public $id;
    public $ime;
    public $alias;
    public $active;
    public $fk_jezik;
    public $broj_casova;
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
                alias = :alias,
                fk_jezik = :fk_jezik,
                active = :active,
                broj_casova = :broj_casova,
                created = :created
                
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->ime = htmlspecialchars(strip_tags($this->ime));
        $this->alias = htmlspecialchars(strip_tags($this->alias));
        $this->active = htmlspecialchars(strip_tags($this->active));
         // bind the values
        $stmt->bindParam(':ime', $this->ime);
        $stmt->bindParam(':alias', $this->alias);
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':fk_jezik', $this->fk_jezik);
        $stmt->bindParam(':broj_casova', $this->broj_casova);
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
                    " . $this->table_name . ".*, j.ime AS ime_jezika
                FROM
                " . $this->table_name . "
                INNER JOIN jezik j ON " . $this->table_name . ".fk_jezik = j.id
                WHERE " . $this->table_name . ".active=1 ORDER BY " . $this->table_name . ".id ASC
               
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
                alias = :alias,
                fk_jezik = :fk_jezik,
                active = :active,
                broj_casova = :broj_casova
                
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->ime=htmlspecialchars(strip_tags($this->ime));
        $this->alias=htmlspecialchars(strip_tags($this->alias));
        


        // bind the values
        $stmt->bindParam(':ime', $this->ime);
        $stmt->bindParam(':alias', $this->alias);
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':fk_jezik', $this->fk_jezik);
        $stmt->bindParam(':broj_casova', $this->broj_casova);
        
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