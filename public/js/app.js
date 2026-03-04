/**
 * GajiPro - JavaScript Aplikasi Utama
 */

const App = {
    init() {
        this.initTooltips();
        this.initAnimations();
    },

    // Inisialisasi tooltip Bootstrap
    initTooltips() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(t => new bootstrap.Tooltip(t));
    },

    // Animasi elemen saat scroll
    initAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
    },

    // Format angka ke Rupiah Indonesia
    formatRupiah(num) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
    },

    // Tampilkan loading
    showLoading() {
        const overlay = document.createElement('div');
        overlay.className = 'spinner-overlay';
        overlay.id = 'loadingOverlay';
        overlay.innerHTML = '<div class="modern-spinner"></div>';
        document.body.appendChild(overlay);
    },

    // Sembunyikan loading
    hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.remove();
    },

    // Bantuan SweetAlert
    alert: {
        success(title, text) {
            return Swal.fire({
                icon: 'success',
                title: title,
                text: text,
                confirmButtonColor: '#4f46e5',
                timer: 2000,
                timerProgressBar: true
            });
        },
        error(title, text) {
            return Swal.fire({
                icon: 'error',
                title: title,
                text: text,
                confirmButtonColor: '#4f46e5'
            });
        },
        confirm(title, text) {
            return Swal.fire({
                icon: 'warning',
                title: title,
                text: text,
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal'
            });
        },
        toast(icon, title) {
            return Swal.fire({
                icon: icon,
                title: title,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    },

    // Hapus data dengan konfirmasi
    async deleteRecord(url, id, name) {
        const result = await this.alert.confirm(
            'Hapus Data?',
            `Apakah Anda yakin ingin menghapus "${name}"?`
        );
        
        if (result.isConfirmed) {
            this.showLoading();
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}&action=delete`
                });
                const data = await response.json();
                this.hideLoading();
                
                if (data.success) {
                    await this.alert.success('Berhasil!', data.message);
                    location.reload();
                } else {
                    this.alert.error('Gagal!', data.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                this.hideLoading();
                this.alert.error('Error!', 'Terjadi kesalahan pada server');
            }
        }
    },

    // Inisialisasi DataTable dengan opsi default
    initDataTable(selector, options = {}) {
        const defaults = {
            language: {
                search: "",
                searchPlaceholder: "Cari data...",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(filter dari _MAX_ total data)",
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    first: '<i class="bi bi-chevron-double-left"></i>',
                    last: '<i class="bi bi-chevron-double-right"></i>',
                    next: '<i class="bi bi-chevron-right"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>'
                }
            },
            pageLength: 10,
            responsive: true,
            dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
            order: [[0, 'asc']]
        };

        return $(selector).DataTable({ ...defaults, ...options });
    }
};

// Inisialisasi saat DOM siap
document.addEventListener('DOMContentLoaded', () => App.init());
