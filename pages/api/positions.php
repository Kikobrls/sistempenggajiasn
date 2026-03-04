<?php
/**
 * API: Aksi Jabatan
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
        $empCount = $db->prepare("SELECT COUNT(*) FROM employees WHERE position_id=? AND is_active=1");
        $empCount->execute([$id]);
        if ($empCount->fetchColumn() > 0) {
            jsonResponse(['success' => false, 'message' => 'Tidak dapat menghapus jabatan yang masih dimiliki karyawan']);
        }
        $stmt = $db->prepare("UPDATE positions SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['success' => true, 'message' => 'Jabatan berhasil dihapus']);
        break;

    case 'save':
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $department_id = (int)($_POST['department_id'] ?? 0);
        $level = sanitize($_POST['level'] ?? 'Staf');
        $base_salary = (float)str_replace(['.', ','], ['', '.'], $_POST['base_salary'] ?? '0');
        $description = sanitize($_POST['description'] ?? '');

        if (empty($name)) {
            jsonResponse(['success' => false, 'message' => 'Nama jabatan wajib diisi'], 400);
        }

        try {
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE positions SET name=?, department_id=?, level=?, base_salary=?, description=? WHERE id=?");
                $stmt->execute([$name, $department_id, $level, $base_salary, $description, $id]);
                jsonResponse(['success' => true, 'message' => 'Jabatan berhasil diperbarui']);
            } else {
                $stmt = $db->prepare("INSERT INTO positions (name, department_id, level, base_salary, description) VALUES (?,?,?,?,?)");
                $stmt->execute([$name, $department_id, $level, $base_salary, $description]);
                jsonResponse(['success' => true, 'message' => 'Jabatan berhasil ditambahkan']);
            }
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal menyimpan jabatan: ' . $e->getMessage()], 400);
        }
        break;

    case 'get_by_department':
        $dept_id = (int)($_GET['department_id'] ?? 0);
        $positions = $db->prepare("SELECT * FROM positions WHERE department_id=? AND is_active=1 ORDER BY name");
        $positions->execute([$dept_id]);
        jsonResponse(['success' => true, 'data' => $positions->fetchAll()]);
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak valid'], 400);
}
