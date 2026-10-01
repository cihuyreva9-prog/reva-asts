<?php

session_start();

require_once "koneksi.php";

/* =====================================================
   KONFIGURASI FONNTE
===================================================== */

function kirimWhatsApp($target, $message)
{
    $token = "TkaoqfXfFrPGTGJ6UbLZ";

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            "target" => $target,
            "message" => $message,
            "countryCode" => "62"
        ],
        CURLOPT_HTTPHEADER => [
            "Authorization: " . $token
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($curl);

    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);

        return [
            "status" => false,
            "message" => $error
        ];
    }

    curl_close($curl);

    $result = json_decode($response, true);

    if (!is_array($result)) {
        return [
            "status" => false,
            "message" => "Response dari Fonnte tidak valid.",
            "response" => $response
        ];
    }

    return $result;
}


/* =====================================================
   CEK LOGIN
===================================================== */

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: login.php");
    exit;
}


/* =====================================================
   VARIABEL
===================================================== */

$name    = "";
$nisn    = "";
$ttl     = "";
$gender  = "";
$email   = "";
$address = "";
$no_hp   = "";

$success = "";
$error   = "";


/* =====================================================
   PROSES TAMBAH DATA
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name    = trim($_POST["name"] ?? "");
    $nisn    = trim($_POST["nisn"] ?? "");
    $ttl     = trim($_POST["ttl"] ?? "");
    $gender  = trim($_POST["gender"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $no_hp   = trim($_POST["no_hp"] ?? "");


    /* =================================================
       VALIDASI
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

    } elseif (!preg_match('/^[0-9]{1,8}$/', $nisn)) {

        $error = "NISN harus berupa angka dan maksimal 8 digit.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format email tidak valid.";

    } else {

        /* =============================================
           NORMALISASI NOMOR HP
        ============================================= */

        $no_hp = preg_replace('/[^0-9]/', '', $no_hp);

        if (substr($no_hp, 0, 1) === "0") {

            $no_hp = "62" . substr($no_hp, 1);

        } elseif (substr($no_hp, 0, 1) === "8") {

            $no_hp = "62" . $no_hp;
        }


        /* =============================================
           VALIDASI NOMOR HP
        ============================================= */

        if (!preg_match('/^62[0-9]{9,13}$/', $no_hp)) {

            $error = "Nomor WhatsApp tidak valid. Contoh: 628123456789";

        } else {

            /* =========================================
               CEK NISN
            ========================================= */

            $cek = $conn->prepare(
                "SELECT nisn FROM users WHERE nisn = ?"
            );

            if (!$cek) {

                $error = "Query pengecekan NISN gagal: " . $conn->error;

            } else {

                $cek->bind_param("s", $nisn);
                $cek->execute();

                $hasilCek = $cek->get_result();

                if ($hasilCek->num_rows > 0) {

                    $error = "NISN tersebut sudah terdaftar.";

                } else {

                    /* =================================
                       INSERT DATA
                    ================================= */

                    $sql = "INSERT INTO users
                            (name, nisn, ttl, gender, email, address, no_hp)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";

                    $stmt = $conn->prepare($sql);

                    if (!$stmt) {

                        $error = "Query database gagal: " . $conn->error;

                    } else {

                        $stmt->bind_param(
                            "sssssss",
                            $name,
                            $nisn,
                            $ttl,
                            $gender,
                            $email,
                            $address,
                            $no_hp
                        );


                        /* =============================
                           EKSEKUSI
                        ============================= */

                        if ($stmt->execute()) {

                            /* =============================
                               PESAN WHATSAPP
                            ============================= */

                            $pesan  = "🎉 *DATA BERHASIL DITAMBAHKAN!* 🎉\n\n";

                            $pesan .= "Halo, *" . $name . "* 👋\n";
                            $pesan .= "Data kamu telah berhasil ditambahkan ke *Sistem Pendataan Siswa ASTS*.\n\n";

                            $pesan .= "╭──────────────────╮\n";
                            $pesan .= "       📋 *DATA SISWA*\n";
                            $pesan .= "╰──────────────────╯\n\n";

                            $pesan .= "👤 *Nama:* " . $name . "\n";
                            $pesan .= "🆔 *NISN:* " . $nisn . "\n";
                            $pesan .= "🎂 *TTL:* " . $ttl . "\n";
                            $pesan .= "⚧️ *Jenis Kelamin:* " . $gender . "\n";
                            $pesan .= "📧 *Email:* " . $email . "\n";
                            $pesan .= "📱 *WhatsApp:* " . $no_hp . "\n\n";

                            $pesan .= "━━━━━━━━━━━━━━━━━━\n";
                            $pesan .= "✅ *STATUS DATA*\n";
                            $pesan .= "Data berhasil tersimpan ke database.\n";
                            $pesan .= "━━━━━━━━━━━━━━━━━━\n\n";

                            $pesan .= "📌 Simpan pesan ini sebagai bukti bahwa data kamu telah berhasil terdaftar.\n\n";

                            $pesan .= "🏫 *SISTEM PENDATAAN SISWA ASTS*\n";
                            $pesan .= "📡 Automatic Notification System\n\n";

                            $pesan .= "Terima kasih. 🙏\n";
                            $pesan .= "Semoga proses ASTS kamu berjalan lancar! ✨";


                            /* =============================
                               KIRIM WHATSAPP
                            ============================= */

                            $hasilWA = kirimWhatsApp(
                                $no_hp,
                                $pesan
                            );


                            /* =============================
                               HASIL WHATSAPP
                            ============================= */

                            if (
                                isset($hasilWA["status"]) &&
                                (
                                    $hasilWA["status"] === true ||
                                    $hasilWA["status"] === 1 ||
                                    $hasilWA["status"] === "1" ||
                                    $hasilWA["status"] === "true"
                                )
                            ) {

                                $success =
                                    "Data berhasil disimpan dan notifikasi WhatsApp berhasil dikirim.";

                            } else {

                                $success =
                                    "Data berhasil disimpan, tetapi notifikasi WhatsApp gagal dikirim.";
                            }


                            /* =============================
                               RESET FORM
                            ============================= */

                            $name    = "";
                            $nisn    = "";
                            $ttl     = "";
                            $gender  = "";
                            $email   = "";
                            $address = "";
                            $no_hp   = "";

                        } else {

                            $error =
                                "Data gagal disimpan: " .
                                $stmt->error;
                        }

                        $stmt->close();
                    }
                }

                $cek->close();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>ASTS Galaxy | Tambah Siswa</title>

<style>

/* =====================================================
   RESET
===================================================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {

    min-height: 100vh;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    color: #edf7ff;

    background:

        radial-gradient(
            circle at 15% 15%,
            rgba(99,102,241,.20),
            transparent 30%
        ),

        radial-gradient(
            circle at 85% 80%,
            rgba(14,165,233,.16),
            transparent 32%
        ),

        linear-gradient(
            135deg,
            #050816,
            #0b1022 45%,
            #050816
        );

    overflow-x: hidden;
}


/* =====================================================
   BACKGROUND GRID
===================================================== */

body::before {

    content: "";

    position: fixed;

    inset: 0;

    pointer-events: none;

    background-image:

        linear-gradient(
            rgba(129,140,248,.035) 1px,
            transparent 1px
        ),

        linear-gradient(
            90deg,
            rgba(129,140,248,.035) 1px,
            transparent 1px
        );

    background-size: 45px 45px;

    z-index: -2;
}


/* =====================================================
   GLOW
===================================================== */

body::after {

    content: "";

    position: fixed;

    width: 500px;
    height: 500px;

    right: -200px;
    top: -120px;

    border-radius: 50%;

    background:
        rgba(99,102,241,.13);

    filter: blur(100px);

    pointer-events: none;

    z-index: -1;
}


/* =====================================================
   SCROLLBAR
===================================================== */

::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #050816;
}

::-webkit-scrollbar-thumb {

    background:
        linear-gradient(
            #6366f1,
            #06b6d4
        );

    border-radius: 20px;
}


/* =====================================================
   CONTAINER
===================================================== */

.container {

    width: 100%;

    max-width: 1050px;

    margin: auto;

    padding: 35px 20px 50px;
}


/* =====================================================
   HEADER
===================================================== */

.header {

    position: relative;

    overflow: hidden;

    padding: 28px;

    background:

        linear-gradient(
            135deg,
            rgba(18,25,55,.96),
            rgba(9,15,35,.96)
        );

    border:
        1px solid
        rgba(129,140,248,.25);

    border-radius: 24px 24px 0 0;

    box-shadow:
        0 25px 70px rgba(0,0,0,.35);
}


/* HEADER LIGHT */

.header::after {

    content: "";

    position: absolute;

    width: 250px;
    height: 250px;

    right: -90px;
    top: -120px;

    border-radius: 50%;

    background:
        rgba(99,102,241,.16);

    filter: blur(15px);
}


/* =====================================================
   TOP
===================================================== */

.top {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 25px;
}


.brand {

    display: flex;

    align-items: center;

    gap: 14px;
}


.brand-icon {

    width: 48px;
    height: 48px;

    display: grid;

    place-items: center;

    border-radius: 15px;

    color: #fff;

    font-size: 20px;

    background:

        linear-gradient(
            135deg,
            #6366f1,
            #06b6d4
        );

    box-shadow:
        0 10px 30px rgba(99,102,241,.30);
}


.brand-title {

    font-size: 15px;

    font-weight: 800;

    letter-spacing: 1px;
}


.brand-subtitle {

    margin-top: 4px;

    color: #72809f;

    font-size: 10px;

    letter-spacing: 1px;
}


/* =====================================================
   STATUS
===================================================== */

.system-status {

    display: flex;

    align-items: center;

    gap: 8px;

    padding:
        9px 13px;

    color: #67e8f9;

    background:
        rgba(6,182,212,.06);

    border:
        1px solid
        rgba(6,182,212,.18);

    border-radius: 999px;

    font-size: 10px;

    font-weight: 700;
}


.status-dot {

    width: 7px;
    height: 7px;

    border-radius: 50%;

    background: #22d3ee;

    box-shadow:
        0 0 12px #22d3ee;
}


/* =====================================================
   TITLE
===================================================== */

.main-title {

    position: relative;

    z-index: 2;
}


.main-title h1 {

    color: #fff;

    font-size: 29px;

    font-weight: 800;

    letter-spacing: -.5px;
}


.main-title h1 span {

    background:

        linear-gradient(
            90deg,
            #818cf8,
            #22d3ee
        );

    -webkit-background-clip: text;

    background-clip: text;

    color: transparent;
}


.main-title p {

    max-width: 650px;

    margin-top: 9px;

    color: #7886a4;

    font-size: 12px;

    line-height: 1.7;
}


/* =====================================================
   API BAR
===================================================== */

.api-bar {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-top: 22px;

    padding: 12px 14px;

    background:
        rgba(3,7,18,.75);

    border:
        1px solid
        rgba(129,140,248,.15);

    border-radius: 10px;

    font-family:
        "Cascadia Code",
        "Courier New",
        monospace;

    font-size: 11px;
}


.method {

    padding:
        5px 8px;

    color: #a5b4fc;

    background:
        rgba(99,102,241,.12);

    border:
        1px solid
        rgba(99,102,241,.25);

    border-radius: 6px;

    font-weight: bold;
}


.endpoint {

    color: #dbeafe;
}


/* =====================================================
   FORM AREA
===================================================== */

.form-area {

    padding: 30px;

    background:

        linear-gradient(
            145deg,
            rgba(13,20,43,.98),
            rgba(7,12,27,.98)
        );

    border:

        1px solid
        rgba(129,140,248,.20);

    border-top: none;

    border-radius: 0 0 24px 24px;

    box-shadow:
        0 30px 80px rgba(0,0,0,.38);
}


/* =====================================================
   NAVIGATION
===================================================== */

.navigation {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    margin-bottom: 25px;
}


.back-button {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding:
        10px 14px;

    color: #a5b4fc;

    background:
        rgba(99,102,241,.06);

    border:
        1px solid
        rgba(99,102,241,.20);

    border-radius: 9px;

    text-decoration: none;

    font-size: 11px;

    transition: .25s;
}


.back-button:hover {

    color: #fff;

    background:
        rgba(99,102,241,.16);

    border-color:
        #818cf8;

    transform:
        translateX(-3px);
}


/* =====================================================
   FORM HEADING
===================================================== */

.form-heading {

    margin-bottom: 22px;
}


.form-heading h2 {

    color: #f8fafc;

    font-size: 19px;

    font-weight: 750;
}


.form-heading h2 span {

    color: #818cf8;
}


.form-heading p {

    margin-top: 6px;

    color: #64748b;

    font-size: 11px;
}


/* =====================================================
   ALERT
===================================================== */

.alert {

    position: relative;

    margin-bottom: 22px;

    padding:
        14px 17px 14px 45px;

    border-radius: 12px;

    font-size: 11px;

    line-height: 1.6;
}


.alert::before {

    position: absolute;

    left: 17px;

    top: 13px;

    font-size: 16px;
}


.success {

    color: #a7f3d0;

    background:
        rgba(16,185,129,.07);

    border:
        1px solid
        rgba(16,185,129,.25);
}


.success::before {

    content: "✓";
}


.error {

    color: #fecaca;

    background:
        rgba(239,68,68,.07);

    border:
        1px solid
        rgba(239,68,68,.25);
}


.error::before {

    content: "!";
}


/* =====================================================
   FORM GRID
===================================================== */

.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 20px;
}


