<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

// Fetch Dashboard Metrics
$totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$inpatientCount = $pdo->query("SELECT COUNT(*) FROM patients WHERE patient_status = 'Inpatient'")->fetchColumn();
$outpatientCount = $pdo->query("SELECT COUNT(*) FROM patients WHERE patient_status = 'Outpatient'")->fetchColumn();
$emergencyCount = $pdo->query("SELECT COUNT(*) FROM patients WHERE patient_status = 'Emergency'")->fetchColumn();
$dischargedCount = $pdo->query("SELECT COUNT(*) FROM patients WHERE patient_status = 'Discharged'")->fetchColumn();

// Fetch Recent Patients (Last 6 admissions)
$stmtRecent = $pdo->query("SELECT * FROM patients ORDER BY admission_date DESC, id DESC LIMIT 6");
$recentPatients = $stmtRecent->fetchAll();

// Fetch Department Breakdown
$stmtDept = $pdo->query("SELECT department, COUNT(*) as count FROM patients GROUP BY department ORDER BY count DESC LIMIT 5");
$departmentStats = $stmtDept->fetchAll();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-title-wrap">
        <h1>Receptionist Dashboard</h1>
        <p class="page-subtitle">Hospital Patient Management System &bull; Overview & Quick Actions</p>
    </div>
    <div>
        <a href="add-patient.php" class="btn btn-primary btn-lg">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Register Patient
        </a>
    </div>
</div>

<!-- Quick Stat Cards Grid (Modern, Clean & Professional) -->
<div class="stats-grid">
    <!-- Stat 1: Total Patients -->
    <div class="stat-card stat-blue">
        <div class="stat-info">
            <div class="stat-label">Total Registered</div>
            <div class="stat-value"><?= number_format($totalPatients); ?></div>
            <div class="stat-trend">All-time patient records</div>
        </div>
        <div class="stat-icon-wrapper">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
    </div>

    <!-- Stat 2: Active Inpatients -->
    <div class="stat-card stat-teal">
        <div class="stat-info">
            <div class="stat-label">Active Inpatients</div>
            <div class="stat-value"><?= number_format($inpatientCount); ?></div>
            <div class="stat-trend">Currently admitted in wards</div>
        </div>
        <div class="stat-icon-wrapper">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M2 4v16"></path>
                <path d="M2 8h18a2 2 0 0 1 2 2v10"></path>
                <path d="M2 17h20"></path>
                <circle cx="6" cy="8" r="2"></circle>
            </svg>
        </div>
    </div>

    <!-- Stat 3: Outpatients -->
    <div class="stat-card stat-emerald">
        <div class="stat-info">
            <div class="stat-label">Outpatient Visits</div>
            <div class="stat-value"><?= number_format($outpatientCount); ?></div>
            <div class="stat-trend">Consultations & clinic visits</div>
        </div>
        <div class="stat-icon-wrapper">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
        </div>
    </div>

    <!-- Stat 4: Emergency Cases -->
    <div class="stat-card stat-rose">
        <div class="stat-info">
            <div class="stat-label">Emergency Cases</div>
            <div class="stat-value"><?= number_format($emergencyCount); ?></div>
            <div class="stat-trend">Immediate triage attention</div>
        </div>
        <div class="stat-icon-wrapper">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
            </svg>
        </div>
    </div>
</div>

<!-- Integrated Search Banner -->
<div class="search-banner">
    <div class="search-banner-inner">
        <div class="search-banner-text">
            <h2>Instant Patient Lookup</h2>
            <p>Find records instantly by Patient ID (e.g. PAT-2026-0001), Full Name, NIC, or Contact Phone Number.</p>
        </div>
        <form action="patients.php" method="GET" class="search-banner-form">
            <input type="text" name="search" placeholder="Search by name, ID, phone..." required>
            <button type="submit" class="btn btn-primary" style="padding: 11px 20px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                Search
            </button>
        </form>
    </div>
</div>

<!-- Main Grid Section -->
<div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 24px; align-items: start;">
    <!-- Recent Patients Table -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Recent Patient Admissions
            </div>
            <a href="patients.php" class="btn btn-secondary btn-sm">View Directory &rarr;</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 140px;">Patient ID</th>
                        <th>Patient Name</th>
                        <th>Department</th>
                        <th>Doctor</th>
                        <th>Status</th>
                        <th style="text-align: right; width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentPatients)): ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div class="empty-state-icon">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                </div>
                                <h3>No Patient Records Yet</h3>
                                <p>Register your first patient into the system.</p>
                                <a href="add-patient.php" class="btn btn-primary btn-sm">Register Patient</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentPatients as $p): ?>
                            <tr>
                                <td>
                                    <a href="view-patient.php?id=<?= $p['id']; ?>" class="patient-code-badge">
                                        <?= htmlspecialchars($p['patient_code']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="patient-name-cell">
                                        <div class="patient-avatar-mini">
                                            <?= strtoupper(substr($p['first_name'], 0, 1) . substr($p['last_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <a href="view-patient.php?id=<?= $p['id']; ?>" style="font-weight: 700; color: var(--text-primary);">
                                                <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?>
                                            </a>
                                            <div style="font-size: 0.76rem; color: var(--text-muted);">
                                                <?= htmlspecialchars($p['gender']); ?> &bull; <?= calculateAge($p['date_of_birth']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight: 600;"><?= htmlspecialchars($p['department']); ?></span>
                                </td>
                                <td>
                                    <span style="color: var(--text-secondary);"><?= htmlspecialchars($p['assigned_doctor']); ?></span>
                                </td>
                                <td>
                                    <?= getStatusBadge($p['patient_status']); ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        <a href="view-patient.php?id=<?= $p['id']; ?>" class="btn btn-secondary btn-sm" title="View Profile">
                                            View
                                        </a>
                                        <a href="edit-patient.php?id=<?= $p['id']; ?>" class="btn btn-secondary btn-sm" title="Edit Patient Details">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Side Panel -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Top Departments Card -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <div class="card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                    Top Departments
                </div>
            </div>
            <div class="card-body" style="padding: 16px 20px;">
                <?php if (empty($departmentStats)): ?>
                    <p style="color: var(--text-muted); font-size: 0.88rem;">No department data yet.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($departmentStats as $dept): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid var(--border-subtle);">
                                <span style="font-weight: 600; font-size: 0.88rem; color: var(--text-primary);"><?= htmlspecialchars($dept['department']); ?></span>
                                <span class="badge" style="background: var(--bg-muted); color: var(--text-secondary); font-size: 0.8rem; font-weight: 700;">
                                    <?= $dept['count']; ?> <?= ($dept['count'] == 1) ? 'Patient' : 'Patients'; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Receptionist Guidance Card -->
        <div class="card" style="background: #f0fdf4; border-color: #bbf7d0; margin-bottom: 0;">
            <div class="card-body" style="padding: 20px;">
                <h4 style="color: #166534; font-size: 0.95rem; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    Receptionist Protocol
                </h4>
                <p style="font-size: 0.84rem; color: #15803d; line-height: 1.5; margin-bottom: 14px;">
                    Please ensure emergency contact information and any known drug allergies are confirmed with the patient upon admission.
                </p>
                <a href="add-patient.php" class="btn btn-success btn-sm" style="width: 100%;">+ Register New Patient</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
