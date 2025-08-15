<?php

require_once "BaseServiceAbs.php";

class DealService extends BaseServiceAbs
{

    private $table = 'deals';

    public function __construct($connection)
    {
        parent::__construct($connection, $this->table);
    }
    public function getWithContracts($id)
    {
        $connection = $this->connection;

        $data = $connection->query("
            SELECT deals.id, deals.name, deals.sum, contacts.id AS contact_id, contacts.first_name, contacts.last_name FROM deals 
            LEFT JOIN contact_deal ON deals.id = contact_deal.deal_id 
            LEFT JOIN contacts ON contact_deal.contact_id = contacts.id WHERE deals.id=$id")->fetchAll(PDO::FETCH_ASSOC);

        $deal = [
            'id' => $data[0]["id"],
            'name' => $data[0]['name'],
            'sum' => $data[0]['sum'],
            'contacts' => []
        ];

        foreach ($data as $row) {
            if (!empty($row['contact_id'])) {
                $deal['contacts'][] = [
                    'contact_id' => $row['contact_id'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name']
                ];
            }
        }
        return $deal;
    }


    public function save($rows)
    {
        $connection = $this->connection;

        $connection->beginTransaction();

        try {
            $ins = $connection->prepare("INSERT INTO deals (name, sum) VALUES (:name, :sum)");
            $ins->execute([
                ':name' => $rows['name'],
                ':sum' => $rows['sum']
            ]);
            $id = $connection->lastInsertId();

            $stmtInsert = $connection->prepare("INSERT INTO contact_deal (contact_id, deal_id) VALUES (?, ?)");

            foreach ($rows["contacts"] as $contact_id) {
                $stmtInsert->execute([$contact_id, $id]);
            }

            $connection->commit();
        } catch (PDOException $e) {
            $connection->rollBack();
            echo "Ошибка при сохранении: " . $e->getMessage();
        }
    }

    public function update($rows)
    {
        $connection = $this->connection;

        $connection->beginTransaction();

        try {

            if ($rows["contacts"] != null) {
                $stmtDelete = $connection->prepare("DELETE FROM contact_deal WHERE deal_id = ?");
                $stmtDelete->execute([$rows["id"]]);
            }

            $ins = $connection->prepare("UPDATE deals SET name = :name, sum = :sum WHERE id = :id");
            $ins->execute([
                ':id' => $rows['id'],
                ':name' => $rows['name'],
                ':sum' => $rows['sum']
            ]);

            $stmtInsert = $connection->prepare("INSERT INTO contact_deal (contact_id, deal_id) VALUES (?, ?)");

            foreach ($rows["contacts"] as $contact_id) {
                $stmtInsert->execute([$contact_id, $rows["id"]]);
            }

            $connection->commit();
        } catch (PDOException $e) {
            $connection->rollBack();
            echo "Ошибка при сохранении: " . $e->getMessage();
        }
    }
}