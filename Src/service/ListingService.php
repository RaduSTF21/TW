<?php
namespace App\service;

class ListingService
{
    // Fetch all transaction types
    public static function getTransactionTypes(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT id, name FROM transaction_types");
        return $stmt->fetchAll();
    }

    // Fetch all property types
    public static function getPropertyTypes(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT id, name FROM property_types");
        return $stmt->fetchAll();
    }

    // Fetch all amenities
    public static function getAmenities(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT id, name FROM amenities");
        return $stmt->fetchAll();
    }

    // Fetch all risks
    public static function getRisks(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT id, name FROM risks");
        return $stmt->fetchAll();
    }

    // Save or update a property (inserting into pivot tables)
    public static function saveProperty(\PDO $pdo, array $data, array $amenityIds, array $riskIds): int
    {
        // 1) Insert or update properties table, get $propertyId
        if (empty($data['id'])) {
            $sql = "INSERT INTO properties (title, description, price, rooms, transaction_type, property_type, latitude, longitude)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
              $data['title'], $data['description'], $data['price'],
              $data['rooms'], $data['transaction_type'], $data['property_type'],
              $data['latitude'], $data['longitude']
            ]);
            $propertyId = $pdo->lastInsertId();
        } else {
            $sql = "UPDATE properties
                    SET title = ?, description = ?, price = ?, rooms = ?, transaction_type = ?, property_type = ?, latitude = ?, longitude = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
              $data['title'], $data['description'], $data['price'],
              $data['rooms'], $data['transaction_type'], $data['property_type'],
              $data['latitude'], $data['longitude'], $data['id']
            ]);
            $propertyId = $data['id'];

            // Clear out existing pivots
            $pdo->prepare("DELETE FROM property_amenities WHERE property_id = ?")
                ->execute([$propertyId]);
            $pdo->prepare("DELETE FROM property_risks WHERE property_id = ?")
                ->execute([$propertyId]);
        }

        // 2) Insert new amenity pivots
        $stmtA = $pdo->prepare("INSERT INTO property_amenities (property_id, amenity_id) VALUES (?, ?)");
        foreach ($amenityIds as $amenityId) {
            $stmtA->execute([$propertyId, $amenityId]);
        }

        // 3) Insert new risk pivots
        $stmtR = $pdo->prepare("INSERT INTO property_risks (property_id, risk_id) VALUES (?, ?)");
        foreach ($riskIds as $riskId) {
            $stmtR->execute([$propertyId, $riskId]);
        }

        return $propertyId;
    }
}
