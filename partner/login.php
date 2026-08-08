<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Partner / Distributor Login
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
  <title>Partner Login — Grovixo</title>
  <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/img/favicon.png?v=<?php echo Helpers::assetVersion('/assets/img/favicon.png'); ?>" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Playfair+Display:ital,wght@0,500;0,600;1,500;1,600&display=swap" rel="stylesheet" />
  <style>
    :root{--blue:#3b5bff;--navy:#12214f;--ink:#111319;--body:#626977;--line:#e2e7f2;--wash:#f6f9ff;--green:#13845b;--error:#c33b4a}*{box-sizing:border-box}body{margin:0;min-height:100vh;font-family:"DM Sans",sans-serif;color:var(--ink);background:radial-gradient(circle at 12% 10%,#e2e9ff 0,transparent 28%),linear-gradient(145deg,#fff,#f7faff)}a{text-decoration:none}.page{min-height:100vh;display:grid;grid-template-columns:1fr 1fr}.side{position:relative;overflow:hidden;padding:42px clamp(28px,5vw,80px);color:#fff;background:radial-gradient(circle at 90% 10%,#637bff 0,transparent 28%),var(--navy)}.side:after{content:"";position:absolute;width:460px;height:460px;bottom:-270px;left:-160px;border-radius:50%;border:45px solid #3552c8}.brand{position:relative;z-index:1;display:inline-flex;align-items:center}.brand img{display:block;width:139px;filter:brightness(0) invert(1)}.side-content{position:relative;z-index:1;max-width:475px;margin:clamp(80px,16vh,150px) auto 0}.eyebrow{margin:0 0 16px;color:#aebdff;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;font-weight:700}.side h1{margin:0;font-size:clamp(39px,4.2vw,60px);letter-spacing:-3px;line-height:1.05}.side h1 em{font-family:"Playfair Display";font-weight:500}.side-content>p:not(.eyebrow){margin:21px 0 29px;color:#c4cff2;font-size:16px;line-height:1.65}.auth{display:grid;place-items:center;padding:42px}.auth-inner{width:min(100%,560px)}.back{display:inline-block;margin-bottom:42px;color:#6b7280;font-size:12px;font-weight:700}.back:hover{color:var(--blue)}.auth h2{margin:0;font-size:31px;letter-spacing:-1.4px}.auth h2 em{font-family:"Playfair Display";font-weight:500}.lead{margin:9px 0 24px;color:var(--body);font-size:13px;line-height:1.6}.field{display:grid;gap:7px;margin-top:14px}.field label{font-size:12px;color:#414957;font-weight:700}.field input{width:100%;padding:14px;border:1px solid #dce2ee;border-radius:9px;outline:0;background:#fff;color:#202530;font:14px "DM Sans"}.field input:focus{border-color:var(--blue);box-shadow:0 0 0 3px #3b5bff14}.password{position:relative}.password input{padding-right:45px}.reveal{position:absolute;right:8px;bottom:10px;padding:4px 6px;border:0;color:#687083;background:transparent;font-size:11px;font-weight:700;cursor:pointer}.row{display:flex;justify-content:flex-end;align-items:center;margin-top:17px;color:#6c7380;font-size:12px}.row a{color:var(--blue);font-weight:700}.submit{width:100%;margin-top:21px;padding:15px;border:0;border-radius:10px;color:#fff;background:linear-gradient(135deg,#4d6aff,#2d4cf1);box-shadow:0 12px 21px #3b5bff30;font-size:14px;font-weight:700;cursor:pointer}.submit:hover{transform:translateY(-1px)}.submit:disabled{opacity:0.7;cursor:not-allowed;transform:none}.terms{margin:14px 0 0;color:#8a91a0;font-size:11px;line-height:1.55;text-align:center}.message{display:none;margin-top:15px;padding:11px;border-radius:9px;background:#eaf9f2;color:var(--green);font-size:13px;font-weight:700}.error{display:none;margin-top:6px;color:var(--error);font-size:11px}.input-error{border-color:var(--error)!important;box-shadow:0 0 0 3px #c33b4a12!important}@media(max-width:820px){.page{grid-template-columns:1fr}.side{min-height:330px;padding:29px}.side-content{margin:52px auto 0}.auth{padding:34px 22px 50px}}@media(max-width:460px){.side h1{font-size:38px}.auth{padding:28px 19px 44px}.back{margin-bottom:30px}}
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
              <h1>Welcome back, <em>Partner.</em></h1>
              <p>Log in to view your clients, renewal dates, commission, and submit new leads.</p>
          </div>
      </aside>
      <section class="auth">
          <div class="auth-inner">
              <a class="back" href="<?php echo BASE_URL; ?>/index">← Back to Grovixo</a>
              <h2>Partner <em>Login.</em></h2>
              <p class="lead">Log in to your Grovixo Partner account.</p>

              <form id="login-form" novalidate>
                  <?php echo Helpers::csrfField(); ?>
                  <div class="field">
                      <label for="login-email">Email or mobile</label>
                      <input id="login-email" name="email" required placeholder="Your email or mobile" />
                  </div>
                  <div class="field password">
                      <label for="login-password">Password</label>
                      <input id="login-password" name="password" type="password" required placeholder="Your password" />
                      <button class="reveal" type="button" data-reveal="login-password">Show</button>
                  </div>
                  <div class="row">
                      <a href="<?php echo BASE_URL; ?>/forgot_password">Forgot password?</a>
                  </div>

                  <p class="error" id="login-error"></p>
                  <button class="submit" id="login-submit-btn" type="submit">Log in to Partner Portal →</button>
                  <p class="terms">New partner? <a href="<?php echo BASE_URL; ?>/partner/register">Create an account</a>.</p>
                  <div class="message" id="login-success" role="status"></div>
              </form>
          </div>
      </section>
  </main>

  <script>
    const BASE_URL = '<?php echo BASE_URL; ?>';

    document.querySelectorAll('[data-reveal]').forEach(b=>b.addEventListener('click',()=>{
        const input=document.getElementById(b.dataset.reveal),show=input.type==='password';
        input.type=show?'text':'password';
        b.textContent=show?'Hide':'Show';
    }));

    const loginForm = document.getElementById('login-form');
    loginForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const err = document.getElementById('login-error');
        const success = document.getElementById('login-success');
        const btn = document.getElementById('login-submit-btn');
        err.style.display = 'none';
        success.style.display = 'none';

        const valid = loginForm.checkValidity();
        loginForm.querySelectorAll('input[required]').forEach(i => i.classList.toggle('input-error', !i.checkValidity()));
        if (!valid) {
            err.textContent = 'Please enter your email/mobile and password.';
            err.style.display = 'block';
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Logging in...';

        try {
            const formData = new FormData(loginForm);
            formData.append('action', 'login');
            const res = await fetch(BASE_URL + '/api/distributor_auth.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status) {
                success.textContent = "You're signed in. Taking you to your dashboard…";
                success.style.display = 'block';
                setTimeout(() => { window.location.href = BASE_URL + '/partner/dashboard'; }, 900);
            } else {
                err.textContent = data.message || 'Invalid email or password.';
                err.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Log in to Partner Portal →';
            }
        } catch (error) {
            err.textContent = 'A network error occurred. Please try again.';
            err.style.display = 'block';
            btn.disabled = false;
            btn.textContent = 'Log in to Partner Portal →';
        }
    });
  </script>
</body>
</html>
