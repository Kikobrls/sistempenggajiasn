<?php
/**
 * Database Migration Script
 * Remove all "code" fields and prefixed IDs from the payroll system
 * Run this script once to migrate existing data
 */

require_once __DIR__ . '/config/database.php';

try {
    $pdo = db();
    
    echo "Starting database migration to remove code fields...\n";
    
    // Backup existing data first
    echo "Creating backup tables...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS departments_backup AS SELECT * FROM departments");
    $pdo->exec("CREATE TABLE IF NOT EXISTS positions_backup AS SELECT * FROM positions");
    $pdo->exec("CREATE TABLE IF NOT EXISTS allowances_backup AS SELECT * FROM allowances");
    $pdo->exec("CREATE TABLE IF NOT EXISTS deductions_backup AS SELECT * FROM deductions");
    $pdo->exec("CREATE TABLE IF NOT EXISTS employees_backup AS SELECT * FROM employees");
    $pdo->exec("CREATE TABLE IF NOT EXISTS payroll_backup AS SELECT * FROM payroll");
    
    echo "Backup tables created successfully.\n";
    
    // Remove unique constraint on code fields and drop code columns
    echo "Modifying departments table...\n";
    $pdo->exec("ALTER TABLE departments DROP INDEX code"); // Remove unique constraint
    $pdo->exec("ALTER TABLE departments DROP COLUMN code");
    
    echo "Modifying positions table...\n";
    $pdo->exec("ALTER TABLE positions DROP INDEX code");
    $pdo->exec("ALTER TABLE positions DROP COLUMN code");
    
    echo "Modifying allowances table...\n";
    $pdo->exec("ALTER TABLE allowances DROP INDEX code");
    $pdo->exec("ALTER TABLE allowances DROP COLUMN code");
    
    echo "Modifying deductions table...\n";
    $pdo->exec("ALTER TABLE deductions DROP INDEX code");
    $pdo->exec("ALTER TABLE deductions DROP COLUMN code");
    
    echo "Modifying employees table...\n";
    $pdo->exec("ALTER TABLE employees DROP INDEX employee_id");
    $pdo->exec("ALTER TABLE employees DROP COLUMN employee_id");
    
    echo "Modifying payroll table...\n";
    $pdo->exec("ALTER TABLE payroll DROP INDEX payroll_number");
    $pdo->exec("ALTER TABLE payroll DROP COLUMN payroll_number");
    
    // Add new columns if needed
    echo "Adding new ID columns if needed...\n";
    // Employee table already has auto-increment 'id' field, no need to add
    // Payroll table already has auto-increment 'id' field, no need to add
    
    echo "Database migration completed successfully!\n";
    echo "All code fields have been removed.\n";
    echo "Backup tables created: departments_backup, positions_backup, allowances_backup, deductions_backup, employees_backup, payroll_backup\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>