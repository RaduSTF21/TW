<?php
namespace App\service;

class ListingService
{
    // Fetch all transaction types
    public static function getTransactionTypes(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT id, name FROM transaction_types");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Fetch all property types
    public static function getPropertyTypes(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT id, name FROM property_types");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Fetch all amenities
    public static function getAmenities(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT id, name FROM amenities");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Fetch all risks
    public static function getRisks(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT id, name FROM risks");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Fetch all listings for a given user
    public static function getByUserId(\PDO $pdo, int $userId): array
    {
        $stmt = $pdo->prepare(
            'SELECT id, title
               FROM properties
              WHERE user_id = ?
           ORDER BY id DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Save or update a property (inserting into pivot tables), now enforcing user_id
    public static function saveProperty(\PDO $pdo, array $data, array $amenityIds, array $riskIds): int
    {
        if (empty($data['id'])) {
            // INSERT new, include user_id
            $sql = "INSERT INTO properties 
                      (title, description, price, rooms, transaction_type, property_type,
                       latitude, longitude, user_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['title'], $data['description'], $data['price'],
                $data['rooms'], $data['transaction_type'], $data['property_type'],
                $data['latitude'], $data['longitude'], $data['user_id']
            ]);
            $propertyId = (int)$pdo->lastInsertId();
        } else {
            // UPDATE, only if owned by this user
            $sql = "UPDATE properties
                       SET title            = ?,
                           description      = ?,
                           price            = ?,
                           rooms            = ?,
                           transaction_type = ?,
                           property_type    = ?,
                           latitude         = ?,
                           longitude        = ?
                     WHERE id = ? AND user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['title'], $data['description'], $data['price'],
                $data['rooms'], $data['transaction_type'], $data['property_type'],
                $data['latitude'], $data['longitude'], $data['id'], $data['user_id']
            ]);
            $propertyId = (int)$data['id'];

            // Clear existing pivots
            $pdo->prepare("DELETE FROM property_amenities WHERE property_id = ?")
                ->execute([$propertyId]);
            $pdo->prepare("DELETE FROM property_risks     WHERE property_id = ?")
                ->execute([$propertyId]);
        }

        // Re-insert amenity pivots
        $stmtA = $pdo->prepare(
            "INSERT INTO property_amenities (property_id, amenity_id) VALUES (?, ?)"
        );
        foreach ($amenityIds as $amenityId) {
            $stmtA->execute([$propertyId, $amenityId]);
        }

        // Re-insert risk pivots
        $stmtR = $pdo->prepare(
            "INSERT INTO property_risks (property_id, risk_id) VALUES (?, ?)"
        );
        foreach ($riskIds as $riskId) {
            $stmtR->execute([$propertyId, $riskId]);
        }

        return $propertyId;
    }

    // Fetch a single property by ID
    public static function getById(\PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare(
            'SELECT id, title, description, price, rooms, transaction_type,
                    property_type, latitude, longitude, user_id
               FROM properties
              WHERE id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Fetch all images for a given property_id
    public static function getImages(\PDO $pdo, int $propertyId): array
    {
        $stmt = $pdo->prepare(
            'SELECT filename, alt_text
               FROM property_images
              WHERE property_id = ?
           ORDER BY id'
        );
        $stmt->execute([$propertyId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Optionally fetch all listings (admin)
    public static function getAll(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT * FROM properties");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
