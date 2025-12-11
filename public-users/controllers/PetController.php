<?php


require_once __DIR__ . '/PetManagementController.php';

class PetController {
    
    public static function fetchAvailablePets(int $limit = 999, int $offset = 0): array {
        try {
            $pets = PetManagementController::getAvailablePets($limit, $offset);

            foreach ($pets as &$pet) {
                $ownerId = (int)($pet['owner_id'] ?? 0);

                // Fetch supplemental photos list
                $photos = PetManagementController::getPetPhotos((int)($pet['id'] ?? 0));
                $pet['photos'] = $photos;

                // Determine best source for primary image
                $source = '';
                if (!empty($pet['primary_photo'])) {
                    $source = $pet['primary_photo'];
                } elseif (!empty($photos[0]['photo_path'])) {
                    $source = $photos[0]['photo_path'];
                } elseif (!empty($pet['pet_photos'])) {
                    // Fix: If pet_photos is an array, get the first photo path
                    if (is_array($pet['pet_photos'])) {
                        $first = reset($pet['pet_photos']);
                        if (is_array($first) && isset($first['photo_path'])) {
                            $source = $first['photo_path'];
                        } elseif (is_string($first)) {
                            $source = $first;
                        } else {
                            $source = '';
                        }
                    } else {
                        $source = $pet['pet_photos'];
                    }
                }

                // Normalize to a full URL using the shared helper
                $pet['photo'] = PetManagementController::getPhotoUrl($ownerId, $source ?: '/storage/uploads/images/default.png');
                $pet['photo_raw'] = $source;
            }
            unset($pet);

            return $pets;
        } catch (Throwable $e) {
            error_log('PetController::fetchAvailablePets error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search for available pets by keyword in name, breed, species, or description.
     * Returns an array of matching pets suitable for views.
     */
    public static function searchAvailablePets($keyword) {
        // Use PetManagementController's getDB() method
        if (!method_exists('PetManagementController', 'getDB')) {
            throw new Exception('PetManagementController::getDB() does not exist');
        }
        $db = PetManagementController::getDB();
        $sql = "SELECT * FROM pets WHERE status = 'available' AND (
            name LIKE :kw OR
            breed LIKE :kw OR
            species LIKE :kw OR
            description LIKE :kw
        )";
        $stmt = $db->prepare($sql);
        $kw = '%' . $keyword . '%';
        $stmt->bindParam(':kw', $kw, PDO::PARAM_STR);
        $stmt->execute();
        $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Normalize photos for each pet (like in fetchAvailablePets)
        foreach ($pets as &$pet) {
            $ownerId = (int)($pet['owner_id'] ?? 0);
            $photos = PetManagementController::getPetPhotos((int)($pet['id'] ?? 0));
            $pet['photos'] = $photos;

            $source = '';
            if (!empty($pet['primary_photo'])) {
                $source = $pet['primary_photo'];
            } elseif (!empty($photos[0]['photo_path'])) {
                $source = $photos[0]['photo_path'];
            } elseif (!empty($pet['pet_photos'])) {
                // Fix: If pet_photos is an array, get the first photo path
                if (is_array($pet['pet_photos'])) {
                    // If it's an array, get the first element (if it's an array of arrays, get photo_path)
                    $first = reset($pet['pet_photos']);
                    if (is_array($first) && isset($first['photo_path'])) {
                        $source = $first['photo_path'];
                    } elseif (is_string($first)) {
                        $source = $first;
                    } else {
                        $source = '';
                    }
                } else {
                    $source = $pet['pet_photos'];
                }
            }

            $pet['photo'] = PetManagementController::getPhotoUrl($ownerId, $source ?: '/storage/uploads/images/default.png');
            $pet['photo_raw'] = $source;
        }
        unset($pet);

        return $pets;
    }

    public static function getSpeciesList(): array {
        $db = PetManagementController::getDB();
        $stmt = $db->query("SELECT DISTINCT species FROM pets WHERE species IS NOT NULL AND species <> ''");
        $species = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $species[] = $row['species'];
        }
        return $species;
    }

    public static function getBreedList($species): array {
        $db = PetManagementController::getDB();
        $stmt = $db->prepare("SELECT DISTINCT breed FROM pets WHERE species = :species AND breed IS NOT NULL AND breed <> ''");
        $stmt->bindParam(':species', $species, PDO::PARAM_STR);
        $stmt->execute();
        $breeds = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $breeds[] = $row['breed'];
        }
        return $breeds;
    }

