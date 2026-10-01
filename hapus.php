<?php

session_start();

require_once "koneksi.php";
require_once "fonnte.php";


/* =====================================================
   CEK LOGIN
===================================================== */

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: login.php");
    exit;
}


/* =====================================================
   CEK ID
===================================================== */

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET["id"];


/* =====================================================
   AMBIL DATA SISWA SEBELUM DIHAPUS
===================================================== */

$stmt = $conn->prepare("
    SELECT
        name,
        nisn,
        no_hp
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();


/* =====================================================
   CEK DATA
===================================================== */

if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: index.php");
    exit;
}


$data = $result->fetch_assoc();

$stmt->close();


$name = $data["name"];
$nisn = $data["nisn"];
$no_hp = $data["no_hp"];


/* =====================================================
   HAPUS DATA
===================================================== */

$stmt = $conn->prepare("
    DELETE FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $id);


/* =====================================================
   JALANKAN DELETE
===================================================== */

if ($stmt->execute()) {

    /* =================================================
       DATA BERHASIL DIHAPUS
       SEKARANG KIRIM WHATSAPP
    ================================================= */

  $pesan = "⚠️ *DATA TELAH DIHAPUS* ⚠️\n\n";

$pesan .= "Halo, *" . $name . "* 👋\n";
$pesan .= "Kami ingin memberitahukan bahwa data kamu pada *Sistem Pendataan Siswa ASTS* telah dihapus dari sistem.\n\n";

$pesan .= "╭───────────────╮\n";
$pesan .= "   🗑️ *DATA DIHAPUS*\n";
$pesan .= "╰───────────────╯\n\n";

$pesan .= "👤 *Nama:* " . $name . "\n";
$pesan .= "🆔 *NISN:* " . $nisn . "\n\n";

$pesan .= "❌ *Status: Data telah dihapus*\n\n";

$pesan .= "📌 Data tersebut sudah tidak tersedia lagi di dalam sistem.\n";
$pesan .= "Jika penghapusan ini dilakukan karena kesalahan atau kamu masih perlu melakukan pendataan, silakan hubungi admin.\n\n";

$pesan .= "━━━━━━━━━━━━━━━━━━\n";
$pesan .= "🏫 *SISTEM PENDATAAN SISWA ASTS*\n";
$pesan .= "📱 Notifikasi Otomatis\n";
$pesan .= "━━━━━━━━━━━━━━━━━━\n\n";

$pesan .= "Terima kasih. 🙏\n";
$pesan .= "Semoga proses ASTS kamu berjalan lancar! ✨";


    /* =================================================
       KIRIM WHATSAPP
    ================================================= */

    if (!empty($no_hp)) {

        $hasilWA = kirimWhatsApp(
            $no_hp,
            $pesan
        );


        /* =============================================
           CEK HASIL WHATSAPP
        ============================================= */

        if (
            isset($hasilWA["status"]) &&
            $hasilWA["status"] === true
        ) {

            $_SESSION["success"] =
                "Data siswa berhasil dihapus dan WhatsApp berhasil dikirim.";

        } else {

            $_SESSION["success"] =
                "Data siswa berhasil dihapus, tetapi WhatsApp gagal dikirim.";

        }

    } else {

        $_SESSION["success"] =
            "Data siswa berhasil dihapus, tetapi nomor WhatsApp tidak tersedia.";

    }


} else {

    $_SESSION["error"] =
        "Data gagal dihapus: " . $stmt->error;
}


$stmt->close();


/* =====================================================
   KEMBALI KE HALAMAN UTAMA
===================================================== */

header("Location: index.php");
exit;

?>