/* =====================================================
   FORM GROUP
===================================================== */

.form-group {

    position: relative;
}


.full {

    grid-column:
        1 / -1;
}


label {

    display: flex;

    align-items: center;

    gap: 7px;

    margin-bottom: 8px;

    color: #b6c2dc;

    font-size: 11px;

    font-weight: 650;
}


label::before {

    content: "";

    width: 4px;
    height: 13px;

    border-radius: 4px;

    background:

        linear-gradient(
            #818cf8,
            #22d3ee
        );
}


/* =====================================================
   INPUT
===================================================== */

input,
select,
textarea {

    width: 100%;

    padding:
        13px 14px;

    color: #e2e8f0;

    background:
        rgba(3,7,18,.85);

    border:
        1px solid
        rgba(100,116,139,.23);

    border-radius: 10px;

    outline: none;

    font-family:
        "Segoe UI",
        Arial,
        sans-serif;

    font-size: 12px;

    transition: .25s;
}


input::placeholder,
textarea::placeholder {

    color: #475569;
}


input:hover,
select:hover,
textarea:hover {

    border-color:
        rgba(129,140,248,.35);
}


input:focus,
select:focus,
textarea:focus {

    border-color:
        #818cf8;

    background:
        rgba(99,102,241,.045);

    box-shadow:

        0 0 0 3px
        rgba(99,102,241,.07),

        0 0 25px
        rgba(99,102,241,.07);
}


