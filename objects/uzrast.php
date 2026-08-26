<?php


class uzrast
{
    private $conn;
    private $table_name = "uzrasti";

    // object properties
    public $id;
    public $ime;
    public $opis;
    public $active;
    public $fk_platni_razred;

    public function __construct($db){
        $this->conn = $db;
    }

     function create()
    {

        // to get time stamp for 'created' field
       

        // insert query
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                ime = :ime, 
                opis = :opis,
                fk_platni_razred = :fk_platni_razred,
                active = :active
                
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->ime = htmlspecialchars(strip_tags($this->ime));
        $this->opis = htmlspecialchars(strip_tags($this->opis));
        $this->active = htmlspecialchars(strip_tags($this->active));
         // bind the values
        $stmt->bindParam(':ime', $this->ime);
        $stmt->bindParam(':opis', $this->opis);
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':fk_platni_razred', $this->fk_platni_razred);
        

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
                    *
                FROM
                " . $this->table_name . "
                WHERE `active`=1 ORDER BY `id` ASC
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }
    function read_all_platni_razred($fk_platni_razred)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `active`=1 && fk_platni_razred = $fk_platni_razred 
                ORDER BY `id` ASC
               
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
                fk_platni_razred = :fk_platni_razred,
                active = :active
                
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->ime=htmlspecialchars(strip_tags($this->ime));
        $this->opis=htmlspecialchars(strip_tags($this->opis));
        


        // bind the values
        $stmt->bindParam(':ime', $this->ime);
        $stmt->bindParam(':opis', $this->opis);
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':fk_platni_razred', $this->fk_platni_razred);
        
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