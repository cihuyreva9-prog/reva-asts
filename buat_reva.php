<?php

require_once "koneksi.php";

$nama = "Reva Alvay";
$username = "reva";
$email = "reva@gmail.com";
$password = password_hash("reva123", PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO users_login
    (nama, username, email, password)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        nama = VALUES(nama),
        email = VALUES(email),
        password = VALUES(password)
");

$stmt->bind_param(
    "ssss",
    $nama,
    $username,
    $email,
    $password
);

if ($stmt->execute()) {
    echo "Akun berhasil dibuat/diperbarui.<br><br>";
    echo "Username: <b>reva</b><br>";
    echo "Password: <b>reva123</b>";
} else {
    echo "Gagal: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>