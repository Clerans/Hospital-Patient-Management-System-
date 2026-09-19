<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDBConnection();
$patientId = (int)($_GET['id'] ?? 0);

if ($patientId <= 0) {
    setFlash('danger', 'Invalid patient identifier provided.');
    header('Location: patients.php');
    exit;
}

// Fetch existing patient
$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $patientId]);
$patient = $stmt->fetch();

if (!$patient) {
    setFlash('danger', 'Patient record not found.');
    header('Location: patients.php');
    exit;
}

$errors = [];
$formData = $patient; // default to existing DB values

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security verification failed (Invalid CSRF Token). Please reload and try again.';
    }

    // Capture POST fields
    $formData = [
        'id'                         => $patientId,
        'patient_code'               => $patient['patient_code'], // Keep immutable code
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

    // Validation
    if (empty($formData['first_name'])) {
        $errors[] = 'First Name is required.';
    }
    if (empty($formData['last_name'])) {
        $errors[] = 'Last Name is required.';
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
        $errors[] = 'Emergency contact name and phone number are required.';
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

    if (empty($errors)) {
        try {
            $updateSql = "UPDATE patients SET
                first_name = :first_name,
                last_name = :last_name,
                nic_passport = :nic_passport,
                date_of_birth = :date_of_birth,
                gender = :gender,
                blood_group = :blood_group,
                phone = :phone,
                email = :email,
                address = :address,
                city = :city,
                emergency_contact_name = :emergency_contact_name,
                emergency_contact_relation = :emergency_contact_relation,
                emergency_contact_phone = :emergency_contact_phone,
                department = :department,
                assigned_doctor = :assigned_doctor,
                admission_date = :admission_date,
                patient_status = :patient_status,
                room_bed_no = :room_bed_no,
                allergies = :allergies,
                medical_history = :medical_history,
                symptoms_diagnosis = :symptoms_diagnosis
            WHERE id = :id";

            $stmtUpdate = $pdo->prepare($updateSql);
            $stmtUpdate->execute([
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
                ':id'                         => $patientId
            ]);

            setFlash('success', "Patient record <strong>{$formData['patient_code']}</strong> has been updated successfully.");
            header("Location: view-patient.php?id={$patientId}");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database update error: " . $e->getMessage();
        }
    }
}

$deptList = getDepartmentsList();
$bloodList = getBloodGroups();
$admissionDateFormatted = date('Y-m-d\TH:i', strtotime($formData['admission_date']));

$pageTitle = 'Edit Patient Record';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-title-wrap">
        <h1>Edit Patient Details</h1>
        <p class="page-subtitle">Modifying record for <strong><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></strong> (<?= htmlspecialchars($patient['patient_code']); ?>)</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="view-patient.php?id=<?= $patientId; ?>" class="btn btn-secondary">
            &larr; View Profile
        </a>
    </div>
</div>

<!-- Display Errors -->
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

<!-- Edit Patient Form Card -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Update Patient Information
        </div>
        <span class="patient-code-badge"><?= htmlspecialchars($patient['patient_code']); ?></span>
    </div>

    <div class="card-body">
        <form action="edit-patient.php?id=<?= $patientId; ?>" method="POST" id="editPatientForm">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= getCsrfToken(); ?>">

            <!-- SECTION 1: Personal Details -->
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
                        <label class="form-label">Patient Code</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($patient['patient_code']); ?>" readonly style="background-color: var(--bg-subtle); font-family: monospace; font-weight: 700;">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="first_name">First Name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="form-control" value="<?= htmlspecialchars($formData['first_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" class="form-control" value="<?= htmlspecialchars($formData['last_name'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nic_passport">NIC / Passport</label>
                        <input type="text" id="nic_passport" name="nic_passport" class="form-control" value="<?= htmlspecialchars($formData['nic_passport'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="date_of_birth">Date of Birth <span class="required">*</span></label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($formData['date_of_birth'] ?? ''); ?>" required max="<?= date('Y-m-d'); ?>">
                        <span class="form-help">Calculated Age: <strong id="calculated_age_display" style="color: var(--primary-dark);"><?= calculateAge($formData['date_of_birth'] ?? ''); ?></strong></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="gender">Gender <span class="required">*</span></label>
                        <select id="gender" name="gender" class="form-select" required>
                            <option value="Male" <?= (($formData['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?= (($formData['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?= (($formData['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="blood_group">Blood Group</label>
                        <select id="blood_group" name="blood_group" class="form-select">
                            <?php foreach ($bloodList as $bg): ?>
                                <option value="<?= htmlspecialchars($bg); ?>" <?= (($formData['blood_group'] ?? '') === $bg) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($bg); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Contact & Emergency Info -->
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
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($formData['phone'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($formData['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="city">City / District</label>
                        <input type="text" id="city" name="city" class="form-control" value="<?= htmlspecialchars($formData['city'] ?? 'Colombo'); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="address">Residential Address <span class="required">*</span></label>
                    <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($formData['address'] ?? ''); ?>" required>
                </div>

                <div class="emergency-contact-box">
                    <h4 style="color: #9f1239; font-size: 0.95rem; margin-bottom: 12px;">🚨 Emergency Contact Person</h4>
                    <div class="form-row">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="emergency_contact_name">Contact Person Name <span class="required">*</span></label>
                            <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" value="<?= htmlspecialchars($formData['emergency_contact_name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="emergency_contact_relation">Relationship <span class="required">*</span></label>
                            <input type="text" id="emergency_contact_relation" name="emergency_contact_relation" class="form-control" value="<?= htmlspecialchars($formData['emergency_contact_relation'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="emergency_contact_phone">Emergency Phone <span class="required">*</span></label>
                            <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" value="<?= htmlspecialchars($formData['emergency_contact_phone'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: Clinical Details -->
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
                            <?php foreach ($deptList as $d): ?>
                                <option value="<?= htmlspecialchars($d); ?>" <?= (($formData['department'] ?? '') === $d) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($d); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="assigned_doctor">Assigned Attending Doctor <span class="required">*</span></label>
                        <input type="text" id="assigned_doctor" name="assigned_doctor" class="form-control" value="<?= htmlspecialchars($formData['assigned_doctor'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="patient_status">Patient Status <span class="required">*</span></label>
                        <select id="patient_status" name="patient_status" class="form-select" required>
                            <option value="Outpatient" <?= (($formData['patient_status'] ?? '') === 'Outpatient') ? 'selected' : ''; ?>>Outpatient</option>
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
                        <input type="datetime-local" id="admission_date" name="admission_date" class="form-control" value="<?= $admissionDateFormatted; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="room_bed_no">Assigned Ward / Bed / Room</label>
                        <input type="text" id="room_bed_no" name="room_bed_no" class="form-control" value="<?= htmlspecialchars($formData['room_bed_no'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="allergies">Known Allergies</label>
                        <textarea id="allergies" name="allergies" class="form-control"><?= htmlspecialchars($formData['allergies'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="medical_history">Pre-existing Medical History</label>
                        <textarea id="medical_history" name="medical_history" class="form-control"><?= htmlspecialchars($formData['medical_history'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="symptoms_diagnosis">Symptoms & Clinical Diagnosis Notes <span class="required">*</span></label>
                    <textarea id="symptoms_diagnosis" name="symptoms_diagnosis" class="form-control" rows="3" required><?= htmlspecialchars($formData['symptoms_diagnosis'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 14px; margin-top: 24px;">
                <a href="view-patient.php?id=<?= $patientId; ?>" class="btn btn-secondary btn-lg">Cancel</a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
