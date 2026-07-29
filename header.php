<?php
require_once 'config.php';

$sessionUser = $_SESSION['user'] ?? null;
if (isset($_SESSION['user']) && !is_array($sessionUser)) {
    unset($_SESSION['user']);
    $sessionUser = null;
}

$isLoggedIn = is_array($sessionUser);
$displayName = $isLoggedIn
    ? trim(($sessionUser['first_name'] ?? '') . ' ' . ($sessionUser['last_name'] ?? ''))
    : '';
$displayName = $displayName !== '' ? $displayName : ($sessionUser['email'] ?? 'User');
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Ticket Booking</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/home.css">
    <?php if (in_array($currentPage, ['login.php', 'registration.php'], true)): ?>
        <link rel="stylesheet" href="css/auth.css">
    <?php endif; ?>
</head>
<body>
    <header class="site-header sticky-top">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand site-brand" href="index.php">
                    <i class="bi bi-film"></i>
                    <span>Cinema Star</span>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Mở menu">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav mx-lg-auto mb-3 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="index.php">Trang chủ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#movies-list">Phim</a>
                        </li>
                    </ul>

                    <form class="site-search d-flex me-lg-3 mb-3 mb-lg-0" action="index.php#movies-list" method="GET">
                        <input class="form-control" type="search" name="keyword" placeholder="Tìm phim..." aria-label="Tìm phim">
                        <button class="btn" type="submit" aria-label="Tìm kiếm">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>

                    <div class="site-auth <?= $isLoggedIn ? 'is-logged-in' : 'is-logged-out' ?> d-flex align-items-lg-center gap-2 flex-column flex-lg-row">
                        <?php if ($isLoggedIn): ?>
                            <div class="dropdown site-user-dropdown">
                                <button class="btn site-user-toggle dropdown-toggle" type="button" id="siteUserMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle"></i>
                                    <span><?= htmlspecialchars($displayName) ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end site-user-menu" aria-labelledby="siteUserMenu">
                                    <li><a class="dropdown-item" href="profile.php">Hồ sơ cá nhân</a></li>
                                    <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                                </ul>
                            </div>
                            <a class="btn btn-outline-light site-profile-btn" href="profile.php">
                                <i class="bi bi-person-circle"></i>
                                <span><?= htmlspecialchars($displayName) ?></span>
                            </a>
                            <a class="btn btn-site-primary" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Đăng xuất</span>
                            </a>
                        <?php else: ?>
                            <a class="btn btn-outline-light" href="login.php">Đăng nhập</a>
                            <a class="btn btn-site-primary" href="login.php?mode=register">Đăng ký</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main>
