<?php

require_once "BaseServiceAbs.php";

class ContactService extends BaseServiceAbs
{

    private $table = 'contacts';

    public function __construct($connection)
    {
        parent::__construct($connection, $this->table);
    }

    public function getWithDeals($id)
    {
        $connection = $this->connection;

        $data = $connection->query("
            SELECT contacts.id, contacts.first_name, contacts.last_name, deals.id AS deal_id, deals.name FROM contacts 
            LEFT JOIN contact_deal ON contacts.id = contact_deal.contact_id 
            LEFT JOIN deals ON contact_deal.deal_id = deals.id WHERE contacts.id=$id")->fetchAll(PDO::FETCH_ASSOC);

        $contact = [
            'id' => $data[0]["id"],
            'first_name' => $data[0]['first_name'],
            'last_name' => $data[0]['last_name'],
            'deals' => []
        ];

        foreach ($data as $row) {
            if (!empty($row['deal_id'])) {
                $contact['deals'][] = [
                    'deal_id' => $row['deal_id'],
                    'name' => $row['name']
                ];
            }
        }
        return $contact;
    }

    public function save($rows)
    {
        $connection = $this->connection;

        $connection->beginTransaction();

        try {
            $ins = $connection->prepare("INSERT INTO contacts (first_name, last_name) VALUES (:first_name, :last_name)");
            $ins->execute([
                ':first_name' => $rows['first_name'],
                ':last_name' => $rows['last_name']
            ]);
            $id = $connection->lastInsertId();

            $stmtInsert = $connection->prepare("INSERT INTO contact_deal (contact_id, deal_id) VALUES (?, ?)");

            foreach ($rows["deals"] as $dealId) {
                $stmtInsert->execute([$id, $dealId]);
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

            if ($rows["deals"] != null) {
                $stmtDelete = $connection->prepare("DELETE FROM contact_deal WHERE contact_id = ?");
                $stmtDelete->execute([$rows["id"]]);
            }

            $ins = $connection->prepare("UPDATE contacts SET first_name = :first_name, last_name = :last_name WHERE id = :id");
            $ins->execute([
                ':id' => $rows['id'],
                ':first_name' => $rows['first_name'],
                ':last_name' => $rows['last_name']
            ]);

            $stmtInsert = $connection->prepare("INSERT INTO contact_deal (deal_id, contact_id) VALUES (?, ?)");

            foreach ($rows["deals"] as $deal_id) {
                $stmtInsert->execute([$deal_id, $rows['id']]);
            }

            $connection->commit();
        } catch (PDOException $e) {
            $connection->rollBack();
            echo "Ошибка при сохранении: " . $e->getMessage();
        }
    }
}
