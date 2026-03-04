<?php
$pageTitle = 'Edit Karyawan';
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
if (!isLoggedIn()) { header('Location: /pages/auth/login.php'); exit; }

$db = db();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: /pages/employees/index.php'); exit; }

$stmt = $db->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$id]);
$emp = $stmt->fetch();
if (!$emp) { header('Location: /pages/employees/index.php'); exit; }

$departments = $db->query("SELECT * FROM departments WHERE is_active=1 ORDER BY name")->fetchAll();
$positions = $db->query("SELECT * FROM positions WHERE is_active=1 ORDER BY name")->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="content-header-modern">
            <h1 class="animate-fade-in"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Karyawan</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/pages/employees/index.php">Karyawan</a></li>
                    <li class="breadcrumb-item active"><?= $emp['first_name'] . ' ' . $emp['last_name'] ?></li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <form id="employeeForm" novalidate>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= $id ?>">
            
            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="bi bi-person me-2"></i>Informasi Pribadi</h5></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">ID Karyawan</label>
                                    <input type="text" name="employee_id" class="form-control" value="<?= $emp['employee_id'] ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nama Depan <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" value="<?= $emp['first_name'] ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nama Belakang</label>
                                    <input type="text" name="last_name" class="form-control" value="<?= $emp['last_name'] ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="<?= $emp['email'] ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text" name="phone" class="form-control" value="<?= $emp['phone'] ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select name="gender" class="form-select">
                                        <option value="L" <?= $emp['gender']=='L'?'selected':'' ?>>Laki-laki</option>
                                        <option value="P" <?= $emp['gender']=='P'?'selected':'' ?>>Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date" name="birth_date" class="form-control" value="<?= $emp['birth_date'] ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Kota</label>
                                    <input type="text" name="city" class="form-control" value="<?= $emp['city'] ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="address" class="form-control" rows="2"><?= $emp['address'] ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="bi bi-bank me-2"></i>Informasi Bank & Pajak</h5></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Bank</label>
                                    <select name="bank_name" class="form-select">
                                        <option value="">-- Pilih Bank --</option>
                                        <?php foreach(['BCA','Mandiri','BNI','BRI','CIMB','BSI'] as $bank): ?>
                                        <option value="<?=$bank?>" <?= $emp['bank_name']==$bank?'selected':'' ?>><?=$bank?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. Rekening</label>
                                    <input type="text" name="bank_account" class="form-control" value="<?= $emp['bank_account'] ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">NPWP</label>
                                    <input type="text" name="npwp" class="form-control" value="<?= $emp['npwp'] ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">BPJS Kesehatan</label>
                                    <input type="text" name="bpjs_kesehatan" class="form-control" value="<?= $emp['bpjs_kesehatan'] ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">BPJS Ketenagakerjaan</label>
                                    <input type="text" name="bpjs_ketenagakerjaan" class="form-control" value="<?= $emp['bpjs_ketenagakerjaan'] ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="bi bi-briefcase me-2"></i>Informasi Pekerjaan</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Departemen</label>
                                <select name="department_id" id="departmentSelect" class="form-select">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= $emp['department_id']==$d['id']?'selected':'' ?>><?= $d['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jabatan</label>
                                <select name="position_id" id="positionSelect" class="form-select">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($positions as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-dept="<?= $p['department_id'] ?>" data-salary="<?= $p['base_salary'] ?>" <?= $emp['position_id']==$p['id']?'selected':'' ?>><?= $p['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Masuk</label>
                                <input type="date" name="hire_date" class="form-control" value="<?= $emp['hire_date'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status Karyawan</label>
                                <select name="employment_status" class="form-select">
                                    <?php foreach(['Tetap','Kontrak','Magang','Percobaan'] as $s): ?>
                                    <option value="<?=$s?>" <?= $emp['employment_status']==$s?'selected':'' ?>><?=$s?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gaji Pokok</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="base_salary" id="baseSalary" class="form-control" value="<?= number_format($emp['base_salary'],0,',','.') ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2"><i class="bi bi-check-lg me-1"></i> Simpan Perubahan</button>
                            <a href="/pages/employees/index.php" class="btn btn-outline-primary w-100"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('baseSalary').addEventListener('input', function() {
        let val = this.value.replace(/\D/g, '');
        this.value = val ? parseInt(val).toLocaleString('id-ID') : '';
    });
    
    document.getElementById('employeeForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.set('base_salary', formData.get('base_salary').replace(/\./g, ''));
        
        App.showLoading();
        try {
            const resp = await fetch('/pages/api/employees.php', { method: 'POST', body: formData });
            const data = await resp.json();
            App.hideLoading();
            if (data.success) {
                await App.alert.success('Berhasil!', data.message);
                window.location.href = '/pages/employees/index.php';
            } else {
                App.alert.error('Gagal!', data.message);
            }
        } catch (err) {
            App.hideLoading();
            App.alert.error('Error!', 'Terjadi kesalahan pada server');
        }
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
