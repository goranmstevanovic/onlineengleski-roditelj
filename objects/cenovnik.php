<?php


class cenovnik
{
    private $conn;
    private $table_name = "default_cene";
    public $id;
    public $fk_platni_razred;
    public $fk_velicina_grupe;
    public $fk_duzina_casa;
    public $iznos;
    public $created;
    
    public $active;  
    
    public function __construct($db){
        $this->conn = $db;
    }

    public function showError($stmt)
    {
        echo "<pre>";
        print_r($stmt->errorInfo());
        echo "</pre>";
    }


    // function create()
    // {

    //     // to get time stamp for 'created' field
       

    //     // insert query
    //     $query = "INSERT INTO
    //             " . $this->table_name . "
    //         SET
    //             fk_platni_razred =:fk_platni_razred,
    //             fk_velicina_grupe =:fk_velicina_grupe,
    //             fk_duzina_casa =:fk_duzina_casa,
    //             iznos =:iznos,                
    //             active =:active
                              
    //             "; 

    //     // prepare the query
    //     $stmt = $this->conn->prepare($query);

    //     // sanitize
        
    //     $this->active = htmlspecialchars(strip_tags($this->active));
    //     // $this->opis = htmlspecialchars(strip_tags($this->opis));
    //     // bind the values
        
    //     $stmt->bindParam(':active', $this->active);
    //     $stmt->bindParam(':fk_platni_razred', $this->fk_platni_razred);
    //     $stmt->bindParam(':fk_velicina_grupe', $this->fk_velicina_grupe);
    //     $stmt->bindParam(':fk_duzina_casa', $this->fk_duzina_casa);
    //     $stmt->bindParam(':iznos', $this->iznos);
    //     // execute the query, also check if query was successful
    //     if ($stmt->execute()) {
    //         return true;
    //     } else {
    //         $this->showError($stmt);
    //         return false;
    //     }
    // }
    function create(){ // kod prvog upisa u cenovnik stavlja datum oduvek
        // 1️⃣ Provera da li kombinacija već postoji
        $checkQuery = "SELECT COUNT(*) 
                    FROM " . $this->table_name . "
                    WHERE fk_platni_razred = :fk_platni_razred
                    AND fk_velicina_grupe = :fk_velicina_grupe
                    AND fk_duzina_casa = :fk_duzina_casa AND active = 1 ";

        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':fk_platni_razred', $this->fk_platni_razred);
        $checkStmt->bindParam(':fk_velicina_grupe', $this->fk_velicina_grupe);
        $checkStmt->bindParam(':fk_duzina_casa', $this->fk_duzina_casa);
        $checkStmt->execute();

        $count = $checkStmt->fetchColumn();

        // 2️⃣ Ako je prvi unos → postavi custom created datum
        if ($count == 0) {
            $createdValue = '1970-01-01 01:00:00';

            $query = "INSERT INTO " . $this->table_name . "
                SET
                    fk_platni_razred = :fk_platni_razred,
                    fk_velicina_grupe = :fk_velicina_grupe,
                    fk_duzina_casa = :fk_duzina_casa,
                    iznos = :iznos,
                    active = :active,
                    created = :created";
        } else {
            // koristi default CURRENT_TIMESTAMP
            $query = "INSERT INTO " . $this->table_name . "
                SET
                    fk_platni_razred = :fk_platni_razred,
                    fk_velicina_grupe = :fk_velicina_grupe,
                    fk_duzina_casa = :fk_duzina_casa,
                    iznos = :iznos,
                    active = :active";
        }

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->active = htmlspecialchars(strip_tags($this->active));

        // bind
        $stmt->bindParam(':fk_platni_razred', $this->fk_platni_razred);
        $stmt->bindParam(':fk_velicina_grupe', $this->fk_velicina_grupe);
        $stmt->bindParam(':fk_duzina_casa', $this->fk_duzina_casa);
        $stmt->bindParam(':iznos', $this->iznos);
        $stmt->bindParam(':active', $this->active);

