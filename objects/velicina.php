<?php


class velicina
{
    private $conn;
    private $table_name = "velicina";
    public $id;
    public $ime;
    public $active; 
    public function __construct($db){
        $this->conn = $db;
    }

    function create()
    {
   
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                ime = :ime,
                opis = :opis,
                active =:active
               
                
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
       // $stmt->bindParam(':fk_platni_razred', $this->fk_platni_razred);
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
                WHERE `active`=1 ORDER BY `id` ASC
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_one_velicina($idd,$table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " .$this->table_name . "
                WHERE
				`id` = '$idd'"
				;

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
                    " .$this->table_name . "
                WHERE
				`id` = '$idd'"
				;

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
                opis = :opis
               
                
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->ime = htmlspecialchars(strip_tags($this->ime));
        $this->opis = htmlspecialchars(strip_tags($this->opis));
        
        


        // bind the values
        $stmt->bindParam(':ime', $this->ime);
        $stmt->bindParam(':opis', $this->opis);
        
        
        $stmt->bindParam(':idd', $idd);
        // hash the password before saving to database

        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            $this->showError($stmt);
            return false;
        }

    }



}