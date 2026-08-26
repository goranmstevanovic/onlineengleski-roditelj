<?php
/**
 * Created by PhpStorm.
 * User: goran
 * Date: 15.10.2019
 * Time: 10:15
 */

class zaduzenje_djak
{
    private $conn;
   

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


    // constructor
    public function __construct($db){
        $this->conn = $db;
    }

  
    public function zaduzenje_djak_do_danas($id_djak){
       // $djak = new djak($this->conn);
        $zaduzenje = new zaduzenje($this->conn);
        $evidencija = new evidencija($this->conn);
        $cenovnik = new cenovnik($this->conn);
        $uplata = new uplata($this->conn);
        //$djak = new djak($this->conn);
        //$ssts = $djak->read_one($id_djak );
        // $stmt_djak = $djak->read_one($id_djak);
        // $row_category_djak = $stmt_djak->fetch(PDO::FETCH_ASSOC);
        $zaduzenje_djak=0;
        // zaduzenja na rate
        $drmk = $zaduzenje->read_all_iznos_for_student_without_do_danas( $id_djak );
        while ($row_category_medjusuma = $drmk->fetch(PDO::FETCH_ASSOC)){
            $zaduzenje_djak = $zaduzenje_djak + $row_category_medjusuma['iznos'];
        }

        $zaduzenje_po_kursu = $zaduzenje_djak;

        // po odrzanom casu
        $stmt_po_odrzanom_casu = $evidencija->read_all_all_prisustvo_djak( $id_djak );
        $brojac_termina = 0;
            while ($row_category_casovi = $stmt_po_odrzanom_casu->fetch(PDO::FETCH_ASSOC)){
                $brojac_termina++;
                $zaduzenje_od_casa = $cenovnik->odredi_zaduzenje($row_category_casovi);
                if($row_category_casovi["velicina"] == 1 OR $row_category_casovi["velicina"] == 2 ){
                    if($row_category_casovi["prisutan"] == 1 OR $row_category_casovi["opravdao_otsustvo"] == 0){ 
                        if( $zaduzenje_od_casa !== false  ){
                            $zaduzenje_djak = $zaduzenje_djak +  $zaduzenje_od_casa['iznos'] ?? 0;
                        }
                    }
                }else{               
                    if($row_category_casovi["vrtic"] == 1 ){
                        if($row_category_casovi["prisutan"] == 1){
                            $zaduzenje_djak = $zaduzenje_djak +  $zaduzenje_od_casa['iznos'] ?? 0;
                        }else{
                            // $kartica[$i]["iznos"] = 0;
                        }
        
                    }else{
                    // $zaduzenje_od_casa = $cenovnik->odredi_zaduzenje($row_category_casovi);
                        $zaduzenje_djak = $zaduzenje_djak +  $zaduzenje_od_casa['iznos'];
                    }
                } 
            }
        // if($row_category_djak['fk_porodica'] == 8){
        //     echo "BT:".$brojac_termina," / djak: ",$row_category_djak['firstname'],"&nbsp;", $row_category_djak['lastname'] ,"<br>";    
    
        // }    
         $uplate_djak = 0;
        $ddjkm = $uplata->read_all_all_for_student_without( $id_djak );
        while ($row_category_medjusuma_uplate = $ddjkm->fetch(PDO::FETCH_ASSOC)){
            $uplate_djak = $uplate_djak + $row_category_medjusuma_uplate['iznos'];
        }

        // if($row_category_djak['fk_porodica'] == 8){
        //     echo "uplate:".$uplate_djak." Zaduzenje djak : ". $zaduzenje_djak."<br>";    
    
        // }   

        $saldo_djak = $uplate_djak - $zaduzenje_djak;
        return $saldo_djak;

    }



}