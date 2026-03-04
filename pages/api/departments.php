<?php
/**
 * API: Aksi Departemen
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
        $empCount = $db->prepare("SELECT COUNT(*) FROM employees WHERE department_id=? AND is_active=1");
        $empCount->execute([$id]);
        if ($empCount->fetchColumn() > 0) {
            jsonResponse(['success' => false, 'message' => 'Tidak dapat menghapus departemen yang masih memiliki karyawan']);
        }
        $stmt = $db->prepare("UPDATE departments SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(['success' => true, 'message' => 'Departemen berhasil dihapus']);
        break;

    case 'save':
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $manager = sanitize($_POST['manager_name'] ?? '');

        if (empty($name)) {
            jsonResponse(['success' => false, 'message' => 'Nama departemen wajib diisi'], 400);
        }

        try {
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE departments SET name=?, description=?, manager_name=? WHERE id=?");
                $stmt->execute([$name, $description, $manager, $id]);
                jsonResponse(['success' => true, 'message' => 'Departemen berhasil diperbarui']);
            } else {
                $stmt = $db->prepare("INSERT INTO departments (name, description, manager_name) VALUES (?,?,?)");
                $stmt->execute([$name, $description, $manager]);
                jsonResponse(['success' => true, 'message' => 'Departemen berhasil ditambahkan']);
            }
        } catch (PDOException $e) {
            jsonResponse(['success' => false, 'message' => 'Gagal menyimpan departemen: ' . $e->getMessage()], 400);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Aksi tidak valid'], 400);
}
