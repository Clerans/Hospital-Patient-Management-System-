<?php
$pageTitle = 'Patient Directory';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

// Filtering & Search Inputs
$search = trim($_GET['search'] ?? '');
$department = trim($_GET['department'] ?? '');
$status = trim($_GET['status'] ?? '');
$bloodGroup = trim($_GET['blood_group'] ?? '');
$orderBy = trim($_GET['order_by'] ?? 'admission_date');
$orderDir = strtoupper(trim($_GET['order_dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

// Allowed columns for sorting to prevent SQL injection
$allowedSortColumns = [
    'patient_code'   => 'patient_code',
    'first_name'     => 'first_name',
    'date_of_birth'  => 'date_of_birth',
    'department'     => 'department',
    'patient_status' => 'patient_status',
    'admission_date' => 'admission_date'
];

$sortColumn = $allowedSortColumns[$orderBy] ?? 'admission_date';

// Pagination setup
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;
$offset = ($page - 1) * $perPage;

// Build Dynamic SQL Query
$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(patient_code LIKE :s1 
                     OR first_name LIKE :s2 
                     OR last_name LIKE :s3 
                     OR CONCAT(first_name, ' ', last_name) LIKE :s4
                     OR nic_passport LIKE :s5 
                     OR phone LIKE :s6 
                     OR assigned_doctor LIKE :s7)";
    $searchWildcard = "%{$search}%";
    $params[':s1'] = $searchWildcard;
    $params[':s2'] = $searchWildcard;
    $params[':s3'] = $searchWildcard;
    $params[':s4'] = $searchWildcard;
    $params[':s5'] = $searchWildcard;
    $params[':s6'] = $searchWildcard;
    $params[':s7'] = $searchWildcard;
}

if (!empty($department)) {
    $conditions[] = "department = :department";
    $params[':department'] = $department;
}

if (!empty($status)) {
    $conditions[] = "patient_status = :status";
    $params[':status'] = $status;
}

if (!empty($bloodGroup)) {
    $conditions[] = "blood_group = :bloodGroup";
    $params[':bloodGroup'] = $bloodGroup;
}

$whereSql = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Count total records for pagination
$countSql = "SELECT COUNT(*) FROM patients {$whereSql}";
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalRecords = (int)$stmtCount->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Fetch records for current page
$dataSql = "SELECT * FROM patients {$whereSql} ORDER BY {$sortColumn} {$orderDir} LIMIT :offset, :perPage";
$stmt = $pdo->prepare($dataSql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
$stmt->execute();
$patients = $stmt->fetchAll();

// List of available departments & blood groups for filter dropdowns
$deptList = getDepartmentsList();
$bloodList = getBloodGroups();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-title-wrap">
        <h1>Patient Directory</h1>
        <p class="page-subtitle">View, search, filter, and manage all hospital patient records.</p>
    </div>
    <div>
        <a href="add-patient.php" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Register Patient
        </a>
    </div>
</div>

<!-- Search & Filtering Toolbar -->
<div class="filter-toolbar">
    <form action="patients.php" method="GET" class="filter-form">
        <!-- Search Input -->
        <div class="search-input-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" name="search" class="form-control" placeholder="Search patient ID, name, phone, NIC..." value="<?= htmlspecialchars($search); ?>">
        </div>

        <!-- Department Filter -->
        <div>
            <select name="department" class="form-select" onchange="this.form.submit()">
                <option value="">All Departments</option>
                <?php foreach ($deptList as $d): ?>
                    <option value="<?= htmlspecialchars($d); ?>" <?= ($department === $d) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($d); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Status Filter -->
        <div>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="Inpatient" <?= ($status === 'Inpatient') ? 'selected' : ''; ?>>Inpatient</option>
                <option value="Outpatient" <?= ($status === 'Outpatient') ? 'selected' : ''; ?>>Outpatient</option>
                <option value="Emergency" <?= ($status === 'Emergency') ? 'selected' : ''; ?>>Emergency</option>
                <option value="Discharged" <?= ($status === 'Discharged') ? 'selected' : ''; ?>>Discharged</option>
                <option value="Transferred" <?= ($status === 'Transferred') ? 'selected' : ''; ?>>Transferred</option>
            </select>
        </div>

        <!-- Blood Group Filter -->
        <div>
            <select name="blood_group" class="form-select" onchange="this.form.submit()">
                <option value="">All Blood Groups</option>
                <?php foreach ($bloodList as $bg): ?>
                    <option value="<?= htmlspecialchars($bg); ?>" <?= ($bloodGroup === $bg) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($bg); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Submit & Reset Buttons -->
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary" title="Apply Filter">
                Filter
            </button>
            <?php if (!empty($search) || !empty($department) || !empty($status) || !empty($bloodGroup)): ?>
                <a href="patients.php" class="btn btn-secondary" title="Reset Filters">
                    Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Results Information & Sorting Links -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; font-size: 0.88rem; color: var(--text-muted); flex-wrap: wrap; gap: 10px;">
    <div>
        Showing <strong><?= count($patients); ?></strong> of <strong><?= $totalRecords; ?></strong> registered patient records
        <?php if (!empty($search)): ?>
            matching <span style="color: var(--primary-dark); font-weight: 700;">"<?= htmlspecialchars($search); ?>"</span>
        <?php endif; ?>
    </div>
    <div style="display: flex; align-items: center; gap: 8px;">
        <span style="font-weight: 600;">Sort by:</span>
        <a href="patients.php?<?= http_build_query(array_merge($_GET, ['order_by' => 'admission_date', 'order_dir' => ($orderBy === 'admission_date' && $orderDir === 'DESC') ? 'ASC' : 'DESC'])); ?>" class="btn btn-secondary btn-sm">
            Admission Date <?= ($orderBy === 'admission_date') ? ($orderDir === 'DESC' ? '↓' : '↑') : ''; ?>
        </a>
        <a href="patients.php?<?= http_build_query(array_merge($_GET, ['order_by' => 'first_name', 'order_dir' => ($orderBy === 'first_name' && $orderDir === 'ASC') ? 'DESC' : 'ASC'])); ?>" class="btn btn-secondary btn-sm">
            Name <?= ($orderBy === 'first_name') ? ($orderDir === 'ASC' ? '↑' : '↓') : ''; ?>
        </a>
    </div>
</div>

<!-- Patient Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 140px;">Patient ID</th>
                    <th>Patient Name</th>
                    <th>Age / Gender</th>
                    <th>Blood</th>
                    <th>Contact Phone</th>
                    <th>Department & Doctor</th>
                    <th>Status</th>
                    <th>Admission Date</th>
                    <th style="text-align: right; width: 170px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($patients)): ?>
                    <tr>
                        <td colspan="9" class="empty-state">
                            <div class="empty-state-icon">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <h3>No Matching Patients Found</h3>
                            <p>Try modifying your search keywords or clear the filters to see all patient records.</p>
                            <a href="patients.php" class="btn btn-secondary btn-sm">Clear All Filters</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($patients as $p): ?>
                        <tr>
                            <td>
                                <a href="view-patient.php?id=<?= $p['id']; ?>" class="patient-code-badge" title="Click to view profile">
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
                                        <?php if (!empty($p['nic_passport'])): ?>
                                            <div style="font-size: 0.76rem; color: var(--text-muted);">
                                                NIC: <?= htmlspecialchars($p['nic_passport']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><strong><?= calculateAge($p['date_of_birth']); ?></strong></div>
                                <div style="font-size: 0.76rem; color: var(--text-muted);"><?= htmlspecialchars($p['gender']); ?></div>
                            </td>
                            <td>
                                <span class="badge-blood"><?= htmlspecialchars($p['blood_group']); ?></span>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?= htmlspecialchars($p['phone']); ?></div>
                                <div style="font-size: 0.76rem; color: var(--text-muted);"><?= htmlspecialchars($p['city'] ?? 'Colombo'); ?></div>
                            </td>
                            <td>
                                <div><strong><?= htmlspecialchars($p['department']); ?></strong></div>
                                <div style="font-size: 0.76rem; color: var(--text-muted);"><?= htmlspecialchars($p['assigned_doctor']); ?></div>
                            </td>
                            <td>
                                <?= getStatusBadge($p['patient_status']); ?>
                                <?php if (!empty($p['room_bed_no'])): ?>
                                    <div style="font-size: 0.74rem; color: var(--text-muted); margin-top: 2px;">
                                        <?= htmlspecialchars($p['room_bed_no']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size: 0.84rem; font-weight: 600;"><?= formatDate($p['admission_date']); ?></div>
                                <div style="font-size: 0.74rem; color: var(--text-muted);"><?= date('h:i A', strtotime($p['admission_date'])); ?></div>
                            </td>
                            <td style="text-align: right;">
                                <div class="table-actions" style="justify-content: flex-end;">
                                    <a href="view-patient.php?id=<?= $p['id']; ?>" class="btn btn-secondary btn-sm" title="View Full Details">
                                        View
                                    </a>
                                    <a href="edit-patient.php?id=<?= $p['id']; ?>" class="btn btn-secondary btn-sm" title="Edit Patient Info">
                                        Edit
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-sm btn-delete-trigger" 
                                            data-id="<?= $p['id']; ?>" 
                                            data-name="<?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?>"
                                            data-code="<?= htmlspecialchars($p['patient_code']); ?>"
                                            title="Delete Record">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <div>
                Showing Page <strong><?= $page; ?></strong> of <strong><?= $totalPages; ?></strong>
            </div>
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="patients.php?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-btn">
                        &laquo; Prev
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="patients.php?<?= http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="page-btn <?= ($i === $page) ? 'active' : ''; ?>">
                        <?= $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="patients.php?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-btn">
                        Next &raquo;
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
