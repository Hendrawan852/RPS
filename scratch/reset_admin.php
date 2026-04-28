<?php
require_once 'c:/laragon/www/RPS/config/database.php';

$password = 'AdminPassword123!';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$email = 'admin@rps.com';

try {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ? AND role = 'Admin'");
    $stmt->execute([$hashed_password, $email]);
    
    if ($stmt->rowCount() > 0) {
        echo "Successfully updated admin password to hashed version of '$password'.\n";
    } else {
        // Maybe the email is different or user not found?
        // Let's try updating by role if email fails
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE role = 'Admin'");
        $stmt->execute([$hashed_password]);
        if ($stmt->rowCount() > 0) {
            echo "Successfully updated admin password (by role) to hashed version of '$password'.\n";
        } else {
            echo "No admin user found to update.\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
