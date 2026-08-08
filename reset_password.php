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
  <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/img/favicon.png?v=<?php echo Helpers::assetVersion('/assets/img/favicon.png'); ?>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Playfair+Display:ital,wght@0,500;0,600;1,500;1,600&display=swap" rel="stylesheet" />
  <style>
    :root{--blue:#3b5bff;--navy:#12214f;--ink:#111319;--body:#626977;--line:#e2e7f2;--wash:#f6f9ff;--green:#13845b;--error:#c33b4a}*{box-sizing:border-box}body{margin:0;min-height:100vh;font-family:"DM Sans",sans-serif;color:var(--ink);background:radial-gradient(circle at 12% 10%,#e2e9ff 0,transparent 28%),linear-gradient(145deg,#fff,#f7faff)}a{text-decoration:none}.page{min-height:100vh;display:grid;grid-template-columns:1fr 1fr}.side{position:relative;overflow:hidden;padding:42px clamp(28px,5vw,80px);color:#fff;background:radial-gradient(circle at 90% 10%,#637bff 0,transparent 28%),var(--navy)}.side:after{content:"";position:absolute;width:460px;height:460px;bottom:-270px;left:-160px;border-radius:50%;border:45px solid #3552c8}.brand{position:relative;z-index:1;display:inline-flex;align-items:center}.brand img{display:block;width:139px;filter:brightness(0) invert(1)}.side-content{position:relative;z-index:1;max-width:475px;margin:clamp(80px,16vh,150px) auto 0}.eyebrow{margin:0 0 16px;color:#aebdff;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;font-weight:700}.side h1{margin:0;font-size:clamp(39px,4.2vw,60px);letter-spacing:-3px;line-height:1.05}.side h1 em{font-family:"Playfair Display";font-weight:500}.side-content>p:not(.eyebrow){margin:21px 0 29px;color:#c4cff2;font-size:16px;line-height:1.65}.auth{display:grid;place-items:center;padding:42px}.auth-inner{width:min(100%,560px)}.back{display:inline-block;margin-bottom:42px;color:#6b7280;font-size:12px;font-weight:700}.back:hover{color:var(--blue)}.auth h2{margin:0;font-size:31px;letter-spacing:-1.4px}.auth h2 em{font-family:"Playfair Display";font-weight:500}.lead{margin:9px 0 24px;color:var(--body);font-size:13px;line-height:1.6}.field{display:grid;gap:7px;margin-top:14px}.field label{font-size:12px;color:#414957;font-weight:700}.field input{width:100%;padding:14px;border:1px solid #dce2ee;border-radius:9px;outline:0;background:#fff;color:#202530;font:14px "DM Sans"}.field input:focus{border-color:var(--blue);box-shadow:0 0 0 3px #3b5bff14}.password{position:relative}.password input{padding-right:45px}.reveal{position:absolute;right:8px;bottom:10px;padding:4px 6px;border:0;color:#687083;background:transparent;font-size:11px;font-weight:700;cursor:pointer}.submit{width:100%;margin-top:21px;padding:15px;border:0;border-radius:10px;color:#fff;background:linear-gradient(135deg,#4d6aff,#2d4cf1);box-shadow:0 12px 21px #3b5bff30;font-size:14px;font-weight:700;cursor:pointer}.submit:hover{transform:translateY(-1px)}.submit:disabled{opacity:0.7;cursor:not-allowed;transform:none}.message{display:none;margin-top:15px;padding:11px;border-radius:9px;background:#eaf9f2;color:var(--green);font-size:13px;font-weight:700}.error{display:none;margin-top:6px;color:var(--error);font-size:11px}.input-error{border-color:var(--error)!important;box-shadow:0 0 0 3px #c33b4a12!important}@media(max-width:820px){.page{grid-template-columns:1fr}.side{min-height:330px;padding:29px}.side-content{margin:52px auto 0}.auth{padding:34px 22px 50px}}@media(max-width:460px){.side h1{font-size:38px}.auth{padding:28px 19px 44px}.back{margin-bottom:30px}}
    .modal-overlay{position:fixed;inset:0;background:rgba(17,19,25,.55);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:9999;padding:20px;opacity:0;transition:opacity .2s ease}
    .modal-overlay.show{display:flex;opacity:1}
    .modal-card{position:relative;width:min(100%,400px);background:#fff;border-radius:16px;padding:34px 28px 28px;text-align:center;box-shadow:0 24px 48px -12px rgba(17,19,25,.28),0 0 0 1px rgba(17,19,25,.04);transform:translateY(14px) scale(.96);opacity:0;transition:transform .25s cubic-bezier(.16,1,.3,1),opacity .2s ease}
    .modal-overlay.show .modal-card{transform:translateY(0) scale(1);opacity:1}
    .modal-close{position:absolute;top:12px;right:12px;width:30px;height:30px;border:0;border-radius:8px;background:transparent;color:#9aa1b0;font-size:18px;line-height:1;cursor:pointer;display:grid;place-items:center;transition:background .15s,color .15s}
    .modal-close:hover{background:var(--wash);color:var(--ink)}
    .modal-icon{width:56px;height:56px;border-radius:50%;display:grid;place-items:center;margin:0 auto 18px}
    .modal-icon.success{background:#e5f7ee;color:var(--green)}
    .modal-icon.error{background:#fbeaec;color:var(--error)}
    .modal-icon svg{width:26px;height:26px;stroke:currentColor}
    .modal-card h3{margin:0 0 8px;font-family:"Playfair Display";font-weight:600;font-size:22px;letter-spacing:-.5px;color:var(--ink)}
    .modal-card p{margin:0 0 26px;color:var(--body);font-size:14px;line-height:1.6}
    .modal-actions{display:flex;gap:10px}
    .modal-btn{flex:1;padding:12px;border-radius:10px;font-size:14px;font-family:"DM Sans";font-weight:700;cursor:pointer;border:1px solid var(--line);background:#fff;color:var(--ink);transition:all .15s}
    .modal-btn:hover{background:var(--wash)}
    .modal-btn.primary{border:0;color:#fff;background:linear-gradient(135deg,#4d6aff,#2d4cf1);box-shadow:0 12px 21px #3b5bff30}
    .modal-btn.primary:hover{transform:translateY(-1px)}
    .modal-actions.single .modal-btn{flex:none;min-width:140px;margin:0 auto}
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
  
  <div id="custom-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <div class="modal-card">
          <button type="button" class="modal-close" id="modal-close-x" aria-label="Close">&times;</button>
          <div id="modal-icon" class="modal-icon">
              <svg id="modal-icon-success" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M20 6 9 17l-5-5"/></svg>
              <svg id="modal-icon-error" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:none"><circle cx="12" cy="12" r="9"/><path d="M12 8v5"/><path d="M12 16h.01"/></svg>
          </div>
          <h3 id="modal-title"></h3>
          <p id="modal-msg"></p>
          <div class="modal-actions" id="modal-actions">
              <button type="button" class="modal-btn" id="modal-btn-secondary">Close</button>
              <button type="button" class="modal-btn primary" id="modal-btn-primary">Try Again</button>
          </div>
      </div>
  </div>

  <div id="otp-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="otp-modal-title">
      <div class="modal-card">
          <div class="modal-icon" style="background:#eaf0ff;color:var(--blue);">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </div>
          <h3 id="otp-modal-title">Verify It's You</h3>
          <p>Super Admin accounts need an extra check. Enter the 6-digit code we just emailed you.</p>
          <form id="otp-form" novalidate>
              <div class="field">
                  <input id="otp-input" name="otp" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="• • • • • •" style="text-align:center;font-size:22px;letter-spacing:10px;font-weight:700;" autocomplete="one-time-code" required />
              </div>
              <p class="error" id="otp-error"></p>
              <button class="submit" id="btn-otp-submit" type="submit" style="margin-top:16px;">Verify Code →</button>
              <button class="modal-btn" id="btn-otp-resend" type="button" style="width:100%;margin-top:10px;">Resend Code</button>
          </form>
      </div>
  </div>
  
  <?php if ($isValidToken): ?>
  <script>
    const BASE_URL = '<?php echo BASE_URL; ?>';

    const modalOverlay = document.getElementById('custom-modal');
    const modalIcon = document.getElementById('modal-icon');
    const modalActions = document.getElementById('modal-actions');
    const modalBtnPrimary = document.getElementById('modal-btn-primary');
    const modalBtnSecondary = document.getElementById('modal-btn-secondary');

    function showModal(title, msg, isSuccess) {
        document.getElementById('modal-title').textContent = title;
        document.getElementById('modal-msg').textContent = msg;

        modalIcon.classList.toggle('success', isSuccess);
        modalIcon.classList.toggle('error', !isSuccess);
        document.getElementById('modal-icon-success').style.display = isSuccess ? 'block' : 'none';
        document.getElementById('modal-icon-error').style.display = isSuccess ? 'none' : 'block';

        if (isSuccess) {
            modalActions.classList.add('single');
            modalBtnSecondary.style.display = 'none';
            modalBtnPrimary.textContent = 'Done';
            modalBtnPrimary.onclick = closeModal;
        } else {
            modalActions.classList.remove('single');
            modalBtnSecondary.style.display = '';
            modalBtnPrimary.textContent = 'Try Again';
            modalBtnPrimary.onclick = function () {
                closeModal();
                document.getElementById('new_password').focus();
            };
        }

        modalOverlay.style.display = 'flex';
        requestAnimationFrame(() => modalOverlay.classList.add('show'));
    }

    function closeModal() {
        modalOverlay.classList.remove('show');
        setTimeout(() => { modalOverlay.style.display = 'none'; }, 200);
    }

    modalBtnSecondary.addEventListener('click', closeModal);
    document.getElementById('modal-close-x').addEventListener('click', closeModal);
    modalOverlay.addEventListener('click', function (e) {
        if (e.target === modalOverlay) closeModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modalOverlay.classList.contains('show')) closeModal();
    });

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

            if (data.status && data.data && data.data.otp_required) {
                // Super Admin: password is held pending until the emailed OTP is verified
                btn.disabled = false;
                btn.textContent = 'Update Password →';
                openOtpModal(data.message);
            } else if (data.status) {
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

    // ===== Super Admin OTP step =====
    const otpModal = document.getElementById('otp-modal');
    const otpInput = document.getElementById('otp-input');
    const otpError = document.getElementById('otp-error');
    const otpForm = document.getElementById('otp-form');
    const otpSubmitBtn = document.getElementById('btn-otp-submit');
    const otpResendBtn = document.getElementById('btn-otp-resend');

    function openOtpModal(message) {
        otpError.style.display = 'none';
        otpInput.value = '';
        otpModal.style.display = 'flex';
        requestAnimationFrame(() => otpModal.classList.add('show'));
        setTimeout(() => otpInput.focus(), 250);
    }

    function closeOtpModal() {
        otpModal.classList.remove('show');
        setTimeout(() => { otpModal.style.display = 'none'; }, 200);
    }

    otpInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
    });

    otpForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const code = otpInput.value.trim();
        otpError.style.display = 'none';

        if (code.length !== 6) {
            otpError.textContent = 'Enter the 6-digit code from your email.';
            otpError.style.display = 'block';
            return;
        }

        otpSubmitBtn.disabled = true;
        otpSubmitBtn.textContent = 'Verifying...';

        try {
            const body = new FormData();
            body.append('token', document.querySelector('input[name="token"]').value);
            body.append('otp', code);
            body.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

            const res = await fetch(BASE_URL + '/api/verify_reset_otp.php', { method: 'POST', body });
            const data = await res.json();

            if (data.status) {
                closeOtpModal();
                showModal('Success!', data.message + " Redirecting to login...", true);
                document.getElementById('reset-form').reset();
                setTimeout(() => { window.location.href = BASE_URL + '/login'; }, 2000);
            } else {
                otpError.textContent = data.message;
                otpError.style.display = 'block';
            }
        } catch (error) {
            otpError.textContent = 'An error occurred. Please try again.';
            otpError.style.display = 'block';
        } finally {
            otpSubmitBtn.disabled = false;
            otpSubmitBtn.textContent = 'Verify Code →';
        }
    });

    otpResendBtn.addEventListener('click', async function () {
        otpResendBtn.disabled = true;
        otpResendBtn.textContent = 'Sending...';
        try {
            const formData = new FormData(document.getElementById('reset-form'));
            const res = await fetch(BASE_URL + '/api/reset_password.php', { method: 'POST', body: formData });
            const data = await res.json();
            otpError.style.display = 'none';
            if (!data.status) {
                otpError.textContent = data.message;
                otpError.style.display = 'block';
            }
        } catch (error) {
            otpError.textContent = 'Could not resend the code. Please try again.';
            otpError.style.display = 'block';
        } finally {
            otpResendBtn.disabled = false;
            otpResendBtn.textContent = 'Resend Code';
        }
    });
  </script>
  <?php endif; ?>
</body>
</html>
