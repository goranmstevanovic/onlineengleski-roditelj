<?php


class nivo_znanja
{

    private $conn;
    private $table_name = "nivo_znanja";

    public $id;
    public $ime;
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
                active =:active,
                fk_platni_razred = :fk_platni_razred
                
                "; 

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->ime = htmlspecialchars(strip_tags($this->ime));
        $this->active = htmlspecialchars(strip_tags($this->active));
        // bind the values
        $stmt->bindParam(':ime', $this->ime);
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':fk_platni_razred', $this->fk_platni_razred);
        // execute the query, also check if query was successful
        if ($stmt->execute()) {
            return true;
        } else {
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
                WHERE `active`=1
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_platni_razred()
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                `platni_razredi`
                WHERE `active`=1
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_one_nivo($idd,$table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				`id` = '$idd'"
				;

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_one_platni_razred($idd,$table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
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
        $this->created=date('Y-m-d H:i:s');
                // update query
        $query = "UPDATE
                " . $this->table_name . "
            SET
                ime = :ime,
                fk_platni_razred = :fk_platni_razred
                
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->ime=htmlspecialchars(strip_tags($this->ime));
        
        


        // bind the values
        $stmt->bindParam(':ime', $this->ime);
        $stmt->bindParam(':fk_platni_razred', $this->fk_platni_razred);
        
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