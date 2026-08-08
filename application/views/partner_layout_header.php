<?php
/**
 * Invoice & Inventory Management System (IIMS)
 * Shared header/nav for logged-in Distributor/Partner portal pages.
 * Include after $distributor (array from DistributorAuth->user()) and
 * $activeNav ('dashboard'|'leads') are set.
 */
use App\Models\Helpers;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="<?php echo Helpers::getCsrfToken(); ?>">
  <title><?php echo $pageTitle ?? 'Partner Portal'; ?> — Grovixo</title>
  <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>/assets/img/favicon.png?v=<?php echo Helpers::assetVersion('/assets/img/favicon.png'); ?>" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet" />
  <style>
    body { background:#f4f6fb; font-family:"DM Sans",sans-serif; color:#111319; }
    .partner-nav { background:#12214f; padding:0 1.5rem; }
    .partner-nav .brand { display:flex; align-items:center; gap:.5rem; padding:14px 0; color:#fff; font-weight:800; font-size:1.15rem; }
    .partner-nav .brand img { height:28px; }
    .partner-nav .nav-link { color:#c4cff2 !important; font-weight:600; font-size:.92rem; padding:1rem .9rem !important; }
    .partner-nav .nav-link.active { color:#fff !important; border-bottom:2px solid #3b5bff; }
    .partner-nav .nav-link:hover { color:#fff !important; }
    .partner-user { color:#e3e9ff; font-size:.85rem; }
    .partner-container { max-width:1180px; margin:2rem auto; padding:0 1.25rem; }
    .stat-card { background:#fff; border-radius:14px; padding:1.25rem 1.4rem; box-shadow:0 4px 18px rgba(18,33,79,.06); border:1px solid #eef0f6; }
    .stat-card .label { font-size:.72rem; text-transform:uppercase; letter-spacing:.6px; color:#8a91a0; font-weight:700; }
    .stat-card .value { font-size:1.6rem; font-weight:800; margin-top:.35rem; color:#12214f; }
    .panel { background:#fff; border-radius:16px; box-shadow:0 4px 18px rgba(18,33,79,.06); border:1px solid #eef0f6; }
    .panel-hd { padding:1.1rem 1.4rem; border-bottom:1px solid #eef0f6; display:flex; justify-content:space-between; align-items:center; gap:.75rem; flex-wrap:wrap; }
    .panel-hd h5 { margin:0; font-weight:800; color:#12214f; font-size:1.02rem; }
    .table thead th { font-size:.72rem; text-transform:uppercase; letter-spacing:.5px; color:#8a91a0; border-bottom-width:1px; }
    .badge-status { font-weight:700; font-size:.72rem; padding:.4em .7em; }
  </style>
</head>
<body>
  <nav class="partner-nav d-flex align-items-center justify-content-between flex-wrap">
      <div class="d-flex align-items-center gap-4 flex-wrap">
          <a class="brand" href="<?php echo BASE_URL; ?>/partner/dashboard">
              <img src="<?php echo BASE_URL; ?>/assets/img/grovixo_logo%201.png" alt="Grovixo" style="filter:brightness(0) invert(1);" />
              <span>Partners</span>
          </a>
          <div class="d-flex">
              <a class="nav-link <?php echo ($activeNav ?? '') === 'dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/partner/dashboard"><i class="fa-solid fa-gauge-high me-1"></i> Dashboard</a>
              <a class="nav-link <?php echo ($activeNav ?? '') === 'leads' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/partner/leads"><i class="fa-solid fa-bullseye me-1"></i> Leads</a>
          </div>
      </div>
      <div class="d-flex align-items-center gap-3">
          <span class="partner-user"><i class="fa-solid fa-user-tie me-1"></i><?php echo Helpers::sanitize($distributor['name'] ?? ''); ?></span>
          <a href="<?php echo BASE_URL; ?>/partner/logout" class="btn btn-sm btn-outline-light">Sign Out</a>
      </div>
  </nav>
  <div class="partner-container">
