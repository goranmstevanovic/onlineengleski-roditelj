<?php
/**
 * created by PhpStorm.
 * User: goran
 * Date: 6.11.2019
 * Time: 17:05
 */

class porodica_clan
{
    private $conn;
    private $table_name = "porodica_clanovi";

    public $id;
    public $fk_porodica;
    public $fk_djak;
    public $active;
    public $created;
    
   // public $active;

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
                fk_porodica = :fk_porodica,
                fk_djak = :fk_djak,
                
                created = :created,
                active = :active
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_porodica = htmlspecialchars(strip_tags($this->fk_porodica));
        $this->fk_djak = htmlspecialchars(strip_tags($this->fk_djak));
        $this->active = htmlspecialchars(strip_tags($this->active));


        // bind the values
        $stmt->bindParam(':fk_porodica', $this->fk_porodica);
        $stmt->bindParam(':fk_djak', $this->fk_djak);
        //$stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':active', $this->active);

        // execute the query, also check if query was successful
        if ($stmt->execute()) {
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    public function showError($stmt)
    {
        echo "<pre>";
        print_r($stmt->errorInfo());
        echo "</pre>";
    }

    function update($idd)
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');

        // insert query
        $query = "UPDATE
        " . $this->table_name . "
         SET
                fk_porodica = :fk_porodica,
                fk_djak = :fk_djak,
                
                created = :created,
                active = :active
                WHERE id = :idd   
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_porodica = htmlspecialchars(strip_tags($this->fk_porodica));
        $this->fk_djak = htmlspecialchars(strip_tags($this->fk_djak));
        $this->active = htmlspecialchars(strip_tags($this->active));


        // bind the values
        $stmt->bindParam(':fk_porodica', $this->fk_porodica);
        $stmt->bindParam(':fk_djak', $this->fk_djak);
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':created', $this->created);
      //  $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':idd', $idd);

        // execute the query, also check if query was successful
        if ($stmt->execute()) {
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

   
    
    public function read_all_for_porodica($fk_porodica)
    {

        $query = " SELECT * 
        FROM " . $this->table_name . "  
        WHERE `active` = 1 &&  `fk_porodica` = '$fk_porodica'  
        order by id asc
    ";

    $stmt = $this->conn->prepare($query);

    // execute query
    $stmt->execute();

    // get number of rows
   // $num = $stmt->rowCount();

    // return row count
    return $stmt;

    }

    public function read_one($fk_porodica, $fk_djak)
    {

        // query to select all user records
        $query = " SELECT * 
            FROM " . $this->table_name . "  
            WHERE `active` = 1 && `fk_porodica` = $fk_porodica  && `fk_djak` = '$fk_djak'  
            order by id desc limit 1
        ";

        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get number of rows
       // $num = $stmt->rowCount();

        // return row count
        return $stmt;

    }

    // public function read_nekoliko($fk_porodica, $fk_djak)
    // {
    //     $fk_porodica = intval($fk_porodica);
    //     $fk_porodica_plus1 = $fk_porodica + 1;
    //     $fk_porodica_plus2 = $fk_porodica + 2;
    //     $fk_porodica_minus1 = $fk_porodica - 1;
    //     if($fk_porodica == 1){
    //         $query = " SELECT * 
    //         FROM " . $this->table_name . "  
    //         WHERE `active` BETWEEN $fk_porodica AND $fk_porodica_plus2 
    //         && `fk_djak` = '$fk_djak'  
    //         order by id asc 
    //         ";
    //     }else{
    //         $query = " SELECT * 
    //         FROM " . $this->table_name . "  
    //         WHERE `active` BETWEEN $fk_porodica_minus1 AND $fk_porodica_plus1 
    //         && `fk_djak` = '$fk_djak'  
    //         order by id asc 
    //         ";

    //     }
    //     // query to select all user records
    //     // $query = " SELECT * 
    //     //     FROM " . $this->table_name . "  
    //     //     WHERE active = $fk_porodica  && `fk_djak` = '$fk_djak'  
    //     //     order by id desc limit 1
    //     // ";

    //     $stmt = $this->conn->prepare($query);

    //     // execute query
    //     $stmt->execute();

    //     // get number of rows
    //    // $num = $stmt->rowCount();

    //     // return row count
    //     return $stmt;

    // }



    // public function read_all_group_students($id_stud){

    //     // query to select all user records


    //     $query = "
    //     SELECT DISTINCT " . $this->table_name . ".*
    //     FROM " . $this->table_name . "
    //     INNER JOIN grupe ON " . $this->table_name . ".fk_porodica = grupe.id
    //     WHERE " . $this->table_name . ".active = TRUE && " . $this->table_name . ".fk_djak = '$id_stud' && grupe.active = TRUE
    //     ";
    //    // $query = "SELECT * FROM " . $this->table_name . "  WHERE active = TRUE && `fk_djak` = '$id_stud'  ";

    //     // prepare query statement
    //     $stmt = $this->conn->prepare( $query );

    //     // execute query
    //     $stmt->execute();

    //     return $stmt;

    // }

   






    

}