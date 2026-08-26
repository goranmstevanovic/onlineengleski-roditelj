<?php
/**
 * Created by PhpStorm.
 * User: goran
 * Date: 6.11.2019
 * Time: 17:05
 */

class povezivanje
{
    private $conn;
    private $table_name = "povezivanje";

    public $id;
    public $fk_grupa;
    public $fk_djak;
    public $status;
    public $created;
    public $deleted;
    public $obrisao;

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
                fk_grupa = :fk_grupa,
                fk_djak = :fk_djak,
                status = :status,
                created = :created";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_grupa = htmlspecialchars(strip_tags($this->fk_grupa));
        $this->fk_djak = htmlspecialchars(strip_tags($this->fk_djak));
        $this->status = htmlspecialchars(strip_tags($this->status));


        // bind the values
        $stmt->bindParam(':fk_grupa', $this->fk_grupa);
        $stmt->bindParam(':fk_djak', $this->fk_djak);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':created', $this->created);

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

    public function count_student($id_grup){

        // query to select all user records
      //  $query = "SELECT id FROM " . $this->table_name . "  WHERE status = TRUE && `fk_grupa` = '$id_grup'  ";
        $query = "select DISTINCT " . $this->table_name . ".*  from  " . $this->table_name . " INNER JOIN djaci ON " . $this->table_name . ".fk_djak = djaci.id where
        " . $this->table_name . ".status = TRUE && " . $this->table_name . ".fk_grupa = '$id_grup' && djaci.status = TRUE ";

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get number of rows
        $num = $stmt->rowCount();

        // return row count
        return $num;
    }

    function delete($idd_djak, $id_grupa){

        // to get time stamp for 'created' field
        $this->deleted=date('Y-m-d H:i:s');

        // insert query
        $query = "UPDATE
                " . $this->table_name . "
            SET
                status = :status,
                deleted = :deleted
            WHERE fk_grupa = :id_grupa && fk_djak = :idd_djak   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize


        $this->status=htmlspecialchars(strip_tags($this->status));
        $this->deleted=htmlspecialchars(strip_tags($this->deleted));


        // bind the values

        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':deleted', $this->deleted);
        $stmt->bindParam(':id_grupa', $id_grupa);
        $stmt->bindParam(':idd_djak', $idd_djak);
        // hash the password before saving to database

        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            $this->showError($stmt);
            return false;
        }

    }

    public function read_all_students($id_grup)
    {

        // query to select all user records
        // $query = "SELECT * FROM " . $this->table_name . "  WHERE status = TRUE && `fk_grupa` = '$id_grup'  ";
        $query = "SELECT povezivanje.* FROM 
        povezivanje  
        INNER JOIN djaci ON djaci.id = povezivanje.fk_djak
        WHERE povezivanje.status = TRUE && povezivanje.fk_grupa = '$id_grup' && djaci.status = TRUE  ";


        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_students_prevod($id_grup)
    {


        // query to select all user records
        $query = "SELECT DISTINCT " . $this->table_name . ".*
        FROM " . $this->table_name . " 
        INNER JOIN djaci ON ". $this->table_name . ".fk_djak = djaci.id
        WHERE " . $this->table_name . ".status = TRUE && " . $this->table_name . ".fk_grupa = '$id_grup' && djaci.prevod = TRUE  
        ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_one_student_one_grup($id_grup, $id_djak)
    {

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE /* status = TRUE && */ `fk_grupa` = '$id_grup' && `fk_djak` = '$id_djak'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function count_one_student_one_grup($id_grup, $id_djak)
    {

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE status = TRUE && `fk_grupa` = '$id_grup' && `fk_djak` = '$id_djak'  ";

        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get number of rows
        $num = $stmt->rowCount();

        // return row count
        return $num;

    }



    public function read_all_group_students($id_stud){

        // query to select all user records


        $query = "
        SELECT DISTINCT pov.* , grupe.alias AS alias_grupe, u.ime AS uzrast ,
         j.alias AS jezik_ime, grupe.id AS id_grupe , nz.ime AS ime_nivoa, us.color_prof AS color_prof
        FROM " . $this->table_name . " pov
        INNER JOIN grupe ON pov.fk_grupa = grupe.id
        LEFT JOIN uzrasti u ON u.id = grupe.uzrast
        LEFT JOIN jezik j ON j.id = grupe.fk_jezik
        LEFT JOIN nivo_znanja nz ON  nz.id = grupe.nivo
        INNER JOIN users us ON us.id = grupe.fk_profesor
              
        WHERE  pov.status = TRUE  &&  pov.fk_djak = '$id_stud' && grupe.status = TRUE
        ";
       // $query = "SELECT * FROM " . $this->table_name . "  WHERE status = TRUE && `fk_djak` = '$id_stud'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_group_students_po_kursu($id_stud){

        // query to select all user records


        $query = "
        SELECT DISTINCT pov.* , grupe.alias AS alias_grupe, u.ime AS uzrast ,
         j.ime AS jezik_ime
        FROM " . $this->table_name . " pov
        INNER JOIN grupe ON pov.fk_grupa = grupe.id
        LEFT JOIN uzrasti u ON u.id = grupe.uzrast
        LEFT JOIN jezik j ON j.id = grupe.fk_jezik
       
        WHERE  pov.status = TRUE  &&  pov.fk_djak = '$id_stud' && grupe.status = TRUE  AND grupe.fk_nacin_zaduzivanja = 1
        ";
       // $query = "SELECT * FROM " . $this->table_name . "  WHERE status = TRUE && `fk_djak` = '$id_stud'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_group_students_po_kursu_admin($id_stud){

        // query to select all user records


        $query = "
        SELECT pov.* , grupe.alias AS alias_grupe, u.ime AS uzrast ,
         j.ime AS jezik_ime, grupe.id AS id_grupe, nz.ime AS ime_nivoa, us.firstname AS ime_profesora
        FROM " . $this->table_name . " pov
        INNER JOIN grupe ON pov.fk_grupa = grupe.id
        LEFT JOIN uzrasti u ON u.id = grupe.uzrast
        LEFT JOIN jezik j ON j.id = grupe.fk_jezik
        LEFT JOIN  nivo_znanja nz ON nz.id = grupe.nivo
        INNER JOIN users us ON us.id = grupe.fk_profesor
        WHERE  pov.status = TRUE  &&  pov.fk_djak = $id_stud 
        GROUP BY grupe.id
        /* && grupe.status = TRUE   AND grupe.fk_nacin_zaduzivanja = 1 */
        ";
       // $query = "SELECT * FROM " . $this->table_name . "  WHERE status = TRUE && `fk_djak` = '$id_stud'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_group_students_za_finasijsku($id_stud){

        // query to select all user records


        $query = "
        SELECT  pov.* , grupe.alias AS alias_grupe, u.ime AS uzrast , j.ime AS jezik_ime
        FROM " . $this->table_name . " pov
        LEFT JOIN grupe ON pov.fk_grupa = grupe.id
        LEFT JOIN uzrasti u ON u.id = grupe.uzrast
        LEFT JOIN jezik j ON j.id = grupe.fk_jezik
        LEFT JOIN kalendar k ON k.fk_grupa = grupe.id
        WHERE  pov.fk_djak = $id_stud
        GROUP by grupe.id 
     
        ";
       // $query = "SELECT * FROM " . $this->table_name . "  WHERE status = TRUE && `fk_djak` = '$id_stud'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_group_students_fetch($id_stud){

        // query to select all user records


        $query = " SELECT  pov.* , grupe.alias AS alias_grupe, u.ime AS uzrast ,  us.color_prof AS color_prof,
         j.alias AS jezik_ime
        FROM " . $this->table_name . " pov
        INNER JOIN grupe ON pov.fk_grupa = grupe.id
        LEFT JOIN uzrasti u ON u.id = grupe.uzrast
        LEFT JOIN jezik j ON j.id = grupe.fk_jezik
        LEFT JOIN users us ON us.id = grupe.fk_profesor
       
        WHERE pov.status = TRUE && pov.fk_djak = '$id_stud' && grupe.status = 1
        ";
       // $query = "SELECT * FROM " . $this->table_name . "  WHERE status = TRUE && `fk_djak` = '$id_stud'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_allall_group_students($id_stud){

        // query to select all user records


        $query = "
        SELECT DISTINCT " . $this->table_name . ".*
        FROM " . $this->table_name . "
        INNER JOIN grupe ON " . $this->table_name . ".fk_grupa = grupe.id
        WHERE /* " . $this->table_name . ".status = TRUE   &&  */  " . $this->table_name . ".fk_djak = '$id_stud' 
        ORDER BY  " . $this->table_name . ".status DESC
        ";
        // $query = "SELECT * FROM " . $this->table_name . "  WHERE status = TRUE && `fk_djak` = '$id_stud'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }






    public function count_grupa($id_student){

        // query to select all user records

        $query = "
        SELECT DISTINCT p.*
        FROM " . $this->table_name . " p
        INNER JOIN grupe g ON p.fk_grupa = g.id
        WHERE /* p.status = TRUE && */ p.fk_djak = '$id_student' /* && g.status = TRUE */
        ";


        //  $query = "SELECT id FROM " . $this->table_name . "  WHERE status = TRUE && `fk_djak` = '$id_student'  ";

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get number of rows
        $num = $stmt->rowCount();

        // return row count
        return $num;
    }

    
    public function count_grupa_po_kursu($id_student){

        // query to select all user records
        $query = "  SELECT DISTINCT p.*
        FROM " . $this->table_name . " p
        INNER JOIN grupe g ON p.fk_grupa = g.id
        WHERE p.status = TRUE && p.fk_djak = '$id_student' 
        && g.status = TRUE  AND g.fk_nacin_zaduzivanja = 1 
        ";
         // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get number of rows
        $num = $stmt->rowCount();

        // return row count
        return $num;
    }

    public function count_grupa_po_kursu_admin($id_student){

        // query to select all user records
        $query = "  SELECT DISTINCT p.*
        FROM " . $this->table_name . " p
        INNER JOIN grupe g ON p.fk_grupa = g.id
        WHERE p.status = TRUE && p.fk_djak = '$id_student' 
        && g.status = TRUE /* AND g.fk_nacin_zaduzivanja = 1  */
        ";
         // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get number of rows
        $num = $stmt->rowCount();

        // return row count
        return $num;
    }


    public function read_all_profesori_za_djaka($id_stud){

        $query = "
        SELECT DISTINCT us.id AS id_profesora, us.firstname, us.lastname, us.color_prof,
            GROUP_CONCAT(DISTINCT grupe.alias ORDER BY grupe.alias SEPARATOR ', ') AS grupe_alias,
            GROUP_CONCAT(DISTINCT j.alias ORDER BY j.alias SEPARATOR ', ') AS jezici
        FROM " . $this->table_name . " pov
        INNER JOIN grupe ON pov.fk_grupa = grupe.id
        INNER JOIN users us ON us.id = grupe.fk_profesor
        LEFT JOIN jezik j ON j.id = grupe.fk_jezik
        WHERE pov.status = TRUE AND pov.fk_djak = :id_stud AND grupe.status = TRUE AND us.status = TRUE 
        GROUP BY us.id
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_stud', $id_stud);
        $stmt->execute();

        return $stmt;
    }

    public function count_grupa_po_casu_za_karticu($id_student){

        // query to select all user records
        $query = "  SELECT DISTINCT p.*
        FROM " . $this->table_name . " p
        INNER JOIN grupe g ON p.fk_grupa = g.id
        WHERE p.fk_djak = '$id_student' 
         AND g.fk_nacin_zaduzivanja = 2
        ";
         // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get number of rows
        $num = $stmt->rowCount();

        // return row count
        return $num;
    }

}