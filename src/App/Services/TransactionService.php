<?php

declare(strict_types=1);

namespace App\Services;

use Framework\Database;

class TransactionService
{
    public function __construct(private Database $db)
    {
    }

    public function create(array $formData)
    {
        $formattedDate = "{$formData['date']} 00:00:00";

        $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;

        $this->db->query(
            "INSERT INTO transactions(user_id, description, amount, date)
            VALUES(:user_id, :description, :amount, :date)",
            [
                'user_id' => $userId,
                'description' => $formData['description'],
                'amount' => $formData['amount'],
                'date' => $formattedDate
            ]
        );
    }

    public function getUserTransactions(int $length, int $offset)
    {
        $searchTerm = addcslashes($_GET['s'] ?? '', '%_');
        $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;

        $params = [
            'user_id' => $userId,
            'description' => "%{$searchTerm}%"
        ];

        $transactions = $this->db->query(
        "SELECT * , DATE_FORMAT(date, '%Y-%m-%d') as formatted_date
        FROM transactions 
        WHERE user_id = :user_id
        AND description LIKE :description
        LIMIT {$length} OFFSET {$offset}",
        $params
        )->findAll();

        $transactions = array_map(function(array $transaction) {
            $transaction['receipts'] = $this->db->query(
                "SELECT * FROM receipts WHERE transaction_id = :transaction_id",
                ['transaction_id' => $transaction['id']]
            )->findAll();

            return $transaction;
        }, $transactions);

        $transactionCount = $this->db->query(
        "SELECT COUNT(*)
        FROM transactions 
        WHERE user_id = :user_id
        AND description LIKE :description",
        $params
        )->count();

        return [$transactions, $transactionCount];
    }

    public function getUserTransaction(string $id)
    {
        $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;

        return $this->db->query(
            "SELECT *, DATE_FORMAT(date, '%Y-%m-%d') as formatted_date
            FROM transactions
            WHERE id = :id AND user_id = :user_id",
            [
                'id' => $id,
                'user_id' => $userId
            ]
        )->find();
    }

    public function update(array $formData, int $id)
    {
        $formattedDate = "{$formData['date']} 00:00:00";

        $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;

        $this->db->query(
            "UPDATE transactions
            SET description = :description,
            amount = :amount,
            date = :date
        WHERE id = :id
        AND user_id = :user_id",
        [
            'description' => $formData['description'],
            'amount' => $formData['amount'],
            'date' => $formattedDate,
            'id' => $id,
            'user_id' => $userId
        ]
      );
    }

    public function delete(int $id)
    {
        $userId = isset($_SESSION['user']) ? $_SESSION['user'] : null;

        $this->db->query(
            "DELETE FROM transactions WHERE id = :id AND user_id = :user_id",
            [
                'id' => $id,
                'user_id' => $userId
            ]
        );
    }
}
