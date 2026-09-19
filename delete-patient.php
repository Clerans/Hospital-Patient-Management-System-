<?php
/**
 * Delete Patient Controller
 * Hospital Patient Management System
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDBConnection();
$patientId = (int)($_GET['id'] ?? 0);

if ($patientId <= 0) {
    setFlash('danger', 'Invalid patient ID specified for deletion.');
    header('Location: patients.php');
    exit;
}

try {
    // Check if patient exists first
    $stmtFind = $pdo->prepare("SELECT patient_code, first_name, last_name FROM patients WHERE id = :id LIMIT 1");
    $stmtFind->execute([':id' => $patientId]);
    $patient = $stmtFind->fetch();

    if ($patient) {
        $stmtDelete = $pdo->prepare("DELETE FROM patients WHERE id = :id");
        $stmtDelete->execute([':id' => $patientId]);

        setFlash('success', "Patient record for <strong>" . htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) . "</strong> (" . htmlspecialchars($patient['patient_code']) . ") has been permanently deleted.");
    } else {
        setFlash('warning', 'Patient record could not be found or was already deleted.');
    }
} catch (PDOException $e) {
    setFlash('danger', 'Error deleting patient record: ' . $e->getMessage());
}

header('Location: patients.php');
exit;
