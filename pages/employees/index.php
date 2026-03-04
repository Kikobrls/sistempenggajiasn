<?php
$pageTitle = 'Daftar Karyawan';
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
if (!isLoggedIn()) { header('Location: /pages/auth/login.php'); exit; }

$db = db();
$employees = $db->query("SELECT e.*, d.name as department_name, p.name as position_name 
    FROM employees e 
    LEFT JOIN departments d ON e.department_id = d.id 
    LEFT JOIN positions p ON e.position_id = p.id 
    WHERE e.is_active = 1 
    ORDER BY e.first_name ASC")->fetchAll();

$deptColors = ['Sumber Daya Manusia'=>'warning','Keuangan & Akuntansi'=>'primary','Teknologi Informasi'=>'purple',
    'Pemasaran'=>'danger','Operasional'=>'success','Riset & Pengembangan'=>'info','Layanan Pelanggan'=>'teal','Hukum & Kepatuhan'=>'red'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="content-header-modern">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="animate-fade-in"><i class="bi bi-people-fill me-2 text-primary"></i>Daftar Karyawan</h1>
                    <p class="subtitle mb-0">Kelola data karyawan perusahaan</p>
                </div>
                <a href="/pages/employees/create.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Karyawan
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="info-mini-card">
                    <div class="mini-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <div class="fw-bold"><?= count($employees) ?></div>
                        <small class="text-muted">Total Karyawan</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-mini-card">
                    <div class="mini-icon bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <?php $tetap = count(array_filter($employees, fn($e) => $e['employment_status'] == 'Tetap')); ?>
                        <div class="fw-bold"><?= $tetap ?></div>
                        <small class="text-muted">Karyawan Tetap</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-mini-card">
                    <div class="mini-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-file-earmark-text"></i></div>
                    <div>
                        <?php $kontrak = count(array_filter($employees, fn($e) => $e['employment_status'] == 'Kontrak')); ?>
                        <div class="fw-bold"><?= $kontrak ?></div>
                        <small class="text-muted">Karyawan Kontrak</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-mini-card">
                    <div class="mini-icon bg-info bg-opacity-10 text-info"><i class="bi bi-gender-ambiguous"></i></div>
                    <div>
                        <?php
                        $male = count(array_filter($employees, fn($e) => $e['gender'] == 'L'));
                        $female = count($employees) - $male;
                        ?>
                        <div class="fw-bold"><?= $male ?>L / <?= $female ?>P</div>
                        <small class="text-muted">Jenis Kelamin</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="employeeTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ID Karyawan</th>
                                <th>Nama</th>
                                <th>Departemen</th>
                                <th>Jabatan</th>
                                <th>Status</th>
                                <th>Gaji Pokok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $i => $emp): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><code class="text-primary fw-semibold"><?= $emp['employee_id'] ?></code></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-circle avatar-sm" style="background: linear-gradient(135deg, <?= $emp['gender']=='L' ? '#4f46e5,#7c3aed' : '#ec4899,#f43f5e' ?>);">
                                            <?= strtoupper(substr($emp['first_name'],0,1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= $emp['first_name'] . ' ' . $emp['last_name'] ?></div>
                                            <small class="text-muted"><?= $emp['email'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= $emp['department_name'] ?? '-' ?></span></td>
                                <td><?= $emp['position_name'] ?? '-' ?></td>
                                <td>
                                    <?php
                                    $statusClass = match($emp['employment_status']) {
                                        'Tetap' => 'success', 'Kontrak' => 'primary', 'Magang' => 'warning', 'Percobaan' => 'info', default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?> bg-opacity-10 text-<?= $statusClass ?>"><?= $emp['employment_status'] ?></span>
                                </td>
                                <td class="fw-semibold"><?= formatRupiah($emp['base_salary']) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/pages/employees/view.php?id=<?= $emp['id'] ?>" class="btn btn-outline-primary" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/pages/employees/edit.php?id=<?= $emp['id'] ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button onclick="App.deleteRecord('/pages/api/employees.php', <?= $emp['id'] ?>, '<?= $emp['first_name'] . ' ' . $emp['last_name'] ?>')" class="btn btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
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
    App.initDataTable('#employeeTable', { order: [[2, 'asc']] });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
