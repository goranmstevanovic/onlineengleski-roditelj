<?php


class polozeni_ispit
{
    private $conn;
    private $table_name = "polozeni_ispiti";

    // object properties
    public $id;
    public $ime;
    public $opis;
    public $fk_jezik;
    public $fk_nivo_znanja;
    
    public $active;

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

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');

        // insert query
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                ime = :ime, 
                active = :active,
                opis = :opis,
                fk_jezik = :fk_jezik,
                fk_nivo_znanja = :fk_nivo_znanja,
               
                created = :created
                
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->ime = htmlspecialchars(strip_tags($this->ime));
        
        $this->active = htmlspecialchars(strip_tags($this->active));
         // bind the values
        $stmt->bindParam(':ime', $this->ime);
      
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':opis', $this->opis);
        $stmt->bindParam(':fk_jezik', $this->fk_jezik);
        $stmt->bindParam(':fk_nivo_znanja', $this->fk_nivo_znanja);
        $stmt->bindParam(':created', $this->created);
        

        // execute the query, also check if query was successful
        if ($stmt->execute()) {
            return true;
        } else {
            echo "\nPDO::errorInfo():\n";
            print_r($stmt->errorInfo());
            $this->showError($stmt);
            return false;
        }
    }

    function read_all()
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_one1($idd, $table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE `id` = $idd
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
       // $this->updated=date('Y-m-d H:i:s');
                // update query
        $query = "UPDATE
                " . $this->table_name . "
            SET
            ime = :ime, 
            active = :active,
            opis = :opis,
            fk_jezik = :fk_jezik,
            fk_nivo_znanja = :fk_nivo_znanja
                       
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        $this->ime = htmlspecialchars(strip_tags($this->ime));
        
       
         // bind the values
        $stmt->bindParam(':ime', $this->ime);
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':opis', $this->opis);
        $stmt->bindParam(':fk_jezik', $this->fk_jezik);
        $stmt->bindParam(':fk_nivo_znanja', $this->fk_nivo_znanja);
              
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