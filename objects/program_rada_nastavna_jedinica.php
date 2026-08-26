<?php
/**
 * created by PhpStorm.
 * User: goran
 * Date: 6.11.2019
 * Time: 17:05
 */

class program_rada_nastavna_jedinica
{
    private $conn;
    private $table_name = "programi_rada_nastavne_jedinice";

    public $id;
    public $nastavna_jedinica;
    public $fk_program_rada;
    public $redni_br;
    public $created;
    
    public $active;

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
                nastavna_jedinica = :nastavna_jedinica,
                fk_program_rada = :fk_program_rada,
                redni_br = :redni_br,
                created = :created,
                active = :active
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->nastavna_jedinica = htmlspecialchars(strip_tags($this->nastavna_jedinica));
        $this->fk_program_rada = htmlspecialchars(strip_tags($this->fk_program_rada));
        $this->redni_br = htmlspecialchars(strip_tags($this->redni_br));


        // bind the values
        $stmt->bindParam(':nastavna_jedinica', $this->nastavna_jedinica);
        $stmt->bindParam(':fk_program_rada', $this->fk_program_rada);
        $stmt->bindParam(':redni_br', $this->redni_br);
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
                nastavna_jedinica = :nastavna_jedinica,
                fk_program_rada = :fk_program_rada,
                redni_br = :redni_br,
                created = :created,
                active = :active
                WHERE id = :idd   
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->nastavna_jedinica = htmlspecialchars(strip_tags($this->nastavna_jedinica));
        $this->fk_program_rada = htmlspecialchars(strip_tags($this->fk_program_rada));
        $this->redni_br = htmlspecialchars(strip_tags($this->redni_br));


        // bind the values
        $stmt->bindParam(':nastavna_jedinica', $this->nastavna_jedinica);
        $stmt->bindParam(':fk_program_rada', $this->fk_program_rada);
        $stmt->bindParam(':redni_br', $this->redni_br);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':idd', $idd);

        // execute the query, also check if query was successful
        if ($stmt->execute()) {
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

   
    
    public function read_all_for_one($fk_program_rada)
    {

        $query = " SELECT * 
        FROM " . $this->table_name . "  
        WHERE  `fk_program_rada` = '$fk_program_rada' and active = 1  
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

    public function read_one($redni_broj, $fk_program_rada)
    {

        // query to select all user records
        $query = " SELECT * 
            FROM " . $this->table_name . "  
            WHERE redni_br = $redni_broj  && `fk_program_rada` = '$fk_program_rada' && active = 1  
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

    public function exists_active($fk_program_rada, $redni_br){
        $query = "SELECT id FROM " . $this->table_name . " 
                WHERE fk_program_rada = :fk_program_rada 
                AND redni_br = :redni_br 
                AND active = 1
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_program_rada', $fk_program_rada);
        $stmt->bindParam(':redni_br', $redni_br);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function read_nekoliko($redni_broj, $fk_program_rada)
    {
        $redni_broj = intval($redni_broj);
        if ($redni_broj == 1){
                $redni_broj = 2;
        }
        $redni_broj_plus1 = $redni_broj + 1;
        $redni_broj_plus2 = $redni_broj + 2;
        $redni_broj_minus1 = $redni_broj - 1;
        if($redni_broj == 1){
            $query = " SELECT * 
            FROM " . $this->table_name . "  
            WHERE `redni_br` BETWEEN $redni_broj AND $redni_broj_plus2 
            && `fk_program_rada` = '$fk_program_rada'  
            order by id asc 
            ";
        }else{
            $query = " SELECT * 
            FROM " . $this->table_name . "  
            WHERE `redni_br` BETWEEN $redni_broj_minus1 AND $redni_broj_plus1 
            && `fk_program_rada` = '$fk_program_rada'  
            order by id asc 
            ";

        }
        // query to select all user records
        // $query = " SELECT * 
        //     FROM " . $this->table_name . "  
        //     WHERE redni_br = $redni_broj  && `fk_program_rada` = '$fk_program_rada'  
        //     order by id desc limit 1
        // ";

        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get number of rows
       // $num = $stmt->rowCount();

        // return row count
        return $stmt;

    }

    public function read_nekoliko_do_kraja($redni_broj, $fk_program_rada)
    {
        $redni_broj = intval($redni_broj);
      
            $query = " SELECT * 
            FROM " . $this->table_name . "  
            WHERE `redni_br` > $redni_broj && `fk_program_rada` = '$fk_program_rada'  
            order by id asc 
            ";
       
        // query to select all user records
        // $query = " SELECT * 
        //     FROM " . $this->table_name . "  
        //     WHERE redni_br = $redni_broj  && `fk_program_rada` = '$fk_program_rada'  
        //     order by id desc limit 1
        // ";

        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get number of rows
       // $num = $stmt->rowCount();

        // return row count
        return $stmt;

    }



    public function read_all_group_students($id_stud){

        // query to select all user records


        $query = "
        SELECT DISTINCT " . $this->table_name . ".*
        FROM " . $this->table_name . "
        INNER JOIN grupe ON " . $this->table_name . ".nastavna_jedinica = grupe.id
        WHERE " . $this->table_name . ".redni_br = TRUE && " . $this->table_name . ".fk_program_rada = '$id_stud' && grupe.redni_br = TRUE
        ";
       // $query = "SELECT * FROM " . $this->table_name . "  WHERE redni_br = TRUE && `fk_program_rada` = '$id_stud'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

   






    

}