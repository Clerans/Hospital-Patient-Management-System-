<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDBConnection();
$errors = [];
$formData = [];

// Auto-suggest next Patient Code
$nextPatientCode = generatePatientCode($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security verification failed (Invalid CSRF Token). Please reload and try again.';
    }

    // Capture & sanitize POST fields
    $formData = [
        'patient_code'               => trim($_POST['patient_code'] ?? $nextPatientCode),
        'first_name'                 => trim($_POST['first_name'] ?? ''),
        'last_name'                  => trim($_POST['last_name'] ?? ''),
        'nic_passport'               => trim($_POST['nic_passport'] ?? ''),
        'date_of_birth'              => trim($_POST['date_of_birth'] ?? ''),
        'gender'                     => trim($_POST['gender'] ?? ''),
        'blood_group'                => trim($_POST['blood_group'] ?? 'Unknown'),
        'phone'                      => trim($_POST['phone'] ?? ''),
        'email'                      => trim($_POST['email'] ?? ''),
        'address'                    => trim($_POST['address'] ?? ''),
        'city'                       => trim($_POST['city'] ?? 'Colombo'),
        'emergency_contact_name'     => trim($_POST['emergency_contact_name'] ?? ''),
        'emergency_contact_relation' => trim($_POST['emergency_contact_relation'] ?? ''),
        'emergency_contact_phone'    => trim($_POST['emergency_contact_phone'] ?? ''),
        'department'                 => trim($_POST['department'] ?? ''),
        'assigned_doctor'            => trim($_POST['assigned_doctor'] ?? ''),
        'admission_date'             => trim($_POST['admission_date'] ?? date('Y-m-d\TH:i')),
        'patient_status'             => trim($_POST['patient_status'] ?? 'Outpatient'),
        'room_bed_no'                => trim($_POST['room_bed_no'] ?? ''),
        'allergies'                  => trim($_POST['allergies'] ?? ''),
        'medical_history'            => trim($_POST['medical_history'] ?? ''),
        'symptoms_diagnosis'         => trim($_POST['symptoms_diagnosis'] ?? ''),
    ];

    // Field Validations
    if (empty($formData['first_name'])) {
        $errors[] = 'Patient First Name is required.';
    }
    if (empty($formData['last_name'])) {
        $errors[] = 'Patient Last Name is required.';
    }
    if (empty($formData['date_of_birth'])) {
        $errors[] = 'Date of Birth is required.';
    }
    if (empty($formData['gender'])) {
        $errors[] = 'Gender selection is required.';
    }
    if (empty($formData['phone'])) {
        $errors[] = 'Contact phone number is required.';
    }
    if (empty($formData['address'])) {
        $errors[] = 'Residential address is required.';
    }
    if (empty($formData['emergency_contact_name']) || empty($formData['emergency_contact_phone'])) {
        $errors[] = 'Emergency contact person name and contact phone are required.';
    }
    if (empty($formData['department'])) {
        $errors[] = 'Hospital department selection is required.';
    }
    if (empty($formData['assigned_doctor'])) {
        $errors[] = 'Assigned attending doctor name is required.';
    }
    if (empty($formData['symptoms_diagnosis'])) {
        $errors[] = 'Symptoms / Clinical diagnosis notes are required.';
    }

    // Check if patient code already exists
    if (empty($errors)) {
        $chkStmt = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE patient_code = :code");
        $chkStmt->execute([':code' => $formData['patient_code']]);
        if ($chkStmt->fetchColumn() > 0) {
            // Re-generate next available code
            $formData['patient_code'] = generatePatientCode($pdo);
        }
    }

    // Insert into MySQL Database
    if (empty($errors)) {
        try {
            $insertSql = "INSERT INTO patients (
                patient_code, first_name, last_name, nic_passport, date_of_birth, gender, blood_group,
                phone, email, address, city,
                emergency_contact_name, emergency_contact_relation, emergency_contact_phone,
                department, assigned_doctor, admission_date, patient_status, room_bed_no,
                allergies, medical_history, symptoms_diagnosis
            ) VALUES (
                :patient_code, :first_name, :last_name, :nic_passport, :date_of_birth, :gender, :blood_group,
                :phone, :email, :address, :city,
                :emergency_contact_name, :emergency_contact_relation, :emergency_contact_phone,
                :department, :assigned_doctor, :admission_date, :patient_status, :room_bed_no,
                :allergies, :medical_history, :symptoms_diagnosis
            )";

            $stmt = $pdo->prepare($insertSql);
            $stmt->execute([
                ':patient_code'               => $formData['patient_code'],
                ':first_name'                 => $formData['first_name'],
                ':last_name'                  => $formData['last_name'],
                ':nic_passport'               => !empty($formData['nic_passport']) ? $formData['nic_passport'] : null,
                ':date_of_birth'              => $formData['date_of_birth'],
                ':gender'                     => $formData['gender'],
                ':blood_group'                => $formData['blood_group'],
                ':phone'                      => $formData['phone'],
                ':email'                      => !empty($formData['email']) ? $formData['email'] : null,
                ':address'                    => $formData['address'],
                ':city'                       => $formData['city'],
                ':emergency_contact_name'     => $formData['emergency_contact_name'],
                ':emergency_contact_relation' => $formData['emergency_contact_relation'],
                ':emergency_contact_phone'    => $formData['emergency_contact_phone'],
                ':department'                 => $formData['department'],
                ':assigned_doctor'            => $formData['assigned_doctor'],
                ':admission_date'             => str_replace('T', ' ', $formData['admission_date']),
                ':patient_status'             => $formData['patient_status'],
                ':room_bed_no'                => !empty($formData['room_bed_no']) ? $formData['room_bed_no'] : null,
                ':allergies'                  => !empty($formData['allergies']) ? $formData['allergies'] : null,
                ':medical_history'            => !empty($formData['medical_history']) ? $formData['medical_history'] : null,
                ':symptoms_diagnosis'         => $formData['symptoms_diagnosis'],
            ]);

            $newPatientId = $pdo->lastInsertId();
            setFlash('success', "Patient record for <strong>{$formData['first_name']} {$formData['last_name']}</strong> ({$formData['patient_code']}) was registered successfully!");
            header("Location: view-patient.php?id={$newPatientId}");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database insertion error: " . $e->getMessage();
        }
    }
}

