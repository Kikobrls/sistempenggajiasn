<?php
$pageTitle = 'Daftar Penggajian';
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
if (!isLoggedIn()) { header('Location: /pages/auth/login.php'); exit; }

$db = db();

$filterMonth = (int)($_GET['month'] ?? date('m'));
$filterYear = (int)($_GET['year'] ?? date('Y'));
$filterStatus = $_GET['status'] ?? '';

$where = "WHERE p.period_month = {$filterMonth} AND p.period_year = {$filterYear}";
if ($filterStatus) {
    $where .= " AND p.status = " . $db->quote($filterStatus);
}

$payrolls = $db->query("SELECT p.*, e.first_name, e.last_name, e.employee_id as emp_id, d.name as department_name 
    FROM payroll p 
    JOIN employees e ON p.employee_id = e.id 
    LEFT JOIN departments d ON e.department_id = d.id 
    {$where} 
    ORDER BY e.first_name")->fetchAll();

$summary = $db->query("SELECT 
    COUNT(*) as total_records,
    SUM(gross_salary) as total_gross,
    SUM(total_deductions) as total_deductions,
    SUM(tax_amount) as total_tax,
    SUM(net_salary) as total_net,
    SUM(CASE WHEN status='dibayar' THEN 1 ELSE 0 END) as paid_count,
    SUM(CASE WHEN status='draf' THEN 1 ELSE 0 END) as draft_count,
    SUM(CASE WHEN status='disetujui' THEN 1 ELSE 0 END) as approved_count
    FROM payroll p {$where}")->fetch();

$months = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="content-header-modern">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="animate-fade-in"><i class="bi bi-cash-coin me-2 text-primary"></i>Daftar Penggajian</h1>
                    <p class="subtitle mb-0"><?= $months[$filterMonth] ?> <?= $filterYear ?></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/pages/payroll/generate.php" class="btn btn-primary"><i class="bi bi-calculator me-1"></i> Generate Gaji</a>
                </div>
            </div>
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
                        <label class="form-label mb-1" style="font-size:0.8rem;">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="draf" <?=$filterStatus=='draf'?'selected':''?>>Draf</option>
                            <option value="diproses" <?=$filterStatus=='diproses'?'selected':''?>>Diproses</option>
                            <option value="disetujui" <?=$filterStatus=='disetujui'?'selected':''?>>Disetujui</option>
                            <option value="dibayar" <?=$filterStatus=='dibayar'?'selected':''?>>Dibayar</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="info-mini-card">
                    <div class="mini-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-receipt"></i></div>
                    <div>
                        <div class="fw-bold"><?= $summary['total_records'] ?? 0 ?></div>
                        <small class="text-muted">Total Data</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-mini-card">
                    <div class="mini-icon bg-success bg-opacity-10 text-success"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <div class="fw-bold" style="font-size:0.85rem;"><?= formatRupiah($summary['total_net'] ?? 0) ?></div>
                        <small class="text-muted">Total Gaji Bersih</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-mini-card">
                    <div class="mini-icon bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <div class="fw-bold"><?= $summary['paid_count'] ?? 0 ?></div>
                        <small class="text-muted">Sudah Dibayar</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-mini-card">
                    <div class="mini-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-clock"></i></div>
                    <div>
                        <div class="fw-bold"><?= ($summary['draft_count'] ?? 0) + ($summary['approved_count'] ?? 0) ?></div>
                        <small class="text-muted">Menunggu</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Data Penggajian</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-success" onclick="bulkAction('disetujui')"><i class="bi bi-check2-all me-1"></i>Setujui Terpilih</button>
                    <button class="btn btn-sm btn-primary" onclick="bulkAction('dibayar')"><i class="bi bi-cash me-1"></i>Bayar Terpilih</button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="payrollTable" class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll" class="form-check-input"></th>
                                <th>No. Slip</th>
                                <th>Karyawan</th>
                                <th>Departemen</th>
                                <th>Gaji Pokok</th>
                                <th>Tunjangan</th>
                                <th>Potongan</th>
                                <th>Gaji Bersih</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payrolls as $p): ?>
                            <tr>
                                <td><input type="checkbox" class="form-check-input payroll-check" value="<?= $p['id'] ?>"></td>
                                <td><code class="fw-semibold"><?= $p['payroll_number'] ?></code></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle avatar-sm" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);"><?= strtoupper(substr($p['first_name'],0,1)) ?></div>
                                        <div>
                                            <div class="fw-semibold"><?= $p['first_name'] . ' ' . $p['last_name'] ?></div>
                                            <small class="text-muted"><?= $p['emp_id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= $p['department_name'] ?? '-' ?></span></td>
                                <td><?= formatRupiah($p['base_salary']) ?></td>
                                <td class="text-success"><?= formatRupiah($p['total_allowances']) ?></td>
                                <td class="text-danger"><?= formatRupiah($p['total_deductions'] + $p['tax_amount']) ?></td>
                                <td class="fw-bold"><?= formatRupiah($p['net_salary']) ?></td>
                                <td><span class="status-badge status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/pages/payroll/slip.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary" title="Slip Gaji"><i class="bi bi-file-text"></i></a>
                                        <?php if ($p['status'] == 'draf'): ?>
                                        <button onclick="updateStatus(<?=$p['id']?>, 'disetujui')" class="btn btn-outline-success" title="Setujui"><i class="bi bi-check-lg"></i></button>
                                        <?php endif; ?>
                                        <?php if ($p['status'] == 'disetujui'): ?>
                                        <button onclick="updateStatus(<?=$p['id']?>, 'dibayar')" class="btn btn-outline-success" title="Bayar"><i class="bi bi-cash"></i></button>
                                        <?php endif; ?>
                                        <?php if ($p['status'] == 'draf'): ?>
                                        <button onclick="App.deleteRecord('/pages/api/payroll.php', <?=$p['id']?>, '<?=$p['payroll_number']?>')" class="btn btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    App.initDataTable('#payrollTable', { order: [[2, 'asc']], columnDefs: [{orderable: false, targets: [0, 9]}] });
    
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.payroll-check').forEach(cb => cb.checked = this.checked);
    });
});

async function updateStatus(id, status) {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('id', id);
    formData.append('status', status);
    
    App.showLoading();
    try {
        const resp = await fetch('/pages/api/payroll.php', { method: 'POST', body: formData });
        const data = await resp.json();
        App.hideLoading();
        if (data.success) {
            App.alert.toast('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            App.alert.error('Gagal!', data.message);
        }
    } catch (err) {
        App.hideLoading();
        App.alert.error('Error!', 'Terjadi kesalahan');
    }
}

async function bulkAction(status) {
    const checked = [...document.querySelectorAll('.payroll-check:checked')];
    if (!checked.length) {
        App.alert.error('Perhatian', 'Pilih data terlebih dahulu');
        return;
    }
    
    const result = await App.alert.confirm('Konfirmasi', `Ubah status ${checked.length} data ke "${status}"?`);
    if (!result.isConfirmed) return;
    
    const formData = new FormData();
    formData.append('action', 'bulk_status');
    formData.append('status', status);
    checked.forEach(cb => formData.append('ids[]', cb.value));
    
    App.showLoading();
    try {
        const resp = await fetch('/pages/api/payroll.php', { method: 'POST', body: formData });
        const data = await resp.json();
        App.hideLoading();
        if (data.success) {
            await App.alert.success('Berhasil!', data.message);
            location.reload();
        } else {
            App.alert.error('Gagal!', data.message);
        }
    } catch (err) {
        App.hideLoading();
        App.alert.error('Error!', 'Terjadi kesalahan');
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
