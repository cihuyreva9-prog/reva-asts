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
   VARIABEL
===================================================== */

$name = "";
$nisn = "";
$ttl = "";
$gender = "";
$email = "";
$address = "";
$no_hp = "";

$success = "";
$error = "";


/* =====================================================
   AMBIL DATA LAMA
===================================================== */

$stmt = $conn->prepare("
    SELECT 
        name,
        nisn,
        ttl,
        gender,
        email,
        address,
        no_hp
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();

    header("Location: index.php");
    exit;
}

$data = $result->fetch_assoc();

$stmt->close();


/* =====================================================
   ISI DATA KE FORM
===================================================== */

$name = $data["name"];
$nisn = $data["nisn"];
$ttl = $data["ttl"];
$gender = $data["gender"];
$email = $data["email"];
$address = $data["address"];
$no_hp = $data["no_hp"];


/* =====================================================
   PROSES UPDATE
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $nisn = trim($_POST["nisn"] ?? "");
    $ttl = trim($_POST["ttl"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $no_hp = trim($_POST["no_hp"] ?? "");


    /* =================================================
       VALIDASI DATA
    ================================================= */

    if (
        $name === "" ||
        $nisn === "" ||
        $ttl === "" ||
        $gender === "" ||
        $email === "" ||
        $address === "" ||
        $no_hp === ""
    ) {

        $error = "Semua data wajib diisi.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format email tidak valid.";

    } else {


        /* =============================================
           NORMALISASI NOMOR WHATSAPP
        ============================================= */

        $no_hp = preg_replace('/[^0-9]/', '', $no_hp);

        if (substr($no_hp, 0, 1) === "0") {

            $no_hp = "62" . substr($no_hp, 1);

        } elseif (substr($no_hp, 0, 1) === "8") {

            $no_hp = "62" . $no_hp;
        }


        /* =============================================
           VALIDASI NOMOR WHATSAPP
        ============================================= */

        if (!preg_match('/^62[0-9]{9,13}$/', $no_hp)) {

            $error = "Nomor WhatsApp tidak valid. Contoh: 628123456789";

        } else {


            /* =========================================
               CEK NISN MILIK SISWA LAIN
            ========================================= */

            $cek = $conn->prepare("
                SELECT id
                FROM users
                WHERE nisn = ?
                AND id != ?
            ");

            $cek->bind_param("si", $nisn, $id);
            $cek->execute();

            $hasilCek = $cek->get_result();

            if ($hasilCek->num_rows > 0) {

                $error = "NISN tersebut sudah digunakan siswa lain.";

                $cek->close();

            } else {

                $cek->close();


                /* =====================================
                   UPDATE DATA
                ===================================== */

                $sql = "
                    UPDATE users SET
                        name = ?,
                        nisn = ?,
                        ttl = ?,
                        gender = ?,
                        email = ?,
                        address = ?,
                        no_hp = ?
                    WHERE id = ?
                ";

                $stmt = $conn->prepare($sql);

                if (!$stmt) {

                    $error = "Query database gagal: " . $conn->error;

                } else {

                    $stmt->bind_param(
                        "sssssssi",
                        $name,
                        $nisn,
                        $ttl,
                        $gender,
                        $email,
                        $address,
                        $no_hp,
                        $id
                    );


                    /* =================================
                       EKSEKUSI UPDATE
                    ================================= */

                    if ($stmt->execute()) {


                        /* =================================
                           PESAN WHATSAPP
                        ================================= */

                     $pesan = "🔄 *DATA BERHASIL DIPERBARUI!* 🔄\n\n";

$pesan .= "Halo, *" . $name . "* 👋\n";
$pesan .= "Data kamu telah berhasil diperbarui melalui *Sistem Pendataan Siswa ASTS*.\n\n";

$pesan .= "╭───────────────╮\n";
$pesan .= "   📋 *DATA TERBARU*\n";
$pesan .= "╰───────────────╯\n\n";

$pesan .= "👤 *Nama:* " . $name . "\n";
$pesan .= "🆔 *NISN:* " . $nisn . "\n";
$pesan .= "🎂 *TTL:* " . $ttl . "\n";
$pesan .= "⚧️ *Jenis Kelamin:* " . $gender . "\n";
$pesan .= "📧 *Email:* " . $email . "\n";
$pesan .= "📱 *WhatsApp:* " . $no_hp . "\n";
$pesan .= "📍 *Alamat:* " . $address . "\n\n";

$pesan .= "✅ *Status: Data berhasil diperbarui*\n\n";

$pesan .= "📌 Pastikan data di atas sudah sesuai dengan data kamu.\n";
$pesan .= "Jika ada kesalahan, silakan hubungi admin untuk melakukan perubahan kembali.\n\n";

$pesan .= "━━━━━━━━━━━━━━━━━━\n";
$pesan .= "🏫 *SISTEM PENDATAAN SISWA ASTS*\n";
$pesan .= "📱 Notifikasi Otomatis\n";
$pesan .= "━━━━━━━━━━━━━━━━━━\n\n";

$pesan .= "Terima kasih sudah memperbarui data. 🙏\n";
$pesan .= "Semoga proses ASTS kamu berjalan lancar! ✨";

                        /* =================================
                           KIRIM WHATSAPP
                        ================================= */

                        $hasilWA = kirimWhatsApp(
                            $no_hp,
                            $pesan
                        );


                        /* =================================
                           CEK HASIL WHATSAPP
                        ================================= */

                        if (
                            isset($hasilWA["status"]) &&
                            $hasilWA["status"] === true
                        ) {

                            $success = "Data berhasil diperbarui dan WhatsApp berhasil dikirim.";

                        } else {

                            $success = "Data berhasil diperbarui, tetapi WhatsApp gagal dikirim.";

                        }


                    } else {

                        $error = "Data gagal diperbarui: " . $stmt->error;
                    }

                    $stmt->close();
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Data Siswa</title>


    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            background: #0d6efd;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #0b5ed7;
        }

        .success {
            background: #d1e7dd;
            color: #0f5132;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .error {
            background: #f8d7da;
            color: #842029;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #0d6efd;
        }

        .info {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }

    </style>

</head>


<body>


<div class="container">


    <a href="index.php" class="back">
        ← Kembali
    </a>


    <h2>
        Edit Data Siswa
    </h2>


    <?php if ($success !== ""): ?>

        <div class="success">
            <?= htmlspecialchars($success); ?>
        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST" action="">


        <div class="form-group">

            <label for="name">
                Nama Lengkap
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="<?= htmlspecialchars($name); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label for="nisn">
                NISN
            </label>

            <input
                type="text"
                id="nisn"
                name="nisn"
                value="<?= htmlspecialchars($nisn); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label for="ttl">
                Tempat/Tanggal Lahir
            </label>

            <input
                type="text"
                id="ttl"
                name="ttl"
                value="<?= htmlspecialchars($ttl); ?>"
                placeholder="Contoh: Ponorogo, 10 Januari 2008"
                required
            >

        </div>


        <div class="form-group">

            <label for="gender">
                Jenis Kelamin
            </label>

            <select
                id="gender"
                name="gender"
                required
            >

                <option value="">
                    -- Pilih Jenis Kelamin --
                </option>

                <option
                    value="Laki-laki"
                    <?= $gender === "Laki-laki" ? "selected" : ""; ?>
                >
                    Laki-laki
                </option>

                <option
                    value="Perempuan"
                    <?= $gender === "Perempuan" ? "selected" : ""; ?>
                >
                    Perempuan
                </option>

            </select>

        </div>


        <div class="form-group">

            <label for="email">
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($email); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label for="address">
                Alamat
            </label>

            <textarea
                id="address"
                name="address"
                required
            ><?= htmlspecialchars($address); ?></textarea>

        </div>


        <div class="form-group">

            <label for="no_hp">
                Nomor WhatsApp
            </label>

            <input
                type="text"
                id="no_hp"
                name="no_hp"
                value="<?= htmlspecialchars($no_hp); ?>"
                placeholder="628123456789"
                required
            >

            <div class="info">
                Contoh: 628123456789
            </div>

        </div>


        <button type="submit">
            Simpan Perubahan & Kirim WhatsApp
        </button>


    </form>


</div>


</body>

</html>