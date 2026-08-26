<?php
/**
 * Created by PhpStorm.
 * User: goran
 * Date: 11 Feb 2020
 * Time: 11:42
 */

class evidencija
{
    private $conn;
    private $table_name = "evidencija";

    public $id;
    public $djak_id;
    public $prisutan;
    public $evidentirao;
    public $termin_id;
    public $evidentirano;
    public $komentar;
    public $opravdao_otsustvo;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    function upisi($djak_id,$termin_id)
    {

        // to get time stamp for 'created' field
        $this->evidentirano = date('Y-m-d H:i:s');

        // insert query
        $query_provera = "SELECT id FROM " . $this->table_name . "
        WHERE `djak_id` = '$djak_id' && `termin_id` = '$termin_id'
        ";

        // prepare query statement
        $stmt_provera = $this->conn->prepare($query_provera);

        // execute query
        $stmt_provera->execute();

        // get number of rows
        $num = $stmt_provera->rowCount();

        if ($num == 0) {
            $query = "
                INSERT INTO
                " . $this->table_name . "
                SET
                djak_id = :djak_id,
                prisutan = :prisutan,
                opravdao_otsustvo = :opravdao,
                evidentirao = :evidentirao,
                termin_id = :termin_id,
                komentar = :komentar,
                evidentirano = :evidentirano
                ";

            // prepare the query
            $stmt = $this->conn->prepare($query);

            // sanitize
            $this->djak_id = htmlspecialchars(strip_tags($this->djak_id));
            $this->evidentirao = htmlspecialchars(strip_tags($this->evidentirao));
            $this->termin_id = htmlspecialchars(strip_tags($this->termin_id));
         //   $this->komentar = htmlspecialchars(strip_tags($this->komentar));


            // bind the values
            $opravdao = 0; // assuming this is the value you want to set
            $stmt->bindParam(':opravdao', $opravdao);
            $stmt->bindParam(':djak_id', $this->djak_id);
            $stmt->bindParam(':prisutan', $this->prisutan);
            $stmt->bindParam(':evidentirao', $this->evidentirao);
            $stmt->bindParam(':termin_id', $this->termin_id);
            $stmt->bindParam(':komentar', $this->komentar);
            $stmt->bindParam(':evidentirano', $this->evidentirano);

            // execute the query, also check if query was successful
            if ($stmt->execute()) {
                return true;
            } else {
                $this->showError($stmt);
                return false;
            }

        } else {
            // ako postoji samo se updatuje
            $query = "
                UPDATE
                " . $this->table_name . "
                SET
                djak_id = :djak_id,
                prisutan = :prisutan,
                `opravdao_otsustvo` = :opravdao,
                evidentirao = :evidentirao,
                termin_id = :termin_id,
                komentar = :komentar,
                evidentirano = :evidentirano
                
                WHERE `djak_id` = '$djak_id' && `termin_id` = '$termin_id'
                
                ";

            // prepare the query
            $stmt = $this->conn->prepare($query);

            // sanitize
            $this->djak_id = htmlspecialchars(strip_tags($this->djak_id));
            $this->evidentirao = htmlspecialchars(strip_tags($this->evidentirao));
            $this->termin_id = htmlspecialchars(strip_tags($this->termin_id));
           // $this->komentar = htmlspecialchars(strip_tags($this->komentar));


            // bind the values
            $opravdao = 0; // assuming this is the value you want to set
            $stmt->bindParam(':opravdao', $opravdao);
            $stmt->bindParam(':djak_id', $this->djak_id);
            $stmt->bindParam(':prisutan', $this->prisutan);
            $stmt->bindParam(':evidentirao', $this->evidentirao);
            $stmt->bindParam(':termin_id', $this->termin_id);
            $stmt->bindParam(':evidentirano', $this->evidentirano);
            $stmt->bindParam(':komentar', $this->komentar);

            // execute the query, also check if query was successful
            if ($stmt->execute()) {
                return true;
            } else {
                $this->showError($stmt);
                return false;
            }


        }
    }

