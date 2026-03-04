<?php
$pageTitle = 'Detail Karyawan';
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
if (!isLoggedIn()) { header('Location: /pages/auth/login.php'); exit; }

$db = db();
$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT e.*, d.name as department_name, p.name as position_name, p.level as position_level FROM employees e LEFT JOIN departments d ON e.department_id=d.id LEFT JOIN positions p ON e.position_id=p.id WHERE e.id=?");
$stmt->execute([$id]);
$emp = $stmt->fetch();
if (!$emp) { header('Location: /pages/employees/index.php'); exit; }

// Get payroll history
$payrolls = $db->prepare("SELECT * FROM payroll WHERE employee_id=? ORDER BY period_year DESC, period_month DESC LIMIT 12");
$payrolls->execute([$id]);
$payrollHistory = $payrolls->fetchAll();

// Get attendance
$attendances = $db->prepare("SELECT * FROM attendance WHERE employee_id=? ORDER BY period_year DESC, period_month DESC LIMIT 6");
$attendances->execute([$id]);
$attendanceHistory = $attendances->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="content-header-modern">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="animate-fade-in"><?= $emp['first_name'] . ' ' . $emp['last_name'] ?></h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/pages/employees/index.php">Karyawan</a></li>
                            <li class="breadcrumb-item active">Detail</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="/pages/employees/edit.php?id=<?= $emp['id'] ?>" class="btn btn-primary"><i class="bi bi-pencil me-1"></i> Edit</a>
                    <a href="/pages/employees/index.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Profile Card -->
            <div class="col-12 col-lg-4">
                <div class="card text-center">
                    <div class="card-body py-5">
                        <div class="avatar-circle avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, <?= $emp['gender']=='L'?'#4f46e5,#7c3aed':'#ec4899,#f43f5e' ?>); font-size:2rem;">
                            <?= strtoupper(substr($emp['first_name'],0,1) . substr($emp['last_name'],0,1)) ?>
                        </div>
                        <h4 class="fw-bold mb-1"><?= $emp['first_name'] . ' ' . $emp['last_name'] ?></h4>
                        <p class="text-muted mb-2"><?= $emp['position_name'] ?? '-' ?></p>
                        <span class="badge bg-primary bg-opacity-10 text-primary mb-3"><?= $emp['department_name'] ?? '-' ?></span>
                        
                        <hr>
                        
                        <div class="text-start">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">ID Karyawan</span>
                                <strong><?= $emp['employee_id'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Email</span>
                                <strong><?= $emp['email'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Telepon</span>
                                <strong><?= $emp['phone'] ?: '-' ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Jenis Kelamin</span>
                                <strong><?= $emp['gender']=='L'?'Laki-laki':'Perempuan' ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Tanggal Lahir</span>
                                <strong><?= $emp['birth_date'] ? formatDate($emp['birth_date']) : '-' ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Status</span>
                                <?php $sc = match($emp['employment_status']){ 'Tetap'=>'success','Kontrak'=>'primary','Magang'=>'warning',default=>'info' }; ?>
                                <span class="badge bg-<?=$sc?> bg-opacity-10 text-<?=$sc?>"><?= $emp['employment_status'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Tanggal Masuk</span>
                                <strong><?= formatDate($emp['hire_date']) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span class="text-muted">Gaji Pokok</span>
                                <strong class="text-primary"><?= formatRupiah($emp['base_salary']) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bank Info -->
                <div class="card mt-4">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="bi bi-bank me-2"></i>Informasi Bank</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Bank</span>
                            <strong><?= $emp['bank_name'] ?: '-' ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">No. Rekening</span>
                            <strong><?= $emp['bank_account'] ?: '-' ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">NPWP</span>
                            <strong><?= $emp['npwp'] ?: '-' ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">BPJS Kesehatan</span>
                            <strong><?= $emp['bpjs_kesehatan'] ?: '-' ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="col-12 col-lg-8">
                <!-- Payroll History -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="bi bi-cash-coin me-2"></i>Riwayat Penggajian</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Periode</th>
                                        <th>Gaji Pokok</th>
                                        <th>Tunjangan</th>
                                        <th>Potongan</th>
                                        <th>Gaji Bersih</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($payrollHistory)): ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data penggajian</td></tr>
                                    <?php else: ?>
                                    <?php $months = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']; ?>
                                    <?php foreach ($payrollHistory as $p): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= $months[$p['period_month']] . ' ' . $p['period_year'] ?></td>
                                        <td><?= formatRupiah($p['base_salary']) ?></td>
                                        <td class="text-success"><?= formatRupiah($p['total_allowances']) ?></td>
                                        <td class="text-danger"><?= formatRupiah($p['total_deductions']) ?></td>
                                        <td class="fw-bold text-primary"><?= formatRupiah($p['net_salary']) ?></td>
                                        <td><span class="status-badge status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Attendance -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-calendar-check me-2"></i>Riwayat Kehadiran</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Periode</th>
                                        <th>Hari Kerja</th>
                                        <th>Hadir</th>
                                        <th>Absen</th>
                                        <th>Sakit</th>
                                        <th>Cuti</th>
                                        <th>Terlambat</th>
                                        <th>Lembur (Jam)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($attendanceHistory)): ?>
                                    <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data kehadiran</td></tr>
                                    <?php else: ?>
                                    <?php foreach ($attendanceHistory as $a): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= $months[$a['period_month']] . ' ' . $a['period_year'] ?></td>
                                        <td><?= $a['working_days'] ?></td>
                                        <td><span class="badge bg-success bg-opacity-10 text-success"><?= $a['present_days'] ?></span></td>
                                        <td><span class="badge bg-danger bg-opacity-10 text-danger"><?= $a['absent_days'] ?></span></td>
                                        <td><?= $a['sick_days'] ?></td>
                                        <td><?= $a['leave_days'] ?></td>
                                        <td><?= $a['late_days'] ?></td>
                                        <td><?= $a['overtime_hours'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
