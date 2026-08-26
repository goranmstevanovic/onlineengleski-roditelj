<?php
/**
 * Created by PhpStorm.
 * User: goran
 * Date: 11.10.2019
 * Time: 13:51
 */

class user
{
    // database connection and table name
    private $conn;
    private $table_name = "users";

    // object properties
    public $id;
    public $firstname;
    public $lastname;
    public $email;
    public $contact_number;
    public $address;
    public $password;
    public $old_password;
    public $access_level;
    public $access_code;
    public $status;
    public $created;
    public $modified;
    public $plata;
    public $iznos_individual;
    public $iznos_plate;
    public $color_prof;
    public $procentat_za_platu;
	public $procenat_za_platu;
    public $nacin_obracuna;
    public $fk_profesor;
    public $generete_date;
    public $end_date;

    // constructor
    public function __construct($db){
        $this->conn = $db;
    }

    // check if given email exist in the database
    // Funkcija za proveru da li email postoji u bazi
    function emailExists()
    {
        // Upit za proveru postojanja email-a
        $query = "SELECT id, firstname, lastname, access_level, password, status
            FROM " . $this->table_name . "
            WHERE email = ?
            LIMIT 0,1";

        // Priprema upita
        $stmt = $this->conn->prepare($query);

        // Sanitizacija
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Povezivanje vrednosti email-a
        $stmt->bindParam(1, $this->email);

        // Izvršavanje upita
        $stmt->execute();

        // Dobijanje broja redova
        $num = $stmt->rowCount();

        // Ako email postoji, dodeljuju se vrednosti svojstvima klase
        if ($num > 0) {
            // Preuzimanje detalja iz baze
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Dodela vrednosti svojstvima klase
            $this->id = $row['id'];
            $this->firstname = $row['firstname'];
            $this->lastname = $row['lastname'];
            $this->access_level = $row['access_level'];
            $this->password = $row['password'];
            $this->status = $row['status'];

            // Vraća true jer email postoji
            return true;
        }

        // Vraća false ako email ne postoji
        return false;
    }




