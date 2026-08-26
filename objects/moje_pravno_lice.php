<?php


class moje_pravno_lice
{
    private $conn;
    private $table_name = "moja_pravna_lica";

    // object properties
    public $id;
    public $ime;
    public $adresa;
    public $active;

    public function __construct($db){
        $this->conn = $db;
    }

    function create()
    {

        // to get time stamp for 'created' field
       
        $this->created=date('Y-m-d H:i:s');
        // insert query
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                ime = :ime, 
                adresa = :adresa,
                created = :created,
                active = :active
                
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->ime = htmlspecialchars(strip_tags($this->ime));
        $this->adresa = htmlspecialchars(strip_tags($this->adresa));
        
        $this->active = htmlspecialchars(strip_tags($this->active));
         // bind the values
        $stmt->bindParam(':ime', $this->ime);
        
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':adresa', $this->adresa);
        $stmt->bindParam(':created', $this->created);
        
        

        // execute the query, also check if query was successful
        if ($stmt->execute()) {
            return true;
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
                    *
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }
    
    function count_all()
    {
        //select all data
        $query = "SELECT
                    id
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $num = $stmt->rowCount();

        // return row count
        return $num;

       
    }

    function read_one($idd)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE `id` = $idd
                ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

  

    function update($idd)
    {
      //  $this->procenat_za_platu = $procenat_za_platu;

        // to get time stamp for 'created' field
        $this->modified=date('Y-m-d H:i:s');
                // update query
        $query = "UPDATE
                " . $this->table_name . "
            SET
            ime = :ime, 
            created = :created,
            
            adresa = :adresa,
           
            active = :active
          
                
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->ime = htmlspecialchars(strip_tags($this->ime));
        
        $this->adresa = htmlspecialchars(strip_tags($this->adresa));
        
        $this->active = htmlspecialchars(strip_tags($this->active));
        


        // bind the values
        $stmt->bindParam(':ime', $this->ime);
        
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':created', $this->created);
        
        $stmt->bindParam(':adresa', $this->adresa);
        
       
        
        

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