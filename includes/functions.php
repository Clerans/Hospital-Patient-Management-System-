<?php
/**
 * Global Helper Functions & Utilities
 * Hospital Patient Management System
 */

// Enable output buffering to prevent header errors
if (ob_get_level() === 0) {
    ob_start();
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize string input to prevent XSS attacks
 */
function sanitize($data) {
    if (is_null($data)) return '';
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format raw date to readable human format (e.g. 15 Sep 2026)
 */
function formatDate($dateString) {
    if (empty($dateString)) return 'N/A';
    try {
        $date = new DateTime($dateString);
        return $date->format('d M Y');
    } catch (Exception $e) {
        return $dateString;
    }
}

/**
 * Format datetime string (e.g. 15 Sep 2026, 09:30 AM)
 */
function formatDateTime($dateTimeString) {
    if (empty($dateTimeString)) return 'N/A';
    try {
        $date = new DateTime($dateTimeString);
        return $date->format('d M Y, h:i A');
    } catch (Exception $e) {
        return $dateTimeString;
    }
}

/**
 * Calculate age based on Date of Birth
 */
function calculateAge($dobString) {
    if (empty($dobString)) return 'N/A';
    try {
        $dob = new DateTime($dobString);
        $today = new DateTime('today');
        $age = $dob->diff($today)->y;
        return $age . ' yrs';
    } catch (Exception $e) {
        return 'N/A';
    }
}

/**
 * Generate Next Unique Patient Code (e.g., PAT-2026-0009)
 */
function generatePatientCode($pdo) {
    $currentYear = date('Y');
    $stmt = $pdo->prepare("SELECT patient_code FROM patients WHERE patient_code LIKE :prefix ORDER BY id DESC LIMIT 1");
    $stmt->execute([':prefix' => "PAT-$currentYear-%"]);
    $lastCode = $stmt->fetchColumn();

    if ($lastCode) {
        $parts = explode('-', $lastCode);
        $lastNumber = isset($parts[2]) ? (int)$parts[2] : 0;
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $nextNumber = '0001';
    }

    return "PAT-{$currentYear}-{$nextNumber}";
}

/**
 * Render stylish HTML status badge
 */
function getStatusBadge($status) {
    $status = trim((string)$status);
    $badgeClasses = [
        'Inpatient'   => 'badge badge-inpatient',
        'Outpatient'  => 'badge badge-outpatient',
        'Discharged'  => 'badge badge-discharged',
        'Emergency'   => 'badge badge-emergency',
        'Transferred' => 'badge badge-transferred',
    ];

    $class = $badgeClasses[$status] ?? 'badge badge-default';
    return '<span class="' . $class . '"><span class="badge-dot"></span>' . htmlspecialchars($status) . '</span>';
}

/**
 * Flash Notification System
 */
function setFlash($type, $message) {
    $_SESSION['flash_message'] = [
        'type'    => $type, // 'success', 'danger', 'info', 'warning'
        'message' => $message
    ];
}

function displayFlash() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        
        $type = htmlspecialchars($flash['type']);
        $message = htmlspecialchars($flash['message']);
        
        $icons = [
            'success' => '<svg class="flash-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
            'danger'  => '<svg class="flash-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
            'warning' => '<svg class="flash-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
            'info'    => '<svg class="flash-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
        ];
        
        $icon = $icons[$type] ?? $icons['info'];

        echo '<div class="alert alert-' . $type . ' alert-dismissible animate-fade-in" role="alert">';
        echo '  <div class="alert-content">' . $icon . '<span>' . $message . '</span></div>';
        echo '  <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>';
        echo '</div>';
    }
}

/**
 * CSRF Protection
 */
function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * List of Hospital Departments
 */
function getDepartmentsList() {
    return [
        'Cardiology',
        'Pediatrics',
        'Orthopedics',
        'Dermatology',
        'Neurology',
        'General Medicine',
        'ENT',
        'Gynecology & Obstetrics',
        'Oncology',
        'Ophthalmology',
        'Emergency / Trauma',
        'Urology',
        'Psychiatry'
    ];
}

/**
 * List of Blood Groups
 */
function getBloodGroups() {
    return ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'];
}

/**
 * Active Navigation Link Indicator
 */
function isActivePage($pageName) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    return ($currentPage === $pageName) ? 'active' : '';
}
