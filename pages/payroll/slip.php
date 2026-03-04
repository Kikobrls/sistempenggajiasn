<?php
$pageTitle = 'Slip Gaji';
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
if (!isLoggedIn()) { header('Location: /pages/auth/login.php'); exit; }

$db = db();
$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT p.*, e.first_name, e.last_name, e.employee_id as emp_id, e.email, e.phone, 
    e.bank_name, e.bank_account, e.npwp, e.bpjs_kesehatan, e.bpjs_ketenagakerjaan, e.employment_status, e.hire_date,
    d.name as department_name, pos.name as position_name, pos.level as position_level
    FROM payroll p 
    JOIN employees e ON p.employee_id = e.id 
    LEFT JOIN departments d ON e.department_id = d.id 
    LEFT JOIN positions pos ON e.position_id = pos.id 
    WHERE p.id = ?");
$stmt->execute([$id]);
$slip = $stmt->fetch();

if (!$slip) { header('Location: /pages/payroll/index.php'); exit; }

$allowances = json_decode($slip['allowance_details'], true) ?: [];
$deductions = json_decode($slip['deduction_details'], true) ?: [];
$attendance = json_decode($slip['attendance_summary'], true) ?: [];

$months = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="content-header-modern">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="animate-fade-in"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Slip Gaji</h1>
                    <p class="subtitle mb-0">Periode: <?= $months[$slip['period_month']] . ' ' . $slip['period_year'] ?></p>
                </div>
                <div class="d-flex gap-2 no-print">
                    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-1"></i> Cetak</button>
                    <a href="/pages/payroll/index.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="payslip-container">
            <!-- Header -->
            <div class="payslip-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-1"><?= COMPANY_NAME ?></h3>
                        <p class="mb-0 opacity-75" style="font-size:0.9rem;"><?= COMPANY_ADDRESS ?></p>
                        <p class="mb-0 opacity-75" style="font-size:0.85rem;"><?= COMPANY_PHONE ?> | <?= COMPANY_EMAIL ?></p>
                    </div>
                    <div class="text-end">
                        <div style="width:60px;height:60px;background:rgba(255,255,255,0.15);border-radius:14px;display:flex;align-items:center;justify-content:center;margin-left:auto;">
                            <i class="bi bi-cash-stack" style="font-size:1.8rem;"></i>
                        </div>
                    </div>
                </div>
                <hr class="my-3 opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0">SLIP GAJI KARYAWAN</h5>
                        <span class="opacity-75">Periode: <?= $months[$slip['period_month']] . ' ' . $slip['period_year'] ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Body -->
            <div class="payslip-body">
                <!-- Employee Info -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0" style="font-size:0.875rem;">
                            <tr><td class="text-muted" style="width:140px;">Nama</td><td class="fw-semibold">: <?= $slip['first_name'] . ' ' . $slip['last_name'] ?></td></tr>
                            <tr><td class="text-muted">Departemen</td><td>: <?= $slip['department_name'] ?? '-' ?></td></tr>
                            <tr><td class="text-muted">Jabatan</td><td>: <?= $slip['position_name'] ?? '-' ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0" style="font-size:0.875rem;">
                            <tr><td class="text-muted" style="width:140px;">Status</td><td>: <?= $slip['employment_status'] ?></td></tr>
                            <tr><td class="text-muted">Bank</td><td>: <?= ($slip['bank_name'] ?: '-') . ' - ' . ($slip['bank_account'] ?: '-') ?></td></tr>
                            <tr><td class="text-muted">NPWP</td><td>: <?= $slip['npwp'] ?: '-' ?></td></tr>
                            <tr><td class="text-muted">Tgl. Masuk</td><td>: <?= formatDate($slip['hire_date']) ?></td></tr>
                        </table>
                    </div>
                </div>
                
                <!-- Attendance Summary -->
                <?php if (!empty($attendance)): ?>
                <div class="p-3 mb-4" style="background:#f8fafc; border-radius:10px;">
                    <h6 class="fw-bold mb-2" style="font-size:0.85rem;"><i class="bi bi-calendar-check me-1"></i>Ringkasan Kehadiran</h6>
                    <div class="row g-2" style="font-size:0.8rem;">
                        <div class="col-4 col-md-2"><span class="text-muted">Hari Kerja:</span> <strong><?= $attendance['working_days'] ?? 22 ?></strong></div>
                        <div class="col-4 col-md-2"><span class="text-muted">Hadir:</span> <strong class="text-success"><?= $attendance['present_days'] ?? 0 ?></strong></div>
                        <div class="col-4 col-md-2"><span class="text-muted">Absen:</span> <strong class="text-danger"><?= $attendance['absent_days'] ?? 0 ?></strong></div>
                        <div class="col-4 col-md-2"><span class="text-muted">Sakit:</span> <strong><?= $attendance['sick_days'] ?? 0 ?></strong></div>
                        <div class="col-4 col-md-2"><span class="text-muted">Cuti:</span> <strong><?= $attendance['leave_days'] ?? 0 ?></strong></div>
                        <div class="col-4 col-md-2"><span class="text-muted">Lembur:</span> <strong class="text-primary"><?= $attendance['overtime_hours'] ?? 0 ?> jam</strong></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Salary Details -->
                <div class="row g-4">
                    <!-- Pendapatan -->
                    <div class="col-md-6">
                        <h6 class="fw-bold text-success mb-3"><i class="bi bi-plus-circle me-1"></i>PENDAPATAN</h6>
                        <table class="table table-sm" style="font-size:0.875rem;">
                            <tr>
                                <td>Gaji Pokok</td>
                                <td class="text-end fw-semibold"><?= formatRupiah($slip['base_salary']) ?></td>
                            </tr>
                            <?php foreach ($allowances as $a): ?>
                            <tr>
                                <td><?= $a['name'] ?></td>
                                <td class="text-end"><?= formatRupiah($a['amount']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if ($slip['total_overtime'] > 0): ?>
                            <tr>
                                <td>Lembur</td>
                                <td class="text-end"><?= formatRupiah($slip['total_overtime']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="fw-bold" style="border-top:2px solid #10b981;">
                                <td class="text-success">Total Pendapatan</td>
                                <td class="text-end text-success"><?= formatRupiah($slip['gross_salary']) ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Potongan -->
                    <div class="col-md-6">
                        <h6 class="fw-bold text-danger mb-3"><i class="bi bi-dash-circle me-1"></i>POTONGAN</h6>
                        <table class="table table-sm" style="font-size:0.875rem;">
                            <?php foreach ($deductions as $d): ?>
                            <?php if ($d['amount'] > 0): ?>
                            <tr>
                                <td><?= $d['name'] ?></td>
                                <td class="text-end"><?= formatRupiah($d['amount']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            <tr>
                                <td>PPh 21</td>
                                <td class="text-end"><?= formatRupiah($slip['tax_amount']) ?></td>
                            </tr>
                            <tr class="fw-bold" style="border-top:2px solid #ef4444;">
                                <td class="text-danger">Total Potongan</td>
                                <td class="text-end text-danger"><?= formatRupiah($slip['total_deductions'] + $slip['tax_amount']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Footer - Net Salary -->
            <div class="payslip-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1">GAJI BERSIH (Gaji Dibawa Pulang)</h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-badge status-<?= $slip['status'] ?>"><?= ucfirst($slip['status']) ?></span>
                            <?php if ($slip['paid_date']): ?>
                            <small class="text-muted">Dibayar: <?= formatDate($slip['paid_date']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <h3 class="fw-bold text-primary mb-0"><?= formatRupiah($slip['net_salary']) ?></h3>
                </div>
            </div>
            
            <!-- Disclaimer -->
            <div class="p-3 text-center" style="font-size:0.75rem; color:#94a3b8;">
                Dokumen ini digenerate secara otomatis oleh sistem <?= APP_NAME ?>. 
                Jika ada pertanyaan, hubungi HRD di <?= COMPANY_EMAIL ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
