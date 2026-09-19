<?php
/**
 * Database Configuration & Connection (PDO)
 * Hospital Patient Management System
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'hospital_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDBConnection() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Check if database simply does not exist yet and try to guide receptionist/admin
            $errorMessage = $e->getMessage();
            echo '<div style="font-family: system-ui, -apple-system, sans-serif; max-width: 600px; margin: 50px auto; padding: 24px; border: 1px solid #fecaca; background-color: #fef2f2; border-radius: 12px; color: #991b1b; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">';
            echo '<h2 style="margin-top: 0; color: #b91c1c;">⚠️ Database Connection Error</h2>';
            echo '<p>Could not connect to MySQL server. Please ensure MySQL is running in your local environment (e.g. XAMPP, Laragon, or WAMP) and the database <strong>hospital_db</strong> has been imported.</p>';
            echo '<p style="background: #ffffff; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 13px; color: #374151; border: 1px solid #e5e7eb;">' . htmlspecialchars($errorMessage) . '</p>';
            echo '<p style="margin-bottom: 0;"><strong>Quick fix:</strong> Open phpMyAdmin or your MySQL CLI and import <code>database/hospital_db.sql</code>.</p>';
            echo '</div>';
            exit;
        }
    }

    return $pdo;
}