select {

    cursor: pointer;
}


select option {

    color: #e2e8f0;

    background: #0b1022;
}


textarea {

    min-height: 125px;

    resize: vertical;
}


/* =====================================================
   INFO
===================================================== */

.info {

    margin-top: 7px;

    color: #52617b;

    font-size: 9px;
}


.info::before {

    content: "INFO  ";

    color: #22d3ee;

    font-weight: bold;
}


/* =====================================================
   NUMBER INDICATOR
===================================================== */

.field-counter {

    display: flex;

    justify-content: flex-end;

    margin-top: 5px;

    color: #45536d;

    font-size: 9px;
}


/* =====================================================
   ACTION AREA
===================================================== */

.action-area {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 12px;

    margin-top: 27px;

    padding-top: 23px;

    border-top:
        1px solid
        rgba(100,116,139,.12);
}


/* =====================================================
   SAVE BUTTON
===================================================== */

.save-button {

    width: 100%;

    padding: 14px 18px;

    color: #fff;

    background:

        linear-gradient(
            135deg,
            #6366f1,
            #4f46e5
        );

    border:
        1px solid
        rgba(165,180,252,.35);

    border-radius: 10px;

    font-size: 11px;

    font-weight: 800;

    cursor: pointer;

    box-shadow:
        0 10px 30px
        rgba(79,70,229,.20);

    transition: .25s;
}


