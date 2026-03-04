<?php
$pageTitle = 'Laporan Penggajian';
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
if (!isLoggedIn()) { header('Location: /pages/auth/login.php'); exit; }

$db = db();
$filterYear = (int)($_GET['year'] ?? date('Y'));

// Monthly summary for the year
$monthlySummary = $db->query("SELECT period_month, 
    COUNT(*) as total_employees,
    SUM(base_salary) as total_base,
    SUM(total_allowances) as total_allowances,
    SUM(total_overtime) as total_overtime,
    SUM(gross_salary) as total_gross,
    SUM(total_deductions) as total_deductions,
    SUM(tax_amount) as total_tax,
    SUM(net_salary) as total_net
    FROM payroll 
    WHERE period_year = {$filterYear} 
    GROUP BY period_month 
    ORDER BY period_month")->fetchAll();

// Department summary
$deptSummary = $db->query("SELECT d.name as department_name, 
    COUNT(DISTINCT p.employee_id) as total_employees,
    SUM(p.net_salary) as total_net,
    AVG(p.net_salary) as avg_net,
    MAX(p.net_salary) as max_net,
    MIN(p.net_salary) as min_net
    FROM payroll p 
    JOIN employees e ON p.employee_id = e.id 
    LEFT JOIN departments d ON e.department_id = d.id 
    WHERE p.period_year = {$filterYear}
    GROUP BY d.id, d.name 
    ORDER BY total_net DESC")->fetchAll();

// Yearly total
$yearTotal = $db->query("SELECT 
    COUNT(DISTINCT employee_id) as total_employees,
    SUM(gross_salary) as total_gross,
    SUM(total_deductions) as total_deductions,
    SUM(tax_amount) as total_tax,
    SUM(net_salary) as total_net
    FROM payroll WHERE period_year = {$filterYear}")->fetch();

// Top 10 highest salaries
$topSalaries = $db->query("SELECT e.first_name, e.last_name, e.employee_id as emp_id, d.name as dept_name, p.net_salary 
    FROM payroll p 
    JOIN employees e ON p.employee_id=e.id 
    LEFT JOIN departments d ON e.department_id=d.id 
    WHERE p.period_year={$filterYear} AND p.period_month=(SELECT MAX(period_month) FROM payroll WHERE period_year={$filterYear}) 
    ORDER BY p.net_salary DESC LIMIT 10")->fetchAll();

$months = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$monthsFull = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="content-header-modern">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="animate-fade-in"><i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>Laporan Penggajian</h1>
                    <p class="subtitle mb-0">Ringkasan dan analisis data penggajian tahun <?= $filterYear ?></p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <form method="get" class="d-flex gap-2">
                        <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                            <?php for ($y=2024; $y<=2027; $y++): ?>
                            <option value="<?=$y?>" <?=$filterYear==$y?'selected':''?>><?=$y?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                    <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="bi bi-printer me-1"></i> Cetak</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Year Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="card stats-card" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                    <div class="card-body text-white p-4">
                        <div class="stats-label text-white-50 mb-1">TOTAL KARYAWAN</div>
                        <div class="stats-number"><?= number_format($yearTotal['total_employees'] ?? 0) ?></div>
                        <small class="opacity-50">Tahun <?= $filterYear ?></small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card stats-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <div class="card-body text-white p-4">
                        <div class="stats-label text-white-50 mb-1">TOTAL GAJI BERSIH</div>
                        <div class="stats-number" style="font-size:1.2rem;"><?= formatRupiah($yearTotal['total_net'] ?? 0) ?></div>
                        <small class="opacity-50">Setahun</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card stats-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <div class="card-body text-white p-4">
                        <div class="stats-label text-white-50 mb-1">TOTAL POTONGAN</div>
                        <div class="stats-number" style="font-size:1.2rem;"><?= formatRupiah($yearTotal['total_deductions'] ?? 0) ?></div>
                        <small class="opacity-50">Setahun</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card stats-card" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                    <div class="card-body text-white p-4">
                        <div class="stats-label text-white-50 mb-1">TOTAL PAJAK</div>
                        <div class="stats-number" style="font-size:1.2rem;"><?= formatRupiah($yearTotal['total_tax'] ?? 0) ?></div>
                        <small class="opacity-50">PPh 21</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-8">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tren Penggajian Bulanan <?= $filterYear ?></h5>
                    </div>
                    <div class="card-body">
                        <div id="monthlyChart"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Distribusi Per Departemen</h5>
                    </div>
                    <div class="card-body">
                        <div id="deptChart"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Monthly Detail Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-table me-2"></i>Ringkasan Bulanan <?= $filterYear ?></h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th class="text-center">Karyawan</th>
                                <th>Gaji Pokok</th>
                                <th>Tunjangan</th>
                                <th>Lembur</th>
                                <th>Gaji Kotor</th>
                                <th>Potongan</th>
                                <th>Pajak</th>
                                <th class="fw-bold">Gaji Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grandBase = $grandAllow = $grandOT = $grandGross = $grandDed = $grandTax = $grandNet = 0;
                            foreach ($monthlySummary as $ms): 
                                $grandBase += $ms['total_base'];
                                $grandAllow += $ms['total_allowances'];
                                $grandOT += $ms['total_overtime'];
                                $grandGross += $ms['total_gross'];
                                $grandDed += $ms['total_deductions'];
                                $grandTax += $ms['total_tax'];
                                $grandNet += $ms['total_net'];
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= $monthsFull[$ms['period_month']] ?></td>
                                <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary"><?= $ms['total_employees'] ?></span></td>
                                <td><?= formatRupiah($ms['total_base']) ?></td>
                                <td class="text-success"><?= formatRupiah($ms['total_allowances']) ?></td>
                                <td><?= formatRupiah($ms['total_overtime']) ?></td>
                                <td><?= formatRupiah($ms['total_gross']) ?></td>
                                <td class="text-danger"><?= formatRupiah($ms['total_deductions']) ?></td>
                                <td class="text-danger"><?= formatRupiah($ms['total_tax']) ?></td>
                                <td class="fw-bold text-primary"><?= formatRupiah($ms['total_net']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot style="background:#f8fafc;">
                            <tr class="fw-bold">
                                <td>TOTAL</td>
                                <td></td>
                                <td><?= formatRupiah($grandBase) ?></td>
                                <td class="text-success"><?= formatRupiah($grandAllow) ?></td>
                                <td><?= formatRupiah($grandOT) ?></td>
                                <td><?= formatRupiah($grandGross) ?></td>
                                <td class="text-danger"><?= formatRupiah($grandDed) ?></td>
                                <td class="text-danger"><?= formatRupiah($grandTax) ?></td>
                                <td class="text-primary" style="font-size:1rem;"><?= formatRupiah($grandNet) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Department Summary & Top Salaries -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-7">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="bi bi-building me-2"></i>Ringkasan Per Departemen</h5></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Departemen</th>
                                        <th class="text-center">Karyawan</th>
                                        <th>Total Gaji</th>
                                        <th>Rata-rata</th>
                                        <th>Tertinggi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($deptSummary as $ds): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= $ds['department_name'] ?? 'Lainnya' ?></td>
                                        <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary"><?= $ds['total_employees'] ?></span></td>
                                        <td class="fw-bold"><?= formatRupiah($ds['total_net']) ?></td>
                                        <td><?= formatRupiah($ds['avg_net']) ?></td>
                                        <td><?= formatRupiah($ds['max_net']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="card h-100">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="bi bi-trophy me-2"></i>Top 10 Gaji Tertinggi</h5></div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($topSalaries as $i => $ts): ?>
                            <div class="list-group-item d-flex align-items-center gap-3 py-3">
                                <div class="avatar-circle avatar-sm" style="background:linear-gradient(135deg,<?= $i<3?'#f59e0b,#d97706':'#6366f1,#8b5cf6' ?>);">
                                    <?= $i + 1 ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?= $ts['first_name'] . ' ' . $ts['last_name'] ?></div>
                                    <small class="text-muted"><?= $ts['dept_name'] ?? '-' ?></small>
                                </div>
                                <span class="fw-bold text-primary"><?= formatRupiah($ts['net_salary']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyData = <?= json_encode($monthlySummary) ?>;
    const deptData = <?= json_encode($deptSummary) ?>;
    const months = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    
    // Monthly Chart
    if (document.getElementById('monthlyChart')) {
        new ApexCharts(document.getElementById('monthlyChart'), {
            series: [
                { name: 'Gaji Bersih', data: monthlyData.map(d => Math.round(d.total_net)) },
                { name: 'Potongan + Pajak', data: monthlyData.map(d => Math.round(parseFloat(d.total_deductions) + parseFloat(d.total_tax))) }
            ],
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Inter, sans-serif', stacked: false },
            colors: ['#4f46e5', '#f59e0b'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
            xaxis: { categories: monthlyData.map(d => months[d.period_month]) },
            yaxis: { labels: { formatter: v => 'Rp ' + (v/1000000).toFixed(0) + 'jt' } },
            tooltip: { y: { formatter: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) } },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            dataLabels: { enabled: false },
            legend: { position: 'top' }
        }).render();
    }
    
    // Department Chart
    if (document.getElementById('deptChart')) {
        new ApexCharts(document.getElementById('deptChart'), {
            series: deptData.map(d => Math.round(d.total_net)),
            labels: deptData.map(d => d.department_name || 'Lainnya'),
            chart: { type: 'donut', height: 300, fontFamily: 'Inter, sans-serif' },
            colors: ['#4f46e5','#10b981','#f59e0b','#ef4444','#06b6d4','#8b5cf6','#ec4899','#f97316'],
            plotOptions: { pie: { donut: { size: '60%' } } },
            legend: { position: 'bottom', fontSize: '11px' },
            dataLabels: { enabled: false }
        }).render();
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
