<?php
// index.php
session_start();
if (isset($_SESSION['user'])) {
    switch ($_SESSION['user']['role']) {
        case 'admin':
            header("Location: /admin/dashboard.php");
            break;
        case 'motorista':
            header("Location: /motorista/dashboard.php");
            break;
        case 'usuario':
            header("Location: /usuario/dashboard.php");
            break;
        default:
            header("Location: /login.php");
            break;
    }
    exit;
}
header("Location: /login.php");
exit;
?>
