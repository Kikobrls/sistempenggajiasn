<?php
/**
 * API: Aksi Karyawan (CRUD)
 */
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json');
$db = db();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE employees SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(['success' => true, 'message' => 'Karyawan berhasil dihapus']);
        }
        jsonResponse(['success' => false, 'message' => 'ID tidak valid'], 400);
        break;

    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $db->prepare("SELECT e.*, d.name as department_name, p.name as position_name FROM employees e LEFT JOIN departments d ON e.department_id=d.id LEFT JOIN positions p ON e.position_id=p.id WHERE e.id=?");
        $stmt->execute([$id]);
        $employee = $stmt->fetch();
        jsonResponse(['success' => true, 'data' => $employee]);
        break;

    case 'list':
        $employees = $db->query("SELECT e.*, d.name as department_name, p.name as position_name FROM employees e LEFT JOIN departments d ON e.department_id=d.id LEFT JOIN positions p ON e.position_id=p.id WHERE e.is_active=1 ORDER BY e.first_name")->fetchAll();
        jsonResponse(['success' => true, 'data' => $employees]);
        break;

    case 'save':
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'employee_id' => sanitize($_POST['employee_id'] ?? ''),
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'gender' => sanitize($_POST['gender'] ?? 'L'),
            'birth_date' => sanitize($_POST['birth_date'] ?? ''),
            'address' => sanitize($_POST['address'] ?? ''),
            'city' => sanitize($_POST['city'] ?? ''),
            'department_id' => (int)($_POST['department_id'] ?? 0),
            'position_id' => (int)($_POST['position_id'] ?? 0),
            'hire_date' => sanitize($_POST['hire_date'] ?? ''),
            'employment_status' => sanitize($_POST['employment_status'] ?? 'Tetap'),
            'bank_name' => sanitize($_POST['bank_name'] ?? ''),
            'bank_account' => sanitize($_POST['bank_account'] ?? ''),
            'npwp' => sanitize($_POST['npwp'] ?? ''),
            'bpjs_kesehatan' => sanitize($_POST['bpjs_kesehatan'] ?? ''),
            'bpjs_ketenagakerjaan' => sanitize($_POST['bpjs_ketenagakerjaan'] ?? ''),
            'base_salary' => (float)str_replace(['.', ','], ['', '.'], $_POST['base_salary'] ?? '0'),
        ];

        if (empty($data['first_name']) || empty($data['email']) || empty($data['hire_date'])) {
            jsonResponse(['success' => false, 'message' => 'Nama, email, dan tanggal masuk wajib diisi'], 400);
        }

        try {
            if ($id > 0) {
                // Update
                $sql = "UPDATE employees SET employee_id=?, first_name=?, last_name=?, email=?, phone=?, gender=?, birth_date=?, address=?, city=?, department_id=?, position_id=?, hire_date=?, employment_status=?, bank_name=?, bank_account=?, npwp=?, bpjs_kesehatan=?, bpjs_ketenagakerjaan=?, base_salary=? WHERE id=?";
                $stmt = $db->prepare($sql);
                $stmt->execute([...array_values($data), $id]);
                jsonResponse(['success' => true, 'message' => 'Data karyawan berhasil diperbarui']);
            } else {
                // Insert
                $sql = "INSERT INTO employees (employee_id, first_name, last_name, email, phone, gender, birth_date, address, city, department_id, position_id, hire_date, employment_status, bank_name, bank_account, npwp, bpjs_kesehatan, bpjs_ketenagakerjaan, base_salary) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt = $db->prepare($sql);
                $stmt->execute(array_values($data));
                jsonResponse(['success' => true, 'message' => 'Karyawan berhasil ditambahkan', 'id' => $db->lastInsertId()]);
            }
        } catch (PDOException $e) {
            $msg = 'Gagal menyimpan data';
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $msg = 'ID Karyawan atau Email sudah terdaftar';
            }
            jsonResponse(['success' => false, 'message' => $msg], 400);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak valid'], 400);
}
