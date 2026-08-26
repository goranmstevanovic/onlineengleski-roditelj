<?php
/**
 * Created by PhpStorm.
 * User: goran
 * Date: 6.11.2019
 * Time: 17:05
 */

class zaduzenje
{
    private $conn;
    private $table_name = "zaduzenja";

    public $id;
    public $fk_grupa;
    public $fk_djak;
    public $iznos;
    public $aktivan;
    public $od_datuma;
    public $do_datuma;
    public $created;
    public $zaduzio;
    public $deleted;
    public $obrisao;
    public $komentar;
    public $valuta;

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
                iznos = :iznos,
                aktivan = :aktivan,
                od_datuma = :od_datuma,
                do_datuma = :do_datuma,
                created = :created,
                zaduzio = :zaduzio,
                valuta = :valuta,
                komentar = :komentar
                
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_grupa = htmlspecialchars(strip_tags($this->fk_grupa));
        $this->fk_djak = htmlspecialchars(strip_tags($this->fk_djak));
        $this->aktivan = htmlspecialchars(strip_tags($this->aktivan));
        $this->iznos = htmlspecialchars(strip_tags($this->iznos));
        $this->komentar = htmlspecialchars(strip_tags($this->komentar));


        // bind the values
        $stmt->bindParam(':fk_grupa', $this->fk_grupa);
        $stmt->bindParam(':fk_djak', $this->fk_djak);
        $stmt->bindParam(':iznos', $this->iznos);
        $stmt->bindParam(':aktivan', $this->aktivan);
        $stmt->bindParam(':od_datuma', $this->od_datuma);
        $stmt->bindParam(':do_datuma', $this->do_datuma);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':zaduzio', $this->zaduzio);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':valuta', $this->valuta);

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

    public function count_zaduzenja($id_stud,$id_grupa){

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
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_grupa` = '$id_grup'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_one($id)
    {

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE `id` = $id  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }



    public function read_all_group_students($id_stud,$id_grupa)
    {

        // query to select all user records
  /*   $query = "
     select DISTINCT  " . $this->table_name . ".*  from  " . $this->table_name . " INNER JOIN grupe ON " . $this->table_name . ".fk_grupa = grupe.id WHERE
    " . $this->table_name . ".aktivan = TRUE && " . $this->table_name . ".fk_djak = '$id_stud' && " . $this->table_name . ".fk_grupa = '$id_grupa' && grupe.aktivan = TRUE  
    "; */

        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = '$id_grupa' ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_zaduzenja_for_students($id_stud)
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
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `valuta` >= '$granica' ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_all_zaduzenja_for_students($id_stud)
    {
     
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_zaduzenja_for_student_interval($id_stud, $od, $do)
    {
     
        $query = "SELECT * 
        FROM " . $this->table_name . "  
        WHERE aktivan = TRUE && `fk_djak` = $id_stud 
        AND `valuta` >= '$od' AND  `valuta` <= '$do' 
        ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function sum_all_all_zaduzenja_for_student_month($id_stud, $mesec)
    {
     
        $query = "SELECT SUM(iznos) AS iznos 
        FROM " . $this->table_name . "  
        WHERE aktivan = TRUE AND `fk_djak` = $id_stud  AND valuta LIKE '%$mesec%'
        
        ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_zaduzenja_for_student_group($id_stud, $grupa)
    {
       
        // query to select all user records
        $query = "SELECT * 
        FROM " . $this->table_name . "  
        WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = $grupa ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_zaduzenja_for_student_group_interval($id_stud, $grupa, $od, $do)
    {
       
        // query to select all user records
        $query = "SELECT * 
        FROM " . $this->table_name . "  
        WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = $grupa 
        AND valuta >= '$od' AND valuta <= '$do'
        
        ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_zaduzenja_for_porodica($niz_porodica)
    {

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE 
        && `fk_djak` in ( '" . implode( "', '" , $niz_porodica ) . "' ) ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_iznos_for_student_without($id_stud)
    {
        $danas = date('Y-m-d');

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud'    ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_iznos_for_student_without_do_danas($id_stud)
    {
        $danas = date('Y-m-d');

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud'  &&  `valuta` <= '$danas'   ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

   


    public function read_all_iznos_for_student($id_stud, $id_grupa)
    {

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = '$id_grupa' ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }
    public function read_all_iznos_for_student_do_danas($id_stud, $id_grupa)
    {
        $danas = date("Y-m-d");
        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = '$id_grupa' && `valuta` <= '$danas' ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_iznos_for_student_interval($id_stud, $id_grupa, $datum_od, $datum_do)
    {
        if(strtotime($datum_do) < strtotime($datum_od)){
            $pomocni = $datum_do;
            $datum_do = $datum_od;
            $datum_od = $pomocni;
        }

        // query to select all user records
        $query = "
		SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = '$id_grupa' 
		&& (`valuta`>='$datum_od' && `valuta` <= '$datum_do')
		";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_iznos_for_student_bez_interval($id_stud, $id_grupa)
    {


        // query to select all user records
        $query = "
		SELECT * FROM " . $this->table_name . "  WHERE aktivan = TRUE && `fk_djak` = '$id_stud' && `fk_grupa` = '$id_grupa' 
		
		";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function sum_mesec($operativni_datum)
    {

        // query to select all user records
        $query = "
        SELECT SUM(iznos) AS suma 
        FROM " . $this->table_name . " 
        WHERE DATE_FORMAT(valuta, '%Y-%m') = '$operativni_datum' && aktivan = 1
        ";
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function sum_mesec_od_sad($operativni_datum)
    {

        // query to select all user records
        $query = "
        SELECT SUM(iznos) AS suma 
        FROM " . $this->table_name . " 
        WHERE DATE_FORMAT(valuta, '%Y-%m') = '$operativni_datum' && aktivan = 1 && valuta >= CURDATE()
        ";
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

        public function sve_mesec($operativni_datum)
    {

        // query to select all user records
        $query = "
        SELECT z.* , dj.firstname AS djak_ime, dj.lastname AS djak_prezime, g.alias AS alias
        FROM " . $this->table_name . " z
        INNER JOIN djaci dj ON dj.id = z.fk_djak
        INNER JOIN grupe g ON g.id = z.fk_grupa
        WHERE DATE_FORMAT(z.valuta, '%Y-%m') = '$operativni_datum' && z.aktivan = 1
        order by z.valuta ASC
        ";
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

        public function sve_mesec_od_sad($operativni_datum)
    {

        // query to select all user records
        $query = "
        SELECT z.* , dj.firstname AS djak_ime, dj.lastname AS djak_prezime, g.alias AS alias
        FROM " . $this->table_name . " z
        INNER JOIN djaci dj ON dj.id = z.fk_djak
        INNER JOIN grupe g ON g.id = z.fk_grupa
        WHERE DATE_FORMAT(z.valuta, '%Y-%m') = '$operativni_datum' && z.aktivan = 1 && z.valuta >= CURDATE()
        order by z.valuta ASC
        ";
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_iznos_for_student_interval_mesec($stud, $grupa, $mesecc)
    {


        // query to select all user records
        $query = "
		SELECT * FROM " . $this->table_name . "  WHERE aktivan = 1 && `fk_djak` = '$stud' && `fk_grupa` = '$grupa' 
		&& `od_datuma` like '%$mesecc%'  
		";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    public function read_all_iznos_for_student_interval_mesec_prof($id_stud, $id_grupa, $mesec, $profesor)
    {

        $query = "
        SELECT DISTINCT " . $this->table_name . ".* FROM  " . $this->table_name . "
        INNER JOIN grupe  ON  " . $this->table_name . ".fk_grupa = grupe.id
        WHERE  
         " . $this->table_name . ".aktivan = TRUE &&  
         " . $this->table_name . ".od_datuma LIKE '%$mesec%' 
         && " . $this->table_name . ".fk_grupa = '$id_grupa'  
            && grupe.fk_profesor = '$profesor'     
         && " . $this->table_name . ".fk_djak = '$id_stud' 
          ";


        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }


    public function count_grupa($id_student){

        // query to select all user records
        $query = "SELECT id FROM " . $this->table_name . "  WHERE status = TRUE && `fk_djak` = '$id_student'  ";

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get number of rows
        $num = $stmt->rowCount();

        // return row count
        return $num;
    }

    function read_all_zaduzenja_month_profesor($mesec,$profesor,$grupa){


        // query to select all user records
        $query = "SELECT DISTINCT " . $this->table_name . ".* FROM " . $this->table_name . "
        INNER JOIN `grupe` ON " . $this->table_name . ".fk_grupa = grupe.id
        WHERE " . $this->table_name . ".od_datuma LIKE '%$mesec%' && " . $this->table_name . ".aktivan = TRUE  && 
         grupe.fk_profesor = '$profesor' 
        ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_zaduzenja_month_profesor1($mesec,$profesor){


        // query to select all user records
        $query = "SELECT DISTINCT " . $this->table_name . ".* FROM " . $this->table_name . "
        INNER JOIN `grupe` ON " . $this->table_name . ".fk_grupa = grupe.id
        WHERE " . $this->table_name . ".od_datuma LIKE '%$mesec%' && " . $this->table_name . ".aktivan = TRUE  && 
         grupe.fk_profesor = '$profesor' 
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
            fk_grupa = :fk_grupa,
                fk_djak = :fk_djak,
                iznos = :iznos,
                zaduzio = :zaduzio,
                valuta = :valuta,
                komentar = :komentar
            
                
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        $this->fk_djak = htmlspecialchars(strip_tags($this->fk_djak));
        
        $this->iznos = htmlspecialchars(strip_tags($this->iznos));
        $this->komentar = htmlspecialchars(strip_tags($this->komentar));
        

         // bind the values
        $stmt->bindParam(':fk_djak', $this->fk_djak);
        $stmt->bindParam(':fk_grupa', $this->fk_grupa);
        $stmt->bindParam(':iznos', $this->iznos);
        $stmt->bindParam(':zaduzio', $this->zaduzio);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':valuta', $this->valuta);
        
        
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