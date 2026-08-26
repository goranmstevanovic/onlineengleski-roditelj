<?php
/**
 * Created by PhpStorm.
 * User: goran
 * Date: 19 Feb 2020
 * Time: 11:35
 */

class profaktura
{
    private $conn;
    private $table_name = "profakture";

    public $id;
    public $datum;
    public $od;
    public $do;
    public $platilac;
    public $fk_moje_pravno_lice;
    public $eng;
    public $vrsta_platioca;
    public $struktura_profakture;
    public $komentar;
    public $active;
    public $created;
    public $kreirao;
    public $modified;
    public $modifikovao;
    public $deleted;
    public $obrisao;

   public function __construct($db)
    {
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
            $this->created = date('Y-m-d H:i:s');
           // insert query
            $query = "INSERT INTO
                " . $this->table_name . "
            SET 
                datum = :datum,
                od = :od,
                do = :do,
                platilac = :platilac,
                fk_moje_pravno_lice = :fk_moje_pravno_lice,
                eng = :eng,
                vrsta_platioca = :vrsta_platioca,
                struktura_profakture = :struktura_profakture,
                komentar = :komentar,
                created = :created,
                kreirao = :kreirao,
                active = :active
                ";

            // prepare the query
            $stmt = $this->conn->prepare($query);
            // sanitize
            $this->komentar = htmlspecialchars(strip_tags($this->komentar));
         
            // bind the values
            $stmt->bindParam(':datum', $this->datum);
            $stmt->bindParam(':od', $this->od);
            $stmt->bindParam(':do', $this->do);
            $stmt->bindParam(':platilac', $this->platilac);
            $stmt->bindParam(':fk_moje_pravno_lice', $this->fk_moje_pravno_lice);
            $stmt->bindParam(':eng', $this->eng);
            $stmt->bindParam(':vrsta_platioca', $this->vrsta_platioca);
            $stmt->bindParam(':struktura_profakture', $this->struktura_profakture);
            $stmt->bindParam(':komentar', $this->komentar);
            $stmt->bindParam(':created', $this->created);
            $stmt->bindParam(':kreirao', $this->kreirao);
            $stmt->bindParam(':active', $this->active);


            // execute the query, also check if query was successful
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            } else {
                $this->showError($stmt);
                return false;
            }
    }


    function read_one($id)
    {

        // query to select all user records
        $query = "SELECT * 
        FROM " . $this->table_name . "   
        
        WHERE `id` = $id  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }


    function read_last_one($id, $vrsta_platioca )
    {

        // query to select all user records
        $query = "SELECT * 
        FROM " . $this->table_name . "   
        
        WHERE `platilac` = $id AND active = 1 AND `vrsta_platioca` = $vrsta_platioca
        ORDER BY datum DESC  limit 1";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    function read_all_klijent( $platilac,  $vrsta_platioca)
    {

        // query to select all user records
        $query = "SELECT * 
        FROM " . $this->table_name . "   
        
        WHERE `platilac` = $platilac AND `vrsta_platioca` = $vrsta_platioca AND active = 1
        ORDER BY id DESC
        ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    function read_all()
    {

        // query to select all user records
        $query = "SELECT p.* , mpl.ime AS ime_mog_pravnog_lica
        FROM " . $this->table_name . " p
        INNER JOIN moja_pravna_lica mpl ON mpl.id =  p.fk_moje_pravno_lice 
        
        WHERE p.active = 1
        ORDER BY p.id DESC
        ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    function soft_delete($id_fakture, $obrisao){

        // to get time stamp for 'created' field
        $this->deleted=date('Y-m-d H:i:s');

        // insert query
        $query = "UPDATE
                " . $this->table_name . "
            SET
                active = :active,
                deleted = :deleted,
                obrisao = :obrisao
            WHERE id = :id_fakture   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize


       // $this->status=htmlspecialchars(strip_tags($this->status));
       // $this->deleted=htmlspecialchars(strip_tags($this->deleted));


        // bind the values
        $active_value = 0;
        $stmt->bindParam(':active', $active_value);
        $stmt->bindParam(':deleted', $this->deleted);
        $stmt->bindParam(':id_fakture', $id_fakture);
        $stmt->bindParam(':obrisao', $obrisao);
        // hash the password before saving to database

        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            $this->showError($stmt);
            return false;
        }

    }



    function read_all_prijem_novca_profesor_month($prof,$mesec)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
               WHERE `datum` LIKE '%$mesec%' && `platilac` = '$prof' ORDER by id
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }






}

