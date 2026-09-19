<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDBConnection();
$patientId = (int)($_GET['id'] ?? 0);

if ($patientId <= 0) {
    setFlash('danger', 'Invalid patient identifier specified.');
    header('Location: patients.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $patientId]);
$patient = $stmt->fetch();

if (!$patient) {
    setFlash('danger', 'Patient record not found or may have been deleted.');
    header('Location: patients.php');
    exit;
}

$fullName = $patient['first_name'] . ' ' . $patient['last_name'];
$initials = strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1));

$pageTitle = 'Patient Medical Record - ' . $fullName;
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Action Toolbar (No Print) -->
<div class="page-header no-print">
    <div>
        <a href="patients.php" class="btn btn-secondary btn-sm">
            &larr; Back to Patient Directory
        </a>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" class="btn btn-secondary" onclick="window.print();" title="Print Medical Card">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Print Medical Card
        </button>

        <a href="edit-patient.php?id=<?= $patient['id']; ?>" class="btn btn-primary" title="Edit Patient Information">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Edit Record
        </a>

        <button type="button" 
                class="btn btn-outline-danger btn-delete-trigger" 
                data-id="<?= $patient['id']; ?>" 
                data-name="<?= htmlspecialchars($fullName); ?>"
                data-code="<?= htmlspecialchars($patient['patient_code']); ?>"
                title="Delete Patient Record">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
            Delete
        </button>
    </div>
</div>

<!-- Patient Header Hero Card -->
<div class="patient-profile-header">
    <div class="profile-top-bar">
        <div class="profile-user-summary">
            <div class="profile-avatar-lg">
                <?= $initials; ?>
            </div>
            <div class="profile-name-area">
                <h2><?= htmlspecialchars($fullName); ?></h2>
                <div class="profile-meta-tags">
                    <span class="patient-code-badge" style="background: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.25);">
                        <?= htmlspecialchars($patient['patient_code']); ?>
                    </span>
                    <?= getStatusBadge($patient['patient_status']); ?>
                    <span class="badge" style="background: #e11d48; color: #ffffff; font-weight: 800; border: none;">
                        Blood: <?= htmlspecialchars($patient['blood_group']); ?>
                    </span>
                    <span style="font-size: 0.88rem; opacity: 0.9;">
                        <?= htmlspecialchars($patient['gender']); ?> &bull; <?= calculateAge($patient['date_of_birth']); ?> (DOB: <?= formatDate($patient['date_of_birth']); ?>)
                    </span>
                </div>
            </div>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 0.76rem; opacity: 0.75; text-transform: uppercase; letter-spacing: 0.6px;">Admission / Registration Date</div>
            <div style="font-size: 1.05rem; font-weight: 700; margin-top: 2px;"><?= formatDateTime($patient['admission_date']); ?></div>
        </div>
    </div>
</div>

<!-- Profile Details Grid -->
<div class="profile-info-grid">
    <!-- Card 1: Personal & Contact Information -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Personal & Contact Details
            </div>
        </div>
        <div class="card-body">
            <div class="info-item">
                <div class="info-label">Full Name</div>
                <div class="info-value"><?= htmlspecialchars($fullName); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">NIC / Passport Number</div>
                <div class="info-value"><?= !empty($patient['nic_passport']) ? htmlspecialchars($patient['nic_passport']) : '<span style="color: var(--text-light);">Not provided</span>'; ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">Date of Birth & Age</div>
                <div class="info-value"><?= formatDate($patient['date_of_birth']); ?> (<?= calculateAge($patient['date_of_birth']); ?>)</div>
            </div>

            <div class="info-item">
                <div class="info-label">Primary Phone Number</div>
                <div class="info-value">
                    <a href="tel:<?= htmlspecialchars($patient['phone']); ?>" style="color: var(--primary); font-weight: 700;">
                        <?= htmlspecialchars($patient['phone']); ?>
                    </a>
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">Email Address</div>
                <div class="info-value">
                    <?php if (!empty($patient['email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($patient['email']); ?>"><?= htmlspecialchars($patient['email']); ?></a>
                    <?php else: ?>
                        <span style="color: var(--text-light);">Not provided</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">Residential Address</div>
                <div class="info-value"><?= nl2br(htmlspecialchars($patient['address'])); ?>, <?= htmlspecialchars($patient['city'] ?? 'Colombo'); ?></div>
            </div>
        </div>
    </div>

    <!-- Card 2: Hospitalization & Emergency Contact -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
                Hospital & Department Allocation
            </div>
        </div>
        <div class="card-body">
            <div class="info-item">
                <div class="info-label">Department</div>
                <div class="info-value" style="color: var(--primary); font-size: 1.02rem; font-weight: 700;"><?= htmlspecialchars($patient['department']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">Assigned Attending Doctor</div>
                <div class="info-value"><?= htmlspecialchars($patient['assigned_doctor']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">Patient Status</div>
                <div class="info-value"><?= getStatusBadge($patient['patient_status']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">Assigned Ward / Bed / Room</div>
                <div class="info-value">
                    <?= !empty($patient['room_bed_no']) ? htmlspecialchars($patient['room_bed_no']) : '<span style="color: var(--text-light);">Outpatient (No bed allocated)</span>'; ?>
                </div>
            </div>

            <!-- Emergency Contact Block -->
            <div class="emergency-contact-box" style="margin-top: 18px;">
                <div class="info-label" style="color: #9f1239;">🚨 Emergency Contact Person</div>
                <div style="font-weight: 700; font-size: 1rem; color: var(--text-primary); margin-top: 2px;">
                    <?= htmlspecialchars($patient['emergency_contact_name']); ?>
                    <span style="font-size: 0.82rem; font-weight: 500; color: var(--text-muted);">
                        (<?= htmlspecialchars($patient['emergency_contact_relation']); ?>)
                    </span>
                </div>
                <div style="font-size: 0.92rem; font-weight: 700; color: #b91c1c; margin-top: 4px;">
                    📞 <?= htmlspecialchars($patient['emergency_contact_phone']); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card 3: Clinical Notes, Allergies & Medical History -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            Clinical Findings & Medical Background
        </div>
    </div>
    <div class="card-body">
        <div class="form-row">
            <div>
                <div class="info-label">Presenting Symptoms & Initial Diagnosis</div>
                <div class="medical-box" style="background: #ffffff; border-left: 4px solid var(--primary); font-size: 0.92rem;">
                    <?= nl2br(htmlspecialchars($patient['symptoms_diagnosis'])); ?>
                </div>
            </div>
        </div>

        <div class="form-row" style="margin-top: 16px;">
            <div>
                <div class="info-label">Known Allergies</div>
                <div class="medical-box" style="background: #fffbeb; border-left: 4px solid var(--warning);">
                    <?= !empty($patient['allergies']) ? nl2br(htmlspecialchars($patient['allergies'])) : '<span style="color: var(--text-muted);">No known allergies recorded.</span>'; ?>
                </div>
            </div>

            <div>
                <div class="info-label">Pre-existing Medical History</div>
                <div class="medical-box" style="background: var(--bg-muted); border-left: 4px solid var(--secondary);">
                    <?= !empty($patient['medical_history']) ? nl2br(htmlspecialchars($patient['medical_history'])) : '<span style="color: var(--text-muted);">No prior medical history on file.</span>'; ?>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: var(--text-muted); margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--border-main);">
            <div>System Record ID: <strong>#<?= $patient['id']; ?></strong></div>
            <div>Created: <strong><?= formatDateTime($patient['created_at']); ?></strong></div>
            <div>Last Modified: <strong><?= formatDateTime($patient['updated_at']); ?></strong></div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
