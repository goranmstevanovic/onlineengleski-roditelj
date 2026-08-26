<?php
/**
 * Created by PhpStorm.
 * User: goran
 * Date: 15.10.2019
 * Time: 10:15
 */

class djak
{
    private $conn;
    private $table_name = "djaci";

    // object properties
    public $id;
    public $firstname;
    public $lastname;
    public $email;
    public $contact_number;
    public $address;
    public $dat_rodjenja;
    public $komentar;
    public $status;
    public $created;
    public $modified;
    public $komenatr_finansije;
    public $online;
    public $fk_lokacija;
    public $fk_porodica;
    public $role;
    public $password;
    public $photo;
    public $email_verified_at;

    // constructor
    public function __construct($db){
        $this->conn = $db;
    }

      function emailExists()
    {
        // Upit za proveru postojanja email-a
        $query = "SELECT id, firstname, lastname, role, password, status, email_verified_at
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
            $this->role = $row['role'];
            $this->password = trim($row['password']);
            $this->status = $row['status'];
            $this->email_verified_at = $row['email_verified_at'];

            // Vraća true jer email postoji
            return true;
        }

        // Vraća false ako email ne postoji
        return false;
    }

    // novi pacijent zapis
    function create_djak()
    {

        // to get time stamp for 'created' field
        $this->created = date('Y-m-d H:i:s');

        // insert query
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                firstname = :firstname,
                lastname = :lastname,
                email = :email,
                contact_number = :contact_number,
                address = :address,
                dat_rodjenja = :dat_rodjenja,
                komentar = :komentar,
                online = :online,
                status = :status,
                fk_lokacija = :fk_lokacija,
                fk_porodica = :fk_porodica,
                komenatr_finansije = :komenatr_finansije,
                created = :created";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->firstname = htmlspecialchars(strip_tags($this->firstname));
        $this->lastname = htmlspecialchars(strip_tags($this->lastname));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->contact_number = htmlspecialchars(strip_tags($this->contact_number));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->komenatr_finansije = htmlspecialchars(strip_tags($this->komenatr_finansije));

        // bind the values
        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
           // Provera da li je email prazan
        if (empty($this->email)) {
            $stmt->bindValue(':email', null, PDO::PARAM_NULL); // Ako je prazan, postavlja se na NULL
        } else {
            $stmt->bindParam(':email', $this->email); // Ako nije prazan, veže se normalno
        }
        $stmt->bindParam(':contact_number', $this->contact_number);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':online', $this->online);
        $stmt->bindParam(':dat_rodjenja', $this->dat_rodjenja);
        $stmt->bindParam(':komenatr_finansije', $this->komenatr_finansije);
        $stmt->bindParam(':fk_lokacija', $this->fk_lokacija);
        $stmt->bindParam(':fk_porodica', $this->fk_porodica);

        $stmt->bindParam(':komentar', $this->komentar);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':created', $this->created);

        // execute the query, also check if query was successful
        if ($stmt->execute()) {
           // return true;
            return $this->conn->lastInsertId();
           // $lastid=$this->conn->lastInsertId();
        } else {
            $this->showError($stmt);
            return false;
        }
    }
        public function showError($stmt){
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
                   
                    ";
    
            $sttmt = $this->conn->prepare( $query );
            $sttmt->execute();
    
            return $sttmt;
        } 

        function read_all_bez_porodice()
        {
            //select all data
            $query = "SELECT
                        *
                    FROM
                        " . $this->table_name . "  

                    WHERE  fk_porodica = 0 AND status = 1                 
                    ";
    
            $sttmt = $this->conn->prepare( $query );
            $sttmt->execute();
    
            return $sttmt;
        } 
        
        function read_all_lokacija($loc)
        {
            //select all data
            $query = "SELECT
                        *
                    FROM
                        " . $this->table_name . "  
                     WHERE `status` = 1 && `fk_lokacija` = $loc    
                   
                    ";
    
            $sttmt = $this->conn->prepare( $query );
            $sttmt->execute();
    
            return $sttmt;
        } 
        
        function read_all_porodica($porodica)
        {
            //select all data
            $query = "SELECT
                        *
                    FROM
                        " . $this->table_name . "  
                     WHERE `status` = 1 && `fk_porodica` = $porodica    
                   
                    ";
    
            $sttmt = $this->conn->prepare( $query );
            $sttmt->execute();
    
            return $sttmt;
        } 

        function read_all_all_porodica($porodica)
        {
            //select all data
            $query = "SELECT
                        *
                    FROM
                        " . $this->table_name . "  
                     WHERE  `fk_porodica` = $porodica    
                   
                    ";
    
            $sttmt = $this->conn->prepare( $query );
            $sttmt->execute();
    
            return $sttmt;
        } 
        
    

        

    function read_djak($table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
               
				";

        $sttmt = $this->conn->prepare( $query );
        $sttmt->execute();

        return $sttmt;
    }
    function read_all_aktivni($table_name)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE `status` = 1
               
				";

        $sttmt = $this->conn->prepare( $query );
        $sttmt->execute();

        return $sttmt;
    }

    function read_one_djak($table_name, $id)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $table_name . "
                WHERE id = '$id' /* && status = TRUE */
				";

        $sttmt = $this->conn->prepare( $query );
        $sttmt->execute();

        return $sttmt;
    }

    function read_one ($id)
    {
        //select all data
        $query = "SELECT
                    *
                FROM
                    " . $this->table_name . "
                WHERE id = $id /* && status = 1 */
                ";

        $sttmt = $this->conn->prepare( $query );
        $sttmt->execute();

        return $sttmt;
    }

    function readAll_djak(){

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
               " . $this->table_name . ".*
            FROM " . $this->table_name . "
            INNER JOIN lokacije ON lokacije.id = " . $this->table_name . ".fk_lokacija
            WHERE " . $this->table_name . ".status = 1 && lokacije.active = 1
            ORDER BY " . $this->table_name . ".created DESC
            ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
      //  $stmt->bindParam(1, $from_record_num, PDO::PARAM_INT);
      //  $stmt->bindParam(2, $records_per_page, PDO::PARAM_INT);

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }

    function read_all_nezaduzen(){

        // query to read all user records, with limit clause for pagination
        // $query = " SELECT dj.*
        // FROM djaci dj
        // LEFT JOIN povezivanje p ON p.fk_djak = dj.id
        // LEFT JOIN grupe g ON g.id = p.fk_grupa
        // LEFT JOIN zaduzenja z ON z.fk_djak = dj.id
        // WHERE dj.status = 1
        // AND p.status = 1
        // AND g.fk_nacin_zaduzivanja = 1
        // AND g.status = 1
        // GROUP BY dj.id
        // HAVING COALESCE(SUM(z.iznos), 0) = 0;
        // ";

        $query = " SELECT dj.*
        FROM djaci dj
        LEFT JOIN povezivanje p ON p.fk_djak = dj.id
        LEFT JOIN grupe g ON g.id = p.fk_grupa
        LEFT JOIN zaduzenja z ON z.fk_djak = dj.id AND z.fk_grupa = g.id
        WHERE dj.status = 1
        AND p.status = 1
        AND g.fk_nacin_zaduzivanja = 1
        AND g.status = 1
        /*AND z.aktivan = 1 */
        GROUP BY dj.id
        HAVING COUNT(DISTINCT g.id) = SUM(CASE WHEN z.aktivan IS NULL THEN 1 ELSE 0 END); 
        
        
        ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
      //  $stmt->bindParam(1, $from_record_num, PDO::PARAM_INT);
      //  $stmt->bindParam(2, $records_per_page, PDO::PARAM_INT);

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }

    function readAll_djak_prof($prof){

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
               *
            FROM " . $this->table_name . "
            WHERE status = 1
            ORDER BY `created` DESC
            ";

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
      //  $stmt->bindParam(1, $from_record_num, PDO::PARAM_INT);
      //  $stmt->bindParam(2, $records_per_page, PDO::PARAM_INT);

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }





    function readAll_djak_online(){

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
        " . $this->table_name . ".*
     FROM " . $this->table_name . " 
         INNER JOIN povezivanje ON " . $this->table_name . ".id = povezivanje.fk_djak
         INNER JOIN grupe ON grupe.id = povezivanje.fk_grupa
     WHERE " . $this->table_name . ".status = true && grupe.nacin = 1
     ORDER BY id ASC
     ";
            

        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
       

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }

    function readAll_djak_skola_lokacija($loc){

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
               *
            FROM " . $this->table_name . "
            WHERE status = TRUE && fk_lokacija = $loc 
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

    function readAll_djak_skola(){

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
        " . $this->table_name . ".*
     FROM " . $this->table_name . " 
         INNER JOIN povezivanje ON  povezivanje.fk_djak = " . $this->table_name . ".id
         INNER JOIN grupe ON grupe.id = povezivanje.fk_grupa
     WHERE " . $this->table_name . ".status = true && grupe.nacin = 2
     ORDER BY id ASC
     ";


        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
        

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }
	
	function readAll_djak_skola_skola_prof($prof){

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
        " . $this->table_name . ".*
     FROM " . $this->table_name . " 
         INNER JOIN povezivanje ON  povezivanje.fk_djak = " . $this->table_name . ".id
         INNER JOIN grupe ON grupe.id = povezivanje.fk_grupa
     WHERE " . $this->table_name . ".status = true && grupe.nacin = 1 && grupe.fk_profesor = $prof && grupe.status = true
     ORDER BY " . $this->table_name . ".id ASC
     ";


        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
        

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }
	
	function readAll_djak_skola_online_prof($prof){

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
        " . $this->table_name . ".*
     FROM " . $this->table_name . " 
         INNER JOIN povezivanje ON  povezivanje.fk_djak = " . $this->table_name . ".id
         INNER JOIN grupe ON grupe.id = povezivanje.fk_grupa
     WHERE " . $this->table_name . ".status = true && grupe.nacin = 2 && grupe.fk_profesor = $prof && grupe.status = true
     ORDER BY " . $this->table_name . ".id ASC
     ";


        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
        

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }

    function read_all_djak_grupa_cas($grupa, $start, $termin_id){

        // query to read all user records, with limit clause for pagination
        $query = "SELECT  dj.* , e.prisutan AS bio_prisutan, e.opravdao_otsustvo AS opravdao_otsustvo 
         FROM " . $this->table_name . " dj
         INNER JOIN povezivanje p ON  p.fk_djak = dj.id
         INNER JOIN grupe g ON g.id = p.fk_grupa
         INNER JOIN evidencija e ON e.djak_id = dj.id 
     WHERE  p.fk_grupa = $grupa && /* e.termin_id = $termin_id AND */
     ((p.created < '$start' AND p.deleted IS NULL)  OR (p.created < '$start' AND p.deleted > '$start' ) ) 
     Group by dj.id
     ORDER BY dj.id ASC
     ";


        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
        

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }

    function readAll_djak_all_prof($prof){

        // query to read all user records, with limit clause for pagination
        $query = "SELECT dj.*
     FROM " . $this->table_name . " dj
         INNER JOIN povezivanje p ON  p.fk_djak = dj.id
         LEFT JOIN grupe g ON g.id = p.fk_grupa
        WHERE dj.status = true && g.fk_profesor = $prof  && g.status = true 
        group BY  dj.id
        ORDER BY dj.id DESC
     ";


        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
        

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }

    
    function read_all_djak_prof($prof){

        // query to read all user records, with limit clause for pagination
        $query = "SELECT dj.*
     FROM " . $this->table_name . " dj
         INNER JOIN povezivanje p ON  p.fk_djak = dj.id
         LEFT JOIN grupe g ON g.id = p.fk_grupa
        WHERE dj.status = true && g.fk_profesor = $prof  && g.status = true && p.status = 1
        group BY  dj.id
        ORDER BY dj.id DESC
     ";


        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
        

        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }

    function count_all_djak_prof($prof){

        $query = "
            SELECT COUNT(DISTINCT dj.id) AS ukupno
            FROM " . $this->table_name . " dj
            INNER JOIN povezivanje p ON p.fk_djak = dj.id
            LEFT JOIN grupe g ON g.id = p.fk_grupa
            WHERE dj.status = 1
            AND g.fk_profesor = :prof
            AND g.status = 1
            AND p.status = 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':prof', $prof, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }


    function readAll_djak_neaktivan(){

        // query to read all user records, with limit clause for pagination
        $query = "SELECT
                *
                FROM
               " . $this->table_name . "
           
            WHERE status = 0
            ORDER BY id ASC
            ";

            
        // prepare query statement
        $stmt = $this->conn->prepare( $query );

        // bind limit clause variables
       
        // execute query
        $stmt->execute();

        // return values
        return $stmt;
    }
	
	

    public function countAll_djak(){

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

function update($idd){

    // sanitize (zadržaćemo tvoj stil)
    $this->firstname = htmlspecialchars(strip_tags(trim($this->firstname)));
    $this->lastname = htmlspecialchars(strip_tags(trim($this->lastname)));
    $this->email = htmlspecialchars(strip_tags(trim($this->email)));
    $this->contact_number = htmlspecialchars(strip_tags(trim($this->contact_number)));
    $this->address = htmlspecialchars(strip_tags(trim($this->address)));

    // ❗ EMAIL OBAVEZAN
    if(empty($this->email)){
        echo "<div class='alert alert-danger'>Email je obavezan.</div>";
        return false;
    }

    // ❗ VALIDACIJA EMAILA
    if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
        echo "<div class='alert alert-danger'>Email nije validan.</div>";
        return false;
    }

    // ❗ PROVERA DUPLIKATA (osim sebe)
    $checkQuery = "SELECT id FROM " . $this->table_name . " 
                   WHERE email = :email AND id != :id LIMIT 1";

    $checkStmt = $this->conn->prepare($checkQuery);
    $checkStmt->bindParam(":email", $this->email);
    $checkStmt->bindParam(":id", $idd);
    $checkStmt->execute();

    if($checkStmt->rowCount() > 0){
        echo "<div class='alert alert-danger'>Email već postoji.</div>";
        return false;
    }

    // 🧱 UPDATE samo relevantnih polja
    $query = "UPDATE " . $this->table_name . " SET
                firstname = :firstname,
                lastname = :lastname,
                email = :email,
                contact_number = :contact_number,
                address = :address,
                photo = :photo
              WHERE id = :idd";

    $stmt = $this->conn->prepare($query);

    // bind
    $stmt->bindParam(':firstname', $this->firstname);
    $stmt->bindParam(':lastname', $this->lastname);
    $stmt->bindParam(':email', $this->email);
    $stmt->bindParam(':contact_number', $this->contact_number);
    $stmt->bindParam(':address', $this->address);
    $stmt->bindParam(':photo', $this->photo);
    $stmt->bindParam(':idd', $idd);

    // execute
    if($stmt->execute()){
        return true;
    } else {
        $this->showError($stmt);
        return false;
    }
}

    function seed_update_pacijent($idd){

        // to get time stamp for 'created' field
        $this->created=date('Y-m-d H:i:s');

        // insert query
        $query = "UPDATE
                " . $this->table_name . "
            SET
                firstname = :firstname,
                lastname = :lastname,
                contact_number = :contact_number,
                address = :address
				
            WHERE id = :idd   
               ";

        // prepare the query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->firstname=htmlspecialchars(strip_tags($this->firstname));
        $this->lastname=htmlspecialchars(strip_tags($this->lastname));
      //  $this->email=htmlspecialchars(strip_tags($this->email));
        $this->contact_number=htmlspecialchars(strip_tags($this->contact_number));
        $this->address=htmlspecialchars(strip_tags($this->address));
      //  $this->status=htmlspecialchars(strip_tags($this->status));
      //  $this->komentar=htmlspecialchars(strip_tags($this->komentar));


        // bind the values
        $stmt->bindParam(':firstname', $this->firstname);
        $stmt->bindParam(':lastname', $this->lastname);
     //   $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':contact_number', $this->contact_number);
        $stmt->bindParam(':address', $this->address);
     //   $stmt->bindParam(':status', $this->status);
     //   $stmt->bindParam(':komentar', $this->komentar);
     //   $stmt->bindParam(':prevod', $this->prevod);
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