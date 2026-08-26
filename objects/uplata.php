<?php
/**
 * Created by PhpStorm.
 * User: goran
 * Date: 15/11/2019
 * Time: 6:36 PM
 */

class uplata
{
    private $conn;
    private $table_name = "uplate";

    public $id;
    public $fk_djak;
    public $fk_grupa;
    public $iznos;
    public $aktivan;
    public $datum_generisanja;
    public $created;
    public $upisao;
    public $komentar;
    public $racun;
    public $broj_priznanice;


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
                fk_djak = :fk_djak,
                fk_grupa = :fk_grupa,
                iznos = :iznos,
                aktivan = :aktivan,
                datum_generisanja = :datum_generisanja,
                racun = :racun,
                created = :created,
                upisao = :upisao,
                broj_priznanice = :broj_priznanice,
                komentar = :komentar
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize

        $this->fk_djak = htmlspecialchars(strip_tags($this->fk_djak));
        $this->aktivan = htmlspecialchars(strip_tags($this->aktivan));
        $this->iznos = htmlspecialchars(strip_tags($this->iznos));
        $this->komentar = htmlspecialchars(strip_tags($this->komentar));
        $this->broj_priznanice = htmlspecialchars(strip_tags($this->broj_priznanice));



        // bind the values
        $stmt->bindParam(':fk_djak', $this->fk_djak);
        $stmt->bindParam(':fk_grupa', $this->fk_grupa);
        $stmt->bindParam(':iznos', $this->iznos);
        $stmt->bindParam(':aktivan', $this->aktivan);
        $stmt->bindParam(':datum_generisanja', $this->datum_generisanja);
        $stmt->bindParam(':racun', $this->racun);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':upisao', $this->upisao);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':broj_priznanice', $this->broj_priznanice);

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

    public function count_uplata($id_stud, $id_grupa){

        // query to select all user records
        $query = "SELECT id FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = '$id_grupa'   ";


        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get number of rows
        $num = $stmt->rowCount();

        // return row count
        return $num;
    }
    public function read_all_uplate_students($id_stud, $id_grupa)
    {

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = ' $id_grupa'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    

    function read_all_uplate_na_dan($datum, $id_profesor)
    {

        // query to select all user records
        $query = "SELECT DISTINCT " . $this->table_name . ".* FROM " . $this->table_name . " 
        INNER JOIN grupe ON  " . $this->table_name . ".fk_grupa = grupe.id             
        WHERE " . $this->table_name . ".aktivan = TRUE && " . $this->table_name . ".datum_generisanja = '$datum' && grupe.fk_profesor = '$id_profesor'  
        ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();


        return $stmt;

    }

    function sum_all_uplate_na_dan_profesor($datum, $sve_grupe)
    {

        // query to select all user records
        $query = "SELECT sum(u.iznos) AS iznos
        FROM " . $this->table_name . " u 
        INNER JOIN grupe g ON  u.fk_grupa = g.id             
        WHERE u.aktivan = TRUE && u.datum_generisanja LIKE '%$datum%' 
        && u.fk_grupa IN (".implode(',', array_fill(0, count($sve_grupe), '?')).")  
        ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );
        foreach ($sve_grupe as $key => $value) {
            $stmt->bindValue(($key+1), $value, PDO::PARAM_INT);
        }
        // execute query
        $stmt->execute();


        return $stmt;

    }

    
    function sum_all_uplate_na_dan_profesor_prof($datum, $sve_grupe, $prof)
    {

        // query to select all user records
        $query = "SELECT sum(u.iznos) AS iznos
        FROM " . $this->table_name . " u 
        INNER JOIN grupe g ON  u.fk_grupa = g.id             
        WHERE u.aktivan = TRUE && u.datum_generisanja LIKE '%$datum%' 
        && u.fk_grupa IN (".implode(',', array_fill(0, count($sve_grupe), '?')).") AND g.fk_profesor = $prof 
        ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );
        foreach ($sve_grupe as $key => $value) {
            $stmt->bindValue(($key+1), $value, PDO::PARAM_INT);
        }
        // execute query
        $stmt->execute();


        return $stmt;

    }

    public function read_one($idd)
    {

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE id = $idd  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }


    public function read_all_uplate_students1($id_stud)
    {

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_uplate_porodica($niz_porodica)
    {

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE 
        && `fk_djak` in ( '" . implode( "', '" , $niz_porodica ) . "' )  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_for_student($id_stud, $id_grupa)
    {

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = '$id_grupa' ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_for_student_do_danas($id_stud, $id_grupa)
    {
        $danas = date('Y-m-d');
        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = '$id_grupa' && `datum_generisanja` <= '$danas' ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }


    public function read_all_for_student_without($id_stud)
    {   
        $danas = date("Y-m-d");
        $ova_godina = date("Y");  // date("d-m-Y", strtotime($originalDate));
        $prosla_godina = date("Y",strtotime("-1 year"));
        if($danas < date("Y-m-d", strtotime($ova_godina.'-09-01'))){
            $granica = date("Y-m-d", strtotime($prosla_godina."-09-01"));
        }else{
            $granica = date("Y-m-d", strtotime($ova_godina."-09-01"));
        }

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `datum_generisanja` >= '$granica' ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_all_for_student_without($id_stud)
    {   
        // $danas = date("Y-m-d");
        // $ova_godina = date("Y");  // date("d-m-Y", strtotime($originalDate));
        // $prosla_godina = date("Y",strtotime("-1 year"));
        // if($danas < date("Y-m-d", strtotime($ova_godina.'-09-01'))){
        //     $granica = date("Y-m-d", strtotime($prosla_godina."-09-01"));
        // }else{
        //     $granica = date("Y-m-d", strtotime($ova_godina."-09-01"));
        // }

        // query to select all user records
        $query = "SELECT * FROM uplate  WHERE aktivan = TRUE && `fk_djak` = $id_stud  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }
	
	public function read_all_for_student_interval($id_stud, $id_grupa, $datum_od, $datum_do)
    {
        if(strtotime($datum_do) < strtotime($datum_od)){
            $pomocni = $datum_do;
            $datum_do = $datum_od;
            $datum_od = $pomocni;
        }

        // query to select all user records
        $query = "
		SELECT * FROM " . $this->table_name . "  
		WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = '$id_grupa'
		&& (`datum_generisanja` >= '$datum_od' && `datum_generisanja` <= '$datum_do') 
		";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_for_student_bez_interval($id_stud, $id_grupa)
    {

        // query to select all user records
        $query = "
		SELECT * FROM " . $this->table_name . "  
		WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = '$id_grupa'
		";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }


    public function read_all_for_student_interval_mesec($id_stud, $id_grupa, $mesec)
    {

        // query to select all user records
        $query = "
		SELECT * FROM " . $this->table_name . "  
		WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = '$id_grupa' && `datum_generisanja` like '%$mesec%' 
		";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function sum_all_for_student_interval_mesec($id_stud, $id_grupa, $mesec)
    {

        // query to select all user records
        $query = "SELECT SUM(iznos) AS suma_iznosa 
        FROM " . $this->table_name . "  
		WHERE aktivan = TRUE && `fk_djak` = '$id_stud' 
        && `fk_grupa` = '$id_grupa' && `datum_generisanja` like '%$mesec%' 
		";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function sum_all_for_student_prof_interval_mesec($id_stud, $id_grupa, $mesec, $prof)
    {

        // query to select all user records
        $query = "SELECT SUM(u.iznos) AS suma_iznosa 
        FROM " . $this->table_name . " u
        INNER JOIN grupe g ON g.id = u.fk_grupa 
		WHERE u.aktivan = TRUE && u.fk_djak = $id_stud && g.fk_profesor = $prof
        && u.fk_grupa = $id_grupa && u.datum_generisanja like '%$mesec%' 
		";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_for_student_interval_mesec_prof($id_stud, $id_grupa, $mesec, $profesor)
    {

        $query = "
        SELECT DISTINCT " . $this->table_name . ".* FROM  " . $this->table_name . "
        INNER JOIN grupe  ON  " . $this->table_name . ".fk_grupa = grupe.id
        WHERE " . $this->table_name . ".aktivan = TRUE && " . $this->table_name . ".fk_grupa = '$id_grupa'  && " . $this->table_name . ".fk_djak = '$id_stud' && " . $this->table_name . ".datum_generisanja LIKE '%$mesec%' && grupe.fk_profesor = '$profesor'   
        ";


        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }


    function read_all_uplate_month_profesor($mesec,$profesor ){


        // query to select all user records
        $query = "SELECT DISTINCT " . $this->table_name . ".* FROM  " . $this->table_name . "
        INNER JOIN grupe  ON  " . $this->table_name . ".fk_grupa = grupe.id
        WHERE " . $this->table_name . ".aktivan = TRUE &&  " . $this->table_name . ".datum_generisanja LIKE '%$mesec%' && grupe.fk_profesor = '$profesor'   
            
        ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_uplate_month_profesor1($mesec,$profesor ){

        $mesec_ceo = $mesec."-15";
        // query to select all user records
        $query = "SELECT DISTINCT " . $this->table_name . ".* FROM  " . $this->table_name . "
        INNER JOIN grupe  ON  " . $this->table_name . ".fk_grupa = grupe.id
        WHERE " . $this->table_name . ".aktivan = TRUE &&  " . $this->table_name . ".datum_generisanja LIKE '%$mesec%' && date(" . $this->table_name . ".datum_generisanja) <= '$mesec_ceo'   && grupe.fk_profesor = '$profesor'   
            
        ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_uplate_month_profesor2($mesec,$profesor ){

        $mesec_ceo = $mesec."-15";
        // query to select all user records
        $query = "SELECT DISTINCT " . $this->table_name . ".* FROM  " . $this->table_name . "
        INNER JOIN grupe  ON  " . $this->table_name . ".fk_grupa = grupe.id
        WHERE " . $this->table_name . ".aktivan = TRUE &&  " . $this->table_name . ".datum_generisanja LIKE '%$mesec%' && date(" . $this->table_name . ".datum_generisanja) > '$mesec_ceo'   && grupe.fk_profesor = '$profesor'   
            
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
       $this->created = date('Y-m-d H:i:s');
                // update query
        $query = "UPDATE
                " . $this->table_name . "
            SET
            fk_djak = :fk_djak,
                fk_grupa = :fk_grupa,
                iznos = :iznos,
                aktivan = :aktivan,
                datum_generisanja = :datum_generisanja,
                racun = :racun,
                created = :created,
                upisao = :upisao,
                broj_priznanice = :broj_priznanice,
                komentar = :komentar
            
                
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        $this->fk_djak = htmlspecialchars(strip_tags($this->fk_djak));
        $this->aktivan = htmlspecialchars(strip_tags($this->aktivan));
        $this->iznos = htmlspecialchars(strip_tags($this->iznos));
        $this->komentar = htmlspecialchars(strip_tags($this->komentar));
        $this->broj_priznanice = htmlspecialchars(strip_tags($this->broj_priznanice));

         // bind the values
         $stmt->bindParam(':fk_djak', $this->fk_djak);
        $stmt->bindParam(':fk_grupa', $this->fk_grupa);
        $stmt->bindParam(':iznos', $this->iznos);
        $stmt->bindParam(':aktivan', $this->aktivan);
        $stmt->bindParam(':datum_generisanja', $this->datum_generisanja);
        $stmt->bindParam(':racun', $this->racun);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':upisao', $this->upisao);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':broj_priznanice', $this->broj_priznanice);
        
        
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