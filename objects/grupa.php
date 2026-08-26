<?php
/**
 * Created by PhpStorm.
 * User: goran
 * Date: 5.11.2019
 * Time: 17:23
 */

class grupa
{
    private $conn;
    private $table_name = "grupe";

    public $id;
    public $fk_jezik;
    public $nivo;
    public $fk_profesor;
    public $komentar;
    public $alias;
    public $status;
    public $created;
    public $velicina;
    public $nacin;
    public $fk_nacin_zaduzivanja;
    public $fk_lokacija;
    public $vrtic;
    public $uzrast;
    public $isplata_profesoru;
    public $procenat_za_platu;
    public $fk_program_rada;

    public function __construct($db){
        $this->conn = $db;
    }

    function create()
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');

        // insert query
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                fk_jezik = :fk_jezik,
                nivo = :nivo,
                fk_profesor = :fk_profesor,
                fk_lokacija = :fk_lokacija,
                vrtic = :vrtic,
                komentar = :komentar,
                isplata_profesoru = :isplata_profesoru,
                status = :status,
                fk_program_rada = :fk_program_rada,
                alias = :alias,
                created = :created,
                velicina = :velicina,
                nacin = :nacin,
                fk_nacin_zaduzivanja =:fk_nacin_zaduzivanja,
                uzrast = :uzrast
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_jezik = htmlspecialchars(strip_tags($this->fk_jezik));
        $this->nivo = htmlspecialchars(strip_tags($this->nivo));
        $this->fk_profesor = htmlspecialchars(strip_tags($this->fk_profesor));
        $this->komentar = htmlspecialchars(strip_tags($this->komentar));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->isplata_profesoru = htmlspecialchars(strip_tags($this->isplata_profesoru));