    public static function filterAvailablePets($filters): array {
        $db = PetManagementController::getDB();
        $sql = "SELECT * FROM pets WHERE status = 'available'";
        $params = [];
        $where = [];

        // Always cast multi-value filters to array
        $filters['age'] = is_array($filters['age']) ? $filters['age'] : (empty($filters['age']) ? [] : [$filters['age']]);
        $filters['size'] = is_array($filters['size']) ? $filters['size'] : (empty($filters['size']) ? [] : [$filters['size']]);
        $filters['gender'] = is_array($filters['gender']) ? $filters['gender'] : (empty($filters['gender']) ? [] : [$filters['gender']]);
       

        if (!empty($filters['search'])) {
            $where[] = "(name LIKE :search OR breed LIKE :search OR species LIKE :search OR description LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['species'])) {
            $where[] = "species = :species";
            $params[':species'] = $filters['species'];
        }
        if (!empty($filters['breed'])) {
            $where[] = "breed = :breed";
            $params[':breed'] = $filters['breed'];
        }
        if (!empty($filters['age'])) {
            $ageWhere = [];
            foreach ($filters['age'] as $i => $age) {
                $ageWhere[] = "age LIKE :age$i";
                $params[":age$i"] = '%' . $age . '%';
            }
            if ($ageWhere) {
                $where[] = '(' . implode(' OR ', $ageWhere) . ')';
            }
        }
        if (!empty($filters['size'])) {
            $sizeWhere = [];
            foreach ($filters['size'] as $i => $size) {
                $sizeWhere[] = "size LIKE :size$i";
                $params[":size$i"] = '%' . $size . '%';
            }
            if ($sizeWhere) {
                $where[] = '(' . implode(' OR ', $sizeWhere) . ')';
            }
        }
        // Gender is now a string, not array
        if (!empty($filters['gender'])) {
            $where[] = "gender = :gender";
            $params[':gender'] = $filters['gender'];
        }
        if (!empty($filters['good_with'])) {
            $goodWithWhere = [];
            foreach ($filters['good_with'] as $i => $good) {
                $goodWithWhere[] = "description LIKE :good_with$i";
                $params[":good_with$i"] = '%' . $good . '%';
            }
            if ($goodWithWhere) {
                $where[] = '(' . implode(' OR ', $goodWithWhere) . ')';
            }
        }
        if (!empty($filters['color'])) {
            $where[] = "description LIKE :color";
            $params[':color'] = '%' . $filters['color'] . '%';
        }
        if (!empty($filters['coat_length'])) {
            $where[] = "description LIKE :coat_length";
            $params[':coat_length'] = '%' . $filters['coat_length'] . '%';
        }
        if (!empty($filters['activity_level'])) {
            $where[] = "description LIKE :activity_level";
            $params[':activity_level'] = '%' . $filters['activity_level'] . '%';
        
        }
        if (!empty($filters['vaccine_status'])) {
            $where[] = "vaccine_status = :vaccine_status";
            $params[':vaccine_status'] = $filters['vaccine_status'];
        }
        if (!empty($filters['availability'])) {
            $where[] = "status = :availability";
            $params[':availability'] = $filters['availability'];
        }

        if ($where) {
            $sql .= ' AND ' . implode(' AND ', $where);
        }
        $sql .= " ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $val) {
            // Only bind scalar values
            if (is_array($val)) {
                $stmt->bindValue($key, implode(',', $val)); // or handle as needed
            } else {
                $stmt->bindValue($key, $val);
            }
        }
        $stmt->execute();
        $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Normalize photos for each pet (like in fetchAvailablePets)
        foreach ($pets as &$pet) {
            $ownerId = (int)($pet['owner_id'] ?? 0);
            $photos = PetManagementController::getPetPhotos((int)($pet['id'] ?? 0));
            $pet['photos'] = $photos;

            $source = '';
            if (!empty($pet['primary_photo'])) {
                $source = $pet['primary_photo'];
            } elseif (!empty($photos[0]['photo_path'])) {
                $source = $photos[0]['photo_path'];
            } elseif (!empty($pet['pet_photos'])) {
                // Fix: If pet_photos is an array, get the first photo path
                if (is_array($pet['pet_photos'])) {
                    // If it's an array, get the first element (if it's an array of arrays, get photo_path)
                    $first = reset($pet['pet_photos']);
                    if (is_array($first) && isset($first['photo_path'])) {
                        $source = $first['photo_path'];
                    } elseif (is_string($first)) {
                        $source = $first;
                    } else {
                        $source = '';
                    }
                } else {
                    $source = $pet['pet_photos'];
                }
            }

            $pet['photo'] = PetManagementController::getPhotoUrl($ownerId, $source ?: '/storage/uploads/images/default.png');
            $pet['photo_raw'] = $source;
        }
        unset($pet);

        return $pets;
    }
}
