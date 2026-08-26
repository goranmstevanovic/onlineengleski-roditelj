<?php
/**
 * Created by PhpStorm.
 * User: goran
 * Date: 19 Feb 2020
 * Time: 11:35
 */

class tok_novca
{
    private $conn;
    private $table_name = "tokovi_novca";

    public $id;
    public $dan;
    public $fk_profesor;
    public $iznos_predao;
    public $komentar_iznos_predao;
    public $iznos_primio;
    public $iznos_primio2;
    public $komentar_iznos_primio;
    public $primio_racun;
    public $primio_racun2;
    public $created;
    public $upisao;
    public $aktivan;
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


     function create($dan, $fk_profesor)
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');


        $query_provera = "SELECT id FROM " . $this->table_name . "
        WHERE `dan` = '$dan' && `fk_profesor` = '$fk_profesor'
        ";

        // prepare query statement
        $stmt_provera = $this->conn->prepare($query_provera);

        // execute query
        $stmt_provera->execute();

        // get number of rows
        $num = $stmt_provera->rowCount();

        if ($num == 0) {

            // insert query
            $query = "INSERT INTO
                " . $this->table_name . "
            SET 
                dan = :dan,
                fk_profesor = :fk_profesor,
                iznos_predao = :iznos_predao,
                komentar_iznos_predao = :komentar_iznos_predao,
                iznos_primio = :iznos_primio,
                iznos_primio2 = :iznos_primio2,
                komentar_iznos_primio = :komentar_iznos_primio,
                primio_racun = :primio_racun,
                primio_racun2 = :primio_racun2,
                created = :created,
                upisao = :upisao,
                aktivan = :aktivan
                ";

            // prepare the query
            $stmt = $this->conn->prepare($query);

            // sanitize
            $this->komentar_iznos_predao = htmlspecialchars(strip_tags($this->komentar_iznos_predao));
            $this->komentar_iznos_primio = htmlspecialchars(strip_tags($this->komentar_iznos_primio));
            $this->iznos_predao = htmlspecialchars(strip_tags($this->iznos_predao));
            $this->iznos_primio = htmlspecialchars(strip_tags($this->iznos_primio));

            // bind the values
            $stmt->bindParam(':dan', $this->dan);
            $stmt->bindParam(':fk_profesor', $this->fk_profesor);
            $stmt->bindParam(':iznos_predao', $this->iznos_predao);
            $stmt->bindParam(':komentar_iznos_predao', $this->komentar_iznos_predao);
            $stmt->bindParam(':iznos_primio', $this->iznos_primio);
            $stmt->bindParam(':iznos_primio2', $this->iznos_primio2);
            $stmt->bindParam(':komentar_iznos_primio', $this->komentar_iznos_primio);
            $stmt->bindParam(':primio_racun', $this->primio_racun);
            $stmt->bindParam(':primio_racun2', $this->primio_racun2);
            $stmt->bindParam(':created', $this->created);
            $stmt->bindParam(':upisao', $this->upisao);
            $stmt->bindParam(':aktivan', $this->aktivan);


            // execute the query, also check if query was successful
            if ($stmt->execute()) {
                return true;
            } else {
                $this->showError($stmt);
                return false;
            }



        } else {

            $query = "UPDATE
                " . $this->table_name . "
            SET 
                fk_profesor = :fk_profesor,
                iznos_predao = :iznos_predao,
                komentar_iznos_predao = :komentar_iznos_predao,
                iznos_primio = :iznos_primio,
                iznos_primio2 = :iznos_primio2,
                komentar_iznos_primio = :komentar_iznos_primio,
                primio_racun = :primio_racun,
                primio_racun2 = :primio_racun2,
                created = :created,
                upisao = :upisao,
                aktivan = :aktivan
                WHERE `dan` = '$dan' && `fk_profesor` = '$fk_profesor'
                  ";

            // prepare the query
            $stmt = $this->conn->prepare($query);

            // sanitize
            $this->komentar_iznos_predao = htmlspecialchars(strip_tags($this->komentar_iznos_predao));
            $this->komentar_iznos_primio = htmlspecialchars(strip_tags($this->komentar_iznos_primio));
            $this->iznos_predao = htmlspecialchars(strip_tags($this->iznos_predao));
            $this->iznos_primio = htmlspecialchars(strip_tags($this->iznos_primio));

            // bind the values
            $stmt->bindParam(':fk_profesor', $this->fk_profesor);
            $stmt->bindParam(':iznos_predao', $this->iznos_predao);
            $stmt->bindParam(':komentar_iznos_predao', $this->komentar_iznos_predao);
            $stmt->bindParam(':iznos_primio', $this->iznos_primio);
            $stmt->bindParam(':iznos_primio2', $this->iznos_primio2);
            $stmt->bindParam(':komentar_iznos_primio', $this->komentar_iznos_primio);
            $stmt->bindParam(':primio_racun', $this->primio_racun);
            $stmt->bindParam(':primio_racun2', $this->primio_racun2);
            $stmt->bindParam(':created', $this->created);
            $stmt->bindParam(':upisao', $this->upisao);
            $stmt->bindParam(':aktivan', $this->aktivan);


            // execute the query, also check if query was successful
            if ($stmt->execute()) {
                return true;
            } else {
                $this->showError($stmt);
                return false;
            }


        }






    }


    function read_one_day_one_profesor($dan1, $fk_profesor1)
    {

        // query to select all user records
        $query = "SELECT * FROM " . $this->table_name . "   WHERE `dan` = '$dan1' && `fk_profesor` = '$fk_profesor1'  ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // execute query
        $stmt->execute();

        return $stmt;

    }

    function read_all_prijem_novca_profesor_month($prof,$mesec)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
               WHERE `dan` LIKE '%$mesec%' && `fk_profesor` = '$prof' ORDER by id
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }






}

