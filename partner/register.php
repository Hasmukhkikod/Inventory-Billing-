<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Partner / Distributor Registration ("Partner With Us")
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
use App\Models\Helpers;
use App\Models\Database;
use App\Models\DistributorAuth;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();
$distAuth = new DistributorAuth($db);
if ($distAuth->check()) {
    header("Location: " . BASE_URL . "/partner/dashboard");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Partner With Us — Grovixo</title>
  <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/img/favicon.png?v=<?php echo Helpers::assetVersion('/assets/img/favicon.png'); ?>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Playfair+Display:ital,wght@0,500;0,600;1,500;1,600&display=swap" rel="stylesheet" />
  <style>
    :root{--blue:#3b5bff;--navy:#12214f;--ink:#111319;--body:#626977;--line:#e2e7f2;--wash:#f6f9ff;--green:#13845b;--error:#c33b4a}*{box-sizing:border-box}body{margin:0;min-height:100vh;font-family:"DM Sans",sans-serif;color:var(--ink);background:radial-gradient(circle at 12% 10%,#e2e9ff 0,transparent 28%),linear-gradient(145deg,#fff,#f7faff)}a{text-decoration:none}.page{min-height:100vh;display:grid;grid-template-columns:1fr 1fr}.side{position:relative;overflow:hidden;padding:42px clamp(28px,5vw,80px);color:#fff;background:radial-gradient(circle at 90% 10%,#637bff 0,transparent 28%),var(--navy)}.side:after{content:"";position:absolute;width:460px;height:460px;bottom:-270px;left:-160px;border-radius:50%;border:45px solid #3552c8}.brand{position:relative;z-index:1;display:inline-flex;align-items:center}.brand img{display:block;width:139px;filter:brightness(0) invert(1)}.side-content{position:relative;z-index:1;max-width:475px;margin:clamp(60px,14vh,130px) auto 0}.eyebrow{margin:0 0 16px;color:#aebdff;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;font-weight:700}.side h1{margin:0;font-size:clamp(36px,4vw,54px);letter-spacing:-3px;line-height:1.08}.side h1 em{font-family:"Playfair Display";font-weight:500}.side-content>p:not(.eyebrow){margin:21px 0 29px;color:#c4cff2;font-size:16px;line-height:1.65}.perk-list{display:grid;gap:13px;padding:0;margin:0;list-style:none}.perk-list li{display:flex;gap:10px;align-items:center;color:#e3e9ff;font-size:13px}.tick{display:grid;place-items:center;width:21px;height:21px;border-radius:50%;color:#fff;background:#ffffff18;font-size:11px;flex-shrink:0}.auth{display:grid;place-items:center;padding:42px}.auth-inner{width:min(100%,560px)}.back{display:inline-block;margin-bottom:30px;color:#6b7280;font-size:12px;font-weight:700}.back:hover{color:var(--blue)}.auth h2{margin:0;font-size:29px;letter-spacing:-1.4px}.auth h2 em{font-family:"Playfair Display";font-weight:500}.lead{margin:9px 0 22px;color:var(--body);font-size:13px;line-height:1.6}.field{display:grid;gap:7px;margin-top:14px}.field label{font-size:12px;color:#414957;font-weight:700}.field input{width:100%;padding:14px;border:1px solid #dce2ee;border-radius:9px;outline:0;background:#fff;color:#202530;font:14px "DM Sans"}.field input:focus{border-color:var(--blue);box-shadow:0 0 0 3px #3b5bff14}.two{display:grid;grid-template-columns:1fr 1fr;gap:11px}.password{position:relative}.password input{padding-right:45px}.reveal{position:absolute;right:8px;bottom:10px;padding:4px 6px;border:0;color:#687083;background:transparent;font-size:11px;font-weight:700;cursor:pointer}.submit{width:100%;margin-top:21px;padding:15px;border:0;border-radius:10px;color:#fff;background:linear-gradient(135deg,#4d6aff,#2d4cf1);box-shadow:0 12px 21px #3b5bff30;font-size:14px;font-weight:700;cursor:pointer}.submit:hover{transform:translateY(-1px)}.submit:disabled{opacity:0.7;cursor:not-allowed;transform:none}.terms{margin:14px 0 0;color:#8a91a0;font-size:11px;line-height:1.55;text-align:center}.message{display:none;margin-top:15px;padding:11px;border-radius:9px;background:#eaf9f2;color:var(--green);font-size:13px;font-weight:700}.error{display:none;margin-top:6px;color:var(--error);font-size:11px}.input-error{border-color:var(--error)!important;box-shadow:0 0 0 3px #c33b4a12!important}@media(max-width:820px){.page{grid-template-columns:1fr}.side{min-height:290px;padding:29px}.side-content{margin:40px auto 0}.perk-list{display:none}.auth{padding:34px 22px 50px}}@media(max-width:460px){.side h1{font-size:34px}.two{grid-template-columns:1fr}.auth{padding:28px 19px 44px}.back{margin-bottom:24px}}
    .modal-overlay{position:fixed;inset:0;background:rgba(17,19,25,.55);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:9999;padding:20px;opacity:0;transition:opacity .2s ease}
    .modal-overlay.show{display:flex;opacity:1}
    .modal-card{position:relative;width:min(100%,400px);background:#fff;border-radius:16px;padding:34px 28px 28px;text-align:center;box-shadow:0 24px 48px -12px rgba(17,19,25,.28),0 0 0 1px rgba(17,19,25,.04);transform:translateY(14px) scale(.96);opacity:0;transition:transform .25s cubic-bezier(.16,1,.3,1),opacity .2s ease}
    .modal-overlay.show .modal-card{transform:translateY(0) scale(1);opacity:1}
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
              <p class="eyebrow">Grovixo Partner Program</p>
              <h1>Grow with us. <em>Earn on every client.</em></h1>
              <p>Refer shops and businesses to Grovixo, and earn ongoing commission on every client that stays subscribed.</p>
              <ul class="perk-list">
                  <li><span class="tick">✓</span>Track all your clients and renewal dates in one place</li>
                  <li><span class="tick">✓</span>See your commission for every active client</li>
                  <li><span class="tick">✓</span>Submit new leads directly to our team</li>
              </ul>
          </div>
      </aside>
      <section class="auth">
          <div class="auth-inner">
              <a class="back" href="<?php echo BASE_URL; ?>/index">← Back to Grovixo</a>
              <div id="register-pane">
                  <h2>Become a <em>Grovixo Partner.</em></h2>
                  <p class="lead">Register below - we'll email you a code to verify your account.</p>

                  <form id="register-form" novalidate>
                      <?php echo Helpers::csrfField(); ?>
                      <div class="field">
                          <label for="reg-name">Your name</label>
                          <input id="reg-name" name="name" required placeholder="Full name" />
                      </div>
                      <div class="field">
                          <label for="reg-business">Business / Agency name (optional)</label>
                          <input id="reg-business" name="business_name" placeholder="Your business name" />
                      </div>
                      <div class="two">
                          <div class="field">
                              <label for="reg-email">Email address</label>
                              <input id="reg-email" name="email" type="email" required placeholder="Your email" />
                          </div>
                          <div class="field">
                              <label for="reg-mobile">Mobile number</label>
                              <input id="reg-mobile" name="mobile" type="tel" required placeholder="Your mobile" />
                          </div>
                      </div>
                      <div class="field password">
                          <label for="reg-password">Password</label>
                          <input id="reg-password" name="password" type="password" required minlength="6" placeholder="At least 6 characters" />
                          <button class="reveal" type="button" data-reveal="reg-password">Show</button>
                      </div>
                      <div class="field password">
                          <label for="reg-confirm">Confirm password</label>
                          <input id="reg-confirm" name="confirm_password" type="password" required minlength="6" placeholder="Repeat your password" />
                          <button class="reveal" type="button" data-reveal="reg-confirm">Show</button>
                      </div>

                      <p class="error" id="register-error"></p>
                      <button class="submit" id="register-submit-btn" type="submit">Create Partner Account →</button>
                      <p class="terms">Already a partner? <a href="<?php echo BASE_URL; ?>/partner/login">Log in here</a>.</p>
                  </form>
              </div>
          </div>
      </section>
  </main>

  <div id="otp-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="otp-modal-title">
      <div class="modal-card">
          <div class="modal-icon" style="background:#eaf0ff;color:var(--blue);">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </div>
          <h3 id="otp-modal-title">Verify your email</h3>
          <p>Enter the 6-digit code we just emailed you to activate your partner account.</p>
          <form id="otp-form" novalidate>
              <div class="field">
                  <input id="otp-input" name="otp" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="• • • • • •" style="text-align:center;font-size:22px;letter-spacing:10px;font-weight:700;" autocomplete="one-time-code" required />
              </div>
              <p class="error" id="otp-error"></p>
              <button class="submit" id="btn-otp-submit" type="submit" style="margin-top:16px;">Verify & Continue →</button>
              <button class="modal-btn" id="btn-otp-resend" type="button" style="width:100%;margin-top:10px;">Resend Code</button>
          </form>
      </div>
  </div>

  <div id="custom-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <div class="modal-card">
          <div id="modal-icon" class="modal-icon">
              <svg id="modal-icon-success" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M20 6 9 17l-5-5"/></svg>
              <svg id="modal-icon-error" viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:none"><circle cx="12" cy="12" r="9"/><path d="M12 8v5"/><path d="M12 16h.01"/></svg>
          </div>
          <h3 id="modal-title"></h3>
          <p id="modal-msg"></p>
          <div class="modal-actions single">
              <button type="button" class="modal-btn primary" id="modal-btn-primary">Close</button>
          </div>
      </div>
  </div>

  <script>
    const BASE_URL = '<?php echo BASE_URL; ?>';

    document.querySelectorAll('[data-reveal]').forEach(b=>b.addEventListener('click',()=>{
        const input=document.getElementById(b.dataset.reveal),show=input.type==='password';
        input.type=show?'text':'password';
        b.textContent=show?'Hide':'Show';
    }));

    function showModal(title, msg, isSuccess, onClose) {
        document.getElementById('modal-title').textContent = title;
        document.getElementById('modal-msg').textContent = msg;
        const icon = document.getElementById('modal-icon');
        icon.classList.toggle('success', isSuccess);
        icon.classList.toggle('error', !isSuccess);
        document.getElementById('modal-icon-success').style.display = isSuccess ? 'block' : 'none';
        document.getElementById('modal-icon-error').style.display = isSuccess ? 'none' : 'block';
        const overlay = document.getElementById('custom-modal');
        document.getElementById('modal-btn-primary').onclick = function () {
            overlay.classList.remove('show');
            setTimeout(() => { overlay.style.display = 'none'; if (onClose) onClose(); }, 200);
        };
        overlay.style.display = 'flex';
        requestAnimationFrame(() => overlay.classList.add('show'));
    }

    // ===== Registration =====
    const registerForm = document.getElementById('register-form');
    registerForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const err = document.getElementById('register-error');
        const btn = document.getElementById('register-submit-btn');
        err.style.display = 'none';

        const valid = registerForm.checkValidity();
        registerForm.querySelectorAll('input[required]').forEach(i => i.classList.toggle('input-error', !i.checkValidity()));
        const pwd1 = document.getElementById('reg-password').value;
        const pwd2 = document.getElementById('reg-confirm').value;

        if (!valid) {
            err.textContent = 'Please fill in all required fields correctly.';
            err.style.display = 'block';
            return;
        }
        if (pwd1 !== pwd2) {
            err.textContent = 'Passwords do not match.';
            err.style.display = 'block';
            document.getElementById('reg-confirm').classList.add('input-error');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Creating account...';

        try {
            const formData = new FormData(registerForm);
            formData.append('action', 'register');
            const res = await fetch(BASE_URL + '/api/distributor_auth.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status) {
                openOtpModal();
            } else {
                err.textContent = data.message || 'An error occurred.';
                err.style.display = 'block';
            }
        } catch (error) {
            err.textContent = 'A network error occurred. Please try again.';
            err.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Create Partner Account →';
        }
    });

    // ===== OTP verification =====
    const otpModal = document.getElementById('otp-modal');
    const otpInput = document.getElementById('otp-input');
    const otpError = document.getElementById('otp-error');
    const otpForm = document.getElementById('otp-form');
    const otpSubmitBtn = document.getElementById('btn-otp-submit');
    const otpResendBtn = document.getElementById('btn-otp-resend');

    function openOtpModal() {
        otpError.style.display = 'none';
        otpInput.value = '';
        otpModal.style.display = 'flex';
        requestAnimationFrame(() => otpModal.classList.add('show'));
        setTimeout(() => otpInput.focus(), 250);
    }

    otpInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
    });

    otpForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        otpError.style.display = 'none';
        otpSubmitBtn.disabled = true;
        otpSubmitBtn.textContent = 'Verifying...';

        try {
            const csrfToken = document.querySelector('input[name="csrf_token"]').value;
            const body = new URLSearchParams({ action: 'verify_otp', otp: otpInput.value, csrf_token: csrfToken });
            const res = await fetch(BASE_URL + '/api/distributor_auth.php', { method: 'POST', body });
            const data = await res.json();

            if (data.status) {
                otpModal.classList.remove('show');
                setTimeout(() => { otpModal.style.display = 'none'; }, 200);
                showModal('Welcome to Grovixo Partners!', data.message, true, function () {
                    window.location.href = BASE_URL + '/partner/dashboard';
                });
            } else {
                otpError.textContent = data.message;
                otpError.style.display = 'block';
            }
        } catch (error) {
            otpError.textContent = 'A network error occurred. Please try again.';
            otpError.style.display = 'block';
        } finally {
            otpSubmitBtn.disabled = false;
            otpSubmitBtn.textContent = 'Verify & Continue →';
        }
    });

    otpResendBtn.addEventListener('click', async function () {
        otpResendBtn.disabled = true;
        otpResendBtn.textContent = 'Sending...';
        try {
            const csrfToken = document.querySelector('input[name="csrf_token"]').value;
            const body = new URLSearchParams({ action: 'resend_otp', csrf_token: csrfToken });
            const res = await fetch(BASE_URL + '/api/distributor_auth.php', { method: 'POST', body });
            const data = await res.json();
            otpError.style.display = 'block';
            otpError.style.color = data.status ? 'var(--green)' : 'var(--error)';
            otpError.textContent = data.message;
        } catch (error) {
            otpError.style.display = 'block';
            otpError.textContent = 'A network error occurred. Please try again.';
        } finally {
            otpResendBtn.disabled = false;
            otpResendBtn.textContent = 'Resend Code';
        }
    });
  </script>
</body>
</html>
