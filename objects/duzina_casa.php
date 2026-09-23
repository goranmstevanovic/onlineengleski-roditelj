<?php


class duzina_casa
{

    private $conn;
    private $table_name = "duzine_casova";

    public $id;
    
    public $duzina;
    public $opis;
   

    public function __construct($db){
        $this->conn = $db;
    }

    function create()
    {

        // to get time stamp for 'created' field
       

        // insert query
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                
                active =:active,
                duzina = :duzina,
                opis = :opis

                
                "; 

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        
        $this->active = htmlspecialchars(strip_tags($this->active));
        $this->opis = htmlspecialchars(strip_tags($this->opis));
        // bind the values
        
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':duzina', $this->duzina);
        $stmt->bindParam(':opis', $this->opis);
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
                WHERE `active`=1
                ORDER BY duzina
               
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
                WHERE
				`id` = '$idd'"
				;

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        
        return $stmt;
    }

    function read_one_duzina($duzina):int
    {
      //  echo $duzina;
        //select all data
        $query = "SELECT
                    id
                FROM
                    " . $this->table_name . "
                WHERE
				`duzina` = $duzina AND active = 1
                ORDER BY id
                ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $izlaz = $stmt->fetch(PDO::FETCH_ASSOC);

        return $izlaz['id'] ?? 0 ;

       
    }



    


    function update($idd)
    {
      //  $this->procenat_za_platu = $procenat_za_platu;

        // to get time stamp for 'created' field
        $this->created=date('Y-m-d H:i:s');
                // update query
        $query = "UPDATE
                " . $this->table_name . "
            SET
           
            duzina = :duzina,
            opis = :opis
                
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);
        // sanitize
        $this->opis = htmlspecialchars(strip_tags($this->opis));
   
        // bind the values
        
        $stmt->bindParam(':duzina', $this->duzina);
        $stmt->bindParam(':opis', $this->opis);
        
        $stmt->bindParam(':idd', $idd);
        // hash the password before saving to database

        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            $this->showError($stmt);
            return false;
        }

    }



}