$deptList = getDepartmentsList();
$bloodList = getBloodGroups();

$pageTitle = 'Register Patient';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-title-wrap">
        <h1>Patient Admission & Registration</h1>
        <p class="page-subtitle">Complete the form below to register a new patient into the hospital management system.</p>
    </div>
    <a href="patients.php" class="btn btn-secondary">
        &larr; Back to Directory
    </a>
</div>

<!-- Display Validation Errors -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger animate-fade-in" role="alert">
        <div class="alert-content">
            <svg class="flash-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>
                <strong>Please correct the following errors:</strong>
                <ul style="margin-top: 6px; padding-left: 18px; font-size: 0.88rem;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
    </div>
<?php endif; ?>

<!-- Registration Form Card -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
                <line x1="23" y1="11" x2="17" y2="11"></line>
            </svg>
            Patient Registration Form
        </div>
        <span style="font-size: 0.85rem; color: var(--text-muted);">
            Fields marked with <span style="color: var(--danger); font-weight: bold;">*</span> are mandatory
        </span>
    </div>

    <div class="card-body">
        <form action="add-patient.php" method="POST" id="patientForm">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= getCsrfToken(); ?>">

            <!-- SECTION 1: Personal & Identification Details -->
            <div class="form-section">
                <div class="form-section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="7" r="4"></circle>
                        <path d="M5.5 21a8.38 8.38 0 0 1 13 0"></path>
                    </svg>
                    1. Personal & Identification Details
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="patient_code">Patient ID / Code <span class="required">*</span></label>
                        <input type="text" id="patient_code" name="patient_code" class="form-control" value="<?= htmlspecialchars($formData['patient_code'] ?? $nextPatientCode); ?>" readonly style="background-color: var(--bg-subtle); font-family: monospace; font-weight: 700;">
                        <span class="form-help">Auto-generated system reference identifier.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="form-control" placeholder="e.g. Kasun" value="<?= htmlspecialchars($formData['first_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" class="form-control" placeholder="e.g. Perera" value="<?= htmlspecialchars($formData['last_name'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nic_passport">NIC / Passport Number</label>
                        <input type="text" id="nic_passport" name="nic_passport" class="form-control" placeholder="e.g. 199523401234 or N1234567" value="<?= htmlspecialchars($formData['nic_passport'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="date_of_birth">Date of Birth <span class="required">*</span></label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($formData['date_of_birth'] ?? ''); ?>" required max="<?= date('Y-m-d'); ?>">
                        <span class="form-help">Calculated Age: <strong id="calculated_age_display" style="color: var(--primary-dark);">Enter DOB</strong></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="gender">Gender <span class="required">*</span></label>
                        <select id="gender" name="gender" class="form-select" required>
                            <option value="">-- Select Gender --</option>
                            <option value="Male" <?= (($formData['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?= (($formData['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?= (($formData['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="blood_group">Blood Group</label>
                        <select id="blood_group" name="blood_group" class="form-select">
                            <?php foreach ($bloodList as $bg): ?>
                                <option value="<?= htmlspecialchars($bg); ?>" <?= (($formData['blood_group'] ?? 'Unknown') === $bg) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($bg); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Contact & Emergency Information -->
            <div class="form-section">
                <div class="form-section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    2. Contact & Emergency Details
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. 0771234567" value="<?= htmlspecialchars($formData['phone'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="e.g. patient@example.com" value="<?= htmlspecialchars($formData['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="city">City / District</label>
                        <input type="text" id="city" name="city" class="form-control" placeholder="e.g. Colombo" value="<?= htmlspecialchars($formData['city'] ?? 'Colombo'); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="address">Residential Address <span class="required">*</span></label>
                    <input type="text" id="address" name="address" class="form-control" placeholder="e.g. 45/2 Galle Road, Bambalapitiya" value="<?= htmlspecialchars($formData['address'] ?? ''); ?>" required>
                </div>

                <div class="emergency-contact-box">
                    <h4 style="color: #9f1239; font-size: 0.95rem; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 2 22 22 22"></polygon>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        Emergency Contact Person (Mandatory)
                    </h4>
                    <div class="form-row">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="emergency_contact_name">Contact Person Name <span class="required">*</span></label>
                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" placeholder="e.g. Sunil Perera" value="<?= htmlspecialchars($formData['emergency_contact_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="emergency_contact_relation">Relationship <span class="required">*</span></label>
                            <input type="text" id="emergency_contact_relation" name="emergency_contact_relation" class="form-control" placeholder="e.g. Father, Spouse, Sibling" value="<?= htmlspecialchars($formData['emergency_contact_relation'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="emergency_contact_phone">Emergency Phone <span class="required">*</span></label>
                            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" placeholder="e.g. 0779876543" value="<?= htmlspecialchars($formData['emergency_contact_phone'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Clinical & Admission Details -->
            <div class="form-section">
                <div class="form-section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                    </svg>
                    3. Clinical & Admission Details
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="department">Hospital Department <span class="required">*</span></label>
                        <select id="department" name="department" class="form-select" required>
                            <option value="">-- Select Department --</option>
                            <?php foreach ($deptList as $d): ?>
                                <option value="<?= htmlspecialchars($d); ?>" <?= (($formData['department'] ?? '') === $d) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($d); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="assigned_doctor">Assigned Attending Doctor <span class="required">*</span></label>
                        <input type="text" id="assigned_doctor" name="assigned_doctor" class="form-control" placeholder="e.g. Dr. Nihal Senanayake" value="<?= htmlspecialchars($formData['assigned_doctor'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="patient_status">Patient Status <span class="required">*</span></label>
                        <select id="patient_status" name="patient_status" class="form-select" required>
                            <option value="Outpatient" <?= (($formData['patient_status'] ?? 'Outpatient') === 'Outpatient') ? 'selected' : ''; ?>>Outpatient</option>
                            <option value="Inpatient" <?= (($formData['patient_status'] ?? '') === 'Inpatient') ? 'selected' : ''; ?>>Inpatient</option>
                            <option value="Emergency" <?= (($formData['patient_status'] ?? '') === 'Emergency') ? 'selected' : ''; ?>>Emergency</option>
                            <option value="Discharged" <?= (($formData['patient_status'] ?? '') === 'Discharged') ? 'selected' : ''; ?>>Discharged</option>
                            <option value="Transferred" <?= (($formData['patient_status'] ?? '') === 'Transferred') ? 'selected' : ''; ?>>Transferred</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="admission_date">Admission / Visit Date & Time <span class="required">*</span></label>
                        <input type="datetime-local" id="admission_date" name="admission_date" class="form-control" value="<?= htmlspecialchars($formData['admission_date'] ?? date('Y-m-d\TH:i')); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="room_bed_no">Assigned Ward / Room / Bed No</label>
                        <input type="text" id="room_bed_no" name="room_bed_no" class="form-control" placeholder="e.g. Ward 3B - Bed 12 (if Inpatient)" value="<?= htmlspecialchars($formData['room_bed_no'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="allergies">Known Allergies (Food / Drugs)</label>
                        <textarea id="allergies" name="allergies" class="form-control" placeholder="e.g. Penicillin, Sulfa drugs, Seafood (or 'None reported')"><?= htmlspecialchars($formData['allergies'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="medical_history">Pre-existing Medical History</label>
                        <textarea id="medical_history" name="medical_history" class="form-control" placeholder="e.g. Hypertension, Type 2 Diabetes, Asthma..."><?= htmlspecialchars($formData['medical_history'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="symptoms_diagnosis">Symptoms & Initial Clinical Diagnosis <span class="required">*</span></label>
                    <textarea id="symptoms_diagnosis" name="symptoms_diagnosis" class="form-control" rows="3" placeholder="Describe presenting symptoms, clinical notes, and preliminary diagnosis..." required><?= htmlspecialchars($formData['symptoms_diagnosis'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Form Action Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 14px; margin-top: 24px;">
                <a href="patients.php" class="btn btn-secondary btn-lg">Cancel</a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Save & Register Patient
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
