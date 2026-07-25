<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - IIMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verify-card {
            max-width: 450px;
            width: 100%;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            background: #fff;
            text-align: center;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
        }
        .success-icon {
            background-color: #d1e7dd;
            color: #198754;
        }
        .error-icon {
            background-color: #f8d7da;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <?php if ($success): ?>
            <div class="icon-circle success-icon">
                <i class="fa-solid fa-check"></i>
            </div>
            <h3 class="mb-3">Setup Complete!</h3>
            <p class="text-muted mb-4"><?php echo htmlspecialchars($message); ?></p>
            <a href="<?php echo BASE_URL; ?>/demo/login" class="btn btn-primary w-100">Proceed to Login</a>
        <?php elseif (!empty($showForm) && $showForm): ?>
            <h3 class="mb-3">Set Your Password</h3>
            <p class="text-muted mb-4">Please set a password for your account to complete the setup.</p>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
                <div class="mb-3 text-start">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}" title="Must contain at least one number, one uppercase, one lowercase letter, and one special character.">
                    <div class="form-text">Must be at least 8 characters and include uppercase, lowercase, number, and special character.</div>
                </div>
                <div class="mb-4 text-start">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="8">
                </div>
                <button type="submit" class="btn btn-primary w-100">Save Password & Verify</button>
            </form>
        <?php else: ?>
            <div class="icon-circle error-icon">
                <i class="fa-solid fa-xmark"></i>
            </div>
            <h3 class="mb-3">Verification Failed</h3>
            <p class="text-muted mb-4"><?php echo htmlspecialchars($message); ?></p>
            <a href="<?php echo BASE_URL; ?>/demo/login" class="btn btn-outline-secondary w-100">Back to Login</a>
        <?php endif; ?>
    </div>
</body>
</html>
