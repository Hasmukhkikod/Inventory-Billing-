<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Reset Password Page
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

$db = new Database();
$auth = new Auth($db);

// If already logged in, redirect to Dashboard
if ($auth->check()) {
    header("Location: " . BASE_URL . "/index");
    exit;
}

$token = $_GET['token'] ?? '';
$isValidToken = false;
$errorMessage = "";

if (empty($token)) {
    $errorMessage = "Invalid or missing reset token.";
} else {
    // Check if token exists and is valid
    $stmt = $db->query("SELECT id, reset_request_ip FROM users WHERE reset_token = ? AND reset_expires_at > NOW() LIMIT 1", [$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $errorMessage = "This password reset link is invalid or has expired.";
    } else {
        // Strict IP match check (User Request)
        if ($user['reset_request_ip'] !== $_SERVER['REMOTE_ADDR']) {
            $errorMessage = "Security Error: Your current network/IP address does not match the one used to request this reset link.";
        } else {
            $isValidToken = true;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reset Password — Grovixo</title>
  <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/img/Asset%2015%4072x.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Playfair+Display:ital,wght@0,500;0,600;1,500;1,600&display=swap" rel="stylesheet" />
  <style>
    :root{--blue:#3b5bff;--navy:#12214f;--ink:#111319;--body:#626977;--line:#e2e7f2;--wash:#f6f9ff;--green:#13845b;--error:#c33b4a}*{box-sizing:border-box}body{margin:0;min-height:100vh;font-family:"DM Sans",sans-serif;color:var(--ink);background:radial-gradient(circle at 12% 10%,#e2e9ff 0,transparent 28%),linear-gradient(145deg,#fff,#f7faff)}a{text-decoration:none}.page{min-height:100vh;display:grid;grid-template-columns:1fr 1fr}.side{position:relative;overflow:hidden;padding:42px clamp(28px,5vw,80px);color:#fff;background:radial-gradient(circle at 90% 10%,#637bff 0,transparent 28%),var(--navy)}.side:after{content:"";position:absolute;width:460px;height:460px;bottom:-270px;left:-160px;border-radius:50%;border:45px solid #3552c8}.brand{position:relative;z-index:1;display:inline-flex;align-items:center}.brand img{display:block;width:139px;filter:brightness(0) invert(1)}.side-content{position:relative;z-index:1;max-width:475px;margin:clamp(80px,16vh,150px) auto 0}.eyebrow{margin:0 0 16px;color:#aebdff;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;font-weight:700}.side h1{margin:0;font-size:clamp(39px,4.2vw,60px);letter-spacing:-3px;line-height:1.05}.side h1 em{font-family:"Playfair Display";font-weight:500}.side-content>p:not(.eyebrow){margin:21px 0 29px;color:#c4cff2;font-size:16px;line-height:1.65}.auth{display:grid;place-items:center;padding:42px}.auth-inner{width:min(100%,560px)}.back{display:inline-block;margin-bottom:42px;color:#6b7280;font-size:12px;font-weight:700}.back:hover{color:var(--blue)}.auth h2{margin:0;font-size:31px;letter-spacing:-1.4px}.auth h2 em{font-family:"Playfair Display";font-weight:500}.lead{margin:9px 0 24px;color:var(--body);font-size:13px;line-height:1.6}.field{display:grid;gap:7px;margin-top:14px}.field label{font-size:12px;color:#414957;font-weight:700}.field input{width:100%;padding:14px;border:1px solid #dce2ee;border-radius:9px;outline:0;background:#fff;color:#202530;font:14px "DM Sans"}.field input:focus{border-color:var(--blue);box-shadow:0 0 0 3px #3b5bff14}.password{position:relative}.password input{padding-right:45px}.reveal{position:absolute;right:8px;bottom:10px;padding:4px 6px;border:0;color:#687083;background:transparent;font-size:11px;font-weight:700;cursor:pointer}.submit{width:100%;margin-top:21px;padding:15px;border:0;border-radius:10px;color:#fff;background:linear-gradient(135deg,#4d6aff,#2d4cf1);box-shadow:0 12px 21px #3b5bff30;font-size:14px;font-weight:700;cursor:pointer}.submit:hover{transform:translateY(-1px)}.submit:disabled{opacity:0.7;cursor:not-allowed;transform:none}.message{display:none;margin-top:15px;padding:11px;border-radius:9px;background:#eaf9f2;color:var(--green);font-size:13px;font-weight:700}.error{display:none;margin-top:6px;color:var(--error);font-size:11px}.input-error{border-color:var(--error)!important;box-shadow:0 0 0 3px #c33b4a12!important}@media(max-width:820px){.page{grid-template-columns:1fr}.side{min-height:330px;padding:29px}.side-content{margin:52px auto 0}.auth{padding:34px 22px 50px}}@media(max-width:460px){.side h1{font-size:38px}.auth{padding:28px 19px 44px}.back{margin-bottom:30px}}
  </style>
</head>
<body>
  <main class="page">
      <aside class="side">
          <a class="brand" href="<?php echo BASE_URL; ?>/index" aria-label="Grovixo home">
              <img src="<?php echo BASE_URL; ?>/assets/img/grovixo_logo%201.png" alt="Grovixo" onerror="this.src='<?php echo BASE_URL; ?>/assets/images/logo.png'" />
          </a>
          <div class="side-content">
              <p class="eyebrow">Secure Account</p>
              <h1>Set a new <em>Password.</em></h1>
              <p>Choose a strong password that you haven't used before.</p>
          </div>
      </aside>
      <section class="auth">
          <div class="auth-inner">
              <a class="back" href="<?php echo BASE_URL; ?>/login">← Back to Login</a>
              <div class="form-pane active" id="reset-password">
                  <h2>Reset <em>Password.</em></h2>
                  
                  <?php if (!$isValidToken): ?>
                      <div style="margin-top:20px; padding:20px; border:1px solid #ffcccc; background:#fff5f5; border-radius:10px;">
                          <h3 style="color:#c33b4a; margin-top:0;">Access Denied</h3>
                          <p style="color:#626977; margin-bottom:0; font-size:14px;"><?php echo htmlspecialchars($errorMessage); ?></p>
                      </div>
                  <?php else: ?>
                      <p class="lead">Enter your new password below.</p>
                      <form id="reset-form" novalidate>
                          <?php echo Helpers::csrfField(); ?>
                          <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />
                          
                          <div class="field password">
                              <label for="new_password">New Password</label>
                              <input id="new_password" name="new_password" type="password" required minlength="6" placeholder="Must be at least 6 characters" />
                              <button class="reveal" type="button" data-reveal="new_password">Show</button>
                          </div>
                          
                          <div class="field password">
                              <label for="confirm_password">Confirm New Password</label>
                              <input id="confirm_password" name="confirm_password" type="password" required minlength="6" placeholder="Repeat your new password" />
                              <button class="reveal" type="button" data-reveal="confirm_password">Show</button>
                          </div>
                          
                          <p class="error" id="form-error">Please fill out all fields correctly.</p>
                          <div class="message" id="form-success"></div>
                          
                          <button class="submit" id="btn-submit" type="submit">Update Password →</button>
                      </form>
                  <?php endif; ?>
              </div>
          </div>
      </section>
  </main>
  
  <div id="custom-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
      <div style="background:#fff; padding:30px; border-radius:12px; max-width:400px; width:90%; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
          <div id="modal-icon" style="font-size:48px; margin-bottom:15px;"></div>
          <h3 id="modal-title" style="margin:0 0 10px; font-family:'Playfair Display'; font-size:24px; color:#12214f;"></h3>
          <p id="modal-msg" style="color:#626977; font-size:14px; line-height:1.5; margin:0 0 20px;"></p>
          <button onclick="document.getElementById('custom-modal').style.display='none'" style="background:var(--blue); color:#fff; border:none; padding:10px 24px; border-radius:8px; cursor:pointer; font-weight:700; font-family:'DM Sans';">Close</button>
      </div>
  </div>
  
  <?php if ($isValidToken): ?>
  <script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
    
    function showModal(title, msg, isSuccess) {
        document.getElementById('modal-title').textContent = title;
        document.getElementById('modal-msg').textContent = msg;
        document.getElementById('modal-icon').innerHTML = isSuccess ? '✅' : '❌';
        document.getElementById('custom-modal').style.display = 'flex';
    }
    
    document.querySelectorAll('[data-reveal]').forEach(b=>b.addEventListener('click',()=>{
        const input=document.getElementById(b.dataset.reveal),show=input.type==='password';
        input.type=show?'text':'password';
        b.textContent=show?'Hide':'Show';
    }));

    document.getElementById('reset-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = this;
        const valid = form.checkValidity();
        const err = document.getElementById('form-error');
        const success = document.getElementById('form-success');
        const btn = document.getElementById('btn-submit');
        
        const pwd1 = document.getElementById('new_password').value;
        const pwd2 = document.getElementById('confirm_password').value;
        
        err.style.display = 'none';
        success.style.display = 'none';
        
        form.querySelectorAll('input[required]').forEach(i => i.classList.toggle('input-error', !i.checkValidity()));
        
        if(!valid) {
            err.textContent = "Please fill out all fields correctly (min 6 characters).";
            err.style.display = 'block';
            return;
        }
        
        if(pwd1 !== pwd2) {
            err.textContent = "Passwords do not match.";
            err.style.display = 'block';
            document.getElementById('confirm_password').classList.add('input-error');
            return;
        }

        const formData = new FormData(form);
        
        btn.disabled = true;
        btn.textContent = 'Updating...';

        try {
            const res = await fetch(BASE_URL + '/api/reset_password.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (data.status) {
                showModal('Success!', data.message + " Redirecting to login...", true);
                form.reset();
                setTimeout(() => {
                    window.location.href = BASE_URL + '/login';
                }, 2000);
            } else {
                showModal('Error', data.message, false);
                btn.disabled = false;
                btn.textContent = 'Update Password →';
            }
        } catch (error) {
            showModal('Error', "An error occurred. Please try again later.", false);
            btn.disabled = false;
            btn.textContent = 'Update Password →';
        }
    });
  </script>
  <?php endif; ?>
</body>
</html>
