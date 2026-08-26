<?php
/**
 * Created by PhpStorm.
 * User: goran
 * Date: 13.10.2019
 * Time: 15:12
 */

class kalendar
{
    // database connection and table name
    private $conn;
    private $table_name = "kalendar";

    // object properties
    public $id;
    public $start;
    public $fk_grupa;
    public $lastname;
    public $pocetak;
    public $kraj;
    public $fk_ucionica;
    public $fk_profesor;
    public $fk_profesor_grupa;
    public $komentar;
    public $fk_lokacija;
    public $djak;
    public $created;
    public $modified;
    public $status;
    public $color;
    public $zoom_skype;
    public $od;
    public $do;


    // constructor
    public function __construct($db){
        $this->conn = $db;
    }

    // create new user record
    function create_termin()
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');

         $query = "INSERT INTO
                " . $this->table_name . "
            SET
                fk_ucionica = :ucionica,
                fk_grupa = :grupa,
                komentar = :komentar,
                fk_lokacija = :fk_lokacija,
                fk_profesor_grupa = :fk_profesor_grupa,
                start = :pocetak,
                end = :kraj,
                status = :status,
                color = :color,
                zoom_skype = :zoom_skype,
                created = :created
                
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize



        // bind the values

        $stmt->bindParam(':ucionica', $this->ucionica);
        $stmt->bindParam(':grupa', $this->grupa);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':pocetak', $this->pocetak);
        $stmt->bindParam(':kraj', $this->kraj);

        // hash the password before saving to database


        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':zoom_skype', $this->zoom_skype);
        $stmt->bindParam(':fk_lokacija', $this->fk_lokacija);
        $stmt->bindParam(':fk_profesor_grupa', $this->fk_profesor_grupa);
        

        // execute the query, also check if query was successful
        if ($stmt->execute()) {
          /*  $stmt5 = $this->conn->prepare("SELECT MAX(Id) AS max_id FROM kalendar");
            $stmt5 -> execute();
            $invNum = $stmt5 -> fetch(PDO::FETCH_ASSOC);
            $max_id = $invNum['max_id']; */
          $lastid=$this->conn->lastInsertId();
            $query2="UPDATE `kalendar` SET url = CONCAT('../dogadjaj.php?id=',id)  where id = $lastid  ";
            $stmt2 = $this->conn->prepare($query2);
            if ($stmt2->execute())
            {
              return true;
            }
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    public function count_broj_casova_grupa_vreme($id_grupe,$start_termina)
    {

        // query to select all user records
        $query = "SELECT id FROM " . $this->table_name . " where `fk_grupa` = $id_grupe 
        && `start` < '$start_termina' and `status` = 2
        
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

    
    public function count_broj_casova_grupa_vreme1($id_grupe)
    {

        // query to select all user records
        $query = "SELECT id FROM " . $this->table_name . " where `fk_grupa` = $id_grupe 
        and `status` = 2
        
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



    function provera_zauzetosti_ucionice_termin($datum_vreme, $ucionica)
    {
        $query = "
        SELECT id FROM kalendar  WHERE start < '$datum_vreme' && end > '$datum_vreme' && 
        status in (1,2) && fk_ucionica = $ucionica  
        ";

            $stmt = $this->conn->prepare( $query );

            // execute query
            $stmt->execute();
            $num = $stmt->rowCount();
    
            return $num;
    }

    function count_zauzetosti_profesor($od, $do, $profesor)
    {
        $query = "SELECT   kal.*
            FROM
                kalendar kal
            INNER JOIN grupe ON grupe.id = kal.fk_grupa 
            WHERE
                ((`kal`.start < '$od' && `kal`.end > '$od' ) ||  (`kal`.start < '$do' && `kal`.end > '$do' ) || ( `kal`.start = '$od' && `kal`.end = '$do' ) || 
                 (`kal`.start > '$od' && `kal`.start < '$do') || (`kal`.end < '$do' && `kal`.end > '$od' )  ||  (`kal`.start > '$od' && `kal`.end < '$do' )
                 || ( `kal`.start = '$od' && `kal`.end < '$do' )  || ( `kal`.start = '$od' && `kal`.end > '$do' ) || ( `kal`.start > '$od' && `kal`.end = '$do' )
                 || ( `kal`.start < '$od' && `kal`.end = '$do' )
                 )
                && (kal.fk_profesor = '$profesor' || (grupe.fk_profesor = '$profesor' &&  kal.fk_profesor is NULL  ) ) && `kal`.status IN (1,2) /* && grupe.status = 1 */
            ";

        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        $num = $stmt->rowCount();

        return $num;
    }

    function count_casovi_danas()
    {
        $todayStart = date('Y-m-d 00:00:00');
        $tomorrowStart = date('Y-m-d 00:00:00', strtotime('+1 day'));

        $query = "SELECT COUNT(*) as total 
                FROM kalendar 
                WHERE start >= :today_start and status in (1,2,3)
                AND start < :tomorrow_start";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':today_start', $todayStart);
        $stmt->bindParam(':tomorrow_start', $tomorrowStart);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$row['total'];
    }


    function count_casovi_danas_prof( $prof)
    {
        $todayStart = date('Y-m-d 00:00:00');
        $tomorrowStart = date('Y-m-d 00:00:00', strtotime('+1 day'));

        $query = "SELECT COUNT(*) as total 
                FROM kalendar 
                WHERE start >= :today_start 
                AND start < :tomorrow_start AND status in (1,2,3)
                AND ((fk_profesor = :prof ) OR (fk_profesor is null AND fk_profesor_grupa = :prof))  
                ";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':today_start', $todayStart);
        $stmt->bindParam(':tomorrow_start', $tomorrowStart);
        $stmt->bindParam(':prof', $prof);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)$row['total'];
    }

    function provera_zauzetosti_profesor($od, $do, $profesor)
    {
        $query = "SELECT   kal.* grupe.fk_profesor AS taj_profesor
            FROM
                kalendar kal
            INNER JOIN grupe ON grupe.id = kal.fk_grupa 
            WHERE
                ((`kal`.start < '$od' && `kal`.end > '$od' ) ||  (`kal`.start < '$do' && `kal`.end > '$do' ) || ( `kal`.start = '$od' && `kal`.end = '$do' ) || 
                 (`kal`.start > '$od' && `kal`.start < '$do') || (`kal`.end < '$do' && `kal`.end > '$od' )  ||  (`kal`.start > '$od' && `kal`.end < '$do' )
                 || ( `kal`.start = '$od' && `kal`.end < '$do' )  || ( `kal`.start = '$od' && `kal`.end > '$do' ) || ( `kal`.start > '$od' && `kal`.end = '$do' )
                 || ( `kal`.start < '$od' && `kal`.end = '$do' )
                 )
                && (kal.fk_profesor = '$profesor' || (grupe.fk_profesor = '$profesor' &&  kal.fk_profesor is NULL  ) ) && `kal`.status IN (1,2) /* && grupe.status = 1 */
            ";

        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        // $num = $stmt->rowCount();

        return $stmt;
    }


    public function read_all_grupa_vremenski_interval($id_grupe,$start_termina)
    {

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . " where `fk_grupa` = $id_grupe 
        && `start` < '$start_termina' and `status` in (1,2,3) ORDER BY start ASC
        
        ";

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        if ( $stmt->execute() ) {
            return $stmt;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

   

    function count_provera_postojanja_termina_djak_dan($taj_dan, $ta_grupa)
    {
        $query = "SELECT  id
            FROM
                " . $this->table_name . "
            WHERE
            `start` LIKE '%$taj_dan%' && `fk_grupa` = '$ta_grupa' &&  `status` in (1,2)
            
            ";

            $stmt = $this->conn->prepare( $query );
           

            // execute query
            $stmt->execute();
            $num = $stmt->rowCount();
            
    
            return $num;
    }

    function brisi_termine_grupa_dan($taj_dan, $ta_grupa)
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');
      /*  if($this->ucionica==1){$this->color='#58FA58';}
        if($this->ucionica==2){$this->color='#00BFFF';}
        if($this->ucionica==3){$this->color='#F7FE2E';} */


        $query1 = " UPDATE  " . $this->table_name . "               
                 SET
                    status = 4
                    WHERE
                    start LIKE '%$taj_dan%' && `fk_grupa` = '$ta_grupa'
                     ";

        // prepare the query
        $stmt = $this->conn->prepare($query1);

        // bind the values

               
        // execute the query, also check if query was successful
        if ( $stmt->execute() ) {
                return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    function brisi_termine_grupa_interval($datum_od, $datum_do , $ta_grupa)
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');
      /*  if($this->ucionica==1){$this->color='#58FA58';}
        if($this->ucionica==2){$this->color='#00BFFF';}
        if($this->ucionica==3){$this->color='#F7FE2E';} */


        $query1 = " UPDATE  " . $this->table_name . "               
                 SET
                    status = 4
                    WHERE
                    date(start) >= '$datum_od' && date(start) <= '$datum_do' && `fk_grupa` = '$ta_grupa'
                     ";

        // prepare the query
        $stmt = $this->conn->prepare($query1);

        // bind the values

               
        // execute the query, also check if query was successful
        if ( $stmt->execute() ) {
                return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }


    public function read_all_sati(){
        $query = "SELECT  *
            FROM
                `sati`
            
            ";

            $stmt = $this->conn->prepare( $query );

            // execute query
            $stmt->execute();
            
    
            return $stmt;
        

    }

    public function read_all_minuti(){
        $query = "SELECT  *
            FROM
                `minuti`
            
            ";

            $stmt = $this->conn->prepare( $query );

            // execute query
            $stmt->execute();
    
            return $stmt;
        

    }

    function truncate_roll()
    {
        $sql = "TRUNCATE TABLE `kalendar_roll`";
        //Prepare the SQL query.
        $statement = $this->conn->prepare($sql);
        //Execute the statement.
        $statement->execute();
    }

    function create_termin_roll()
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');

        $query = "INSERT INTO
                kalendar_roll
            SET
                fk_ucionica = :ucionica,
                fk_grupa = :grupa,
                komentar = :komentar,
                start = :pocetak,
                end = :kraj,
                status = :status,
                color = :color,
                created = :created";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize



        // bind the values

        $stmt->bindParam(':ucionica', $this->ucionica);
        $stmt->bindParam(':grupa', $this->grupa);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':pocetak', $this->pocetak);
        $stmt->bindParam(':kraj', $this->kraj);

        // hash the password before saving to database


        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':color', $this->color);

        // execute the query, also check if query was successful
        if ($stmt->execute()) {
            $query2="UPDATE kalendar SET url = CONCAT(\"../dogadjaj.php?id=\",id)  ";
            $stmt2 = $this->conn->prepare($query2);
            if ($stmt2->execute())
            {
                return true;
            }
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    function create_termin_slobodan()
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');

        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                fk_grupa = :grupa,
                komentar = :komentar,
                start = :pocetak,
                end = :kraj,
                status = :status,
                fk_profesor = :fk_profesor,
                fk_lokacija = :fk_lokacija,
                color = :color,
                created = :created";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize



        // bind the values

        $stmt->bindParam(':grupa', $this->grupa);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':pocetak', $this->pocetak);
        $stmt->bindParam(':kraj', $this->kraj);
        $stmt->bindParam(':fk_profesor', $this->fk_profesor);
        $stmt->bindParam(':fk_lokacija', $this->fk_lokacija);

        // hash the password before saving to database


        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':color', $this->color);

        // execute the query, also check if query was successful
        if ($stmt->execute()) {
            $query2="UPDATE kalendar SET url = CONCAT(\"../dogadjaj.php?id=\",id)  ";
            $stmt2 = $this->conn->prepare($query2);
            if ($stmt2->execute())
            {
                return true;
            }
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

    function readOne($i)
    {
        $query = "SELECT  *
            FROM
                " . $this->table_name . "
            WHERE
                `id` = $i
            LIMIT
                0,1";

        $stmt = $this->conn->prepare( $query );
        //$stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->fk_grupa = $row['fk_grupa'];
        $this->start = $row['start'];
        $this->kraj = $row['end'];
        $this->fk_profesor = $row['fk_profesor'];
        $this->fk_profesor_grupa = $row['fk_profesor_grupa'];
        $this->komentar = $row['komentar'];
        $this->fk_ucionica = $row['fk_ucionica'];
        $this->status = $row['status'];
        $this->zoom_skype = $row['zoom_skype'];
		$this->color = $row['color'];
    }


    

    function read_selected_intervals($od, $do)
    {
        $query = "SELECT  *
            FROM
                " . $this->table_name . "
            WHERE
                date(`start`) >= '$od' && date(`start`) <= '$do' && `status` !=4 && `fk_grupa` != 9177 
            ";

        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;
    }

    function update_termin_profesor($id)
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');
      /*  if($this->ucionica==1){$this->color='#58FA58';}
        if($this->ucionica==2){$this->color='#00BFFF';}
        if($this->ucionica==3){$this->color='#F7FE2E';} */


        $query1 = " UPDATE  " . $this->table_name . "               
                 SET
                    fk_ucionica = :ucionica,
                    komentar = :komentar,
                    zoom_skype = :zoom_skype,
                    status = :status,
                    color = :color
                    WHERE id= :id ";

        // prepare the query
        $stmt = $this->conn->prepare($query1);

        // bind the values

        $stmt->bindParam(':ucionica', $this->ucionica);
       
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':zoom_skype', $this->zoom_skype);
        $stmt->bindParam(':id', $id);
        
        // execute the query, also check if query was successful
        if ( $stmt->execute() ) {
                return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    function update_termin($id)
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');
      /*  if($this->ucionica==1){$this->color='#58FA58';}
        if($this->ucionica==2){$this->color='#00BFFF';}
        if($this->ucionica==3){$this->color='#F7FE2E';} */


        $query1 = " UPDATE  " . $this->table_name . "               
                 SET
                    fk_ucionica = :ucionica,
                    fk_profesor = :fk_profesor,
                    fk_profesor_grupa = :fk_profesor_grupa,
                    komentar = :komentar,
                    zoom_skype = :zoom_skype,
                    status = :status,
                    color = :color
                    WHERE id= :id ";

        // prepare the query
        $stmt = $this->conn->prepare($query1);

        // bind the values

        $stmt->bindParam(':ucionica', $this->ucionica);
        $stmt->bindParam(':fk_profesor', $this->fk_profesor);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':zoom_skype', $this->zoom_skype);
        $stmt->bindParam(':fk_profesor_grupa', $this->fk_profesor_grupa);
        $stmt->bindParam(':id', $id);
        
        // execute the query, also check if query was successful
        if ( $stmt->execute() ) {
                return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    function update_termin_periodicni($id)
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');
      /*  if($this->ucionica==1){$this->color='#58FA58';}
        if($this->ucionica==2){$this->color='#00BFFF';}
        if($this->ucionica==3){$this->color='#F7FE2E';} */


        $query1 = " UPDATE  " . $this->table_name . "               
                 SET
                    fk_ucionica = :ucionica,
                    komentar = :komentar,
                    fk_grupa = :grupa,
                    zoom_skype = :zoom_skype,
                    fk_profesor_grupa = :fk_profesor_grupa,
                    start = :pocetak,
                    end = :kraj,
                    status = :status,
                    color = :color,
                    created = :created
                    WHERE id= :id ";

        // prepare the query
        $stmt = $this->conn->prepare($query1);

        // bind the values

        $stmt->bindParam(':ucionica', $this->ucionica);
        $stmt->bindParam(':grupa', $this->grupa);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':pocetak', $this->pocetak);
        $stmt->bindParam(':kraj', $this->kraj);
        $stmt->bindParam(':fk_profesor_grupa', $this->fk_profesor_grupa);

        // hash the password before saving to database


        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':zoom_skype', $this->zoom_skype);
        $stmt->bindParam(':id', $id);
        
        // execute the query, also check if query was successful
        if ( $stmt->execute() ) {
                return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }


    function update_slobodan_termin($id)
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');
         $query1 = " UPDATE  " . $this->table_name . "               
                 SET
                    komentar = :komentar,
                    status = :status,
                    color = :color
                    WHERE id= :id ";

        // prepare the query
        $stmt = $this->conn->prepare($query1);

        // bind the values

        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':id', $id);

        // execute the query, also check if query was successful
        if ( $stmt->execute() ) {
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    function read_all_termin_grupa_student($djak, $grupa){
        // query to select all user records
        $query = "SELECT k.* , g.fk_nacin_zaduzivanja AS nacin_zaduzivanja_grupe, g.uzrast AS uzrast,
        g.velicina AS velicina
        FROM " . $this->table_name . "  k
        LEFT JOIN povezivanje p ON k.fk_grupa = p.fk_grupa
        INNER JOIN grupe g ON g.id = k.fk_grupa
        WHERE  k.fk_grupa = $grupa && p.fk_djak = $djak  && k.status = 2  
        && ((k.start > p.created AND p.deleted IS NULL  ) OR (k.start > p.created AND k.start < p.deleted ) )   
        ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_termin_month_profesor($mesec,$profesor){
        // sa zamenom

        // query to select all user records
        $query = "SELECT DISTINCT k.* , g.fk_nacin_zaduzivanja AS nacin_zaduzivanja_grupe, g.uzrast AS uzrast, 
        g.velicina AS velicina, g.isplata_profesoru AS isplata_profesoru
        FROM " . $this->table_name . " k
        INNER JOIN grupe g ON  k.fk_grupa = g.id
        WHERE 
        k.start LIKE '%$mesec%' && k.status in (2)  
        && ( k.fk_profesor_grupa = '$profesor' && (k.fk_profesor IS NULL || k.fk_profesor = 0 || k.fk_profesor = g.fk_profesor )  || k.fk_profesor = '$profesor' ) 
        ORDER BY k.start ASC
        ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_termin_month_profesor_1($mesec,$profesor){

        // query to select all user records
        $mesec_ceo = $mesec."-15";
        $query = "SELECT DISTINCT t.* , gr.uzrast AS uzrast, gr.velicina AS velicina, gr.isplata_profesoru AS isplata_profesoru
        FROM " . $this->table_name . " t
        INNER JOIN grupe gr ON t.fk_grupa = gr.id
        WHERE t.start LIKE '%$mesec%' && date(t.start) <= '$mesec_ceo' && t.status in (2)  
        && ( t.fk_profesor_grupa  = '$profesor'  && (t.fk_profesor IS NULL || t.fk_profesor = 0 || t.fk_profesor = gr.fk_profesor )  
        || t.fk_profesor = '$profesor' ) 
        ORDER BY  t.start ASC
        ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_termin_month_profesor_2($mesec,$profesor){
        // query to select all user records
        $mesec_ceo = $mesec."-15";
        $query = "SELECT DISTINCT t.* , gr.uzrast AS uzrast, gr.velicina AS velicina, gr.isplata_profesoru AS isplata_profesoru
        FROM " . $this->table_name . " t
        INNER JOIN grupe gr ON t.fk_grupa = gr.id
        WHERE t.start LIKE '%$mesec%' && date(t.start) > '$mesec_ceo' && t.status in (2)  
        && ( t.fk_profesor_grupa  = '$profesor'  && (t.fk_profesor IS NULL || t.fk_profesor = 0 || t.fk_profesor = gr.fk_profesor )  
        || t.fk_profesor = '$profesor' ) 
        ORDER BY  t.start ASC
        ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function plata(array $row_cas, int $id_profesora):int{
        if($row_cas['isplata_profesoru'] != null && $row_cas['isplata_profesoru'] != 0 ){
            $za_platu_od_casa = $row_cas['isplata_profesoru'];
            return $za_platu_od_casa;
        }else{
            $duzina_casa = new duzina_casa($this->conn);
            $uzrast = new uzrast($this->conn);
            $isplate_profesoru = new isplate_profesoru($this->conn);
            $datetime1_cas = new DateTime($row_cas['start']);
            $datetime2_cas = new DateTime($row_cas['end']);
            $interval_casa = $datetime1_cas->diff($datetime2_cas);
            $sum_interval = $interval_casa->format('%h') * 60 + $interval_casa->format('%i'); // duzina casa u minutima
            $id_duzine_casa = $duzina_casa->read_one_duzina($sum_interval);
            $stmt_uzrast = $uzrast->read_one($row_cas['uzrast']);
            $row_uzrast = $stmt_uzrast->fetch(PDO::FETCH_ASSOC);
            $stmt_isplate = $isplate_profesoru->read_one_isplata_profesor_vreme_last($id_profesora, $row_cas['velicina'], $row_uzrast['fk_platni_razred'],$id_duzine_casa,$row_cas['start']);
            $row_isplta_profesoru = $stmt_isplate->fetch(PDO::FETCH_ASSOC);
            if($row_isplta_profesoru != false){ 

                $za_platu_od_casa = $row_isplta_profesoru['iznos'] ?? 0;
                return $za_platu_od_casa;
            }else{
                // ako nemamo promene vezano za ovog profesora, citamo iz osnovne default isplate tabele
                $isplata = new isplata($this->conn);
                if($id_duzine_casa != 0){
                    $smtp_opsta_isplata = $isplata->read_one( $row_cas['velicina'], $row_uzrast['fk_platni_razred'],$id_duzine_casa);
                    $row_isplta_profesoru = $smtp_opsta_isplata->fetch(PDO::FETCH_ASSOC);
                    // echo "<p style='color:orange'>isplata:<pre> ",var_dump($row_isplta_profesoru),"</pre></p><br/>";
                    $za_platu_od_casa = $row_isplta_profesoru['iznos'] ?? 0;
                    
                   
                }else{
                    $za_platu_od_casa = 0;
                }
                return $za_platu_od_casa;
            }           

        }

    }

    function procenat_za_platu(array $row_cas, int $id_profesora):int{
        if($row_cas['isplata_profesoru'] != null && $row_cas['isplata_profesoru'] != 0 ){
            $za_platu_od_casa = $row_cas['isplata_profesoru'];
            return $za_platu_od_casa;
        }else{
            $duzina_casa = new duzina_casa($this->conn);
            $uzrast = new uzrast($this->conn);
            $isplate_profesoru = new isplate_profesoru($this->conn);
            $datetime1_cas = new DateTime($row_cas['start']);
            $datetime2_cas = new DateTime($row_cas['end']);
            $interval_casa = $datetime1_cas->diff($datetime2_cas);
            $sum_interval = $interval_casa->format('%h') * 60 + $interval_casa->format('%i'); // duzina casa u minutima
            $id_duzine_casa = $duzina_casa->read_one_duzina($sum_interval);
            $stmt_uzrast = $uzrast->read_one($row_cas['uzrast']);
            $row_uzrast = $stmt_uzrast->fetch(PDO::FETCH_ASSOC);
            $stmt_isplate = $isplate_profesoru->read_one_isplata_profesor_vreme_last($id_profesora, $row_cas['velicina'], $row_uzrast['fk_platni_razred'],$id_duzine_casa,$row_cas['start']);
            $row_isplta_profesoru = $stmt_isplate->fetch(PDO::FETCH_ASSOC);
            if($row_isplta_profesoru != false){ 

                $za_platu_od_casa = $row_isplta_profesoru['iznos'];
                return $za_platu_od_casa;
            }else{
                $isplata = new isplata($this->conn);
                if($id_duzine_casa != 0){
                    $smtp_opsta_isplata = $isplata->read_one( $row_cas['velicina'], $row_uzrast['fk_platni_razred'],$id_duzine_casa);
                    $row_isplta_profesoru = $smtp_opsta_isplata->fetch(PDO::FETCH_ASSOC);
                    // echo "<p style='color:orange'>isplata:<pre> ",var_dump($row_isplta_profesoru),"</pre></p><br/>";
                    $za_platu_od_casa = $row_isplta_profesoru['iznos'];
                    //include ('matematika_duzine_trajanja_casa.php');
                    // $row_isplta_profesoru = array();
                   
                }else{
                    $za_platu_od_casa = 0;
                }
                return $za_platu_od_casa;
            }           

        }

    }

    function za_platu_zaduzenje(array $row_cas, int $id_profesora){
    
            $duzina_casa = new duzina_casa($this->conn);
            $uzrast = new uzrast($this->conn);
            $isplate_profesoru = new isplate_profesoru($this->conn);
            $datetime1_cas = new DateTime($row_cas['start']);
            $datetime2_cas = new DateTime($row_cas['end']);
            $interval_casa = $datetime1_cas->diff($datetime2_cas);
            $sum_interval = $interval_casa->format('%h') * 60 + $interval_casa->format('%i'); // duzina casa u minutima
            $id_duzine_casa = $duzina_casa->read_one_duzina($sum_interval);
            $stmt_uzrast = $uzrast->read_one($row_cas['uzrast']);
            $row_uzrast = $stmt_uzrast->fetch(PDO::FETCH_ASSOC);
             var_dump($row_uzrast);


            // $stmt_isplate = $isplate_profesoru->read_one_isplata_profesor_vreme_last($id_profesora, $row_cas['velicina'], $row_uzrast['fk_platni_razred'],$id_duzine_casa,$row_cas['start']);
            // $row_isplta_profesoru = $stmt_isplate->fetch(PDO::FETCH_ASSOC);
            // if($row_isplta_profesoru != false){ 

            //     $za_platu_od_casa = $row_isplta_profesoru['iznos'];
            //     return $za_platu_od_casa;
            // }else{
            //     $isplata = new isplata($this->conn);
            //     $smtp_opsta_isplata = $isplata->read_one( $row_cas['velicina'], $row_uzrast['fk_platni_razred'],$id_duzine_casa);
            //     $row_isplta_profesoru = $smtp_opsta_isplata->fetch(PDO::FETCH_ASSOC);
            //     // echo "<p style='color:orange'>isplata:<pre> ",var_dump($row_isplta_profesoru),"</pre></p><br/>";
            //     $za_platu_od_casa = $row_isplta_profesoru['iznos'];
            //     //include ('matematika_duzine_trajanja_casa.php');
            //     // $row_isplta_profesoru = array();
            //     return $za_platu_od_casa;
            // }           

        

    }

    function read_all_termin_month_grupa($mesec,$grupa){
        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "
        WHERE `start` LIKE '%$mesec%' && `fk_grupa` = '$grupa' && `status` in (2)
        ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }
    function read_all_termin_month_grupa_profesor($mesec,$grupa, $profesor){
        // query to select all user records
        $query = "SELECT k.* 
        FROM " . $this->table_name . " k
        INNER JOIN grupe g ON g.id = k.fk_grupa
        WHERE k.start LIKE '%$mesec%' && k.fk_grupa = '$grupa' && k.status in (2) 
        && ( k.fk_profesor_grupa = '$profesor' && (k.fk_profesor IS NULL || k.fk_profesor = 0 || k.fk_profesor = g.fk_profesor )  || k.fk_profesor = '$profesor' ) 
        ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_termin_month_grupa1($mesec,$grupa){
        // query to select all user records
        $mesec_ceo = $mesec."-15";
        $query = "SELECT * FROM " . $this->table_name . "
        WHERE `start` LIKE '%$mesec%' && date(start) <= '$mesec_ceo'  && `fk_grupa` = '$grupa' && `status` in (2)
        ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_termin_month_grupa2($mesec,$grupa){
        // query to select all user records
        $mesec_ceo = $mesec."-15";
        $query = "SELECT * FROM " . $this->table_name . "
        WHERE `start` LIKE '%$mesec%' && date(start) > '$mesec_ceo'  && `fk_grupa` = '$grupa' && `status` in (2)
        ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }


}