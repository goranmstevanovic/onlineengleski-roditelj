<?php

class dokument_pristup
{
    private $conn;
    private $table_name = "dokument_pristup";

    public $id;
    public $fk_dokument;
    public $tip_pristupa;     // grupa | djak | profesor
    public $fk_entitet;
    public $pravo_pregled = 1;
    public $pravo_download = 0;
    public $created_at;

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

    public function create()
    {
        $this->created_at = date('Y-m-d H:i:s');

        $query = "INSERT INTO " . $this->table_name . "
            SET
                fk_dokument = :fk_dokument,
                tip_pristupa = :tip_pristupa,
                fk_entitet = :fk_entitet,
                pravo_pregled = :pravo_pregled,
                pravo_download = :pravo_download,
                created_at = :created_at";

        $stmt = $this->conn->prepare($query);

        $this->tip_pristupa = htmlspecialchars(strip_tags($this->tip_pristupa));

        $stmt->bindParam(':fk_dokument', $this->fk_dokument);
        $stmt->bindParam(':tip_pristupa', $this->tip_pristupa);
        $stmt->bindParam(':fk_entitet', $this->fk_entitet);
        $stmt->bindParam(':pravo_pregled', $this->pravo_pregled);
        $stmt->bindParam(':pravo_download', $this->pravo_download);
        $stmt->bindParam(':created_at', $this->created_at);

        if ($stmt->execute()) {
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    public function delete_by_dokument($fk_dokument)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE fk_dokument = :fk_dokument";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_dokument', $fk_dokument);

        if ($stmt->execute()) {
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    public function delete_one_target($fk_dokument, $tip_pristupa, $fk_entitet)
    {
        $query = "DELETE FROM " . $this->table_name . "
                  WHERE fk_dokument = :fk_dokument
                  AND tip_pristupa = :tip_pristupa
                  AND fk_entitet = :fk_entitet";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_dokument', $fk_dokument);
        $stmt->bindParam(':tip_pristupa', $tip_pristupa);
        $stmt->bindParam(':fk_entitet', $fk_entitet);

        if ($stmt->execute()) {
            return true;
        } else {
            $this->showError($stmt);
            return false;
        }
    }

    public function read_by_dokument($fk_dokument)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE fk_dokument = :fk_dokument
                  ORDER BY tip_pristupa, fk_entitet";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_dokument', $fk_dokument);
        $stmt->execute();

        return $stmt;
    }

    public function read_by_entitet($tip_pristupa, $fk_entitet)
    {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE tip_pristupa = :tip_pristupa
                  AND fk_entitet = :fk_entitet
                  ORDER BY fk_dokument DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tip_pristupa', $tip_pristupa);
        $stmt->bindParam(':fk_entitet', $fk_entitet);
        $stmt->execute();

        return $stmt;
    }

    public function user_can_view($fk_dokument, $tip_pristupa, $fk_entitet)
    {
        $query = "SELECT id
                  FROM " . $this->table_name . "
                  WHERE fk_dokument = :fk_dokument
                  AND tip_pristupa = :tip_pristupa
                  AND fk_entitet = :fk_entitet
                  AND pravo_pregled = 1
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_dokument', $fk_dokument);
        $stmt->bindParam(':tip_pristupa', $tip_pristupa);
        $stmt->bindParam(':fk_entitet', $fk_entitet);
        $stmt->execute();

        return ($stmt->rowCount() > 0);
    }

    public function user_can_download($fk_dokument, $tip_pristupa, $fk_entitet)
    {
        $query = "SELECT id
                  FROM " . $this->table_name . "
                  WHERE fk_dokument = :fk_dokument
                  AND tip_pristupa = :tip_pristupa
                  AND fk_entitet = :fk_entitet
                  AND pravo_download = 1
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fk_dokument', $fk_dokument);
        $stmt->bindParam(':tip_pristupa', $tip_pristupa);
        $stmt->bindParam(':fk_entitet', $fk_entitet);
        $stmt->execute();

        return ($stmt->rowCount() > 0);
    }

    public function create_multiple_for_groups($fk_dokument, $grupe = array(), $pravo_pregled = 1, $pravo_download = 0)
    {
        if (empty($grupe) || !is_array($grupe)) {
            return true;
        }

        $query = "INSERT INTO " . $this->table_name . "
                  SET
                      fk_dokument = :fk_dokument,
                      tip_pristupa = 'grupa',
                      fk_entitet = :fk_entitet,
                      pravo_pregled = :pravo_pregled,
                      pravo_download = :pravo_download,
                      created_at = :created_at";

        $stmt = $this->conn->prepare($query);
        $created_at = date('Y-m-d H:i:s');

        foreach ($grupe as $grupa_id) {
            $grupa_id = (int)$grupa_id;

            if ($grupa_id <= 0) {
                continue;
            }

            $stmt->bindParam(':fk_dokument', $fk_dokument);
            $stmt->bindParam(':fk_entitet', $grupa_id);
            $stmt->bindParam(':pravo_pregled', $pravo_pregled);
            $stmt->bindParam(':pravo_download', $pravo_download);
            $stmt->bindParam(':created_at', $created_at);

            if (!$stmt->execute()) {
                $this->showError($stmt);
                return false;
            }
        }

        return true;
    }

    public function create_multiple_for_students($fk_dokument, $djaci = array(), $pravo_pregled = 1, $pravo_download = 0)
    {
        if (empty($djaci) || !is_array($djaci)) {
            return true;
        }

        $query = "INSERT INTO " . $this->table_name . "
                  SET
                      fk_dokument = :fk_dokument,
                      tip_pristupa = 'djak',
                      fk_entitet = :fk_entitet,
                      pravo_pregled = :pravo_pregled,
                      pravo_download = :pravo_download,
                      created_at = :created_at";

        $stmt = $this->conn->prepare($query);
        $created_at = date('Y-m-d H:i:s');

        foreach ($djaci as $djak_id) {
            $djak_id = (int)$djak_id;

            if ($djak_id <= 0) {
                continue;
            }

            $stmt->bindParam(':fk_dokument', $fk_dokument);
            $stmt->bindParam(':fk_entitet', $djak_id);
            $stmt->bindParam(':pravo_pregled', $pravo_pregled);
            $stmt->bindParam(':pravo_download', $pravo_download);
            $stmt->bindParam(':created_at', $created_at);

            if (!$stmt->execute()) {
                $this->showError($stmt);
                return false;
            }
        }

        return true;
    }

    public function create_multiple_for_profesori($fk_dokument, $profesori = array(), $pravo_pregled = 1, $pravo_download = 0)
    {
        if (empty($profesori) || !is_array($profesori)) {
            return true;
        }

        $query = "INSERT INTO " . $this->table_name . "
                  SET
                      fk_dokument = :fk_dokument,
                      tip_pristupa = 'profesor',
                      fk_entitet = :fk_entitet,
                      pravo_pregled = :pravo_pregled,
                      pravo_download = :pravo_download,
                      created_at = :created_at";

        $stmt = $this->conn->prepare($query);
        $created_at = date('Y-m-d H:i:s');

        foreach ($profesori as $profesor_id) {
            $profesor_id = (int)$profesor_id;

            if ($profesor_id <= 0) {
                continue;
            }

            $stmt->bindParam(':fk_dokument', $fk_dokument);
            $stmt->bindParam(':fk_entitet', $profesor_id);
            $stmt->bindParam(':pravo_pregled', $pravo_pregled);
            $stmt->bindParam(':pravo_download', $pravo_download);
            $stmt->bindParam(':created_at', $created_at);

            if (!$stmt->execute()) {
                $this->showError($stmt);
                return false;
            }
        }

        return true;
    }

    public function replace_for_dokument(
        $fk_dokument,
        $grupe = array(),
        $djaci = array(),
        $profesori = array(),
        $group_download = 0,
        $student_download = 0,
        $profesor_download = 0
    ) {
        try {
            $this->conn->beginTransaction();

            $query_delete = "DELETE FROM " . $this->table_name . " WHERE fk_dokument = :fk_dokument";
            $stmt_delete = $this->conn->prepare($query_delete);
            $stmt_delete->bindParam(':fk_dokument', $fk_dokument);

            if (!$stmt_delete->execute()) {
                $this->showError($stmt_delete);
                $this->conn->rollBack();
                return false;
            }

            if (!$this->create_multiple_for_groups($fk_dokument, $grupe, 1, $group_download)) {
                $this->conn->rollBack();
                return false;
            }

            if (!$this->create_multiple_for_students($fk_dokument, $djaci, 1, $student_download)) {
                $this->conn->rollBack();
                return false;
            }

            if (!$this->create_multiple_for_profesori($fk_dokument, $profesori, 1, $profesor_download)) {
                $this->conn->rollBack();
                return false;
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            echo "<pre>";
            print_r($e->getMessage());
            echo "</pre>";
            return false;
        }
    }
}