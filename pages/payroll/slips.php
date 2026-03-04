<?php
$pageTitle = 'Cetak Slip Gaji';
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
if (!isLoggedIn()) { header('Location: /pages/auth/login.php'); exit; }

$db = db();

$filterMonth = (int)($_GET['month'] ?? date('m'));
$filterYear = (int)($_GET['year'] ?? date('Y'));

$payrolls = $db->query("SELECT p.id, p.payroll_number, p.period_month, p.period_year, p.net_salary, p.status, 
    e.first_name, e.last_name, e.employee_id as emp_id, d.name as department_name
    FROM payroll p 
    JOIN employees e ON p.employee_id=e.id 
    LEFT JOIN departments d ON e.department_id=d.id 
    WHERE p.period_month={$filterMonth} AND p.period_year={$filterYear} 
    ORDER BY e.first_name")->fetchAll();

$months = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="content-header-modern">
            <h1 class="animate-fade-in"><i class="bi bi-printer me-2 text-primary"></i>Slip Gaji</h1>
            <p class="subtitle mb-0">Cetak slip gaji karyawan</p>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body py-3">
                <form method="get" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label mb-1" style="font-size:0.8rem;">Bulan</label>
                        <select name="month" class="form-select form-select-sm">
                            <?php for ($m=1; $m<=12; $m++): ?>
                            <option value="<?=$m?>" <?=$filterMonth==$m?'selected':''?>><?=$months[$m]?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label mb-1" style="font-size:0.8rem;">Tahun</label>
                        <select name="year" class="form-select form-select-sm">
                            <?php for ($y=2024; $y<=2027; $y++): ?>
                            <option value="<?=$y?>" <?=$filterYear==$y?'selected':''?>><?=$y?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($payrolls)): ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-file-earmark-x"></i></div>
                    <h5>Belum Ada Data</h5>
                    <p class="text-muted">Tidak ada data penggajian untuk periode <?= $months[$filterMonth] ?> <?= $filterYear ?></p>
                    <a href="/pages/payroll/generate.php" class="btn btn-primary"><i class="bi bi-calculator me-1"></i> Generate Gaji</a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Slip Gaji - <?= $months[$filterMonth] ?> <?= $filterYear ?></h5>
                <span class="badge bg-primary bg-opacity-10 text-primary"><?= count($payrolls) ?> karyawan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Karyawan</th>
                                <th>Departemen</th>
                                <th>Gaji Bersih</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payrolls as $i => $p): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td>
                                    <div class="fw-semibold"><?= $p['first_name'] . ' ' . $p['last_name'] ?></div>
                                </td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= $p['department_name'] ?? '-' ?></span></td>
                                <td class="fw-bold"><?= formatRupiah($p['net_salary']) ?></td>
                                <td><span class="status-badge status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                                <td>
                                    <a href="/pages/payroll/slip.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bi bi-file-text me-1"></i> Lihat Slip
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
