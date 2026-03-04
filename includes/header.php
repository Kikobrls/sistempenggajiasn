<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

// Get current page for sidebar active state
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Get user info
$userName = $_SESSION['full_name'] ?? 'Admin';
$userRole = $_SESSION['role'] ?? 'admin';
$userRoleLabel = match($userRole) {
    'admin' => 'Administrator',
    'manajer_sdm' => 'Manajer SDM',
    'staf_sdm' => 'Staf SDM',
    'peninjau' => 'Peninjau',
    default => 'Pengguna'
};

// Statistik ringkas untuk sidebar
try {
    $db = db();
    $totalEmployees = $db->query("SELECT COUNT(*) FROM employees WHERE is_active=1")->fetchColumn();
    $pendingPayroll = $db->query("SELECT COUNT(*) FROM payroll WHERE status IN ('draf','diproses')")->fetchColumn();
} catch(Exception $e) {
    $totalEmployees = 0;
    $pendingPayroll = 0;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?= $pageTitle ?? 'Dasbor' ?> - <?= APP_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#4f46e5" />
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- OverlayScrollbars -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    
    <!-- AdminLTE -->
    <link rel="stylesheet" href="/public/css/adminlte.css" />
    
    <!-- ApexCharts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" crossorigin="anonymous" />
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />
    
    <!-- Gaya Modern Kustom -->
    <link rel="stylesheet" href="/public/css/custom.css" />
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary" style="font-family: 'Inter', sans-serif;">
    <div class="app-wrapper">
        <!-- Navigasi Atas -->
        <nav class="app-header navbar navbar-expand bg-body" style="backdrop-filter: blur(10px);">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list fs-5"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <span class="nav-link text-muted">
                            <i class="bi bi-calendar3 me-1"></i> <?= date('l, d F Y') ?>
                        </span>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <!-- Aksi Cepat -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-bs-toggle="dropdown" href="#" title="Aksi Cepat">
                            <i class="bi bi-lightning-charge-fill text-warning"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                            <h6 class="dropdown-header">Aksi Cepat</h6>
                            <a href="/pages/employees/create.php" class="dropdown-item">
                                <i class="bi bi-person-plus me-2 text-primary"></i> Tambah Karyawan
                            </a>
                            <a href="/pages/payroll/generate.php" class="dropdown-item">
                                <i class="bi bi-calculator me-2 text-success"></i> Generate Gaji
                            </a>
                            <a href="/pages/reports/index.php" class="dropdown-item">
                                <i class="bi bi-file-earmark-bar-graph me-2 text-info"></i> Lihat Laporan
                            </a>
                        </div>
                    </li>
                    
                    <!-- Notifikasi -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-bs-toggle="dropdown" href="#">
                            <i class="bi bi-bell-fill"></i>
                            <?php if($pendingPayroll > 0): ?>
                            <span class="navbar-badge badge text-bg-danger"><?= $pendingPayroll ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow-lg border-0">
                            <span class="dropdown-item dropdown-header bg-primary text-white">Notifikasi</span>
                            <div class="dropdown-divider"></div>
                            <?php if($pendingPayroll > 0): ?>
                            <a href="/pages/payroll/index.php" class="dropdown-item">
                                <i class="bi bi-cash-coin me-2 text-warning"></i> <?= $pendingPayroll ?> penggajian menunggu
                            </a>
                            <?php endif; ?>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-people-fill me-2 text-info"></i> <?= $totalEmployees ?> karyawan aktif
                            </a>
                        </div>
                    </li>
                    
                    <!-- Layar Penuh -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                            <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                            <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
                        </a>
                    </li>
                    
                    <!-- Menu Pengguna -->
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <div class="d-inline-flex align-items-center">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;font-size:14px;">
                                    <?= strtoupper(substr($userName, 0, 1)) ?>
                                </div>
                                <span class="d-none d-md-inline fw-semibold"><?= $userName ?></span>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                            <li class="px-3 py-2">
                                <div class="fw-bold"><?= $userName ?></div>
                                <small class="text-muted"><?= $userRoleLabel ?></small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Profil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Pengaturan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/pages/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Bilah Sisi -->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="/index.php" class="brand-link">
                    <span class="brand-image opacity-75 d-flex align-items-center justify-content-center" style="width:33px;height:33px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:8px;">
                        <i class="bi bi-cash-stack text-white" style="font-size:18px;"></i>
                    </span>
                    <span class="brand-text fw-bold"><?= APP_NAME ?></span>
                </a>
            </div>
            
            <div class="sidebar-wrapper">
                <nav class="mt-2">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                        <!-- Dasbor -->
                        <li class="nav-item">
                            <a href="/index.php" class="nav-link <?= ($currentPage == 'index' && $currentDir == 'webapp') ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-grid-1x2-fill"></i>
                                <p>Dasbor</p>
                            </a>
                        </li>
                        
                        <!-- Header: Data Utama -->
                        <li class="nav-header">DATA UTAMA</li>
                        
                        <!-- Karyawan -->
                        <li class="nav-item <?= $currentDir == 'employees' ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link <?= $currentDir == 'employees' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-people-fill"></i>
                                <p>Karyawan <i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="/pages/employees/index.php" class="nav-link <?= ($currentDir == 'employees' && $currentPage == 'index') ? 'active' : '' ?>">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Daftar Karyawan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/pages/employees/create.php" class="nav-link <?= ($currentDir == 'employees' && $currentPage == 'create') ? 'active' : '' ?>">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Tambah Karyawan</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Departemen -->
                        <li class="nav-item <?= $currentDir == 'departments' ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link <?= $currentDir == 'departments' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-building"></i>
                                <p>Departemen <i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="/pages/departments/index.php" class="nav-link <?= ($currentDir == 'departments' && $currentPage == 'index') ? 'active' : '' ?>">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Daftar Departemen</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Jabatan -->
                        <li class="nav-item <?= $currentDir == 'positions' ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link <?= $currentDir == 'positions' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-briefcase-fill"></i>
                                <p>Jabatan <i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="/pages/positions/index.php" class="nav-link <?= ($currentDir == 'positions' && $currentPage == 'index') ? 'active' : '' ?>">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Daftar Jabatan</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Header: Penggajian -->
                        <li class="nav-header">PENGGAJIAN</li>
                        
                        <!-- Penggajian -->
                        <li class="nav-item <?= $currentDir == 'payroll' ? 'menu-open' : '' ?>">
                            <a href="#" class="nav-link <?= $currentDir == 'payroll' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-cash-coin"></i>
                                <p>Penggajian <i class="nav-arrow bi bi-chevron-right"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="/pages/payroll/index.php" class="nav-link <?= ($currentDir == 'payroll' && $currentPage == 'index') ? 'active' : '' ?>">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Daftar Gaji</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/pages/payroll/generate.php" class="nav-link <?= ($currentDir == 'payroll' && $currentPage == 'generate') ? 'active' : '' ?>">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Generate Gaji</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/pages/payroll/slips.php" class="nav-link <?= ($currentDir == 'payroll' && $currentPage == 'slips') ? 'active' : '' ?>">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Slip Gaji</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Header: Laporan -->
                        <li class="nav-header">LAPORAN</li>
                        
                        <li class="nav-item">
                            <a href="/pages/reports/index.php" class="nav-link <?= ($currentDir == 'reports') ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-bar-chart-line-fill"></i>
                                <p>Laporan Gaji</p>
                            </a>
                        </li>
                        
                        <!-- Header: Pengaturan -->
                        <li class="nav-header">PENGATURAN</li>
                        <li class="nav-item">
                            <a href="/pages/auth/logout.php" class="nav-link">
                                <i class="nav-icon bi bi-box-arrow-right"></i>
                                <p>Keluar</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        
        <!-- Konten Utama -->
        <main class="app-main">
