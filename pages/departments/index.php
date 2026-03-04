<?php
$pageTitle = 'Manajemen Departemen';
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
if (!isLoggedIn()) { header('Location: /pages/auth/login.php'); exit; }

$db = db();
$departments = $db->query("SELECT d.*, (SELECT COUNT(*) FROM employees WHERE department_id=d.id AND is_active=1) as emp_count, (SELECT COUNT(*) FROM positions WHERE department_id=d.id AND is_active=1) as pos_count FROM departments d WHERE d.is_active=1 ORDER BY d.name")->fetchAll();

$deptIcons = ['Sumber Daya Manusia'=>'bi-people-fill','Keuangan & Akuntansi'=>'bi-calculator','Teknologi Informasi'=>'bi-cpu','Pemasaran'=>'bi-megaphone','Operasional'=>'bi-gear-wide-connected','Riset & Pengembangan'=>'bi-lightbulb','Layanan Pelanggan'=>'bi-headset','Hukum & Kepatuhan'=>'bi-shield-check'];
$deptColors = ['Sumber Daya Manusia'=>'#f59e0b','Keuangan & Akuntansi'=>'#3b82f6','Teknologi Informasi'=>'#8b5cf6','Pemasaran'=>'#ec4899','Operasional'=>'#10b981','Riset & Pengembangan'=>'#6366f1','Layanan Pelanggan'=>'#06b6d4','Hukum & Kepatuhan'=>'#ef4444'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="content-header-modern">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="animate-fade-in"><i class="bi bi-building me-2 text-primary"></i>Manajemen Departemen</h1>
                    <p class="subtitle mb-0">Kelola departemen perusahaan</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#deptModal" onclick="resetForm()">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Departemen
                </button>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row g-3">
            <?php foreach ($departments as $dept): ?>
            <div class="col-12 col-md-6 col-xl-4 animate-fade-in">
                <div class="card employee-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:white;background:<?= $deptColors[$dept['name']] ?? '#6366f1' ?>;">
                                    <i class="bi <?= $deptIcons[$dept['name']] ?? 'bi-building' ?>"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0"><?= $dept['name'] ?></h5>
                                    <small class="text-muted">ID: <?= $dept['id'] ?></small>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item" href="#" onclick="editDept(<?= htmlspecialchars(json_encode($dept)) ?>)"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="App.deleteRecord('/pages/api/departments.php', <?=$dept['id']?>, '<?=$dept['name']?>')"><i class="bi bi-trash me-2"></i>Hapus</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <p class="text-muted mb-3" style="font-size:0.85rem;"><?= $dept['description'] ?: 'Tidak ada deskripsi' ?></p>
                        
                        <div class="d-flex gap-3 mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#f1f5f9;">
                                    <i class="bi bi-people text-primary" style="font-size:0.9rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size:0.9rem;"><?= $dept['emp_count'] ?></div>
                                    <small class="text-muted" style="font-size:0.7rem;">Karyawan</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#f1f5f9;">
                                    <i class="bi bi-briefcase text-success" style="font-size:0.9rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size:0.9rem;"><?= $dept['pos_count'] ?></div>
                                    <small class="text-muted" style="font-size:0.7rem;">Jabatan</small>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($dept['manager_name']): ?>
                        <div class="d-flex align-items-center gap-2 pt-3 border-top">
                            <div class="avatar-circle avatar-sm" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                                <?= strtoupper(substr($dept['manager_name'],0,1)) ?>
                            </div>
                            <div>
                                <small class="text-muted">Manajer</small>
                                <div class="fw-semibold" style="font-size:0.85rem;"><?= $dept['manager_name'] ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Department Modal -->
<div class="modal fade" id="deptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="deptModalTitle">Tambah Departemen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="deptForm">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" id="deptId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Nama Departemen <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="deptName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="deptDesc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Manajer</label>
                        <input type="text" name="manager_name" id="deptManager" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveDept()">
                    <i class="bi bi-check-lg me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('deptModalTitle').textContent = 'Tambah Departemen';
    document.getElementById('deptId').value = 0;
    document.getElementById('deptForm').reset();
}

function editDept(dept) {
    document.getElementById('deptModalTitle').textContent = 'Edit Departemen';
    document.getElementById('deptId').value = dept.id;
    document.getElementById('deptName').value = dept.name;
    document.getElementById('deptDesc').value = dept.description || '';
    document.getElementById('deptManager').value = dept.manager_name || '';
    new bootstrap.Modal(document.getElementById('deptModal')).show();
}

async function saveDept() {
    const formData = new FormData(document.getElementById('deptForm'));
    App.showLoading();
    try {
        const resp = await fetch('/pages/api/departments.php', { method: 'POST', body: formData });
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
