<?php


class ucionica
{
    private $conn;
    private $table_name = "ucionice";

    // object properties
    public $id;
    public $ime;
    public $color_room;
    public $fk_lokacija;
    public $active;

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
                color_room = :color_room,
                fk_lokacija = :fk_lokacija,
                active = :active
                
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->ime = htmlspecialchars(strip_tags($this->ime));
        $this->color_room = htmlspecialchars(strip_tags($this->color_room));
        $this->active = htmlspecialchars(strip_tags($this->active));
         // bind the values
        $stmt->bindParam(':ime', $this->ime);
        $stmt->bindParam(':color_room', $this->color_room);
        $stmt->bindParam(':fk_lokacija', $this->fk_lokacija);
        $stmt->bindParam(':active', $this->active);
        

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
                    u.*, l.ime AS ime_lokacije                
                FROM  " . $this->table_name . " u
                INNER JOIN lokacije l ON u.fk_lokacija = l.id 
                WHERE u.active = 1 && l.active = 1 order BY u.fk_lokacija ASC
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }
    function read_all_lokacija($loc)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 && `fk_lokacija` = $loc
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }


    function read_one_ucionica($idd, $table_name)
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
                WHERE `id` = $idd
                ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_one_ucionica1($idd)
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
                color_room = :color_room,
                fk_lokacija = :fk_lokacija
                
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->ime=htmlspecialchars(strip_tags($this->ime));
        $this->color_room=htmlspecialchars(strip_tags($this->color_room));
        $stmt->bindParam(':fk_lokacija', $this->fk_lokacija);
        


        // bind the values
        $stmt->bindParam(':ime', $this->ime);
        $stmt->bindParam(':color_room', $this->color_room);
        
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