.save-button:hover {

    transform:
        translateY(-3px);

    box-shadow:
        0 15px 35px
        rgba(79,70,229,.35);
}


/* =====================================================
   DASHBOARD BUTTON
===================================================== */

.dashboard-button {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding: 14px 18px;

    color: #94a3b8;

    background:
        rgba(255,255,255,.025);

    border:
        1px solid
        rgba(100,116,139,.20);

    border-radius: 10px;

    text-decoration: none;

    font-size: 11px;

    font-weight: 700;

    transition: .25s;
}


.dashboard-button:hover {

    color: #fff;

    border-color:
        rgba(129,140,248,.45);

    background:
        rgba(99,102,241,.08);

    transform:
        translateY(-3px);
}


/* =====================================================
   INFORMATION CARDS
===================================================== */

.info-cards {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 12px;

    margin-top: 22px;
}


.info-card {

    padding: 15px;

    background:
        rgba(255,255,255,.018);

    border:
        1px solid
        rgba(100,116,139,.13);

    border-radius: 12px;
}


.info-icon {

    width: 30px;
    height: 30px;

    display: grid;

    place-items: center;

    margin-bottom: 10px;

    color: #a5b4fc;

    background:
        rgba(99,102,241,.09);

    border-radius: 8px;
}


.info-card strong {

    display: block;

    color: #cbd5e1;

    font-size: 10px;
}