     public function read_prisustva_po_terminima($termin_ids) {
        $in_query = implode(',', array_fill(0, count($termin_ids), '?'));
        $query = "SELECT * FROM evidencija WHERE termin_id IN ($in_query)";
        $stmt = $this->conn->prepare($query);
        foreach ($termin_ids as $k => $id) {
            $stmt->bindValue(($k+1), $id, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt;
    }

    function update_opravdanje_dolaska($djak_id, $termin_id){
        $query = "
        UPDATE
        " . $this->table_name . "
        SET
        `opravdao_otsustvo` = :opravdao
        WHERE `djak_id` = :djak_id AND `termin_id` = :termin_id";
    
        // prepare the query
        $stmt = $this->conn->prepare($query);
    
        // bind parameters
        $opravdao = 1; // assuming this is the value you want to set
        $stmt->bindParam(':opravdao', $opravdao);
        $stmt->bindParam(':djak_id', $djak_id);
        $stmt->bindParam(':termin_id', $termin_id);
    
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

    function read_one_prisustvo($djak_id,$termin_id)
    {

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "   WHERE `djak_id` = '$djak_id' && `termin_id` = '$termin_id'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    function read_all_prisustvo_djak($djak_id)
    {
       $danas = date("Y-m-d");
        $ova_godina = date("Y");  // date("d-m-Y", strtotime($originalDate));
        $prosla_godina = date("Y",strtotime("-1 year"));
        if($danas < date("Y-m-d", strtotime($ova_godina.'-09-01'))){
            $granica = date("Y-m-d", strtotime($prosla_godina."-09-01"));
        }else{
            $granica = date("Y-m-d", strtotime($ova_godina."-09-01"));
        }
       //echo $granica ;

        // query to select all user records
        $query = " 
          SELECT DISTINCT " . $this->table_name . ".* 
          FROM " . $this->table_name . "  
          LEFT JOIN  kalendar ON " . $this->table_name . ".termin_id = kalendar.id
          WHERE " . $this->table_name . ".djak_id = '$djak_id' && kalendar.status in (2) && kalendar.start >= '$granica'  ORDER BY kalendar.start ASC
           ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    function read_all_all_prisustvo_djak($djak_id)
    {
     

        // query to select all user records
        $query = " SELECT e.djak_id, e.prisutan AS prisutan, e.opravdao_otsustvo AS opravdao_otsustvo, 
          k.komentar AS komentar, k.id AS id , k.start AS start, k.end AS end, k.fk_profesor AS fk_profesor, 
          k.fk_profesor_grupa AS fk_profesor_grupa, g.uzrast AS uzrast, g.velicina AS velicina,
          g.fk_nacin_zaduzivanja AS nacin_zaduzivanja_grupe, g.id AS fk_grupa, g.vrtic AS vrtic
          FROM " . $this->table_name . "  e
          LEFT JOIN  kalendar k ON e.termin_id = k.id
          INNER JOIN grupe g ON g.id = k.fk_grupa
          INNER JOIN povezivanje p ON p.fk_djak = e.djak_id
          WHERE  e.djak_id = $djak_id AND k.status in (2) AND g.fk_nacin_zaduzivanja = 2 AND 
          ((p.created < k.start AND p.status = 1) OR ( p.created < k.start AND p.deleted > k.start AND p.status = 0 ))
          GROUP BY k.id
           ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    
    function read_all_all_prisustvo_djak_kartica($djak_id, $ta_grupa)
    {
     

        // query to select all user records
        $query = " SELECT e.djak_id, e.prisutan AS prisutan, e.opravdao_otsustvo AS opravdao_otsustvo, 
          k.komentar AS komentar, k.id AS id , k.start AS start, k.end AS end, k.fk_profesor AS fk_profesor, 
          k.fk_profesor_grupa AS fk_profesor_grupa, g.uzrast AS uzrast, g.velicina AS velicina,
          g.fk_nacin_zaduzivanja AS nacin_zaduzivanja_grupe, g.id AS fk_grupa, g.vrtic AS vrtic
          FROM " . $this->table_name . "  e
          LEFT JOIN  kalendar k ON e.termin_id = k.id
          INNER JOIN grupe g ON g.id = k.fk_grupa
          INNER JOIN povezivanje p ON p.fk_djak = e.djak_id
          WHERE k.fk_grupa = $ta_grupa AND e.djak_id = $djak_id AND k.status in (2) AND g.fk_nacin_zaduzivanja = 2 
          /* AND ((p.created < k.start AND p.status = 1) OR ( p.created < k.start AND p.deleted > k.start AND p.status = 0 )) */
          GROUP BY k.id
           ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    
    function read_all_all_prisustvo_djak_interval($djak_id, $od, $do)
    {
     

        // query to select all user records
        $query = " SELECT e.djak_id, e.prisutan AS prisutan, e.opravdao_otsustvo AS opravdao_otsustvo, 
          k.komentar AS komentar, k.id AS id , k.start AS start, k.end AS end, k.fk_profesor AS fk_profesor, 
          k.fk_profesor_grupa AS fk_profesor_grupa, g.uzrast AS uzrast, g.velicina AS velicina,
          g.fk_nacin_zaduzivanja AS nacin_zaduzivanja_grupe, g.id AS fk_grupa, g.vrtic AS vrtic
          FROM " . $this->table_name . "  e
          LEFT JOIN  kalendar k ON e.termin_id = k.id
          INNER JOIN grupe g ON g.id = k.fk_grupa
          INNER JOIN povezivanje p ON p.fk_djak = e.djak_id
          WHERE e.djak_id = $djak_id AND k.status in (2) AND g.fk_nacin_zaduzivanja = 2 
          AND date(k.start) >= '$od' AND date(k.start) <= '$do'  AND 
          ((p.created < k.start AND p.status = 1) OR ( p.created < k.start AND p.deleted > k.start AND p.status = 0 ))
         GROUP BY k.id
         ORDER BY k.start
           ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    function read_all_all_prisustvo_djak_interval_grupa($djak_id, $od, $do, $ta_grupa)
    {
     

        // query to select all user records
        $query = " SELECT e.djak_id, e.prisutan AS prisutan, e.opravdao_otsustvo AS opravdao_otsustvo, 
          k.komentar AS komentar, k.id AS id , k.start AS start, k.end AS end, k.fk_profesor AS fk_profesor, 
          k.fk_profesor_grupa AS fk_profesor_grupa, g.uzrast AS uzrast, g.velicina AS velicina,
          g.fk_nacin_zaduzivanja AS nacin_zaduzivanja_grupe, g.id AS fk_grupa, g.vrtic AS vrtic
          FROM " . $this->table_name . "  e
          LEFT JOIN  kalendar k ON e.termin_id = k.id
          INNER JOIN grupe g ON g.id = k.fk_grupa
          INNER JOIN povezivanje p ON p.fk_djak = e.djak_id
          WHERE g.id = $ta_grupa AND e.djak_id = $djak_id AND k.status in (2) AND g.fk_nacin_zaduzivanja = 2 
          AND date(k.start) >= '$od' AND date(k.start) <= '$do'  
          /*   AND  ((p.created < k.start AND p.status = 1) OR ( p.created < k.start AND p.deleted > k.start AND p.status = 0 )) */
         GROUP BY k.id
         ORDER BY k.start
           ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

        
    function read_all_all_prisustvo_djak_interval_kartica($djak_id,$ta_grupa, $od, $do)
    {
     

        // query to select all user records
        $query = " SELECT e.djak_id, e.prisutan AS prisutan, e.opravdao_otsustvo AS opravdao_otsustvo, 
          k.komentar AS komentar, k.id AS id , k.start AS start, k.end AS end, k.fk_profesor AS fk_profesor, 
          k.fk_profesor_grupa AS fk_profesor_grupa, g.uzrast AS uzrast, g.velicina AS velicina,
          g.fk_nacin_zaduzivanja AS nacin_zaduzivanja_grupe, g.id AS fk_grupa, g.vrtic AS vrtic
          FROM " . $this->table_name . "  e
          LEFT JOIN  kalendar k ON e.termin_id = k.id
          INNER JOIN grupe g ON g.id = k.fk_grupa
          INNER JOIN povezivanje p ON p.fk_djak = e.djak_id
          WHERE k.fk_grupa = $ta_grupa AND  e.djak_id = $djak_id AND k.status in (2) AND g.fk_nacin_zaduzivanja = 2 
          AND date(k.start) >= '$od' AND date(k.start) <= '$do'  AND 
          ((p.created < k.start AND p.status = 1) OR ( p.created < k.start AND p.deleted > k.start AND p.status = 0 ))
          GROUP BY k.id
          ORDER BY k.start
           ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

// za sredjivanje profakture i verovatno fakture
    function read_all_all_prisustvo_djak_termin( $terminid)
    {
        $query = " SELECT e.djak_id, e.prisutan AS prisutan, e.opravdao_otsustvo AS opravdao_otsustvo, 
          k.komentar AS komentar, k.id AS id , k.start AS start, k.end AS end, k.fk_profesor AS fk_profesor, 
          k.fk_profesor_grupa AS fk_profesor_grupa, g.uzrast AS uzrast, g.velicina AS velicina,
          g.fk_nacin_zaduzivanja AS nacin_zaduzivanja_grupe, g.id AS fk_grupa, g.vrtic AS vrtic
          FROM " . $this->table_name . "  e
          LEFT JOIN  kalendar k ON e.termin_id = k.id
          INNER JOIN grupe g ON g.id = k.fk_grupa
          INNER JOIN povezivanje p ON p.fk_djak = e.djak_id
          WHERE k.status in (2) AND g.fk_nacin_zaduzivanja = 2  AND k.id = $terminid  
          AND ((p.created < k.start AND p.status = 1) OR ( p.created < k.start AND p.deleted > k.start AND p.status = 0 ))
         GROUP BY k.id
         
           ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    function read_all_all_prisustvo_djak_termin_djak( $terminid, $id_djaka)
    {
        $query = " SELECT e.djak_id, e.prisutan AS prisutan, e.opravdao_otsustvo AS opravdao_otsustvo, 
          k.komentar AS komentar, k.id AS id , k.start AS start, k.end AS end, k.fk_profesor AS fk_profesor, 
          k.fk_profesor_grupa AS fk_profesor_grupa, g.uzrast AS uzrast, g.velicina AS velicina,
          g.fk_nacin_zaduzivanja AS nacin_zaduzivanja_grupe, g.id AS fk_grupa, g.vrtic AS vrtic
          FROM " . $this->table_name . "  e
          LEFT JOIN  kalendar k ON e.termin_id = k.id
          INNER JOIN grupe g ON g.id = k.fk_grupa
          INNER JOIN povezivanje p ON p.fk_djak = e.djak_id
          WHERE k.status in (2) AND g.fk_nacin_zaduzivanja = 2  AND k.id = $terminid  AND e.djak_id = $id_djaka
          AND ((p.created < k.start AND p.status = 1) OR ( p.created < k.start AND p.deleted > k.start AND p.status = 0 ))
         GROUP BY k.id
         
           ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    function read_all_grupe_prisustvo_djak($djak_id)
    {
     

        // query to select all user records
        $query = " SELECT g.id AS fk_grupa, p.created AS created , g.status AS status
          FROM " . $this->table_name . "  e
          LEFT JOIN  kalendar k ON e.termin_id = k.id
          INNER JOIN grupe g ON g.id = k.fk_grupa
          INNER JOIN povezivanje p ON p.fk_djak = e.djak_id
          WHERE e.djak_id = $djak_id AND k.status = 2 
          GROUP BY g.id 
         
         
           ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }


    
    function read_all_grupe_prisustvo_djak_interval  ($djak_id, $od, $do)
    {
     

        // query to select all user records
        $query = " SELECT g.id AS fk_grupa, p.created AS created , g.status AS status
          FROM " . $this->table_name . "  e
          LEFT JOIN  kalendar k ON e.termin_id = k.id
          INNER JOIN grupe g ON g.id = k.fk_grupa
          INNER JOIN povezivanje p ON p.fk_djak = e.djak_id
          WHERE e.djak_id = $djak_id AND k.status = 2 AND date(k.start) >= '$od' AND date(k.start) <= '$do'
          GROUP BY g.id 
         
         
           ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    // function read_all_prisustvo_djak($djak_id)
    // {


    //     // query to select all user records
    //     $query = " 
    //       SELECT DISTINCT " . $this->table_name . ".* 
    //       FROM " . $this->table_name . "  INNER JOIN  kalendar ON " . $this->table_name . ".termin_id = kalendar.id
    //       WHERE " . $this->table_name . ".djak_id = '$djak_id' && kalendar.status in (2) ORDER BY kalendar.start ASC
    //        ";

    //     // prepare query statement
    //     $stmt = $this->conn->prepare( $query );

    //     // execute query
    //     $stmt->execute();

    //     return $stmt;

    // }



}