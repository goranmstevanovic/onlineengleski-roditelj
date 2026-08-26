<?php

class vrsta_roditelja
{
    private $conn;
    private $table_name = "vrste_roditelja";

    public $id;
    public $ime;
    public $opis;
    public $active;

    public function __construct($db){
        $this->conn = $db;
    }

    function read_all()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE `active` = 1 ORDER BY `id` ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

}
