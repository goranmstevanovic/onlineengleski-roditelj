<?php


class udzbenik_grupa
{
    private $conn;
    private $table_name = "udzbenici_grupe";

    // object properties
    public $id;
    public $komentar;
    public $fk_udzbenik;
    public $fk_grupa;
    
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
        $this->created = date('Y-m-d H:i:s');

        // insert query
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                active = :active,
                komentar = :komentar,
                fk_udzbenik = :fk_udzbenik,
                fk_grupa = :fk_grupa,
               
                created = :created
                
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);
        // sanitize
        
        
        $this->active = htmlspecialchars(strip_tags($this->active));
         // bind the values
       
      
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':fk_udzbenik', $this->fk_udzbenik);
        $stmt->bindParam(':fk_grupa', $this->fk_grupa);
        $stmt->bindParam(':created', $this->created);
        

        // execute the query, also check if query was successful
        if ($stmt->execute()) {
            return true;
        } else {
            echo "\nPDO::errorInfo():\n";
            print_r($stmt->errorInfo());
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

    function delete_all_uzbenike_grupa($iid)
    {
      

        // query to select all user records
        $query = "delete
        FROM " . $this->table_name . " 
        WHERE fk_grupa = $iid   
        ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;             
    }

    function read_all_udzbenik_grupa($iid)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE `fk_grupa` = '$iid' && `active` = 1
                ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

   
    function  provera_dupliranja($id_djak,$id_ispit)
    {
        //select all data
        $query = "SELECT
                    `id`
                FROM
                    " . $this->table_name . "
                WHERE `fk_udzbenik` = $id_djak &&  `fk_grupa`= $id_ispit  && `active` = 1
                ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();
        $num = $stmt->rowCount();

        return $num;
    }

    function read_one($idd)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `id` = '$idd'
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
            datum = :datum, 
            active = :active,
            komentar = :komentar,
            fk_udzbenik = :fk_udzbenik,
            fk_grupa = :fk_grupa
                       
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);
        // sanitize
        
        
        $this->active = htmlspecialchars(strip_tags($this->active));
         // bind the values
       
        $stmt->bindParam(':active', $this->active);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':fk_udzbenik', $this->fk_udzbenik);
        $stmt->bindParam(':fk_grupa', $this->fk_grupa);
      
              
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