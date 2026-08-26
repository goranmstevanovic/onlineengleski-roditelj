<?php
/**
 * Created by PhpStorm.
 * User: goran
 * Date: 19 Feb 2020
 * Time: 11:35
 */

class faktura_zaduzenje1
{
    private $conn;
    private $table_name = "fakture_zaduzenja1";

    public $id;
    public $djak;
    public $fk_faktura;
    public $iznos;
    public $popust;
    public $ukupno;
    public $jedinicna_cena;
    public $komada;
   
    public $fk_djak;
    public $fk_nacin_zaduzivanja;
    public $fk_grupa;
    public $valuta;
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
                fk_faktura = :fk_faktura,
                iznos = :iznos,
                popust = :popust,
                ukupno = :ukupno,
                fk_djak = :fk_djak,
                jedinicna_cena = :jedinicna_cena,
                komada = :komada,
                fk_grupa = :fk_grupa,
                valuta = :valuta,
                fk_nacin_zaduzivanja = :fk_nacin_zaduzivanja,
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
            $stmt->bindParam(':fk_faktura', $this->fk_faktura);
            $stmt->bindParam(':iznos', $this->iznos);

            $stmt->bindParam(':popust', $this->popust);
            $stmt->bindParam(':ukupno', $this->ukupno);
          //  $stmt->bindParam(':iznos', $this->iznos);

          $stmt->bindParam(':jedinicna_cena', $this->jedinicna_cena);
          $stmt->bindParam(':komada', $this->komada);


            $stmt->bindParam(':fk_djak', $this->fk_djak);
            $stmt->bindParam(':fk_grupa', $this->fk_grupa);
            $stmt->bindParam(':valuta', $this->valuta);
            $stmt->bindParam(':fk_nacin_zaduzivanja', $this->fk_nacin_zaduzivanja);
            $stmt->bindParam(':komentar', $this->komentar);
            $stmt->bindParam(':created', $this->created);
            $stmt->bindParam(':kreirao', $this->kreirao);
            $stmt->bindParam(':active', $this->active);


            // execute the query, also check if query was successful
            if ($stmt->execute()) {
                return true;
            } else {
                $this->showError($stmt);
                return false;
            }
    }


    function read_all_faktura($fk_faktura )
    {

        // query to select all user records
        $query = "SELECT pz.* , dj.firstname AS djak_ime, dj.lastname AS djak_prezime, 
        nz.ime AS ime_nacina_zaduzivanja, dj.id AS id_djaka
        
        FROM " . $this->table_name . "   pz
        LEFT JOIN djaci dj ON dj.id = fk_djak
        LEFT JOIN nacini_zaduzivanja nz ON nz.id = pz.fk_nacin_zaduzivanja
        WHERE pz.fk_faktura = $fk_faktura AND pz.active = 1
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
               WHERE `fk_faktura` LIKE '%$mesec%' && `platilac` = '$prof' ORDER by id
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }








}