        // bind the values
        $stmt->bindParam(':fk_jezik', $this->fk_jezik);
        $stmt->bindParam(':nivo', $this->nivo);
        $stmt->bindParam(':fk_profesor', $this->fk_profesor);
        $stmt->bindParam(':alias', $this->alias);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':fk_program_rada', $this->fk_program_rada);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':velicina', $this->velicina);
        $stmt->bindParam(':nacin', $this->nacin);
        $stmt->bindParam(':fk_nacin_zaduzivanja', $this->fk_nacin_zaduzivanja);
        $stmt->bindParam(':uzrast', $this->uzrast);
        $stmt->bindParam(':isplata_profesoru', $this->isplata_profesoru);
        $stmt->bindParam(':fk_lokacija', $this->fk_lokacija);
        $stmt->bindParam(':vrtic', $this->vrtic);

        // execute the query, also check if query was successful
        if ($stmt->execute()) {
           // return true;
           return $this->conn->lastInsertId();
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

    function read_all()
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `status` = TRUE
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_online()
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `status` = TRUE && `nacin` = 2
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_moji($profa)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `status` = TRUE && `fk_profesor` = $profa
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_skola()
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `status` = TRUE && `nacin` = 1
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_skola_lokacija($loc)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                " . $this->table_name . "
                WHERE `status` = TRUE && `nacin` = 1 && `fk_lokacija` = $loc
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }



    function read_all_grups_profesor($iid, $table_name )
    {
        //select all data



        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
               WHERE `fk_profesor` = '$iid' && `status` = TRUE ORDER by id
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_all_grups_profesor_prevod($iid, $table_name )
    {
        //select all data



        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
               WHERE `fk_profesor` = '$iid' && `status` = TRUE && `prevod` = TRUE ORDER by id
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function readAll_grupa($from_record_num, $records_per_page){

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
                *
            FROM " . $this->table_name . "
            ORDER BY id DESC
            LIMIT ?, ?";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
        $stmt->bindParam(1, $from_record_num, PDO::PARAM_INT);
        $stmt->bindParam(2, $records_per_page, PDO::PARAM_INT);

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }
    public function countAll_grupa(){

        // query to select all user records
        $query = "SELECT id FROM " . $this->table_name . "";

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get number of rows
        $num = $stmt->rowCount();

        // return row count
        return $num;
    }

   

    public function countAll(){

        // query to select all user records
        $query = "SELECT id FROM " . $this->table_name . "  WHERE status = TRUE ";

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        // get number of rows
        $num = $stmt->rowCount();

        // return row count
        return $num;
    }
    function read_one_grupa($idd,$table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				`id` = '$idd'"
				;

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_one($idd)
    {
        //select all data
        $query = "SELECT
                   g.*, j.alias AS ime_jezika,j.ime AS imme_jezika, 
                   nz.ime AS ime_nivoa, u.ime AS ime_uzrasta, v.ime AS ime_velicine, pr.ime AS ime_programa
                FROM
                    " . $this->table_name . " g
                INNER JOIN jezik j ON j.id = g.fk_jezik
                INNER JOIN nivo_znanja nz ON nz.id = g.nivo
                INNER JOIN uzrasti u ON u.id = g.uzrast
                INNER JOIN velicina v ON g.velicina = v.id
                LEFT JOIN  programi_rada pr ON g.fk_program_rada = pr.id
                WHERE
				g.`id` = '$idd'
                ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
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
                fk_jezik = :fk_jezik,
                nivo = :nivo,
                fk_profesor = :fk_profesor,
                fk_lokacija = :fk_lokacija,
                vrtic = :vrtic,
                alias = :alias,
                komentar = :komentar,
                isplata_profesoru = :isplata_profesoru,
                procenat_za_platu = :procenat_za_platu,
                status = :status,
                fk_program_rada = :fk_program_rada,
                velicina = :velicina,
                nacin = :nacin,
                fk_nacin_zaduzivanja =:fk_nacin_zaduzivanja,
                uzrast = :uzrast
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_jezik=htmlspecialchars(strip_tags($this->fk_jezik));
        $this->nivo=htmlspecialchars(strip_tags($this->nivo));
        $this->fk_profesor=htmlspecialchars(strip_tags($this->fk_profesor));
        $this->alias=htmlspecialchars(strip_tags($this->alias));
        $this->komentar=htmlspecialchars(strip_tags($this->komentar));
        $this->status=htmlspecialchars(strip_tags($this->status));
        $this->velicina=htmlspecialchars(strip_tags($this->velicina));
        $this->nacin=htmlspecialchars(strip_tags($this->nacin));
        $this->isplata_profesoru = htmlspecialchars(strip_tags($this->isplata_profesoru));


        // bind the values
        $stmt->bindParam(':fk_jezik', $this->fk_jezik);
        $stmt->bindParam(':nivo', $this->nivo);
        $stmt->bindParam(':fk_profesor', $this->fk_profesor);
        $stmt->bindParam(':alias', $this->alias);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':procenat_za_platu', $this->procenat_za_platu);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':fk_program_rada', $this->fk_program_rada);
        $stmt->bindParam(':velicina', $this->velicina);
        $stmt->bindParam(':nacin', $this->nacin);
        $stmt->bindParam(':fk_nacin_zaduzivanja', $this->fk_nacin_zaduzivanja);
        $stmt->bindParam(':uzrast', $this->uzrast);
        $stmt->bindParam(':fk_lokacija', $this->fk_lokacija);
        $stmt->bindParam(':vrtic', $this->vrtic);
        $stmt->bindParam(':isplata_profesoru', $this->isplata_profesoru);
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

    function update_profesor($idd)
    {
      //  $this->procenat_za_platu = $procenat_za_platu;

        // to get time stamp for 'created' field
        $this->created=date('Y-m-d H:i:s');
                // update query
        $query = "UPDATE
                " . $this->table_name . "
            SET
                fk_jezik = :fk_jezik,
                nivo = :nivo,
                fk_profesor = :fk_profesor,
                fk_lokacija = :fk_lokacija,
                alias = :alias,
                komentar = :komentar,
                vrtic = :vrtic,
                procenat_za_platu = :procenat_za_platu,
                 fk_program_rada = :fk_program_rada,
                status = :status,
                velicina = :velicina,
                nacin = :nacin,
                uzrast = :uzrast
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_jezik=htmlspecialchars(strip_tags($this->fk_jezik));
        $this->nivo=htmlspecialchars(strip_tags($this->nivo));
        $this->fk_profesor=htmlspecialchars(strip_tags($this->fk_profesor));
        $this->alias=htmlspecialchars(strip_tags($this->alias));
        $this->komentar=htmlspecialchars(strip_tags($this->komentar));
        $this->status=htmlspecialchars(strip_tags($this->status));
        $this->velicina=htmlspecialchars(strip_tags($this->velicina));
        $this->nacin=htmlspecialchars(strip_tags($this->nacin));
        


        // bind the values
        $stmt->bindParam(':fk_jezik', $this->fk_jezik);
        $stmt->bindParam(':nivo', $this->nivo);
        $stmt->bindParam(':fk_profesor', $this->fk_profesor);
        $stmt->bindParam(':alias', $this->alias);
        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':procenat_za_platu', $this->procenat_za_platu);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':fk_program_rada', $this->fk_program_rada);
        $stmt->bindParam(':velicina', $this->velicina);
        $stmt->bindParam(':nacin', $this->nacin);
        $stmt->bindParam(':uzrast', $this->uzrast);
        $stmt->bindParam(':fk_lokacija', $this->fk_lokacija);
        $stmt->bindParam(':vrtic', $this->vrtic);
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




    function read_allgrupa($table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				status = TRUE    
                ORDER BY `id` DESC";

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }

    function read_allgrupa_lokacija($loc)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE
				status = TRUE && fk_lokacija = $loc && nacin = 1   
                ORDER BY `id` DESC";

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }

    function read_allgrupa_lokacija_profesor($loc, $profesor)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE
				status = TRUE && fk_lokacija = $loc && nacin = 1   &&  fk_profesor = $profesor
                ORDER BY `id` DESC";

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }

    function read_allgrupa_lokacija_profesor_onlineplus($loc, $profesor)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE
				status = TRUE && ((fk_lokacija = $loc && nacin = 1) || (nacin = 2))   &&  fk_profesor = $profesor
                ORDER BY `id` DESC";

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }

    function read_allgrupa_online($table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				status = TRUE && nacin = 2    
                ORDER BY `id` DESC";

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }

    function read_allgrupa_online_profesor($profesor)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE
				status = TRUE && nacin = 2 &&  fk_profesor = $profesor 
                ORDER BY `id` DESC";

              

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }

    function read_allgrupa_inactive($table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				status = FALSE    
                ORDER BY id ";

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }

	function read_allgrupa_profesor($table_name, $profesor)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				status = TRUE && fk_profesor = '$profesor'     
                ORDER BY id desc";

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }

    function read_allgrupa_profesor_lokacija($table_name, $profesor, $lokacija)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				status = TRUE && fk_profesor = '$profesor' && fk_lokacija = $lokacija && nacin = 1    
                ORDER BY id desc";

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }

    function read_allgrupa_osim($table_name, $osim)
    {
        //select all data
        $query = "SELECT g.*, l.ime AS ime_lokacije
                FROM
                    " . $table_name . " g
                LEFT JOIN lokacije l ON l.id = g.fk_lokacija
                WHERE
				g.status = 1 && g.id not in ( '" . implode( "', '" , $osim ) . "' )
                ORDER BY g.id ";

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }

    function read_allgrupa_osim_lokacija($table_name, $osim, $loc)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				status = 1 && id not in ( '" . implode( "', '" , $osim ) . "' ) && ((`fk_lokacija` = '$loc' and `nacin` = 1) || (`nacin` = 2))
                ORDER BY id ";

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }

    


    function read_allgrupa_osim_prof($table_name, $osim, $profesor)
    {
        //select all data
        $query = "SELECT g.*, l.ime AS ime_lokacije, nz.ime AS ime_nivoa, j.ime AS ime_jezika
                FROM
                    " . $table_name . " g
                LEFT JOIN lokacije l ON l.id = g.fk_lokacija 
                LEFT JOIN nivo_znanja nz ON nz.id = g.nivo
                INNER JOIN jezik j ON j.id = g.fk_jezik     
                WHERE
				g.status = 1 && g.id not in ( '" . implode( "', '" , $osim ) . "' ) && fk_profesor = $profesor
                ORDER BY g.id ";

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }


    function read_allgrupa_samo($table_name, $osim)
    {
        //select all data
        $query = "SELECT g.*, l.ime AS ime_lokacije, nz.ime AS ime_nivoa, j.ime AS ime_jezika
                FROM
                    " . $table_name . " g
                LEFT JOIN lokacije l ON l.id = g.fk_lokacija 
                LEFT JOIN nivo_znanja nz ON nz.id = g.nivo
                INNER JOIN jezik j ON j.id = g.fk_jezik 
                
                WHERE
				/* g.status = 1 && */ g.id in ( '" . implode( "', '" , $osim ) . "' )
                ORDER BY g.id DESC ";

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }


    function readAll($from_record_num, $records_per_page)
    {

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
                *
            FROM " . $this->table_name . "
            WHERE status = 1
            ORDER BY id DESC
            ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
        $stmt->bindParam(1, $from_record_num, PDO::PARAM_INT);
        $stmt->bindParam(2, $records_per_page, PDO::PARAM_INT);

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }

    function read_one_grupa1($group_id)
    {
        //select all data
        $query = "SELECT g.*, nz.ime AS ime_nivoa, j.ime AS ime_jezika
                FROM
                    grupe g
                LEFT JOIN nivo_znanja nz ON nz.id = g.nivo
                INNER JOIN jezik j ON j.id = g.fk_jezik
                WHERE
				g.id = $group_id    
                ORDER BY g.id ";

        $stttmt = $this->conn->prepare( $query );
        $stttmt->execute();

        return $stttmt;
    }

    function read_one_grupa_event($idd)
    {
        //select all data
        $query = "SELECT
                    " . $this->table_name . ".alias, jezik.alias as ime_jezika, nivo_znanja.ime as ime_nivo_znanja
                FROM
                    " . $this->table_name . "
                INNER JOIN jezik ON  " . $this->table_name . ".fk_jezik = jezik.id    
                INNER JOIN nivo_znanja ON  " . $this->table_name . ".nivo = nivo_znanja.id   
                WHERE
				" . $this->table_name . ".id = $idd
                ";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }




}