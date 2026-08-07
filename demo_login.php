<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Authentication Page (Email-based)
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
use App\Models\Auth;
use App\Models\Helpers;
use App\Models\Database;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['is_demo'] = true;

$db = new Database();
$auth = new Auth($db);

// If already logged in, redirect to Dashboard
if ($auth->check()) {
    header("Location: " . BASE_URL . "/index");
    exit;
}

$errorMessage = "";
$loginSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Helpers::verifyCsrf()) {
        $errorMessage = "Security Validation Failed. Please refresh and try again.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        // Rate limiting check
        $attempts = $_SESSION['login_attempts'] ?? 0;
        $lockoutTime = $_SESSION['login_lockout'] ?? 0;

        if ($lockoutTime > time()) {
            $wait = ceil(($lockoutTime - time()) / 60);
            $errorMessage = "Too many failed attempts. Please try again in {$wait} minutes.";
        } else {
            if (empty($email) || empty($password)) {
                $errorMessage = "Please enter both email and password.";
            } else {
                if ($auth->login($email, $password)) {
                    // Reset attempts on success
                    unset($_SESSION['login_attempts']);
                    unset($_SESSION['login_lockout']);
                    $loginSuccess = true;
                } else {
                    // Increment failed attempts
                    $_SESSION['login_attempts'] = $attempts + 1;
                    if ($_SESSION['login_attempts'] >= 5) {
                        $_SESSION['login_lockout'] = time() + (5 * 60); // 5 minutes lockout
                        $errorMessage = "Account locked due to 5 failed attempts. Try again in 5 minutes.";
                    } else {
                        $errorMessage = "Invalid email or password, or account is disabled. Attempt {$_SESSION['login_attempts']} of 5.";
                    }
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Start your free trial — Grovixo</title>
  <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/img/Asset%2015%4072x.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Playfair+Display:ital,wght@0,500;0,600;1,500;1,600&display=swap" rel="stylesheet" />
  <?php if ($loginSuccess): ?>
  <meta http-equiv="refresh" content="1.5;url=<?php echo BASE_URL; ?>/index">
  <?php endif; ?>
  <style>
    :root{--blue:#3b5bff;--navy:#12214f;--ink:#111319;--body:#626977;--line:#e2e7f2;--wash:#f6f9ff;--green:#13845b;--error:#c33b4a}*{box-sizing:border-box}body{margin:0;min-height:100vh;font-family:"DM Sans",sans-serif;color:var(--ink);background:radial-gradient(circle at 12% 10%,#e2e9ff 0,transparent 28%),linear-gradient(145deg,#fff,#f7faff)}a{text-decoration:none}.page{min-height:100vh;display:grid;grid-template-columns:1fr 1fr}.side{position:relative;overflow:hidden;padding:42px clamp(28px,5vw,80px);color:#fff;background:radial-gradient(circle at 90% 10%,#637bff 0,transparent 28%),var(--navy)}.side:after{content:"";position:absolute;width:460px;height:460px;bottom:-270px;left:-160px;border-radius:50%;border:45px solid #3552c8}.brand{position:relative;z-index:1;display:inline-flex;align-items:center}.brand img{display:block;width:139px;filter:brightness(0) invert(1)}.side-content{position:relative;z-index:1;max-width:475px;margin:clamp(80px,16vh,150px) auto 0}.eyebrow{margin:0 0 16px;color:#aebdff;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;font-weight:700}.side h1{margin:0;font-size:clamp(39px,4.2vw,60px);letter-spacing:-3px;line-height:1.05}.side h1 em{font-family:"Playfair Display";font-weight:500}.side-content>p:not(.eyebrow){margin:21px 0 29px;color:#c4cff2;font-size:16px;line-height:1.65}.trial-list{display:grid;gap:13px;padding:0;margin:0;list-style:none}.trial-list li{display:flex;gap:10px;align-items:center;color:#e3e9ff;font-size:13px}.tick{display:grid;place-items:center;width:21px;height:21px;border-radius:50%;color:#fff;background:#ffffff18;font-size:11px}.quote{margin-top:58px;padding:20px;border:1px solid #ffffff22;border-radius:16px;background:#ffffff0d}.quote p{margin:0;color:#e8edff;font:500 17px/1.5 "Playfair Display"}.quote span{display:block;margin-top:12px;color:#aebadb;font-size:11px}.auth{display:grid;place-items:center;padding:42px}.auth-inner{width:min(100%,560px)}.back{display:inline-block;margin-bottom:42px;color:#6b7280;font-size:12px;font-weight:700}.back:hover{color:var(--blue)}.tabs{display:flex;gap:4px;padding:4px;margin-bottom:30px;border-radius:11px;background:#f0f3fa}.tab{flex:1;padding:10px;border:0;border-radius:8px;color:#747b89;background:transparent;font-size:13px;font-weight:700;cursor:pointer}.tab.active{color:#26345c;background:#fff;box-shadow:0 3px 10px #13215410}.form-pane{display:none}.form-pane.active{display:block;animation:show .25s ease}@keyframes show{from{opacity:0;transform:translateY(7px)}to{opacity:1;transform:none}}.auth h2{margin:0;font-size:31px;letter-spacing:-1.4px}.auth h2 em{font-family:"Playfair Display";font-weight:500}.lead{margin:9px 0 24px;color:var(--body);font-size:13px;line-height:1.6}.notice{display:flex;gap:10px;align-items:flex-start;padding:12px;margin:0 0 20px;border:1px solid #dbe4ff;border-radius:10px;background:#f3f6ff;color:#40527e;font-size:11px;line-height:1.5}.notice strong{color:var(--blue)}.field{display:grid;gap:7px;margin-top:14px}.field label{font-size:12px;color:#414957;font-weight:700}.field input{width:100%;padding:14px;border:1px solid #dce2ee;border-radius:9px;outline:0;background:#fff;color:#202530;font:14px "DM Sans"}.field input:focus{border-color:var(--blue);box-shadow:0 0 0 3px #3b5bff14}.two{display:grid;grid-template-columns:1fr 1fr;gap:11px}.password{position:relative}.password input{padding-right:45px}.reveal{position:absolute;right:8px;bottom:10px;padding:4px 6px;border:0;color:#687083;background:transparent;font-size:11px;font-weight:700;cursor:pointer}.row{display:flex;justify-content:space-between;align-items:center;margin-top:17px;color:#6c7380;font-size:12px}.row label{display:flex;align-items:center;gap:7px;cursor:pointer}.row input{accent-color:var(--blue)}.row a{color:var(--blue);font-weight:700}.submit{width:100%;margin-top:21px;padding:15px;border:0;border-radius:10px;color:#fff;background:linear-gradient(135deg,#4d6aff,#2d4cf1);box-shadow:0 12px 21px #3b5bff30;font-size:14px;font-weight:700;cursor:pointer}.submit:hover{transform:translateY(-1px)}.submit:disabled{opacity:0.7;cursor:not-allowed;transform:none}.terms{margin:14px 0 0;color:#8a91a0;font-size:11px;line-height:1.55;text-align:center}.terms a{color:#5b6fe1;text-decoration:underline}.message{display:none;margin-top:15px;padding:11px;border-radius:9px;background:#eaf9f2;color:var(--green);font-size:13px;font-weight:700}.error{display:none;margin-top:6px;color:var(--error);font-size:11px}.input-error{border-color:var(--error)!important;box-shadow:0 0 0 3px #c33b4a12!important}@media(max-width:820px){.page{grid-template-columns:1fr}.side{min-height:330px;padding:29px}.side-content{margin:52px auto 0}.quote{display:none}.auth{padding:34px 22px 50px}}@media(max-width:460px){.side h1{font-size:38px}.two{grid-template-columns:1fr}.auth{padding:28px 19px 44px}.back{margin-bottom:30px}}
  </style>
</head>
<body>
  <main class="page">
      <aside class="side">
          <a class="brand" href="index" aria-label="Grovixo home">
              <img src="<?php echo BASE_URL; ?>/assets/img/grovixo_logo%201.png" alt="Grovixo" onerror="this.src='<?php echo BASE_URL; ?>/assets/images/logo.png'" />
          </a>
          <div class="side-content">
              <p class="eyebrow">Built for growing businesses</p>
              <h1>Start today. Feel the difference for <em>15 days.</em></h1>
              <p>Bring billing, inventory, invoices and reports into one calm workspace — no credit card required.</p>
              <ul class="trial-list">
                  <li><span class="tick">✓</span>Full access to Grovixo for 15 days</li>
                  <li><span class="tick">✓</span>GST invoices, POS billing and inventory tools</li>
                  <li><span class="tick">✓</span>Set up your shop in just a few minutes</li>
              </ul>
              <div class="quote">
                  <p>“Our counter became easier to run from the first day.”</p>
                  <span>Riya Mehta · The Paper Room</span>
              </div>
          </div>
      </aside>
      <section class="auth">
          <div class="auth-inner">
              <a class="back" href="<?php echo BASE_URL; ?>/index">← Back to Grovixo</a>
              <div class="tabs" role="tablist" aria-label="Account access">
                  <button class="tab <?php echo (empty($errorMessage) && !$loginSuccess) ? 'active' : ''; ?>" data-tab="register" role="tab" aria-selected="true">Start free trial</button>
                  <button class="tab <?php echo (!empty($errorMessage) || $loginSuccess) ? 'active' : ''; ?>" data-tab="login" role="tab" aria-selected="false">Log in</button>
              </div>
              
              <div class="form-pane <?php echo (empty($errorMessage) && !$loginSuccess) ? 'active' : ''; ?>" id="register">
                  <h2>Your 15-day trial starts <em>here.</em></h2>
                  <p class="lead">Create your account and start setting up Grovixo today.</p>
                  <p class="notice"><span>✦</span><span><strong>15 days, completely free.</strong><br>No credit card. No commitment. Just your business, organised.</span></p>
                  
                  <form id="register-form" novalidate>
                      <div class="two">
                          <div class="field">
                              <label for="first-name">First name</label>
                              <input id="first-name" name="firstName" autocomplete="given-name" required placeholder="First name" />
                          </div>
                          <div class="field">
                              <label for="last-name">Last name</label>
                              <input id="last-name" name="lastName" autocomplete="family-name" required placeholder="Last name" />
                          </div>
                      </div>
                      <div class="field">
                          <label for="register-email">Work email</label>
                          <input id="register-email" name="email" type="email" autocomplete="email" required placeholder="Your Mail id" />
                      </div>
                      <div class="field">
                          <label for="register-mobile">Mobile Number</label>
                          <input id="register-mobile" name="mobile" type="tel" autocomplete="tel" required placeholder="Your Mobile Number" />
                      </div>
                      <p class="error" id="register-error" style="display:none;"></p>
                      <button class="submit" type="submit" id="register-submit-btn">Create my free account →</button>
                      <p class="terms">By creating an account, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</p>
                      <div class="message" role="status" id="register-success"></div>
                  </form>
              </div>

              <div class="form-pane <?php echo (!empty($errorMessage) || $loginSuccess) ? 'active' : ''; ?>" id="login">
                  <h2>Welcome to <em>Demo.</em></h2>
                  <p class="lead">Log in to your free trial workspace.</p>
                  
                  <form id="login-form" method="POST" action="<?php echo BASE_URL; ?>/demo/login" novalidate>
                      <?php echo Helpers::csrfField(); ?>
                      <div class="field">
                          <label for="login-email">Email or Mobile</label>
                          <input id="login-email" name="email" type="text" autocomplete="username" required placeholder="Your Email or Mobile" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
                      </div>
                      <div class="field password">
                          <label for="login-password">Password</label>
                          <input id="login-password" name="password" type="password" autocomplete="current-password" required placeholder="Your password" />
                          <button class="reveal" type="button" data-reveal="login-password">Show</button>
                      </div>
                      <div class="row">
                          <label><input type="checkbox" /> Remember me</label>
                          <a href="<?php echo BASE_URL; ?>/forgot_password">Forgot password?</a>
                      </div>
                      
                      <?php if (!empty($errorMessage)): ?>
                          <p class="error" style="display:block;"><?php echo htmlspecialchars($errorMessage); ?></p>
                      <?php endif; ?>
                      
                      <p class="error" id="login-error">Enter a valid email/mobile and your password to continue.</p>
                      
                      <button class="submit" type="submit">Log in to Grovixo →</button>
                      <p class="terms">New to Grovixo? <a href="#" data-show-register>Start your free 15-day trial.</a></p>
                      
                      <?php if ($loginSuccess): ?>
                          <div class="message" role="status" style="display:block;">You’re signed in. Taking you to your workspace…</div>
                      <?php endif; ?>
                  </form>
              </div>
          </div>
      </section>
  </main>
  
  <script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
    const tabs=document.querySelectorAll('.tab'),panes=document.querySelectorAll('.form-pane');
    
    function showTab(name){
        tabs.forEach(t=>{const on=t.dataset.tab===name;t.classList.toggle('active',on);t.setAttribute('aria-selected',on)});
        panes.forEach(p=>p.classList.toggle('active',p.id===name));
        history.replaceState(null,'',name==='login'?'#login':'#register');
    }
    
    tabs.forEach(t=>t.addEventListener('click',()=>showTab(t.dataset.tab)));
    document.querySelector('[data-show-register]').addEventListener('click',e=>{e.preventDefault();showTab('register')});
    if(location.hash==='#login')showTab('login');
    
    document.querySelectorAll('[data-reveal]').forEach(b=>b.addEventListener('click',()=>{
        const input=document.getElementById(b.dataset.reveal),show=input.type==='password';
        input.type=show?'text':'password';
        b.textContent=show?'Hide':'Show';
    }));
    
    // Login Validation (traditional submit)
    const loginForm = document.getElementById('login-form');
    loginForm.addEventListener('submit',e=>{
        const valid = loginForm.checkValidity();
        const err = document.getElementById('login-error');
        err.style.display=valid?'none':'block';
        loginForm.querySelectorAll('input[required]').forEach(i=>i.classList.toggle('input-error',!i.checkValidity()));
        
        if(!valid) {
            e.preventDefault();
        } else {
            const btn = loginForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display:inline-block;width:1rem;height:1rem;border:0.15em solid currentColor;border-right-color:transparent;border-radius:50%;animation:spinner-border .75s linear infinite;"></span> Logging in...';
        }
    });

    // Registration Fetch Logic
    const registerForm = document.getElementById('register-form');
    registerForm.addEventListener('submit', async e => {
        e.preventDefault();
        const err = document.getElementById('register-error');
        const success = document.getElementById('register-success');
        const btn = document.getElementById('register-submit-btn');
        
        err.style.display = 'none';
        
        const valid = registerForm.checkValidity();
        registerForm.querySelectorAll('input[required]').forEach(i=>i.classList.toggle('input-error',!i.checkValidity()));
        
        if (!valid) {
            err.textContent = "Please fill in all required fields correctly.";
            err.style.display = 'block';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display:inline-block;width:1rem;height:1rem;border:0.15em solid currentColor;border-right-color:transparent;border-radius:50%;animation:spinner-border .75s linear infinite;"></span> Creating account...';

        const formData = new FormData(registerForm);
        try {
            const res = await fetch(BASE_URL + '/api/register.php?demo=1', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            
            if (data.status) {
                // Remove the inline message
                success.style.display = 'none';
                
                // Show modal
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Account Created!',
                        text: data.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3b5bff'
                    });
                } else {
                    alert(data.message);
                }
                
                btn.innerHTML = "Account created ✓";
            } else {
                err.textContent = data.message || "An error occurred.";
                err.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = "Create my free account →";
            }
        } catch (error) {
            err.textContent = "A network error occurred. Please try again.";
            err.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = "Create my free account →";
        }
    });

  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    @keyframes spinner-border {
      to { transform: rotate(360deg); }
    }
  </style>
</body>
</html>
