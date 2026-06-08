<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | KSP Bhak'Ti Jaya</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0d6efd 0%, #003d99 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 15px;
        }

        .card {
            border: none;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .card-header {
            background: transparent;
            border: none;
            padding: 40px 30px 10px 30px;
            text-align: center;
        }

        .logo-box {
            width: 80px;
            height: 80px;
            background: #fff;
            border-radius: 20px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .logo-box img {
            width: 60px;
            height: 60px;
            border-radius: 15px;
        }

        .card-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #444;
            font-size: 0.9rem;
        }

        .input-group {
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            background: #fff;
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: #6c757d;
            padding-left: 15px;
        }

        .form-control {
            background: transparent;
            border: none;
            padding: 12px 15px;
            font-size: 1rem;
        }

        .form-control:focus {
            box-shadow: none;
            background: transparent;
        }

        .btn-login {
            background: #0d6efd;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }

        .footer-text {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            text-align: center;
            margin-top: 20px;
        }

        /* Alert styling */
        .alert {
            border-radius: 12px;
            font-size: 0.85rem;
            border: none;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <div class="logo-box">
                    <img src="logo.jpeg" alt="Logo">
                </div>
                <h4 class="fw-bold text-dark mb-1">KSP BHAK'TI JAYA</h4>
                <p class="text-muted small">Silakan masuk ke akun Anda</p>
            </div>

            <div class="card-body">
                <?php if(isset($_GET['pesan'])): ?>
                    <?php if($_GET['pesan'] == "gagal"): ?>
                        <div class="alert alert-danger d-flex align-items-center mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i> Username atau password salah!
                        </div>
                    <?php elseif($_GET['pesan'] == "logout"): ?>
                        <div class="alert alert-success d-flex align-items-center mb-4">
                            <i class="fas fa-check-circle me-2"></i> Berhasil logout.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <form action="proses_login.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Ketik username..." required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Ketik password..." required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login w-100">
                        Masuk Sekarang <i class="fas fa-sign-in-alt ms-2"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <div class="footer-text">
            &copy; 2026 KSP Bhak'Ti Jaya | Ambon, Maluku
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>