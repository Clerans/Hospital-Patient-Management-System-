<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

// Set default page title if not set
if (!isset($pageTitle)) {
    $pageTitle = 'Hospital Patient Management System';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($pageTitle); ?> | MediCare Hospital</title>
    <meta name="description" content="A simple, secure, and modern web-based Hospital Patient Management System developed with PHP and MySQL.">
    
    <!-- Design & Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>

    <!-- Header Navigation Bar -->
    <header class="navbar">
        <div class="app-container">
            <div class="navbar-inner">
                <!-- Brand Logo & Title -->
                <a href="index.php" class="brand">
                    <div class="brand-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 6v12m6-6H6"/>
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                        </svg>
                    </div>
                    <div class="brand-title">
                        MediCare <span>Hospital</span>
                        <span class="brand-tag">PMS</span>
                    </div>
                </a>

                <!-- Navigation Links -->
                <nav>
                    <ul class="nav-links">
                        <li>
                            <a href="index.php" class="nav-link <?= isActivePage('index.php'); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="patients.php" class="nav-link <?= isActivePage('patients.php'); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                All Patients
                            </a>
                        </li>
                        <li>
                            <a href="add-patient.php" class="nav-link <?= isActivePage('add-patient.php'); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="16"></line>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                                Register Patient
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- Actions / Time Info -->
                <div class="nav-actions">
                    <div class="header-time" title="Current Hospital System Time">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span id="liveClock">Loading...</span>
                    </div>
                    <a href="add-patient.php" class="btn btn-primary btn-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        New Admission
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="main-content">
        <div class="app-container">
            <?php displayFlash(); ?>
