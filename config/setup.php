<?php
/**
 * Skrip Setup & Migrasi Database
 * Jalankan file ini sekali untuk membuat semua tabel dan data awal
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

try {
    $pdo = db();
    
    // Buat tabel departemen
    $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        manager_name VARCHAR(100),
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Buat tabel jabatan
    $pdo->exec("CREATE TABLE IF NOT EXISTS positions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        department_id INT,
        level ENUM('Staf','Penyelia','Manajer','Direktur','Eksekutif') DEFAULT 'Staf',
        base_salary DECIMAL(15,2) DEFAULT 0,
        description TEXT,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Buat tabel karyawan
    $pdo->exec("CREATE TABLE IF NOT EXISTS employees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20),
        gender ENUM('L','P') NOT NULL,
        birth_date DATE,
        address TEXT,
        city VARCHAR(50),
        department_id INT,
        position_id INT,
        hire_date DATE NOT NULL,
        employment_status ENUM('Tetap','Kontrak','Magang','Percobaan') DEFAULT 'Tetap',
        bank_name VARCHAR(50),
        bank_account VARCHAR(30),
        npwp VARCHAR(30),
        bpjs_kesehatan VARCHAR(30),
        bpjs_ketenagakerjaan VARCHAR(30),
        base_salary DECIMAL(15,2) DEFAULT 0,
        photo VARCHAR(255),
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
        FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Buat tabel tunjangan
    $pdo->exec("CREATE TABLE IF NOT EXISTS allowances (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        type ENUM('fixed','percentage') DEFAULT 'fixed',
        default_amount DECIMAL(15,2) DEFAULT 0,
        is_taxable TINYINT(1) DEFAULT 1,
        description TEXT,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Buat tabel potongan
    $pdo->exec("CREATE TABLE IF NOT EXISTS deductions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        type ENUM('fixed','percentage') DEFAULT 'fixed',
        default_amount DECIMAL(15,2) DEFAULT 0,
        description TEXT,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Buat tabel kehadiran
    $pdo->exec("CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        period_month INT NOT NULL,
        period_year INT NOT NULL,
        working_days INT DEFAULT 22,
        present_days INT DEFAULT 0,
        absent_days INT DEFAULT 0,
        sick_days INT DEFAULT 0,
        leave_days INT DEFAULT 0,
        late_days INT DEFAULT 0,
        overtime_hours DECIMAL(5,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
        UNIQUE KEY unique_attendance (employee_id, period_month, period_year)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Buat tabel penggajian
    $pdo->exec("CREATE TABLE IF NOT EXISTS payroll (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_id INT NOT NULL,
        period_month INT NOT NULL,
        period_year INT NOT NULL,
        base_salary DECIMAL(15,2) DEFAULT 0,
        total_allowances DECIMAL(15,2) DEFAULT 0,
        total_overtime DECIMAL(15,2) DEFAULT 0,
        gross_salary DECIMAL(15,2) DEFAULT 0,
        total_deductions DECIMAL(15,2) DEFAULT 0,
        tax_amount DECIMAL(15,2) DEFAULT 0,
        net_salary DECIMAL(15,2) DEFAULT 0,
        allowance_details JSON,
        deduction_details JSON,
        attendance_summary JSON,
        notes TEXT,
        status ENUM('draf','diproses','disetujui','dibayar') DEFAULT 'draf',
        processed_by INT,
        approved_by INT,
        paid_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
        UNIQUE KEY unique_payroll (employee_id, period_month, period_year)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Buat tabel pengguna
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        role ENUM('admin','manajer_sdm','staf_sdm','peninjau') DEFAULT 'staf_sdm',
        avatar VARCHAR(255),
        is_active TINYINT(1) DEFAULT 1,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Buat tabel log aktivitas
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(50) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ==================== DATA AWAL ====================

    // Data awal departemen
    $pdo->exec("INSERT IGNORE INTO departments (name, description, manager_name) VALUES
        ('Sumber Daya Manusia', 'Departemen Sumber Daya Manusia', 'Siti Nurhaliza'),
        ('Keuangan & Akuntansi', 'Departemen Keuangan dan Akuntansi', 'Budi Santoso'),
        ('Teknologi Informasi', 'Departemen Teknologi Informasi', 'Andi Wijaya'),
        ('Pemasaran', 'Departemen Pemasaran', 'Dewi Lestari'),
        ('Operasional', 'Departemen Operasional', 'Rudi Hartono'),
        ('Riset & Pengembangan', 'Departemen Riset dan Pengembangan', 'Fajar Nugroho'),
        ('Layanan Pelanggan', 'Departemen Layanan Pelanggan', 'Maya Sari'),
        ('Hukum & Kepatuhan', 'Departemen Hukum dan Kepatuhan', 'Ahmad Fauzi')
    ");

    // Data awal jabatan
    $pdo->exec("INSERT IGNORE INTO positions (name, department_id, level, base_salary) VALUES
        ('Manajer SDM', 1, 'Manajer', 15000000),
        ('Staf SDM', 1, 'Staf', 6500000),
        ('Spesialis Rekrutmen', 1, 'Staf', 7000000),
        ('Manajer Keuangan', 2, 'Manajer', 16000000),
        ('Akuntan', 2, 'Staf', 7500000),
        ('Akuntan Senior', 2, 'Penyelia', 10000000),
        ('Manajer TI', 3, 'Manajer', 18000000),
        ('Pengembang Perangkat Lunak', 3, 'Staf', 10000000),
        ('Pengembang Senior', 3, 'Penyelia', 14000000),
        ('Administrator Sistem', 3, 'Staf', 9000000),
        ('Manajer Pemasaran', 4, 'Manajer', 15000000),
        ('Staf Pemasaran', 4, 'Staf', 6000000),
        ('Pemasaran Digital', 4, 'Staf', 7000000),
        ('Manajer Operasional', 5, 'Manajer', 14000000),
        ('Staf Operasional', 5, 'Staf', 5500000),
        ('Manajer Riset', 6, 'Manajer', 17000000),
        ('Peneliti', 6, 'Staf', 9000000),
        ('Penyelia Layanan Pelanggan', 7, 'Penyelia', 8000000),
        ('Agen Layanan Pelanggan', 7, 'Staf', 5000000),
        ('Penasihat Hukum', 8, 'Penyelia', 12000000)
    ");

    // Data awal tunjangan
    $pdo->exec("INSERT IGNORE INTO allowances (name, type, default_amount, is_taxable) VALUES
        ('Tunjangan Transportasi', 'fixed', 500000, 1),
        ('Tunjangan Makan', 'fixed', 750000, 1),
        ('Tunjangan Kesehatan', 'fixed', 400000, 0),
        ('Tunjangan Komunikasi', 'fixed', 200000, 1),
        ('Tunjangan Jabatan', 'percentage', 10, 1),
        ('Tunjangan Keluarga', 'fixed', 300000, 0),
        ('Tunjangan Perumahan', 'fixed', 600000, 1),
        ('Insentif Kinerja', 'percentage', 5, 1)
    ");

    // Data awal potongan
    $pdo->exec("INSERT IGNORE INTO deductions (name, type, default_amount) VALUES
        ('BPJS Kesehatan', 'percentage', 1),
        ('BPJS Ketenagakerjaan', 'percentage', 2),
        ('PPh 21', 'percentage', 5),
        ('Iuran Pensiun', 'percentage', 1),
        ('Pinjaman Karyawan', 'fixed', 0),
        ('Keterlambatan', 'fixed', 50000),
        ('Asuransi', 'fixed', 150000)
    ");

    // Data awal karyawan (25 karyawan)
    $employees = [
        ['Siti','Nurhaliza','siti.nur@company.com','081234567001','P','1985-03-15','Jl. Merdeka No. 10','Jakarta',1,1,'2018-01-15','Tetap','BCA','1234567890','12.345.678.9-001','0001234567001','TK001234567001',15000000],
        ['Budi','Santoso','budi.santo@company.com','081234567002','L','1982-07-22','Jl. Sudirman No. 25','Jakarta',2,4,'2017-06-01','Tetap','Mandiri','2345678901','12.345.678.9-002','0001234567002','TK001234567002',16000000],
        ['Andi','Wijaya','andi.wijaya@company.com','081234567003','L','1990-11-05','Jl. Gatot Subroto No. 5','Jakarta',3,7,'2019-03-10','Tetap','BNI','3456789012','12.345.678.9-003','0001234567003','TK001234567003',18000000],
        ['Dewi','Lestari','dewi.lestari@company.com','081234567004','P','1988-09-12','Jl. Thamrin No. 8','Jakarta',4,11,'2018-08-20','Tetap','BRI','4567890123','12.345.678.9-004','0001234567004','TK001234567004',15000000],
        ['Rudi','Hartono','rudi.hartono@company.com','081234567005','L','1986-01-30','Jl. Rasuna Said No. 12','Jakarta',5,14,'2017-11-15','Tetap','BCA','5678901234','12.345.678.9-005','0001234567005','TK001234567005',14000000],
        ['Maya','Sari','maya.sari@company.com','081234567006','P','1992-05-18','Jl. Kuningan No. 3','Jakarta',7,18,'2020-02-01','Tetap','Mandiri','6789012345','12.345.678.9-006','0001234567006','TK001234567006',8000000],
        ['Fajar','Nugroho','fajar.nugroho@company.com','081234567007','L','1987-12-08','Jl. Senayan No. 7','Jakarta',6,16,'2019-07-01','Tetap','BNI','7890123456','12.345.678.9-007','0001234567007','TK001234567007',17000000],
        ['Rina','Wulandari','rina.wulan@company.com','081234567008','P','1993-04-25','Jl. Kemang No. 15','Jakarta',1,2,'2021-01-10','Tetap','BCA','8901234567','12.345.678.9-008','0001234567008','TK001234567008',6500000],
        ['Hendra','Pratama','hendra.pra@company.com','081234567009','L','1991-08-14','Jl. Pondok Indah No. 20','Jakarta',2,5,'2020-04-15','Tetap','Mandiri','9012345678','12.345.678.9-009','0001234567009','TK001234567009',7500000],
        ['Lina','Marlina','lina.marlina@company.com','081234567010','P','1994-02-28','Jl. Blok M No. 9','Jakarta',3,8,'2021-06-01','Kontrak','BRI','0123456789','12.345.678.9-010','0001234567010','TK001234567010',10000000],
        ['Dimas','Prasetyo','dimas.pras@company.com','081234567011','L','1989-06-17','Jl. Menteng No. 11','Jakarta',3,9,'2019-09-01','Tetap','BCA','1122334455','12.345.678.9-011','0001234567011','TK001234567011',14000000],
        ['Anisa','Rahma','anisa.rahma@company.com','081234567012','P','1995-10-03','Jl. Cikini No. 6','Jakarta',4,12,'2022-01-15','Kontrak','Mandiri','2233445566','12.345.678.9-012','0001234567012','TK001234567012',6000000],
        ['Yoga','Setiawan','yoga.setiawan@company.com','081234567013','L','1990-03-21','Jl. Salemba No. 14','Jakarta',4,13,'2021-03-01','Tetap','BNI','3344556677','12.345.678.9-013','0001234567013','TK001234567013',7000000],
        ['Putri','Handayani','putri.handay@company.com','081234567014','P','1993-07-09','Jl. Kelapa Gading No. 22','Jakarta',2,6,'2020-08-01','Tetap','BCA','4455667788','12.345.678.9-014','0001234567014','TK001234567014',10000000],
        ['Wahyu','Hidayat','wahyu.hidayat@company.com','081234567015','L','1988-11-27','Jl. Pluit No. 17','Jakarta',5,15,'2019-05-15','Tetap','BRI','5566778899','12.345.678.9-015','0001234567015','TK001234567015',5500000],
        ['Ratna','Dewi','ratna.dewi@company.com','081234567016','P','1991-01-14','Jl. Puri Indah No. 4','Jakarta',1,3,'2020-11-01','Tetap','Mandiri','6677889900','12.345.678.9-016','0001234567016','TK001234567016',7000000],
        ['Agus','Saputra','agus.saputra@company.com','081234567017','L','1987-09-06','Jl. Bintaro No. 30','Jakarta',3,10,'2018-12-01','Tetap','BNI','7788990011','12.345.678.9-017','0001234567017','TK001234567017',9000000],
        ['Fitri','Amalia','fitri.amalia@company.com','081234567018','P','1994-06-20','Jl. Serpong No. 8','Tangerang',6,17,'2021-09-01','Kontrak','BCA','8899001122','12.345.678.9-018','0001234567018','TK001234567018',9000000],
        ['Rizki','Ramadan','rizki.ramadan@company.com','081234567019','L','1992-12-01','Jl. Depok No. 12','Depok',7,19,'2022-03-01','Kontrak','Mandiri','9900112233','12.345.678.9-019','0001234567019','TK001234567019',5000000],
        ['Nurul','Hasanah','nurul.hasanah@company.com','081234567020','P','1996-04-15','Jl. Bogor No. 5','Bogor',7,19,'2022-06-01','Magang','BRI','0011223344','12.345.678.9-020','0001234567020','TK001234567020',5000000],
        ['Ahmad','Fauzi','ahmad.fauzi@company.com','081234567021','L','1984-08-10','Jl. Bekasi No. 18','Bekasi',8,20,'2018-04-01','Tetap','BCA','1100223344','12.345.678.9-021','0001234567021','TK001234567021',12000000],
        ['Dian','Permata','dian.permata@company.com','081234567022','P','1993-02-14','Jl. Tangerang No. 7','Tangerang',2,5,'2021-07-01','Tetap','Mandiri','2200334455','12.345.678.9-022','0001234567022','TK001234567022',7500000],
        ['Bayu','Prakoso','bayu.prakoso@company.com','081234567023','L','1991-10-28','Jl. Cilandak No. 21','Jakarta',3,8,'2020-01-15','Tetap','BNI','3300445566','12.345.678.9-023','0001234567023','TK001234567023',10000000],
        ['Eka','Putri','eka.putri@company.com','081234567024','P','1995-06-05','Jl. Tebet No. 13','Jakarta',5,15,'2022-09-01','Percobaan','BCA','4400556677','12.345.678.9-024','0001234567024','TK001234567024',5500000],
        ['Taufik','Ismail','taufik.ismail@company.com','081234567025','L','1989-04-18','Jl. Pancoran No. 9','Jakarta',4,13,'2019-11-01','Tetap','BRI','5500667788','12.345.678.9-025','0001234567025','TK001234567025',7000000]
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO employees (first_name, last_name, email, phone, gender, birth_date, address, city, department_id, position_id, hire_date, employment_status, bank_name, bank_account, npwp, bpjs_kesehatan, bpjs_ketenagakerjaan, base_salary) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    
    foreach ($employees as $emp) {
        $stmt->execute($emp);
    }

    // Data awal pengguna admin (password: admin123)
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO users (username, password, full_name, email, role) VALUES
        ('admin', '{$adminPassword}', 'Administrator', 'admin@payrollpro.com', 'admin'),
        ('manajer_sdm', '{$adminPassword}', 'Siti Nurhaliza', 'siti@payrollpro.com', 'manajer_sdm'),
        ('staf_sdm', '{$adminPassword}', 'Rina Wulandari', 'rina@payrollpro.com', 'staf_sdm')
    ");

    // Data awal kehadiran untuk 3 bulan terakhir
    $employees_data = $pdo->query("SELECT id FROM employees")->fetchAll();
    $stmtAtt = $pdo->prepare("INSERT IGNORE INTO attendance (employee_id, period_month, period_year, working_days, present_days, absent_days, sick_days, leave_days, late_days, overtime_hours) VALUES (?,?,?,?,?,?,?,?,?,?)");
    
    for ($m = 1; $m <= 3; $m++) {
        foreach ($employees_data as $emp) {
            $workingDays = 22;
            $absent = rand(0, 2);
            $sick = rand(0, 1);
            $leave = rand(0, 2);
            $late = rand(0, 3);
            $present = $workingDays - $absent - $sick - $leave;
            $overtime = rand(0, 20);
            $stmtAtt->execute([$emp['id'], $m, 2026, $workingDays, $present, $absent, $sick, $leave, $late, $overtime]);
        }
    }

    // Data awal penggajian untuk Januari dan Februari 2026
    $empAll = $pdo->query("SELECT e.*, d.name as dept_name, p.name as pos_name FROM employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        LEFT JOIN positions p ON e.position_id = p.id")->fetchAll();

    $stmtPayroll = $pdo->prepare("INSERT IGNORE INTO payroll (employee_id, period_month, period_year, base_salary, total_allowances, total_overtime, gross_salary, total_deductions, tax_amount, net_salary, allowance_details, deduction_details, attendance_summary, status, paid_date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    for ($m = 1; $m <= 2; $m++) {
        foreach ($empAll as $i => $emp) {
            $baseSalary = $emp['base_salary'];
            
            // Hitung tunjangan
            $transport = 500000;
            $meal = 750000;
            $health = 400000;
            $comm = 200000;
            $position_allow = $baseSalary * 0.10;
            $totalAllowances = $transport + $meal + $health + $comm + $position_allow;
            
            // Lembur
            $att = $pdo->query("SELECT * FROM attendance WHERE employee_id={$emp['id']} AND period_month={$m} AND period_year=2026")->fetch();
            $overtimeHours = $att ? $att['overtime_hours'] : 0;
            $overtimePay = $overtimeHours * ($baseSalary / 173) * 1.5;
            
            $grossSalary = $baseSalary + $totalAllowances + $overtimePay;
            
            // Hitung potongan
            $bpjsK = $grossSalary * 0.01;
            $bpjsT = $grossSalary * 0.02;
            $pension = $grossSalary * 0.01;
            $tax = ($grossSalary - $bpjsK - $bpjsT - $pension) * 0.05;
            $totalDeductions = $bpjsK + $bpjsT + $pension;
            
            $netSalary = $grossSalary - $totalDeductions - $tax;
            
            $allowanceDetails = json_encode([
                ['name' => 'Tunjangan Transportasi', 'amount' => $transport],
                ['name' => 'Tunjangan Makan', 'amount' => $meal],
                ['name' => 'Tunjangan Kesehatan', 'amount' => $health],
                ['name' => 'Tunjangan Komunikasi', 'amount' => $comm],
                ['name' => 'Tunjangan Jabatan', 'amount' => $position_allow],
            ]);
            
            $deductionDetails = json_encode([
                ['name' => 'BPJS Kesehatan', 'amount' => $bpjsK],
                ['name' => 'BPJS Ketenagakerjaan', 'amount' => $bpjsT],
                ['name' => 'Iuran Pensiun', 'amount' => $pension],
            ]);
            
            $attendanceSummary = json_encode($att ?: []);
            
            $status = ($m <= 1) ? 'dibayar' : 'disetujui';
            $paidDate = ($m <= 1) ? "2026-{$m}-25" : null;
            
            $stmtPayroll->execute([
                $emp['id'], $m, 2026, $baseSalary,
                $totalAllowances, $overtimePay, $grossSalary,
                $totalDeductions, $tax, $netSalary,
                $allowanceDetails, $deductionDetails, $attendanceSummary,
                $status, $paidDate
            ]);
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Setup database berhasil diselesaikan!',
        'tables_created' => ['departments', 'positions', 'employees', 'allowances', 'deductions', 'attendance', 'payroll', 'users', 'activity_log'],
        'seed_data' => [
            'departemen' => 8,
            'jabatan' => 20,
            'karyawan' => 25,
            'pengguna' => 3,
            'tunjangan' => 8,
            'potongan' => 7
        ],
        'default_login' => [
            'username' => 'admin',
            'password' => 'admin123'
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'line' => $e->getLine()
    ]);
}