        if ($count == 0) {
            $stmt->bindParam(':created', $createdValue);
        }

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
                WHERE `active` = 1 
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_one( $fk_velicina_grupe, $fk_platni_razred,$id_duzina_casa)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 &&  `fk_velicina_grupe`= $fk_velicina_grupe && `fk_duzina_casa` = $id_duzina_casa 
                && `fk_platni_razred`=$fk_platni_razred
                ORDER BY ID DESC LIMIT 1
			";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_one_po_id( $id)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 &&  `id`= $id
                ORDER BY ID DESC LIMIT 1
			";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_po_velicini($velicina)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 && `fk_velicina_grupe` = $velicina
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function provera_postojanja($fk_velicina_grupe, $fk_platni_razred ,$fk_duzina_casa )
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 &&  fk_platni_razred = $fk_platni_razred AND  fk_velicina_grupe = $fk_velicina_grupe AND
                fk_duzina_casa = $fk_duzina_casa
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $num = $stmt->rowCount();

        return $num;
    }

    function nadji_poslednjeg($fk_velicina_grupe, $fk_platni_razred ,$fk_duzina_casa )
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 &&  fk_platni_razred = $fk_platni_razred AND  fk_velicina_grupe = $fk_velicina_grupe AND
                fk_duzina_casa = $fk_duzina_casa
                ORDER BY created DESC limit 1
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
       // $num = $stmt->rowCount();

        return $stmt;
    }

    function citaj_sve_promene_cena (){
                //select all data
                $query = "SELECT
                c.*, pr.ime AS ime_plat_razreda, v.ime AS ime_velicine, dc.duzina AS duzina_casa
            FROM
            " . $this->table_name . " c
            INNER JOIN platni_razredi pr ON pr.id = c.fk_platni_razred
            INNER JOIN velicina v ON v.id = c.fk_velicina_grupe
            INNER JOIN duzine_casova dc ON dc.id = c.fk_duzina_casa
            WHERE c.active = 1 && c.created > '2021-01-01' 
            ORDER BY c.created DESC 
            
            ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        // $num = $stmt->rowCount();

        return $stmt;
    }  

    function provera_postojanja_komplet($fk_velicina_grupe, $fk_platni_razred ,$fk_duzina_casa, $iznos )
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 &&  fk_platni_razred = $fk_platni_razred AND  fk_velicina_grupe = $fk_velicina_grupe AND
                fk_duzina_casa = $fk_duzina_casa AND iznos = $iznos
               order by id DESC LiMIT 1
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $num = $stmt->rowCount();

        return $num;
    }



    function odredi_zaduzenje( array $row_cas)
    {
        $row_osnovno_zaduzenje = array();
       // $row_osnovno_zaduzenje['iznos'] = 0;
        if($row_cas['nacin_zaduzivanja_grupe'] == 2 ){
           // echo "**";
            $duzina_casa = new duzina_casa($this->conn);
            $uzrast = new uzrast($this->conn);
            $datetime1_cas = new DateTime($row_cas['start']);
            $datetime2_cas = new DateTime($row_cas['end']);
            $interval_casa = $datetime1_cas->diff($datetime2_cas);
            $duzina_tog_casa = $interval_casa->format('%h') * 60 + $interval_casa->format('%i'); // duzina casa u minutima
            // odredimo platni razred
            $stmt_uzrast = $uzrast->read_one($row_cas['uzrast']);
            $row_uzrast = $stmt_uzrast->fetch(PDO::FETCH_ASSOC);
           // echo "<br> Duzina casa: ";
          //  var_dump($duzina_tog_casa);

            $id_duzine_casa = $duzina_casa->read_one_duzina($duzina_tog_casa);
            // $stmt_uzrast = $uzrast->read_one($row_cas['uzrast']);
            if($id_duzine_casa == 0){
                $row_osnovno_zaduzenje = []; // Pretvori u prazan niz
                $row_osnovno_zaduzenje['iznos'] = 0;
            }else{
                $stmt_osnovno_zaduzenje = $this->nadji_cenu_za_termin($row_cas['velicina'], $row_uzrast['fk_platni_razred'],  $id_duzine_casa, $row_cas['start']);
                // var_dump($stmt_osnovno_zaduzenje);
                 $row_osnovno_zaduzenje = $stmt_osnovno_zaduzenje->fetch(PDO::FETCH_ASSOC);
                // var_dump($row_osnovno_zaduzenje);
            }
            if($row_osnovno_zaduzenje == false ){
                $row_osnovno_zaduzenje = []; // Pretvori u prazan niz
                $row_osnovno_zaduzenje['iznos'] = 0;
            }
            return $row_osnovno_zaduzenje;
        }else{
            $row_osnovno_zaduzenje = []; // Pretvori u prazan niz
           $row_osnovno_zaduzenje['iznos'] = 0;
        }
        return $row_osnovno_zaduzenje;
    }

    function nadji_cenu_osnovnu($fk_velicina_grupe, $fk_platni_razred ,$fk_duzina_casa){
        $query = "SELECT
        iznos
            FROM
            " . $this->table_name . "
            WHERE `active` = 1 &&  fk_platni_razred = $fk_platni_razred AND  fk_velicina_grupe = $fk_velicina_grupe AND
            fk_duzina_casa = $fk_duzina_casa
            ORDER BY id DESC limit 1
            ";
        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        //$num = $stmt->rowCount();
        return $stmt;
    }

    function nadji_cenu_za_termin($fk_velicina_grupe, $fk_platni_razred ,$fk_duzina_casa, $start){
        $query = "SELECT
        iznos
            FROM
            " . $this->table_name . "
            WHERE `active` = 1 &&  fk_platni_razred = $fk_platni_razred AND  fk_velicina_grupe = $fk_velicina_grupe AND
            fk_duzina_casa = $fk_duzina_casa && created < '$start' 
            ORDER BY created DESC limit 1
        
            ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        //$num = $stmt->rowCount();

        return $stmt;



    }



    function provera_postojanja_vrednost($fk_velicina_grupe, $fk_platni_razred ,$fk_duzina_casa )
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 &&  fk_platni_razred = $fk_platni_razred AND  fk_velicina_grupe = $fk_velicina_grupe AND
                fk_duzina_casa = $fk_duzina_casa
                ORDER BY id DESC limit 1
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        //$num = $stmt->rowCount();

        return $stmt;
    }

    function read_po_velicini_platni_razred($velicina, $platni)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE  `active` = 1 &&  `fk_velicina_grupe` = $velicina && `fk_platni_razred` = $platni
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_po_velicini_platni_razred_velicina_trajanje($velicina, $platni,$fk_duzina_casa)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE  `active` = 1 &&  `fk_velicina_grupe` = $velicina && `fk_platni_razred` = $platni && fk_duzina_casa = $fk_duzina_casa
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_po_velicini_platni_razred_velicina_trajanje_last($velicina, $platni,$fk_duzina_casa)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE  `active` = 1 &&  `fk_velicina_grupe` = $velicina && `fk_platni_razred` = $platni && fk_duzina_casa = $fk_duzina_casa
                ORDER BY created DESC limit 1 
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    public function update($fk_velicina_grupe, $fk_platni_razred ,$fk_duzina_casa )
    {
      //  $this->procenat_za_platu = $procenat_za_platu;

        // to get time stamp for 'created' field
       // $this->created=date('Y-m-d H:i:s');
                // update query
        $query = "UPDATE
                " . $this->table_name . "
            SET
            iznos = :iznos
            WHERE fk_platni_razred = :fk_platni_razred AND  fk_velicina_grupe = :fk_velicina_grupe AND
            fk_duzina_casa = :fk_duzina_casa
            ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_duzina_casa=htmlspecialchars(strip_tags($this->fk_duzina_casa));
        $this->iznos=htmlspecialchars(strip_tags($this->iznos));
               
        $stmt->bindParam(':fk_velicina_grupe', $fk_velicina_grupe);
        $stmt->bindParam(':fk_platni_razred', $fk_platni_razred);
        $stmt->bindParam(':fk_duzina_casa', $fk_duzina_casa);
        $stmt->bindParam(':iznos', $this->iznos);
        
        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            $this->showError($stmt);
            return false;
        }

    }

    
    public function update_cena_datum_id($id )
    {
     
        $query = "UPDATE
                " . $this->table_name . "
            SET
            iznos = :iznos,
            created = :created
            WHERE id = :id 
            ";

        // prepare the query
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->iznos=htmlspecialchars(strip_tags($this->iznos));
        // bind the values
        $stmt->bindParam(':iznos', $this->iznos);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':created', $this->created);
        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            $this->showError($stmt);
            return false;
        }
    }
}