.info-card span {

    display: block;

    margin-top: 5px;

    color: #53627b;

    font-size: 9px;

    line-height: 1.5;
}


/* =====================================================
   FOOTER
===================================================== */

.footer {

    margin-top: 25px;

    color: #39455c;

    text-align: center;

    font-size: 9px;

    letter-spacing: .7px;
}


.footer span {

    color: #6366f1;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 750px) {

    .container {

        padding:
            20px 12px 35px;
    }


    .header {

        padding: 21px;

        border-radius:
            18px 18px 0 0;
    }


    .form-area {

        padding: 21px;

        border-radius:
            0 0 18px 18px;
    }


    .top {

        align-items: flex-start;

        flex-direction: column;
    }


    .system-status {

        align-self: flex-start;
    }


    .form-grid {

        grid-template-columns: 1fr;
    }


    .full {

        grid-column: auto;
    }


    .info-cards {

        grid-template-columns: 1fr;
    }


    .action-area {

        grid-template-columns: 1fr;
    }
}


@media (max-width: 500px) {

    .main-title h1 {

        font-size: 22px;
    }


    .api-bar {

        align-items: flex-start;

        flex-direction: column;
    }


    .navigation {

        align-items: stretch;

        flex-direction: column;
    }


    .back-button {

        justify-content: center;
    }
}

</style>

</head>


<body>

