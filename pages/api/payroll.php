<?php
/**
 * API: Payroll Actions
 */
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json');
$db = db();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'generate':
        $month = (int)($_POST['month'] ?? date('m'));
        $year = (int)($_POST['year'] ?? date('Y'));
        $employeeIds = $_POST['employee_ids'] ?? [];
        
        if (empty($employeeIds)) {
            // Generate for all active employees
            $employees = $db->query("SELECT e.*, d.name as dept_name, p.name as pos_name FROM employees e LEFT JOIN departments d ON e.department_id=d.id LEFT JOIN positions p ON e.position_id=p.id WHERE e.is_active=1")->fetchAll();
        } else {
            $placeholders = implode(',', array_fill(0, count($employeeIds), '?'));
            $stmt = $db->prepare("SELECT e.*, d.name as dept_name, p.name as pos_name FROM employees e LEFT JOIN departments d ON e.department_id=d.id LEFT JOIN positions p ON e.position_id=p.id WHERE e.id IN ({$placeholders}) AND e.is_active=1");
            $stmt->execute($employeeIds);
            $employees = $stmt->fetchAll();
        }
        
        $generated = 0;
        $skipped = 0;
        
        foreach ($employees as $emp) {
            // Check if already exists
            $check = $db->prepare("SELECT id FROM payroll WHERE employee_id=? AND period_month=? AND period_year=?");
            $check->execute([$emp['id'], $month, $year]);
            if ($check->fetch()) {
                $skipped++;
                continue;
            }
            
            // Ensure attendance exists
            $att = $db->prepare("SELECT * FROM attendance WHERE employee_id=? AND period_month=? AND period_year=?");
            $att->execute([$emp['id'], $month, $year]);
            $attendance = $att->fetch();
            
            if (!$attendance) {
                // Create default attendance
                $db->prepare("INSERT INTO attendance (employee_id, period_month, period_year, working_days, present_days, absent_days, sick_days, leave_days, late_days, overtime_hours) VALUES (?,?,?,22,22,0,0,0,0,0)")
                    ->execute([$emp['id'], $month, $year]);
                $att->execute([$emp['id'], $month, $year]);
                $attendance = $att->fetch();
            }
            
            $baseSalary = (float)$emp['base_salary'];
            
            // Calculate allowances
            $transport = 500000;
            $meal = 750000;
            $health = 400000;
            $comm = 200000;
            $positionAllow = $baseSalary * 0.10;
            $totalAllowances = $transport + $meal + $health + $comm + $positionAllow;
            
            // Overtime
            $overtimeHours = (float)($attendance['overtime_hours'] ?? 0);
            $overtimePay = $overtimeHours * ($baseSalary / 173) * 1.5;
            
            // Absent deduction
            $absentDays = (int)($attendance['absent_days'] ?? 0);
            $absentDeduction = $absentDays * ($baseSalary / 22);
            
            // Late deduction
            $lateDays = (int)($attendance['late_days'] ?? 0);
            $lateDeduction = $lateDays * 50000;
            
            $grossSalary = $baseSalary + $totalAllowances + $overtimePay - $absentDeduction - $lateDeduction;
            
            // Deductions
            $bpjsK = $grossSalary * 0.01;
            $bpjsT = $grossSalary * 0.02;
            $pension = $grossSalary * 0.01;
            $totalDeductions = $bpjsK + $bpjsT + $pension;
            
            // Tax
            $taxable = $grossSalary - $bpjsK - $bpjsT - $pension;
            $tax = max(0, $taxable * 0.05);
            
            $netSalary = $grossSalary - $totalDeductions - $tax;
            
            $payrollNum = sprintf('GJI-%04d%02d-%03d', $year, $month, $emp['id']);
            
            $allowanceDetails = json_encode([
                ['name' => 'Tunjangan Transportasi', 'amount' => $transport],
                ['name' => 'Tunjangan Makan', 'amount' => $meal],
                ['name' => 'Tunjangan Kesehatan', 'amount' => $health],
                ['name' => 'Tunjangan Komunikasi', 'amount' => $comm],
                ['name' => 'Tunjangan Jabatan (10%)', 'amount' => $positionAllow],
            ]);
            
            $deductionDetails = json_encode([
                ['name' => 'BPJS Kesehatan (1%)', 'amount' => $bpjsK],
                ['name' => 'BPJS Ketenagakerjaan (2%)', 'amount' => $bpjsT],
                ['name' => 'Iuran Pensiun (1%)', 'amount' => $pension],
                ['name' => 'Potongan Absen (' . $absentDays . ' hari)', 'amount' => $absentDeduction],
                ['name' => 'Potongan Keterlambatan (' . $lateDays . ' hari)', 'amount' => $lateDeduction],
            ]);
            
            $attendanceSummary = json_encode($attendance);
            
            $stmt = $db->prepare("INSERT INTO payroll (payroll_number, employee_id, period_month, period_year, base_salary, total_allowances, total_overtime, gross_salary, total_deductions, tax_amount, net_salary, allowance_details, deduction_details, attendance_summary, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,'draf')");
            $stmt->execute([
                $payrollNum, $emp['id'], $month, $year, $baseSalary,
                $totalAllowances, $overtimePay, $grossSalary,
                $totalDeductions + $absentDeduction + $lateDeduction, $tax, $netSalary,
                $allowanceDetails, $deductionDetails, $attendanceSummary
            ]);
            $generated++;
        }
        
        jsonResponse([
            'success' => true,
            'message' => "Berhasil generate gaji: {$generated} karyawan. Dilewati: {$skipped} (sudah ada).",
            'generated' => $generated,
            'skipped' => $skipped
        ]);
        break;

    case 'update_status':
        $id = (int)($_POST['id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        $validStatuses = ['draf','diproses','disetujui','dibayar'];
        
        if (!in_array($status, $validStatuses)) {
            jsonResponse(['success' => false, 'message' => 'Status tidak valid'], 400);
        }
        
        $extra = '';
        if ($status === 'dibayar') {
            $extra = ", paid_date = CURDATE()";
        }
        
        $stmt = $db->prepare("UPDATE payroll SET status = ?{$extra} WHERE id = ?");
        $stmt->execute([$status, $id]);
        jsonResponse(['success' => true, 'message' => 'Status berhasil diperbarui']);
        break;

    case 'bulk_status':
        $ids = $_POST['ids'] ?? [];
        $status = sanitize($_POST['status'] ?? '');
        
        if (empty($ids) || empty($status)) {
            jsonResponse(['success' => false, 'message' => 'Data tidak lengkap'], 400);
        }
        
        $extra = $status === 'dibayar' ? ", paid_date = CURDATE()" : '';
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("UPDATE payroll SET status = ?{$extra} WHERE id IN ({$placeholders})");
        $stmt->execute(array_merge([$status], $ids));
        jsonResponse(['success' => true, 'message' => count($ids) . ' data berhasil diperbarui']);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM payroll WHERE id = ? AND status = 'draf'");
        $stmt->execute([$id]);
        if ($stmt->rowCount() > 0) {
            jsonResponse(['success' => true, 'message' => 'Data penggajian berhasil dihapus']);
        }
        jsonResponse(['success' => false, 'message' => 'Hanya data draf yang bisa dihapus'], 400);
        break;

    case 'get_slip':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT p.*, e.first_name, e.last_name, e.employee_id as emp_id, e.email, e.phone, e.bank_name, e.bank_account, e.npwp, e.bpjs_kesehatan, e.bpjs_ketenagakerjaan, e.employment_status, d.name as department_name, pos.name as position_name FROM payroll p JOIN employees e ON p.employee_id=e.id LEFT JOIN departments d ON e.department_id=d.id LEFT JOIN positions pos ON e.position_id=pos.id WHERE p.id=?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if ($data) {
            $data['allowance_details'] = json_decode($data['allowance_details'], true);
            $data['deduction_details'] = json_decode($data['deduction_details'], true);
            $data['attendance_summary'] = json_decode($data['attendance_summary'], true);
        }
        jsonResponse(['success' => true, 'data' => $data]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak valid'], 400);
}
