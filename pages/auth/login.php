<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        try {
            $stmt = db()->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                
                // Update last login
                $stmt = db()->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Log activity
                $stmt = db()->prepare("INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?, 'login', 'Pengguna berhasil masuk', ?)");
                $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
                
                header('Location: /index.php');
                exit;
            } else {
                $error = 'Username atau password salah';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan pada server';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Masuk - <?= APP_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="/public/css/adminlte.css" />
    <link rel="stylesheet" href="/public/css/custom.css" />
    <style>
        .login-floating-shapes .shape {
            position: absolute;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
        }
        .shape-1 { width: 200px; height: 200px; top: 10%; left: 5%; animation-delay: 0s; }
        .shape-2 { width: 150px; height: 150px; top: 60%; right: 10%; animation-delay: 5s; }
        .shape-3 { width: 100px; height: 100px; bottom: 20%; left: 20%; animation-delay: 2s; }
        .shape-4 { width: 250px; height: 250px; top: -5%; right: -5%; animation-delay: 8s; border-radius: 30%; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-20px) rotate(5deg); }
            50% { transform: translateY(10px) rotate(-3deg); }
            75% { transform: translateY(-15px) rotate(2deg); }
        }
        
        .login-input {
            background: #f8fafc !important;
            border: 2px solid #e2e8f0 !important;
            border-radius: 12px !important;
            padding: 0.8rem 1rem 0.8rem 3rem !important;
            font-size: 0.95rem !important;
            transition: all 0.3s ease !important;
        }
        
        .login-input:focus {
            background: white !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
        }
        
        .login-input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
            z-index: 5;
            transition: color 0.3s;
        }
        
        .login-input-wrapper:focus-within .login-input-icon {
            color: #6366f1;
        }
        
        .login-btn {
            background: linear-gradient(135deg, #4f46e5, #7c3aed) !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 0.85rem !important;
            font-size: 1rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.5px;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4) !important;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.5) !important;
        }
        
        .demo-credentials {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(124, 58, 237, 0.05));
            border: 1px dashed rgba(99, 102, 241, 0.3);
            border-radius: 12px;
            padding: 1rem 1.25rem;
        }
    </style>
</head>
<body style="font-family: 'Inter', sans-serif; margin: 0; padding: 0;">
    <div class="login-page-modern">
        <!-- Floating Shapes -->
        <div class="login-floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
        
        <div class="login-card-modern animate-fade-in">
            <!-- Logo -->
            <div class="login-logo">
                <i class="bi bi-cash-stack text-white" style="font-size: 28px;"></i>
            </div>
            
            <h2 class="text-center fw-bold mb-1" style="color: #0f172a; letter-spacing: -0.5px;">
                <?= APP_NAME ?>
            </h2>
            <p class="text-center text-muted mb-4" style="font-size: 0.9rem;">
                Sistem Penggajian Karyawan
            </p>
            
            <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center py-2 px-3 mb-3" style="border-radius: 10px; font-size: 0.875rem; border: none; background: #fef2f2;">
                <i class="bi bi-exclamation-circle me-2 text-danger"></i>
                <span class="text-danger"><?= $error ?></span>
            </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #475569;">Username</label>
                    <div class="position-relative login-input-wrapper">
                        <i class="bi bi-person login-input-icon"></i>
                        <input type="text" name="username" class="form-control login-input" placeholder="Masukkan username" value="<?= sanitize($_POST['username'] ?? '') ?>" required autofocus>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold" style="font-size: 0.85rem; color: #475569;">Password</label>
                    <div class="position-relative login-input-wrapper">
                        <i class="bi bi-lock login-input-icon"></i>
                        <input type="password" name="password" class="form-control login-input" placeholder="Masukkan password" required>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" style="border-radius: 4px;">
                        <label class="form-check-label" for="remember" style="font-size: 0.85rem; color: #64748b;">
                            Ingat saya
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 login-btn text-white">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Masuk
                </button>
            </form>
            
            <div class="demo-credentials mt-4">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-info-circle text-primary me-2"></i>
                    <span style="font-size: 0.8rem; font-weight: 600; color: #4f46e5;">Login Demo</span>
                </div>
                <div style="font-size: 0.8rem; color: #64748b;">
                    <div><strong>Username:</strong> admin &nbsp;|&nbsp; <strong>Password:</strong> admin123</div>
                </div>
            </div>
            
            <p class="text-center mt-4 mb-0" style="font-size: 0.8rem; color: #94a3b8;">
                &copy; <?= date('Y') ?> <?= COMPANY_NAME ?>
            </p>
        </div>
    </div>
</body>
</html>