<div class="container">


    <!-- =================================================
         HEADER
    ================================================== -->

    <header class="header">

        <div class="top">

            <div class="brand">

                <div class="brand-icon">
                    ✦
                </div>

                <div>

                    <div class="brand-title">
                        ASTS GALAXY SYSTEM
                    </div>

                    <div class="brand-subtitle">
                        STUDENT DATA MANAGEMENT
                    </div>

                </div>

            </div>


            <div class="system-status">

                <span class="status-dot"></span>

                SYSTEM ONLINE

            </div>

        </div>


        <div class="main-title">

            <h1>
                Tambah Data
                <span>Siswa</span>
            </h1>

            <p>
                Tambahkan data siswa baru ke database
                dan kirimkan notifikasi otomatis melalui
                WhatsApp.
            </p>

        </div>


        <div class="api-bar">

            <span class="method">
                POST
            </span>

            <span class="endpoint">
                /api/siswa/create
            </span>

        </div>

    </header>



    <!-- =================================================
         FORM AREA
    ================================================== -->

    <main class="form-area">


        <!-- NAVIGATION -->

        <div class="navigation">

            <a
                href="index.php"
                class="back-button"
            >
                ← Dashboard
            </a>

        </div>



        <!-- ALERT SUCCESS -->

        <?php if ($success !== ""): ?>

            <div class="alert success">

                <?= htmlspecialchars($success); ?>

            </div>

        <?php endif; ?>



        <!-- ALERT ERROR -->

        <?php if ($error !== ""): ?>

            <div class="alert error">

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>



        <!-- FORM HEADING -->

        <div class="form-heading">

            <h2>
                Formulir
                <span>Data Siswa</span>
            </h2>

            <p>
                Lengkapi seluruh informasi siswa di bawah ini.
            </p>

        </div>



        <!-- FORM -->

        <form
            method="POST"
            action=""
            autocomplete="off"
        >

            <div class="form-grid">


                <!-- NAMA -->

                <div class="form-group">

                    <label for="name">
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($name); ?>"
                        placeholder="Contoh: Reva Alvay"
                        required
                        maxlength="100"
                    >

                </div>



                <!-- NISN -->

                <div class="form-group">

                    <label for="nisn">
                        NISN
                    </label>

                    <input
                        type="text"
                        id="nisn"
                        name="nisn"
                        value="<?= htmlspecialchars($nisn); ?>"
                        placeholder="Masukkan NISN"
                        maxlength="8"
                        pattern="[0-9]{1,8}"
                        inputmode="numeric"
                        required
                    >

                    <div class="field-counter">
                        Maksimal 8 digit
                    </div>

                </div>



                <!-- TTL -->

                <div class="form-group">

                    <label for="ttl">
                        Tempat & Tanggal Lahir
                    </label>

                    <input
                        type="text"
                        id="ttl"
                        name="ttl"
                        value="<?= htmlspecialchars($ttl); ?>"
                        placeholder="Contoh: Sumenep, 10 Januari 2008"
                        required
                        maxlength="150"
                    >

                </div>



                <!-- GENDER -->

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
                            Pilih jenis kelamin
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



                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($email); ?>"
                        placeholder="nama@email.com"
                        required
                        maxlength="150"
                    >

                </div>



                <!-- WHATSAPP -->

                <div class="form-group">

                    <label for="no_hp">
                        Nomor WhatsApp
                    </label>

                    <input
                        type="text"
                        id="no_hp"
                        name="no_hp"
                        value="<?= htmlspecialchars($no_hp); ?>"
                        placeholder="081234567890"
                        inputmode="numeric"
                        required
                        maxlength="15"
                    >

                    <div class="info">
                        Digunakan untuk menerima notifikasi WhatsApp.
                    </div>

                </div>



                <!-- ALAMAT -->

                <div class="form-group full">

                    <label for="address">
                        Alamat Lengkap
                    </label>

                    <textarea
                        id="address"
                        name="address"
                        placeholder="Masukkan alamat lengkap siswa..."
                        required
                        maxlength="500"
                    ><?= htmlspecialchars($address); ?></textarea>

                </div>


            </div>



            <!-- =================================================
                 ACTION
            ================================================== -->

            <div class="action-area">

                <button
                    type="submit"
                    class="save-button"
                >

                    ✓
                    SIMPAN DATA & KIRIM WHATSAPP

                </button>


                <a
                    href="index.php"
                    class="dashboard-button"
                >

                    ←

                    KEMBALI KE DASHBOARD

                </a>

            </div>


        </form>



        <!-- =================================================
             INFORMATION
        ================================================== -->

        <div class="info-cards">


            <div class="info-card">

                <div class="info-icon">
                    ✓
                </div>

                <strong>
                    Database
                </strong>

                <span>
                    Data siswa akan langsung
                    disimpan ke database.
                </span>

            </div>



            <div class="info-card">

                <div class="info-icon">
                    #
                </div>

                <strong>
                    NISN
                </strong>

                <span>
                    NISN diperiksa agar tidak
                    terjadi data duplikat.
                </span>

            </div>



            <div class="info-card">

                <div class="info-icon">
                    WA
                </div>

                <strong>
                    WhatsApp
                </strong>

                <span>
                    Notifikasi dikirim otomatis
                    menggunakan gateway Fonnte.
                </span>

            </div>


        </div>



        <!-- FOOTER -->

        <div class="footer">

            ASTS GALAXY SYSTEM

            <span>•</span>

            STUDENT API

            <span>•</span>

            MYSQL DATABASE

            <span>•</span>

            WHATSAPP GATEWAY

        </div>


    </main>

</div>


<script>

/* =====================================================
   NISN HANYA ANGKA
===================================================== */

const nisnInput =
    document.getElementById("nisn");

if (nisnInput) {

    nisnInput.addEventListener(
        "input",
        function () {

            this.value =
                this.value
                    .replace(/\D/g, "")
                    .substring(0, 8);

        }
    );
}


/* =====================================================
   NOMOR WHATSAPP HANYA ANGKA
===================================================== */

const hpInput =
    document.getElementById("no_hp");

if (hpInput) {

    hpInput.addEventListener(
        "input",
        function () {

            this.value =
                this.value
                    .replace(/\D/g, "")
                    .substring(0, 15);

        }
    );
}


/* =====================================================
   FORM SUBMIT LOADING
===================================================== */

const form =
    document.querySelector("form");

const saveButton =
    document.querySelector(".save-button");

if (form && saveButton) {

    form.addEventListener(
        "submit",
        function () {

            saveButton.disabled = true;

            saveButton.innerHTML =
                "⏳ MENYIMPAN DATA...";

            saveButton.style.opacity =
                "0.7";

            saveButton.style.cursor =
                "wait";

        }
    );
}


/* =====================================================
   AUTO FOCUS
===================================================== */

const nameInput =
    document.getElementById("name");

if (nameInput) {

    nameInput.focus();

}

</script>

</body>

</html>