<?php

session_start();

require_once "koneksi.php";
require_once "fonnte.php";

if (
    !isset($_SESSION["login"]) ||
    $_SESSION["login"] !== true
) {
    header("Location: login.php");
    exit;
}

$id = (int) ($_GET["id"] ?? 0);

if ($id <= 0) {
    die("ID siswa tidak valid.");
}

$stmt = $conn->prepare("
    SELECT name, nisn, no_hp
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$siswa = $result->fetch_assoc();

if (!$siswa) {
    die("Data siswa tidak ditemukan.");
}

if (empty($siswa["no_hp"])) {
    die("Nomor WhatsApp siswa belum diisi.");
}

$nama = $siswa["name"];
$nisn = $siswa["nisn"];
$nomor = $siswa["no_hp"];

$message =
    "Halo " . $nama . " 👋\n\n" .
    "Data kamu sudah terdaftar di Sistem Pendataan Siswa ASTS.\n\n" .
    "Nama: " . $nama . "\n" .
    "NISN: " . $nisn . "\n\n" .
    "Terima kasih.";

$response = kirimWhatsApp($nomor, $message);

if (isset($response["status"]) && $response["status"] === true) {

    echo "
    <script>
        alert('Pesan WhatsApp berhasil dikirim.');
        window.location.href = 'index.php';
    </script>
    ";

} else {

    echo "
    <script>
        alert('Pesan WhatsApp gagal dikirim.');
        window.location.href = 'index.php';
    </script>
    ";
}