    // create new user record
    function create(){

        // to get time stamp for 'created' field
        $this->created=date('Y-m-d H:i:s');

        // insert query
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                firstname = :firstname,
                lastname = :lastname,
                email = :email,
                contact_number = :contact_number,
                address = :address,
                procentat_za_platu = :procentat_za_platu,
                nacin_obracuna = :nacin_obracuna,
                password = :password,
                access_level = :access_level,
                status = :status,
                plata = :plata,
                iznos_plate = :iznos_plate,
                color_prof = :color_prof,
                created = :created";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->firstname=htmlspecialchars(strip_tags($this->firstname));
        $this->lastname=htmlspecialchars(strip_tags($this->lastname));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->contact_number=htmlspecialchars(strip_tags($this->contact_number));
        $this->address=htmlspecialchars(strip_tags($this->address));
        $this->password=htmlspecialchars(strip_tags($this->password));
        $this->access_level=htmlspecialchars(strip_tags($this->access_level));
        $this->status=htmlspecialchars(strip_tags($this->status));

        // bind the values
        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':contact_number', $this->contact_number);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':procentat_za_platu', $this->procentat_za_platu);
        $stmt->bindParam(':nacin_obracuna', $this->nacin_obracuna);
        $stmt->bindParam(':plata', $this->plata);
        $stmt->bindParam(':iznos_plate', $this->iznos_plate);

        // hash the password before saving to database
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(':password', $password_hash);

        $stmt->bindParam(':access_level', $this->access_level);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':created', $this->created);
        $stmt->bindParam(':color_prof', $this->color_prof);


        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            $this->showError($stmt);
            return false;
        }

    }
    public function showError($stmt){
        echo "<pre>";
        print_r($stmt->errorInfo());
        echo "</pre>";
    }

    // read all user records
    function readAll($from_record_num, $records_per_page)
    {

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
                *
            FROM " . $this->table_name . "
            WHERE status = TRUE
            ORDER BY access_level DESC
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

    function read_All()
    {

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
                *
            FROM " . $this->table_name . "
            WHERE status = TRUE
            ORDER BY access_level DESC
            ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
      

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }

    function read_All_prof()
    {

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
                *
            FROM " . $this->table_name . "
            WHERE status = TRUE && access_level = 'Customer'
            ORDER BY access_level DESC
            ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
      

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }


    function readAll_inactive($from_record_num, $records_per_page)
    {

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
                *
            FROM " . $this->table_name . "
            WHERE status = FALSE
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

    function readAllP($from_record_num, $records_per_page)
    {

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
                *
            FROM " . $this->table_name . "
            WHERE status = TRUE && access_level = 'Customer'
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

    function read_All_profesor()
    {

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
                *
            FROM " . $this->table_name . "
            WHERE status = TRUE && access_level = 'Customer'
            ORDER BY id DESC
            ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
       

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }

    // used for paging users
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

    function read1_profesor($table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				access_level = 'Customer'
				ORDER BY
                    id";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_jedan_profesor($id_prof,$table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				access_level = 'Customer' && status = TRUE && id = '$id_prof'
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_last_one_profesor($table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
               ORDER by id DESC limit 1
				
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }


    function read_name_user($id_user,$table_name)
    {
        //select all data
        $query = "SELECT
                    id, firstname, lastname
                FROM
                    " . $table_name . "
                WHERE
				status = TRUE && id = '$id_user'
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_profesor($table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				access_level = 'Customer' && status = 1
               
				ORDER BY
                    id";

        $sttmt = $this->conn->prepare( $query );
        $sttmt->execute();

        return $sttmt;
    }

    function count_profesor()
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE
				access_level = 'Customer' && status = 1
               
				ORDER BY
                    id";

        $sttmt = $this->conn->prepare( $query );
        $sttmt->execute();
        $num = $sttmt->rowCount();
    
            return $num;
        
    }

    function read_profesor1_svi($table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                
               
				ORDER BY
                    id";

        $sttmt = $this->conn->prepare( $query );
        $sttmt->execute();

        return $sttmt;
    }







    function read_one_profesor($idd,$table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				`id` = '$idd'
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function read_one( $idd )
    {
        //select all data
        $query = " SELECT * FROM `users` WHERE `id` = $idd
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    function update($idd){

        // to get time stamp for 'created' field
        $this->created=date('Y-m-d H:i:s');

        // insert query
        $query = "UPDATE
                " . $this->table_name . "
            SET
                firstname = :firstname,
                lastname = :lastname,
                email = :email,
                contact_number = :contact_number,
                address = :address,
                procentat_za_platu = :procentat_za_platu,
                nacin_obracuna = :nacin_obracuna,
                color_prof = :color_prof,
                plata = :plata,
                iznos_plate = :iznos_plate,
                status = :status
             WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->firstname=htmlspecialchars(strip_tags($this->firstname));
        $this->lastname=htmlspecialchars(strip_tags($this->lastname));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->contact_number=htmlspecialchars(strip_tags($this->contact_number));
        $this->address=htmlspecialchars(strip_tags($this->address));
        $this->status=htmlspecialchars(strip_tags($this->status));
        $this->color_prof=htmlspecialchars(strip_tags($this->color_prof));


        // bind the values
        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':contact_number', $this->contact_number);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':procentat_za_platu', $this->procentat_za_platu);
        $stmt->bindParam(':color_prof', $this->color_prof);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':plata', $this->plata);
        $stmt->bindParam(':iznos_plate', $this->iznos_plate);
        $stmt->bindParam(':nacin_obracuna', $this->nacin_obracuna);
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

    function update_pass($idd){

        // insert query
        $query = "UPDATE
                " . $this->table_name . "
            SET
                password = :password
               
             WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->password = htmlspecialchars(strip_tags($this->password));

        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':idd', $idd);

        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            $this->showError($stmt);
            return false;
        }

    }

    public function insert_procenat( $fk_profesor , $procentat_za_platu )
    {

        // to get time stamp for 'created' field
        $this->generete_date=date('Y-m-d');
       // $this->end_date = null;
        $this->fk_profesor = $fk_profesor;

        // insert query
        $query = "
            INSERT INTO `vremenski_intervali_procenat_plata`
            SET
                fk_profesor = :fk_profesor,
                procenat_za_platu = :procenat_za_platu,
                generete_date = :generete_date
             /*   end_date = :end_date */
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_profesor=htmlspecialchars(strip_tags($this->fk_profesor));
        $this->procenat_za_platu=htmlspecialchars(strip_tags($this->procenat_za_platu));
        $this->generete_date=htmlspecialchars(strip_tags($this->generete_date));
     //   $this->end_date=htmlspecialchars(strip_tags($this->end_date));

        // bind the values
        $stmt->bindParam(':fk_profesor', $this->fk_profesor);
        $stmt->bindParam(':procenat_za_platu', $this->procenat_za_platu);
        $stmt->bindParam(':generete_date', $this->generete_date);
      //  $stmt->bindParam(':end_date', $this->end_date);

        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            $this->showError($stmt);
            return false;
        }

    }

    public function insert_nacin_obracuna( $fk_profesor , $nacin_obracuna )
    {

        // to get time stamp for 'created' field
        $this->generete_date=date('Y-m-d');
        // $this->end_date = null;
        $this->fk_profesor = $fk_profesor;

        // insert query
        $query = "
            INSERT INTO `vremenski_intervali_vrsta_obracuna`
            SET
                fk_profesor = :fk_profesor,
                nacin_obracuna = :nacin_obracuna,
                generete_date = :generete_date
             /*   end_date = :end_date */
                ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->fk_profesor=htmlspecialchars(strip_tags($this->fk_profesor));
        $this->nacin_obracuna=htmlspecialchars(strip_tags($this->nacin_obracuna));
        $this->generete_date=htmlspecialchars(strip_tags($this->generete_date));
        //   $this->end_date=htmlspecialchars(strip_tags($this->end_date));

        // bind the values
        $stmt->bindParam(':fk_profesor', $this->fk_profesor);
        $stmt->bindParam(':nacin_obracuna', $this->nacin_obracuna);
        $stmt->bindParam(':generete_date', $this->generete_date);
        //  $stmt->bindParam(':end_date', $this->end_date);

        // execute the query, also check if query was successful
        if($stmt->execute()){
            return true;
        }else{
            $this->showError($stmt);
            return false;
        }

    }

    public function read_all_promene_procenat_jedan_profesor($id_prof,$table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				fk_profesor = '$id_prof'
				order by generete_date asc
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }

    public function read_all_promene_nacin_obracuna_jedan_profesor($id_prof,$table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE
				fk_profesor = '$id_prof'
				order by generete_date asc
				";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }



}