<?php

require_once "BaseService.php";

abstract class BaseServiceAbs implements BaseService{

    protected $connection;
    private $table;

    /**
     * @param $connection
     * @param $table
     */
    public function __construct($connection, $table)
    {
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function gteOne($id){
        return $this->connection->query("SELECT * FROM $this->table WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $id
     * @return void
     */
    public function delete($id){
        $del = $this->connection->prepare("DELETE FROM $this->table WHERE id=:id");
        $del->execute([
            ':id' => $id
        ]);
    }

    /**
     * @return mixed
     */
    public function getAll() {
        return $this->connection->query("SELECT * FROM $this->table")->fetchAll(PDO::FETCH_ASSOC);
    }
}
