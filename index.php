<?php
$pageTitle = 'Dasbor';
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
if (!isLoggedIn()) { header('Location: /pages/auth/login.php'); exit; }

$db = db();

// Stats
$totalEmployees = $db->query("SELECT COUNT(*) FROM employees WHERE is_active=1")->fetchColumn();
$totalDepartments = $db->query("SELECT COUNT(*) FROM departments WHERE is_active=1")->fetchColumn();
$currentMonth = (int)date('m');
$currentYear = (int)date('Y');

$totalPayrollThisMonth = $db->query("SELECT COALESCE(SUM(net_salary),0) FROM payroll WHERE period_month={$currentMonth} AND period_year={$currentYear}")->fetchColumn();
$totalPayrollLastMonth = $db->query("SELECT COALESCE(SUM(net_salary),0) FROM payroll WHERE period_month=" . ($currentMonth > 1 ? $currentMonth-1 : 12) . " AND period_year=" . ($currentMonth > 1 ? $currentYear : $currentYear-1))->fetchColumn();

$pendingPayroll = $db->query("SELECT COUNT(*) FROM payroll WHERE status IN ('draf','diproses')")->fetchColumn();
$paidPayroll = $db->query("SELECT COUNT(*) FROM payroll WHERE status='dibayar' AND period_month={$currentMonth} AND period_year={$currentYear}")->fetchColumn();

$avgSalary = $db->query("SELECT COALESCE(AVG(net_salary),0) FROM payroll WHERE period_month={$currentMonth} AND period_year={$currentYear}")->fetchColumn();

// Department distribution
$deptDist = $db->query("SELECT d.name, COUNT(e.id) as count FROM departments d LEFT JOIN employees e ON d.id=e.department_id AND e.is_active=1 WHERE d.is_active=1 GROUP BY d.id, d.name ORDER BY count DESC")->fetchAll();

// Monthly payroll trend (last 6 months)
$monthlyTrend = $db->query("SELECT period_month, period_year, SUM(net_salary) as total FROM payroll WHERE period_year >= " . ($currentYear - 1) . " GROUP BY period_year, period_month ORDER BY period_year, period_month LIMIT 6")->fetchAll();

// Recent payroll activities
$recentPayrolls = $db->query("SELECT p.*, e.first_name, e.last_name, e.employee_id as emp_id FROM payroll p JOIN employees e ON p.employee_id = e.id ORDER BY p.updated_at DESC LIMIT 8")->fetchAll();

// Employment status distribution
$empStatus = $db->query("SELECT employment_status, COUNT(*) as count FROM employees WHERE is_active=1 GROUP BY employment_status")->fetchAll();

// Gender distribution
$genderDist = $db->query("SELECT gender, COUNT(*) as count FROM employees WHERE is_active=1 GROUP BY gender")->fetchAll();

// New hires this month
$newHires = $db->query("SELECT COUNT(*) FROM employees WHERE MONTH(hire_date)={$currentMonth} AND YEAR(hire_date)={$currentYear}")->fetchColumn();

include __DIR__ . '/includes/header.php';
?>

