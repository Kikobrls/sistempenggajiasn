<?php
$pageTitle = 'Generate Gaji';
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';
if (!isLoggedIn()) { header('Location: /pages/auth/login.php'); exit; }

$db = db();
$departments = $db->query("SELECT * FROM departments WHERE is_active=1 ORDER BY name")->fetchAll();
$months = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="content-header-modern">
            <h1 class="animate-fade-in"><i class="bi bi-calculator me-2 text-primary"></i>Generate Gaji</h1>
            <p class="subtitle mb-0">Hitung dan generate gaji karyawan untuk periode tertentu</p>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bi bi-gear me-2"></i>Pengaturan Generate</h5>
                    </div>
                    <div class="card-body">
                        <form id="generateForm">
                            <input type="hidden" name="action" value="generate">
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Bulan <span class="text-danger">*</span></label>
                                    <select name="month" class="form-select" required>
                                        <?php for ($m=1; $m<=12; $m++): ?>
                                        <option value="<?=$m?>" <?=(int)date('m')==$m?'selected':''?>><?=$months[$m]?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tahun <span class="text-danger">*</span></label>
                                    <select name="year" class="form-select" required>
                                        <?php for ($y=2024; $y<=2027; $y++): ?>
                                        <option value="<?=$y?>" <?=(int)date('Y')==$y?'selected':''?>><?=$y?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="alert alert-info d-flex align-items-start gap-2" style="border-radius:10px; border:none; background:rgba(99,102,241,0.08);">
                                <i class="bi bi-info-circle text-primary fs-5 mt-1"></i>
                                <div style="font-size:0.85rem;">
                                    <strong>Informasi:</strong><br>
                                    Generate akan menghitung gaji untuk <strong>semua karyawan aktif</strong> yang belum memiliki data gaji pada periode yang dipilih. Komponen yang dihitung:
                                    <ul class="mb-0 mt-1">
                                        <li>Gaji Pokok</li>
                                        <li>Tunjangan (Transportasi, Makan, Kesehatan, Komunikasi, Jabatan)</li>
                                        <li>Lembur</li>
                                        <li>Potongan (BPJS, Pensiun, Absen, Keterlambatan)</li>
                                        <li>PPh 21 (5%)</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 mt-3" id="generateBtn">
                                <i class="bi bi-calculator me-2"></i> Generate Gaji Sekarang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-lg-6">
                <!-- Result area -->
                <div class="card" id="resultCard" style="display:none;">
                    <div class="card-header bg-success bg-opacity-10">
                        <h5 class="card-title mb-0 text-success"><i class="bi bi-check-circle me-2"></i>Hasil Generate</h5>
                    </div>
                    <div class="card-body text-center py-5" id="resultBody">
                    </div>
                </div>
                
                <!-- Tips -->
                <div class="card" id="tipsCard">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3"><i class="bi bi-lightbulb text-warning me-2"></i>Tips</h5>
                        <div class="d-flex gap-3 mb-3 p-3" style="background:#f8fafc; border-radius:10px;">
                            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#4f46e5,#7c3aed);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-1-circle text-white"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Pastikan Data Kehadiran</div>
                                <small class="text-muted">Input data kehadiran karyawan sebelum generate gaji agar perhitungan akurat.</small>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-3 p-3" style="background:#f8fafc; border-radius:10px;">
                            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-2-circle text-white"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Review Sebelum Approve</div>
                                <small class="text-muted">Periksa slip gaji setiap karyawan sebelum mengubah status ke approved/paid.</small>
                            </div>
                        </div>
                        <div class="d-flex gap-3 p-3" style="background:#f8fafc; border-radius:10px;">
                            <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="bi bi-3-circle text-white"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Tidak Duplikat</div>
                                <small class="text-muted">Karyawan yang sudah memiliki data gaji pada periode yang sama akan dilewati.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('generateForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const result = await App.alert.confirm('Konfirmasi Generate', 'Generate gaji untuk periode yang dipilih?');
    if (!result.isConfirmed) return;
    
    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
    
    App.showLoading();
    try {
        const formData = new FormData(this);
        const resp = await fetch('/pages/api/payroll.php', { method: 'POST', body: formData });
        const data = await resp.json();
        App.hideLoading();
        
        if (data.success) {
            document.getElementById('tipsCard').style.display = 'none';
            document.getElementById('resultCard').style.display = 'block';
            document.getElementById('resultBody').innerHTML = `
                <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;">
                    <i class="bi bi-check-lg text-white" style="font-size:2.5rem;"></i>
                </div>
                <h4 class="fw-bold mb-2">Generate Berhasil!</h4>
                <p class="text-muted mb-4">${data.message}</p>
                <div class="d-flex justify-content-center gap-3 mb-3">
                    <div class="text-center px-4 py-2" style="background:#f0fdf4;border-radius:10px;">
                        <div class="fw-bold text-success fs-4">${data.generated}</div>
                        <small class="text-muted">Digenerate</small>
                    </div>
                    <div class="text-center px-4 py-2" style="background:#fef9c3;border-radius:10px;">
                        <div class="fw-bold text-warning fs-4">${data.skipped}</div>
                        <small class="text-muted">Dilewati</small>
                    </div>
                </div>
                <a href="/pages/payroll/index.php?month=${new FormData(document.getElementById('generateForm')).get('month')}&year=${new FormData(document.getElementById('generateForm')).get('year')}" class="btn btn-primary">
                    <i class="bi bi-eye me-1"></i> Lihat Data Gaji
                </a>
            `;
        } else {
            App.alert.error('Gagal!', data.message);
        }
    } catch (err) {
        App.hideLoading();
        App.alert.error('Error!', 'Terjadi kesalahan pada server');
    }
    
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-calculator me-2"></i> Generate Gaji Sekarang';
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
