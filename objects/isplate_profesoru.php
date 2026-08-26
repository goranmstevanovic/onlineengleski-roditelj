<?php


class isplate_profesoru
{

    private $conn;
    private $table_name = "isplate_profesoru";
    public $id;
    public $fk_profesor;
    public $fk_platni_razred;
    public $fk_velicina_grupe;
    public $fk_duzina_casa;
    
    public $iznos; 
    public $active; 
    public $created;
    public $modified; 
    
    public function __construct($db){
        $this->conn = $db;
    }

    public function showError($stmt)
    {
        echo "<pre>";
        print_r($stmt->errorInfo());
        echo "</pre>";
    }

    function create()
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');
        $this->active = 1;
        // insert query
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                fk_profesor = :fk_profesor,
                fk_platni_razred = :fk_platni_razred,
                fk_velicina_grupe = :fk_velicina_grupe,
                fk_duzina_casa = :fk_duzina_casa,
                iznos = :iznos,
                active = :active,
                created = :created
                
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_duzina_casa = htmlspecialchars(strip_tags($this->fk_duzina_casa));
       // $this->iznos_din_jedan = htmlspecialchars(strip_tags($this->iznos_din_jedan));
        $this->iznos = htmlspecialchars(strip_tags($this->iznos));
       


        // bind the values
        $stmt->bindParam(':fk_profesor', $this->fk_profesor);
        $stmt->bindParam(':fk_platni_razred', $this->fk_platni_razred);
        $stmt->bindParam(':fk_velicina_grupe', $this->fk_velicina_grupe);
        $stmt->bindParam(':fk_duzina_casa', $this->fk_duzina_casa);
       // $stmt->bindParam(':iznos_din_jedan', $this->iznos_din_jedan);
        $stmt->bindParam(':iznos', $this->iznos);
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':created', $this->created);
       
       

        // execute the query, also check if query was successful
        if ($stmt->execute()) {
           // return true;
           return $this->conn->lastInsertId();
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
    
    function read_all_izmene_jedan_profesor($id_profesor)
    {
        //select all data
        $query = "SELECT ip.*, v.ime AS ime_velicine, pr.ime AS ime_platnog_razreda, dc.duzina AS duz_casa 
                FROM
                " . $this->table_name . " ip
                LEFT JOIN platni_razredi pr ON pr.id = ip.fk_platni_razred
                LEFT JOIN velicina v ON v.id = ip.fk_velicina_grupe
                LEFT JOIN duzine_casova dc ON dc.id = ip.fk_duzina_casa
                WHERE ip.active = 1 && ip.fk_profesor = '$id_profesor' 
                ORDER by ip.created DESC
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function provera_postojanja($fk_profesor,$fk_velicina_grupe, $fk_platni_razred ,$fk_duzina_casa )
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 && `fk_profesor` = $fk_profesor  && fk_platni_razred = $fk_platni_razred AND  fk_velicina_grupe = $fk_velicina_grupe AND
                fk_duzina_casa = $fk_duzina_casa
                ORDER BY  created DESC LIMIT 1
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
       // $num = $stmt->rowCount();

        return $stmt;
    }

    function read_one_last($fk_profesor, $fk_velicina_grupe, $fk_platni_razred, $fk_duzina_casa )
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 && `fk_profesor` = $fk_profesor && 
                `fk_velicina_grupe`= $fk_velicina_grupe 
                && `fk_platni_razred`=$fk_platni_razred && 
                `fk_duzina_casa` = $fk_duzina_casa
                ORDER BY `created` DESC LIMIT 1
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
                WHERE `id`=$idd 
			";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_one_isplata_profesor_vreme_last($fk_profesor, $fk_velicina_grupe, $fk_platni_razred,$id_duzina_casa,$start_time)
    {   
        $query = "SELECT *
                FROM
                `isplate_profesoru`
                WHERE `active` = 1 && `fk_profesor` = $fk_profesor && 
                `fk_velicina_grupe`= $fk_velicina_grupe && `fk_platni_razred`=$fk_platni_razred 
                && fk_duzina_casa = '$id_duzina_casa'
                && '$start_time' > `created` 
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

    function read_po_velicini_platni_razred($velicina, $platni)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `active` = 1 && `fk_velicina_grupe` = $velicina && `fk_platni_razred` = $platni
               
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    public function update($fk_profesor, $fk_velicina_grupe, $fk_platni_razred)
    {
      //  $this->procenat_za_platu = $procenat_za_platu;

        // to get time stamp for 'created' field
       // $this->created=date('Y-m-d H:i:s');
                // update query
        $query = "UPDATE
                " . $this->table_name . "
            SET
            fk_duzina_casa = :fk_duzina_casa,
            iznos_din_jedan = :iznos_din_jedan,
            iznos = :iznos
                
            WHERE fk_platni_razred = :fk_platni_razred &&  fk_velicina_grupe = :fk_velicina_grupe 
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_duzina_casa=htmlspecialchars(strip_tags($this->fk_duzina_casa));
        $this->iznos_din_jedan=htmlspecialchars(strip_tags($this->iznos_din_jedan));
        $this->iznos=htmlspecialchars(strip_tags($this->iznos));
        


        // bind the values
        $stmt->bindParam(':fk_duzina_casa', $this->fk_duzina_casa);
        $stmt->bindParam(':iznos_din_jedan', $this->iznos_din_jedan);
        $stmt->bindParam(':iznos', $this->iznos);
        
        $stmt->bindParam(':fk_velicina_grupe', $fk_velicina_grupe);
        $stmt->bindParam(':fk_platni_razred', $fk_platni_razred);
        
        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            $this->showError($stmt);
            return false;
        }

    }

    public function update_prof($fk_profesor, $fk_velicina_grupe, $fk_platni_razred)
    {
      //  $this->procenat_za_platu = $procenat_za_platu;

        // to get time stamp for 'created' field
       // $this->created=date('Y-m-d H:i:s');
                // update query
        $query = "UPDATE
                " . $this->table_name . "
            SET
            fk_duzina_casa = :fk_duzina_casa,
            iznos_din_jedan = :iznos_din_jedan,
            iznos = :iznos
                
            WHERE fk_profesor = :fk_profesor && fk_platni_razred = :fk_platni_razred &&  fk_velicina_grupe = :fk_velicina_grupe 
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_duzina_casa=htmlspecialchars(strip_tags($this->fk_duzina_casa));
        $this->iznos_din_jedan=htmlspecialchars(strip_tags($this->iznos_din_jedan));
        $this->iznos=htmlspecialchars(strip_tags($this->iznos));
        


        // bind the values
        $stmt->bindParam(':fk_duzina_casa', $this->fk_duzina_casa);
        $stmt->bindParam(':iznos_din_jedan', $this->iznos_din_jedan);
        $stmt->bindParam(':iznos', $this->iznos);

        $stmt->bindParam(':fk_profesor', $fk_profesor);
        $stmt->bindParam(':fk_velicina_grupe', $fk_velicina_grupe);
        $stmt->bindParam(':fk_platni_razred', $fk_platni_razred);
        
        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            $this->showError($stmt);
            return false;
        }

    }

    public function update_iz_edita($indeks)
    {
      //  $this->procenat_za_platu = $procenat_za_platu;

        // to get time stamp for 'created' field
       // $this->created=date('Y-m-d H:i:s');
                // update query
        $query = "UPDATE
                " . $this->table_name . "
            SET
            fk_duzina_casa = :fk_duzina_casa,
            iznos = :iznos,
            fk_profesor = :fk_profesor,
            fk_platni_razred = :fk_platni_razred,
            fk_velicina_grupe = :fk_velicina_grupe,
            created = :created
            WHERE `id` = :indeks 
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_duzina_casa=htmlspecialchars(strip_tags($this->fk_duzina_casa));
      //  $this->fk_duzina_casa=htmlspecialchars(strip_tags($this->fk_duzina_casa));
        $this->iznos=htmlspecialchars(strip_tags($this->iznos));
        


        // bind the values
       // $stmt->bindParam(':fk_duzina_casa', $this->fk_duzina_casa);
        $stmt->bindParam(':fk_duzina_casa', $this->fk_duzina_casa,PDO::PARAM_INT);
        $stmt->bindParam(':iznos', $this->iznos,PDO::PARAM_INT);
        $stmt->bindParam(':fk_profesor', $this->fk_profesor);
        $stmt->bindParam(':fk_platni_razred', $this->fk_platni_razred);
        $stmt->bindParam(':fk_velicina_grupe', $this->fk_velicina_grupe);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':indeks', $indeks);
        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            $this->showError($stmt);
            return false;
        }

    }

}