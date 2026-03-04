<?php
$pageTitle = 'Manajemen Jabatan';
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
if (!isLoggedIn()) { header('Location: /pages/auth/login.php'); exit; }

$db = db();
$positions = $db->query("SELECT p.*, d.name as department_name, (SELECT COUNT(*) FROM employees WHERE position_id=p.id AND is_active=1) as emp_count FROM positions p LEFT JOIN departments d ON p.department_id=d.id WHERE p.is_active=1 ORDER BY d.name, p.name")->fetchAll();

$departments = $db->query("SELECT * FROM departments WHERE is_active=1 ORDER BY name")->fetchAll();

$levelColors = ['Staf'=>'primary','Penyelia'=>'info','Manajer'=>'warning','Direktur'=>'danger','Eksekutif'=>'dark'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="content-header-modern">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="animate-fade-in"><i class="bi bi-briefcase-fill me-2 text-primary"></i>Manajemen Jabatan</h1>
                    <p class="subtitle mb-0">Kelola jabatan dan posisi karyawan</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#posModal" onclick="resetPosForm()">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Jabatan
                </button>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="positionTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Jabatan</th>
                                <th>Departemen</th>
                                <th>Level</th>
                                <th>Gaji Pokok</th>
                                <th>Karyawan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($positions as $i => $pos): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="fw-semibold"><?= $pos['name'] ?></td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= $pos['department_name'] ?? '-' ?></span></td>
                                <td>
                                    <?php $lc = $levelColors[$pos['level']] ?? 'secondary'; ?>
                                    <span class="badge bg-<?=$lc?> bg-opacity-10 text-<?=$lc?>"><?= $pos['level'] ?></span>
                                </td>
                                <td class="fw-semibold"><?= formatRupiah($pos['base_salary']) ?></td>
                                <td><span class="badge bg-info bg-opacity-10 text-info"><?= $pos['emp_count'] ?></span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button onclick='editPos(<?= json_encode($pos) ?>)' class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></button>
                                        <button onclick="App.deleteRecord('/pages/api/positions.php', <?=$pos['id']?>, '<?=$pos['name']?>')" class="btn btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
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

<!-- Position Modal -->
<div class="modal fade" id="posModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="posModalTitle">Tambah Jabatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="posForm">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" id="posId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="posName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Departemen</label>
                        <select name="department_id" id="posDept" class="form-select">
                            <option value="">-- Pilih --</option>
                            <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= $d['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Level</label>
                        <select name="level" id="posLevel" class="form-select">
                            <option value="Staf">Staf</option>
                            <option value="Penyelia">Penyelia</option>
                            <option value="Manajer">Manajer</option>
                            <option value="Direktur">Direktur</option>
                            <option value="Eksekutif">Eksekutif</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gaji Pokok</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="base_salary" id="posSalary" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" id="posDesc" class="form-control" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="savePos()"><i class="bi bi-check-lg me-1"></i> Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    App.initDataTable('#positionTable');
    
    document.getElementById('posSalary').addEventListener('input', function() {
        let val = this.value.replace(/\D/g, '');
        this.value = val ? parseInt(val).toLocaleString('id-ID') : '';
    });
});

function resetPosForm() {
    document.getElementById('posModalTitle').textContent = 'Tambah Jabatan';
    document.getElementById('posId').value = 0;
    document.getElementById('posForm').reset();
}

function editPos(pos) {
    document.getElementById('posModalTitle').textContent = 'Edit Jabatan';
    document.getElementById('posId').value = pos.id;
    document.getElementById('posName').value = pos.name;
    document.getElementById('posDept').value = pos.department_id || '';
    document.getElementById('posLevel').value = pos.level;
    document.getElementById('posSalary').value = parseInt(pos.base_salary).toLocaleString('id-ID');
    document.getElementById('posDesc').value = pos.description || '';
    new bootstrap.Modal(document.getElementById('posModal')).show();
}

async function savePos() {
    const formData = new FormData(document.getElementById('posForm'));
    formData.set('base_salary', formData.get('base_salary').replace(/\./g, ''));
    App.showLoading();
    try {
        const resp = await fetch('/pages/api/positions.php', { method: 'POST', body: formData });
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
