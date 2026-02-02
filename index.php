<?php
/**
 * Trang chuyển hướng chính
 * Redirect đến login hoặc dashboard
 */
require_once 'config/config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
} else {
    redirect('auth/login.php');
}
?>
