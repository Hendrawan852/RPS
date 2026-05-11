<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login.php");
    exit();
}
$active_page = basename($_SERVER['PHP_SELF'], ".php");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucwords(str_replace('_', ' ', $active_page)); ?> - Admin RPS [REFRESHED]</title>
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Common Layout CSS -->
    <link rel="stylesheet" href="../css/admin/layout.css?v=4.1">
    <!-- Page Specific CSS -->
    <?php if (file_exists("../css/admin/$active_page.css")): ?>
        <link rel="stylesheet" href="../css/admin/<?php echo $active_page; ?>.css?v=3.0">
    <?php endif; ?>
    <link rel="stylesheet" href="../css/admin/premium_cards.css?v=2.0">
</head>
<body>
    <div class="admin-wrapper">
