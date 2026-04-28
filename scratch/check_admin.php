<?php
require_once 'c:/laragon/www/RPS/config/database.php';

try {
    $stmt = $pdo->query("SELECT nip_nidn, email, password, role FROM users WHERE role = 'Admin'");
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "Admin found:\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "NIP: " . $admin['nip_nidn'] . "\n";
        echo "Hash: " . $admin['password'] . "\n";
        
        if (password_verify('AdminPassword123!', $admin['password'])) {
            echo "Password 'AdminPassword123!' is VALID for this hash.\n";
        } elseif (password_verify('password', $admin['password'])) {
            echo "Password 'password' is VALID for this hash.\n";
        } else {
            echo "Password does not match common defaults.\n";
        }
    } else {
        echo "No admin user found in database.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
