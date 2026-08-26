<?php


class isplata
{
    private $conn;
    private $table_name = "default_placanje";
    public $id;
    public $fk_platni_razred;
    public $fk_velicina_grupe;
    public $fk_duzina_casa;
    public $iznos;
    
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


    function create()
    {

        // to get time stamp for 'created' field
       

        // insert query
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                fk_platni_razred =:fk_platni_razred,
                fk_velicina_grupe =:fk_velicina_grupe,
                fk_duzina_casa =:fk_duzina_casa,
                iznos =:iznos,                
                active =:active
                              
                "; 

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        
        $this->active = htmlspecialchars(strip_tags($this->active));
        // $this->opis = htmlspecialchars(strip_tags($this->opis));
        // bind the values
        
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':fk_platni_razred', $this->fk_platni_razred);
        $stmt->bindParam(':fk_velicina_grupe', $this->fk_velicina_grupe);
        $stmt->bindParam(':fk_duzina_casa', $this->fk_duzina_casa);
        $stmt->bindParam(':iznos', $this->iznos);
        // execute the query, also check if query was successful
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
      //  $this->iznosipo=htmlspecialchars(strip_tags($this->iznosipo));
        


        // bind the values
       
        $stmt->bindParam(':iznos', $this->iznos);
       
        
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
    


}