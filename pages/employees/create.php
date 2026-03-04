<?php
$pageTitle = 'Tambah Karyawan';
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
if (!isLoggedIn()) { header('Location: /pages/auth/login.php'); exit; }

$db = db();
$departments = $db->query("SELECT * FROM departments WHERE is_active=1 ORDER BY name")->fetchAll();
$positions = $db->query("SELECT * FROM positions WHERE is_active=1 ORDER BY name")->fetchAll();

// Generate next employee ID
$lastEmp = $db->query("SELECT employee_id FROM employees ORDER BY id DESC LIMIT 1")->fetchColumn();
$nextNum = 1;
if ($lastEmp && preg_match('/KRY-(\d+)/', $lastEmp, $m)) {
    $nextNum = (int)$m[1] + 1;
}
$nextEmpId = sprintf('KRY-%03d', $nextNum);

include __DIR__ . '/../../includes/header.php';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="content-header-modern">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="animate-fade-in"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Tambah Karyawan</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/pages/employees/index.php">Karyawan</a></li>
                            <li class="breadcrumb-item active">Tambah Baru</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <form id="employeeForm" novalidate>
            <input type="hidden" name="action" value="save">
            
            <div class="row g-4">
                <!-- Personal Info -->
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-person me-2"></i>Informasi Pribadi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">ID Karyawan <span class="text-danger">*</span></label>
                                    <input type="text" name="employee_id" class="form-control" value="<?= $nextEmpId ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nama Depan <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" placeholder="Nama depan" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nama Belakang</label>
                                    <input type="text" name="last_name" class="form-control" placeholder="Nama belakang">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" placeholder="email@company.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select" required>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date" name="birth_date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Kota</label>
                                    <input type="text" name="city" class="form-control" placeholder="Kota">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Alamat lengkap"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bank & Tax Info -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-bank me-2"></i>Informasi Bank & Pajak</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Bank</label>
                                    <select name="bank_name" class="form-select">
                                        <option value="">-- Pilih Bank --</option>
                                        <option value="BCA">BCA</option>
                                        <option value="Mandiri">Mandiri</option>
                                        <option value="BNI">BNI</option>
                                        <option value="BRI">BRI</option>
                                        <option value="CIMB">CIMB Niaga</option>
                                        <option value="BSI">BSI</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">No. Rekening</label>
                                    <input type="text" name="bank_account" class="form-control" placeholder="Nomor rekening">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">NPWP</label>
                                    <input type="text" name="npwp" class="form-control" placeholder="XX.XXX.XXX.X-XXX">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">BPJS Kesehatan</label>
                                    <input type="text" name="bpjs_kesehatan" class="form-control" placeholder="No. BPJS Kesehatan">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">BPJS Ketenagakerjaan</label>
                                    <input type="text" name="bpjs_ketenagakerjaan" class="form-control" placeholder="No. BPJS TK">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Employment Info -->
                <div class="col-12 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-briefcase me-2"></i>Informasi Pekerjaan</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Departemen <span class="text-danger">*</span></label>
                                <select name="department_id" id="departmentSelect" class="form-select" required>
                                    <option value="">-- Pilih Departemen --</option>
                                    <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= $d['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                                <select name="position_id" id="positionSelect" class="form-select" required>
                                    <option value="">-- Pilih Jabatan --</option>
                                    <?php foreach ($positions as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-dept="<?= $p['department_id'] ?>" data-salary="<?= $p['base_salary'] ?>"><?= $p['name'] ?> (<?= $p['level'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                                <input type="date" name="hire_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status Karyawan</label>
                                <select name="employment_status" class="form-select">
                                    <option value="Tetap">Tetap</option>
                                    <option value="Kontrak">Kontrak</option>
                                    <option value="Magang">Magang</option>
                                    <option value="Percobaan">Percobaan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Gaji Pokok <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" name="base_salary" id="baseSalary" class="form-control" placeholder="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-check-lg me-1"></i> Simpan Karyawan
                            </button>
                            <a href="/pages/employees/index.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Filter positions by department
    const deptSelect = document.getElementById('departmentSelect');
    const posSelect = document.getElementById('positionSelect');
    const allOptions = [...posSelect.querySelectorAll('option[data-dept]')];
    
    deptSelect.addEventListener('change', function() {
        const deptId = this.value;
        posSelect.innerHTML = '<option value="">-- Pilih Jabatan --</option>';
        allOptions.filter(o => o.dataset.dept === deptId).forEach(o => {
            posSelect.appendChild(o.cloneNode(true));
        });
    });
    
    // Auto-fill salary from position
    posSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.dataset.salary) {
            document.getElementById('baseSalary').value = parseInt(selected.dataset.salary).toLocaleString('id-ID');
        }
    });
    
    // Format salary input
    document.getElementById('baseSalary').addEventListener('input', function() {
        let val = this.value.replace(/\D/g, '');
        this.value = val ? parseInt(val).toLocaleString('id-ID') : '';
    });
    
    // Form submit
    document.getElementById('employeeForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        // Clean salary
        let salary = formData.get('base_salary').replace(/\./g, '');
        formData.set('base_salary', salary);
        
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
