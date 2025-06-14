<?php
namespace App\Service;

use PDO;
use PDOException;


class ListingService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    
    public function create(int $userId, string $title, string $description, float $price, ?string $location): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO listings (user_id, title, description, price, location, created_at) VALUES (:user_id, :title, :description, :price, :location, NOW())'
            );
            $result = $stmt->execute([
                'user_id'    => $userId,
                'title'      => $title,
                'description'=> $description,
                'price'      => $price,
                'location'   => $location,
            ]);
            if (!$result) {
                return ['success' => false, 'listingId' => null, 'error' => 'Eroare la crearea anunțului.'];
            }
            return ['success' => true, 'listingId' => (int)$this->pdo->lastInsertId(), 'error' => null];
        } catch (PDOException $e) {
            return ['success' => false, 'listingId' => null, 'error' => $e->getMessage()];
        }
    }

   
    public function getAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT l.id, l.user_id, u.name AS author, l.title, l.description, l.price, l.location, l.created_at, l.updated_at
             FROM listings l
             JOIN users u ON l.user_id = u.id
             ORDER BY l.created_at DESC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

 
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, user_id, title, description, price, location, created_at, updated_at
             FROM listings WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $listing = $stmt->fetch(PDO::FETCH_ASSOC);
        return $listing ?: null;
    }

  
    public function update(int $id, string $title, string $description, float $price, ?string $location): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE listings
                 SET title = :title, description = :description, price = :price, location = :location, updated_at = NOW()
                 WHERE id = :id'
            );
            $success = $stmt->execute([
                'id'          => $id,
                'title'       => $title,
                'description' => $description,
                'price'       => $price,
                'location'    => $location,
            ]);
            if (!$success) {
                return ['success' => false, 'error' => 'Eroare la actualizarea anunțului.'];
            }
            return ['success' => true, 'error' => null];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

  
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM listings WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}