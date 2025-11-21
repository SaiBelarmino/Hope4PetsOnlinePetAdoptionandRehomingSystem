<?php

    // Legacy filename kept (plural) for compatibility. Provides AdminDashboardController.
    require_once __DIR__ . '/../../../controllers/BaseController.php';

    class AdminDashboardController extends BaseController {
        /**
         * Return high-level stats for admin dashboard.
         * TODO: Replace placeholder queries with real table names/logic.
         */
        public static function stats(): array {
            return [
                // Pets
                'total_pets' => (int) self::fetchValue("SELECT COUNT(*) FROM pets", '', [] , 0),

                // Users
                'registered_users' => (int) self::fetchValue("SELECT COUNT(*) FROM users", '', [] , 0),

                // Shelters
                'total_shelters' => (int) self::fetchValue("SELECT COUNT(*) FROM shelters", '', [] , 0),

                // Adoption requests
                'adoption_requests_total' => (int) self::fetchValue("SELECT COUNT(*) FROM adoption_requests", '', [] , 0),
                'adoption_requests_pending' => (int) self::fetchValue("SELECT COUNT(*) FROM adoption_requests WHERE status='pending'", '', [] , 0),
                'approved_adoptions' => (int) self::fetchValue("SELECT COUNT(*) FROM adoption_requests WHERE status='approved'", '', [] , 0),

                // Rehoming / surrender requests
                'rehoming_requests_total' => (int) self::fetchValue("SELECT COUNT(*) FROM rehoming_requests", '', [] , 0),

                // Donations (example existing metric)
                'today_donations' => (float) self::fetchValue("SELECT COALESCE(SUM(amount),0) FROM donations WHERE DATE(created_at)=CURDATE()", '', [] , 0.0)
            ];
        }

        /**
         * Get monthly adoption trends for the last 12 months.
         */
        public static function getMonthlyAdoptionTrends(): array {
            $query = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') AS month, 
                        COUNT(*) AS adoptions 
                      FROM adoption_requests 
                      WHERE status = 'approved' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                      GROUP BY month 
                      ORDER BY month ASC";
            return self::fetchAll($query);
        }

        /**
         * Get the distribution of pet types.
         */
        public static function getPetTypeDistribution(): array {
            $query = "SELECT type, COUNT(*) AS count FROM pets GROUP BY type";
            return self::fetchAll($query);
        }

        /**
         * Get new user registration counts for the last 30 days.
         */
        public static function getNewUserRegistrations(): array {
            $query = "SELECT 
                        DATE(created_at) AS registration_date, 
                        COUNT(*) AS user_count 
                      FROM users 
                      WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                      GROUP BY registration_date 
                      ORDER BY registration_date ASC";
            return self::fetchAll($query);
        }

        /**
         * Get a feed of recent activities across the system.
         * This uses UNION ALL to combine different event types into one feed.
         */
        public static function getRecentActivities(int $limit = 10): array {
            $query = "
                (
                    SELECT 
                        'adoption_request' AS type, 
                        ar.created_at AS activity_date, 
                        CONCAT('New adoption request for ''', p.name, ''' from user ''', u.username, '''.') AS description
                    FROM adoption_requests ar
                    JOIN users u ON ar.user_id = u.user_id
                    JOIN pets p ON ar.pet_id = p.pet_id
                )
                UNION ALL
                (
                    SELECT 
                        'user_registered' AS type, 
                        u.created_at AS activity_date, 
                        CONCAT('New user registered: ', u.username) AS description
                    FROM users u
                )
                UNION ALL
                (
                    SELECT 
                        'pet_added' AS type, 
                        p.created_at AS activity_date, 
                        CONCAT('New pet added to the system: ', p.name) AS description
                    FROM pets p
                )
                UNION ALL
                (
                    SELECT 
                        'adoption_approved' AS type, 
                        ar.updated_at AS activity_date, 
                        CONCAT('Adoption approved for ''', p.name, '''.') AS description
                    FROM adoption_requests ar
                    JOIN pets p ON ar.pet_id = p.pet_id
                    WHERE ar.status = 'approved'
                )
                ORDER BY activity_date DESC
                LIMIT ?
            ";
            return self::fetchAll($query, 'i', [$limit]);
        }
    }
?>