<!-- Content Header -->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="content-header-modern">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="animate-fade-in">Dasbor</h1>
                    <p class="subtitle mb-0">Selamat datang, <strong><?= $_SESSION['full_name'] ?? 'Admin' ?></strong>! Berikut ringkasan data penggajian.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/pages/payroll/generate.php" class="btn btn-primary">
                        <i class="bi bi-calculator me-1"></i> Generate Gaji
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3 animate-fade-in delay-1">
                <div class="card stats-card h-100" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                    <div class="card-body text-white p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stats-label text-white-50 mb-1">Total Karyawan</div>
                                <div class="stats-number"><?= number_format($totalEmployees) ?></div>
                                <div class="mt-2">
                                    <span class="stats-trend bg-white bg-opacity-25 text-white">
                                        <i class="bi bi-plus-lg"></i> <?= $newHires ?> baru
                                    </span>
                                </div>
                            </div>
                            <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-sm-6 col-xl-3 animate-fade-in delay-2">
                <div class="card stats-card h-100" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <div class="card-body text-white p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stats-label text-white-50 mb-1">Total Gaji Bulan Ini</div>
                                <div class="stats-number" style="font-size:1.4rem;"><?= formatRupiah($totalPayrollThisMonth) ?></div>
                                <div class="mt-2">
                                    <?php 
                                    $diff = $totalPayrollLastMonth > 0 ? (($totalPayrollThisMonth - $totalPayrollLastMonth) / $totalPayrollLastMonth) * 100 : 0;
                                    $diffIcon = $diff >= 0 ? 'arrow-up' : 'arrow-down';
                                    $diffColor = $diff >= 0 ? 'bg-white bg-opacity-25' : 'bg-danger bg-opacity-75';
                                    ?>
                                    <span class="stats-trend <?= $diffColor ?> text-white">
                                        <i class="bi bi-<?= $diffIcon ?>"></i> <?= number_format(abs($diff), 1) ?>%
                                    </span>
                                </div>
                            </div>
                            <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-sm-6 col-xl-3 animate-fade-in delay-3">
                <div class="card stats-card h-100" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <div class="card-body text-white p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stats-label text-white-50 mb-1">Rata-rata Gaji</div>
                                <div class="stats-number" style="font-size:1.4rem;"><?= formatRupiah($avgSalary) ?></div>
                                <div class="mt-2">
                                    <span class="stats-trend bg-white bg-opacity-25 text-white">
                                        <i class="bi bi-graph-up"></i> Per Karyawan
                                    </span>
                                </div>
                            </div>
                            <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="bi bi-bar-chart-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-sm-6 col-xl-3 animate-fade-in delay-4">
                <div class="card stats-card h-100" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                    <div class="card-body text-white p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stats-label text-white-50 mb-1">Departemen</div>
                                <div class="stats-number"><?= number_format($totalDepartments) ?></div>
                                <div class="mt-2">
                                    <span class="stats-trend bg-white bg-opacity-25 text-white">
                                        <i class="bi bi-building"></i> Aktif
                                    </span>
                                </div>
                            </div>
                            <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="bi bi-building"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row -->
        <div class="row g-3 mb-4">
            <!-- Payroll Trend Chart -->
            <div class="col-12 col-lg-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Tren Penggajian</h5>
                            <small class="text-muted">Total pengeluaran gaji per bulan</small>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary"><?= $currentYear ?></span>
                    </div>
                    <div class="card-body">
                        <div id="payrollTrendChart"></div>
                    </div>
                </div>
            </div>
            
            <!-- Department Distribution -->
            <div class="col-12 col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Distribusi Karyawan</h5>
                        <small class="text-muted">Per departemen</small>
                    </div>
                    <div class="card-body">
                        <div id="deptChart"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Info Cards & Recent Activity -->
        <div class="row g-3 mb-4">
            <!-- Quick Info -->
            <div class="col-12 col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Cepat</h5>
                    </div>
                    <div class="card-body">
                        <!-- Employment Status -->
                        <h6 class="fw-bold text-muted mb-3" style="font-size:0.8rem; letter-spacing:0.5px;">STATUS KARYAWAN</h6>
                        <?php foreach ($empStatus as $es): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <?php
                                $statusColors = ['Tetap'=>'success','Kontrak'=>'primary','Magang'=>'warning','Percobaan'=>'info'];
                                $color = $statusColors[$es['employment_status']] ?? 'secondary';
                                ?>
                                <div style="width:8px;height:8px;border-radius:50%;background:var(--bs-<?=$color?>)"></div>
                                <span style="font-size:0.875rem"><?= $es['employment_status'] ?></span>
                            </div>
                            <span class="badge bg-<?=$color?> bg-opacity-10 text-<?=$color?>"><?= $es['count'] ?></span>
                        </div>
                        <?php endforeach; ?>
                        
                        <hr class="my-3">
                        
                        <!-- Gender -->
                        <h6 class="fw-bold text-muted mb-3" style="font-size:0.8rem; letter-spacing:0.5px;">JENIS KELAMIN</h6>
                        <?php foreach ($genderDist as $g): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-<?= $g['gender'] == 'L' ? 'gender-male text-primary' : 'gender-female text-danger' ?>"></i>
                                <span style="font-size:0.875rem"><?= $g['gender'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></span>
                            </div>
                            <span class="badge bg-<?= $g['gender']=='L'?'primary':'danger' ?> bg-opacity-10 text-<?= $g['gender']=='L'?'primary':'danger' ?>"><?= $g['count'] ?></span>
                        </div>
                        <?php endforeach; ?>
                        
                        <hr class="my-3">
                        
                        <!-- Payroll Status -->
                        <h6 class="fw-bold text-muted mb-3" style="font-size:0.8rem; letter-spacing:0.5px;">STATUS GAJI BULAN INI</h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="font-size:0.875rem"><i class="bi bi-check-circle text-success me-1"></i> Dibayar</span>
                            <span class="badge bg-success bg-opacity-10 text-success"><?= $paidPayroll ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="font-size:0.875rem"><i class="bi bi-clock text-warning me-1"></i> Menunggu</span>
                            <span class="badge bg-warning bg-opacity-10 text-warning"><?= $pendingPayroll ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Payroll -->
            <div class="col-12 col-lg-8">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Aktivitas Penggajian Terbaru</h5>
                            <small class="text-muted">Daftar transaksi gaji terbaru</small>
                        </div>
                        <a href="/pages/payroll/index.php" class="btn btn-sm btn-outline-primary">
                            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Karyawan</th>
                                        <th>Periode</th>
                                        <th>Gaji Bersih</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPayrolls as $rp): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-circle avatar-sm" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                                                    <?= strtoupper(substr($rp['first_name'],0,1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold" style="font-size:0.875rem"><?= $rp['first_name'] . ' ' . $rp['last_name'] ?></div>
                                                    <small class="text-muted"><?= $rp['emp_id'] ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $months = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                                            echo $months[$rp['period_month']] . ' ' . $rp['period_year'];
                                            ?>
                                        </td>
                                        <td class="fw-semibold"><?= formatRupiah($rp['net_salary']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $rp['status'] ?>">
                                                <?= ucfirst($rp['status']) ?>
                                            </span>
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
    </div>
</div>

<!-- Charts Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payroll Trend Chart
    const trendData = <?= json_encode($monthlyTrend) ?>;
    const months = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    
    if (document.getElementById('payrollTrendChart')) {
        new ApexCharts(document.getElementById('payrollTrendChart'), {
            series: [{
                name: 'Total Gaji',
                data: trendData.map(d => Math.round(d.total))
            }],
            chart: {
                type: 'area',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#4f46e5'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: {
                categories: trendData.map(d => months[d.period_month] + ' ' + d.period_year),
                labels: { style: { fontSize: '12px', colors: '#94a3b8' } }
            },
            yaxis: {
                labels: {
                    formatter: val => 'Rp ' + new Intl.NumberFormat('id-ID').format(val),
                    style: { fontSize: '11px', colors: '#94a3b8' }
                }
            },
            tooltip: {
                y: { formatter: val => 'Rp ' + new Intl.NumberFormat('id-ID').format(val) }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4
            },
            dataLabels: { enabled: false }
        }).render();
    }
    
    // Department Chart
    const deptData = <?= json_encode($deptDist) ?>;
    
    if (document.getElementById('deptChart')) {
        new ApexCharts(document.getElementById('deptChart'), {
            series: deptData.map(d => d.count),
            labels: deptData.map(d => d.name),
            chart: {
                type: 'donut',
                height: 320,
                fontFamily: 'Inter, sans-serif'
            },
            colors: ['#4f46e5','#10b981','#f59e0b','#ef4444','#06b6d4','#8b5cf6','#ec4899','#f97316'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '14px',
                                fontWeight: 700,
                                color: '#64748b'
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                fontSize: '12px',
                markers: { radius: 4 }
            },
            dataLabels: { enabled: false },
            stroke: { width: 2, colors: ['#fff'] }
        }).render();
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
