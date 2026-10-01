<?php

session_start();

require_once __DIR__ . "/koneksi.php";
require_once __DIR__ . "/fonnte.php";

/*
|--------------------------------------------------------------------------
| FUNGSI NORMALISASI NOMOR HP
|--------------------------------------------------------------------------
*/

function normalisasiNomorHP($nomor)
{
    $nomor = trim((string) $nomor);

    // Hanya angka dan tanda +
    $nomor = preg_replace('/[^0-9+]/', '', $nomor);

    // Hilangkan +
    $nomor = ltrim($nomor, '+');

    // 08xxxx -> 628xxxx
    if (substr($nomor, 0, 1) === '0') {
        $nomor = '62' . substr($nomor, 1);
    }

    return $nomor;
}


/*
|--------------------------------------------------------------------------
| VARIABEL
|--------------------------------------------------------------------------
*/

$pesan = "";
$tipePesan = "";

$nama = "";
$username = "";
$email = "";
$no_hp = "";


/*
|--------------------------------------------------------------------------
| PROSES REGISTER
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $no_hp = trim($_POST["no_hp"] ?? "");

    $password = $_POST["password"] ?? "";
    $konfirmasi_password = $_POST["konfirmasi_password"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | VALIDASI INPUT
    |--------------------------------------------------------------------------
    */

    if (
        $nama === "" ||
        $username === "" ||
        $email === "" ||
        $no_hp === "" ||
        $password === "" ||
        $konfirmasi_password === ""
    ) {

        $pesan = "Semua data wajib diisi.";
        $tipePesan = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $pesan = "Format email tidak valid.";
        $tipePesan = "error";

    } elseif ($password !== $konfirmasi_password) {

        $pesan = "Konfirmasi password tidak sama.";
        $tipePesan = "error";

    } elseif (strlen($password) < 6) {

        $pesan = "Password minimal 6 karakter.";
        $tipePesan = "error";

    } else {


        /*
        |--------------------------------------------------------------------------
        | NORMALISASI NOMOR HP
        |--------------------------------------------------------------------------
        */

        $no_hp_normal = normalisasiNomorHP($no_hp);


        /*
        |--------------------------------------------------------------------------
        | VALIDASI NOMOR HP
        |--------------------------------------------------------------------------
        */

        if (
            $no_hp_normal === "" ||
            strlen($no_hp_normal) < 10 ||
            strlen($no_hp_normal) > 15
        ) {

            $pesan = "Nomor HP tidak valid.";
            $tipePesan = "error";

        } else {


            /*
            |--------------------------------------------------------------------------
            | CEK USERNAME
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                SELECT id
                FROM users_login
                WHERE username = ?
                LIMIT 1
            ");

            if (!$stmt) {

                $pesan = "Gagal memeriksa username: " . $conn->error;
                $tipePesan = "error";

            } else {

                $stmt->bind_param("s", $username);
                $stmt->execute();

                $hasil = $stmt->get_result();

                if ($hasil->num_rows > 0) {

                    $pesan = "Username sudah digunakan.";
                    $tipePesan = "error";

                    $stmt->close();

                } else {

                    $stmt->close();


                    /*
                    |--------------------------------------------------------------------------
                    | CEK EMAIL
                    |--------------------------------------------------------------------------
                    */

                    $stmt = $conn->prepare("
                        SELECT id
                        FROM users_login
                        WHERE email = ?
                        LIMIT 1
                    ");

                    if (!$stmt) {

                        $pesan = "Gagal memeriksa email: " . $conn->error;
                        $tipePesan = "error";

                    } else {

                        $stmt->bind_param("s", $email);
                        $stmt->execute();

                        $hasil = $stmt->get_result();

                        if ($hasil->num_rows > 0) {

                            $pesan = "Email sudah digunakan.";
                            $tipePesan = "error";

                            $stmt->close();

                        } else {

                            $stmt->close();


                            /*
                            |--------------------------------------------------------------------------
                            | CEK NOMOR HP
                            |--------------------------------------------------------------------------
                            */

                            $stmt = $conn->prepare("
                                SELECT id
                                FROM users_login
                                WHERE no_hp = ?
                                LIMIT 1
                            ");

                            if (!$stmt) {

                                $pesan = "Gagal memeriksa nomor HP: " . $conn->error;
                                $tipePesan = "error";

                            } else {

                                $stmt->bind_param("s", $no_hp_normal);
                                $stmt->execute();

                                $hasil = $stmt->get_result();

                                if ($hasil->num_rows > 0) {

                                    $pesan = "Nomor HP sudah terdaftar.";
                                    $tipePesan = "error";

                                    $stmt->close();

                                } else {

                                    $stmt->close();


                                    /*
                                    |--------------------------------------------------------------------------
                                    | HASH PASSWORD
                                    |--------------------------------------------------------------------------
                                    */

                                    $passwordHash = password_hash(
                                        $password,
                                        PASSWORD_DEFAULT
                                    );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | SIMPAN DATA USER
                                    |--------------------------------------------------------------------------
                                    */

                                    $stmt = $conn->prepare("
                                        INSERT INTO users_login
                                        (
                                            nama,
                                            username,
                                            email,
                                            no_hp,
                                            password
                                        )
                                        VALUES (?, ?, ?, ?, ?)
                                    ");

                                    if (!$stmt) {

                                        $pesan =
                                            "Gagal menyiapkan data register: " .
                                            $conn->error;

                                        $tipePesan = "error";

                                    } else {

                                        $stmt->bind_param(
                                            "sssss",
                                            $nama,
                                            $username,
                                            $email,
                                            $no_hp_normal,
                                            $passwordHash
                                        );


                                        /*
                                        |--------------------------------------------------------------------------
                                        | JALANKAN INSERT
                                        |--------------------------------------------------------------------------
                                        */

                                        if ($stmt->execute()) {

                                            $id_user = $conn->insert_id;


                                            /*
                                            |--------------------------------------------------------------------------
                                            | BUAT PESAN WHATSAPP
                                            |--------------------------------------------------------------------------
                                            |
                                            | PENTING:
                                            | Gunakan $nama, bukan $name.
                                            | Gunakan $pesanWA saat dikirim.
                                            |--------------------------------------------------------------------------
                                            */

                                            $pesanWA =
                                                "🎉 *REGISTRASI BERHASIL* 🎉\n\n";

                                            $pesanWA .=
                                                "Halo, *" . $nama . "* 👋\n";

                                            $pesanWA .=
                                                "Selamat! Data akun kamu telah berhasil didaftarkan pada *Sistem Pendataan Siswa ASTS*.\n\n";

                                            $pesanWA .=
                                                "╭───────────────╮\n";

                                            $pesanWA .=
                                                "   📝 *DATA AKUN*\n";

                                            $pesanWA .=
                                                "╰───────────────╯\n\n";

                                            $pesanWA .=
                                                "👤 *Nama:* " . $nama . "\n";

                                            $pesanWA .=
                                                "🆔 *Username:* " . $username . "\n";

                                            $pesanWA .=
                                                "📧 *Email:* " . $email . "\n";

                                            $pesanWA .=
                                                "📱 *No. HP:* " . $no_hp_normal . "\n\n";

                                            $pesanWA .=
                                                "✅ *Status: Registrasi berhasil*\n\n";

                                            $pesanWA .=
                                                "📌 Akun kamu sudah berhasil dibuat dan dapat digunakan untuk login ke sistem.\n";

                                            $pesanWA .=
                                                "Pastikan username dan password kamu tetap aman dan jangan diberikan kepada orang lain.\n\n";

                                            $pesanWA .=
                                                "━━━━━━━━━━━━━━━━━━\n";

                                            $pesanWA .=
                                                "🏫 *SISTEM PENDATAAN SISWA ASTS*\n";

                                            $pesanWA .=
                                                "📱 Notifikasi Otomatis\n";

                                            $pesanWA .=
                                                "━━━━━━━━━━━━━━━━━━\n\n";

                                            $pesanWA .=
                                                "Terima kasih telah melakukan registrasi. 🙏\n";

                                            $pesanWA .=
                                                "Semoga proses ASTS kamu berjalan lancar! ✨";


                                            /*
                                            |--------------------------------------------------------------------------
                                            | KIRIM WHATSAPP
                                            |--------------------------------------------------------------------------
                                            */

                                            $hasilWA = null;

                                            try {

                                                $hasilWA = kirimWhatsApp(
                                                    $no_hp_normal,
                                                    $pesanWA
                                                );

                                            } catch (Throwable $e) {

                                                $hasilWA = [
                                                    "status" => false,
                                                    "message" => $e->getMessage()
                                                ];
                                            }


                                            /*
                                            |--------------------------------------------------------------------------
                                            | CEK HASIL WHATSAPP
                                            |--------------------------------------------------------------------------
                                            */

                                            $statusWA = false;

                                            if (is_array($hasilWA)) {

                                                $nilaiStatus =
                                                    $hasilWA["status"] ?? false;

                                                if (
                                                    $nilaiStatus === true ||
                                                    $nilaiStatus === 1 ||
                                                    $nilaiStatus === "1" ||
                                                    $nilaiStatus === "true"
                                                ) {

                                                    $statusWA = true;
                                                }
                                            }


                                            /*
                                            |--------------------------------------------------------------------------
                                            | SIMPAN SESSION REGISTER
                                            |--------------------------------------------------------------------------
                                            */

                                            $_SESSION["register"] = true;

                                            $_SESSION["register_id"] =
                                                $id_user;

                                            $_SESSION["register_username"] =
                                                $username;

                                            $_SESSION["register_no_hp"] =
                                                $no_hp_normal;


                                            /*
                                            |--------------------------------------------------------------------------
                                            | SIMPAN STATUS WHATSAPP
                                            |--------------------------------------------------------------------------
                                            */

                                            if ($statusWA) {

                                                $_SESSION["register_wa"] = [
                                                    "status" => true,
                                                    "message" =>
                                                        "Registrasi berhasil dan notifikasi WhatsApp berhasil dikirim."
                                                ];

                                            } else {

                                                $detailWA = "";

                                                if (is_array($hasilWA)) {

                                                    if (
                                                        isset($hasilWA["reason"]) &&
                                                        $hasilWA["reason"] !== ""
                                                    ) {

                                                        $detailWA =
                                                            $hasilWA["reason"];

                                                    } elseif (
                                                        isset($hasilWA["message"]) &&
                                                        $hasilWA["message"] !== ""
                                                    ) {

                                                        $detailWA =
                                                            $hasilWA["message"];

                                                    } elseif (
                                                        isset($hasilWA["detail"]) &&
                                                        $hasilWA["detail"] !== ""
                                                    ) {

                                                        $detailWA =
                                                            $hasilWA["detail"];
                                                    }
                                                }


                                                $_SESSION["register_wa"] = [
                                                    "status" => false,

                                                    "message" =>
                                                        "Registrasi berhasil, tetapi notifikasi WhatsApp gagal dikirim.",

                                                    "detail" =>
                                                        $detailWA
                                                ];
                                            }


                                            /*
                                            |--------------------------------------------------------------------------
                                            | REDIRECT LOGIN
                                            |--------------------------------------------------------------------------
                                            */

                                            $stmt->close();

                                            header("Location: login.php");
                                            exit;


                                        } else {

                                            $pesan =
                                                "Registrasi gagal: " .
                                                $stmt->error;

                                            $tipePesan = "error";

                                            $stmt->close();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| HTML
|--------------------------------------------------------------------------
*/

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Register - ASTS Galaxy System</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                radial-gradient(
                    circle at top,
                    #581c87,
                    #17052b 45%,
                    #020008 100%
                );

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 25px;

            color: #ffffff;
        }

        body::before {
            content: "";

            position: fixed;
            inset: 0;

            background-image:
                linear-gradient(
                    rgba(168,85,247,.035) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(168,85,247,.035) 1px,
                    transparent 1px
                );

            background-size: 45px 45px;

            pointer-events: none;
        }

        .container {
            width: 100%;
            max-width: 500px;

            position: relative;
            z-index: 2;
        }

        .card {
            background:
                rgba(13, 7, 24, .94);

            border:
                1px solid rgba(168,85,247,.25);

            border-radius: 25px;

            padding: 35px;

            box-shadow:
                0 30px 80px rgba(0,0,0,.60),
                0 0 60px rgba(124,58,237,.12);

            backdrop-filter: blur(20px);
        }

        .logo {
            width: 72px;
            height: 72px;

            border-radius: 20px;

            margin: 0 auto 18px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 27px;
            font-weight: bold;

            background:
                linear-gradient(
                    135deg,
                    #7c3aed,
                    #a855f7,
                    #d946ef
                );

            box-shadow:
                0 0 35px rgba(168,85,247,.40);
        }

        h1 {
            text-align: center;

            font-size: 29px;

            margin-bottom: 8px;

            background:
                linear-gradient(
                    135deg,
                    #ffffff,
                    #d8b4fe
                );

            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            text-align: center;

            color: #a8a0b8;

            margin-bottom: 28px;

            font-size: 14px;
        }

        .alert {
            padding: 13px 15px;

            border-radius: 12px;

            margin-bottom: 20px;

            font-size: 14px;

            line-height: 1.5;
        }

        .alert.error {
            background:
                rgba(239,68,68,.10);

            border:
                1px solid rgba(239,68,68,.30);

            color: #fca5a5;
        }

        .form-group {
            margin-bottom: 17px;
        }

        label {
            display: block;

            margin-bottom: 7px;

            font-size: 13px;

            color: #ddd6e7;

            font-weight: 600;
        }

        input {
            width: 100%;

            padding: 14px;

            border-radius: 12px;

            border:
                1px solid rgba(168,85,247,.20);

            background:
                rgba(255,255,255,.035);

            color: #ffffff;

            outline: none;

            font-size: 14px;

            transition: .2s;
        }

        input:focus {
            border-color:
                rgba(168,85,247,.70);

            box-shadow:
                0 0 0 3px
                rgba(168,85,247,.10);
        }

        input::placeholder {
            color: #6d6477;
        }

        .hp-info {
            margin-top: 6px;

            font-size: 11px;

            color: #756c81;

            line-height: 1.5;
        }

        .wa-info {
            margin: 18px 0;

            padding: 15px;

            border-radius: 13px;

            background:
                rgba(37,211,102,.055);

            border:
                1px solid rgba(37,211,102,.15);

            color: #bbf7d0;

            font-size: 12px;

            line-height: 1.6;
        }

        .wa-info strong {
            color: #86efac;
        }

        .btn {
            width: 100%;

            padding: 15px;

            border: none;

            border-radius: 13px;

            background:
                linear-gradient(
                    135deg,
                    #7c3aed,
                    #a855f7,
                    #d946ef
                );

            color: #ffffff;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            transition: .2s;

            box-shadow:
                0 12px 30px
                rgba(124,58,237,.25);
        }

        .btn:hover {
            transform: translateY(-2px);

            box-shadow:
                0 16px 35px
                rgba(168,85,247,.35);
        }

        .login-link {
            text-align: center;

            margin-top: 22px;

            color: #93899e;

            font-size: 13px;
        }

        .login-link a {
            color: #c084fc;

            text-decoration: none;

            font-weight: bold;
        }

        .login-link a:hover {
            color: #ffffff;

            text-decoration: underline;
        }

        .footer {
            text-align: center;

            margin-top: 20px;

            color: #554c60;

            font-size: 10px;

            letter-spacing: .5px;
        }

        @media (max-width: 520px) {

            body {
                padding: 15px;
            }

            .card {
                padding: 25px 20px;
            }

            h1 {
                font-size: 24px;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <div class="logo">
            AG
        </div>

        <h1>
            Buat Akun
        </h1>

        <div class="subtitle">
            ASTS Galaxy System
        </div>


        <?php if ($pesan !== ""): ?>

            <div class="alert <?= htmlspecialchars($tipePesan) ?>">

                <?= htmlspecialchars($pesan) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="register.php"
            autocomplete="off"
        >

            <div class="form-group">

                <label for="nama">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    id="nama"
                    name="nama"
                    placeholder="Masukkan nama lengkap"
                    value="<?= htmlspecialchars($nama) ?>"
                    autocomplete="off"
                    required
                >

            </div>


            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Masukkan username"
                    value="<?= htmlspecialchars($username) ?>"
                    autocomplete="off"
                    autocapitalize="none"
                    spellcheck="false"
                    required
                >

            </div>


            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="contoh@email.com"
                    value="<?= htmlspecialchars($email) ?>"
                    autocomplete="off"
                    required
                >

            </div>


            <div class="form-group">

                <label for="no_hp">
                    Nomor HP
                </label>

                <input
                    type="tel"
                    id="no_hp"
                    name="no_hp"
                    placeholder="08xxxxxxxxxx"
                    value="<?= htmlspecialchars($no_hp) ?>"
                    autocomplete="off"
                    required
                >

                <div class="hp-info">

                    Nomor HP ini digunakan sebagai
                    nomor tujuan notifikasi WhatsApp.

                    <br>

                    Contoh:
                    081234567890

                </div>

            </div>


            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Minimal 6 karakter"
                    autocomplete="new-password"
                    required
                >

            </div>


            <div class="form-group">

                <label for="konfirmasi_password">
                    Konfirmasi Password
                </label>

                <input
                    type="password"
                    id="konfirmasi_password"
                    name="konfirmasi_password"
                    placeholder="Ulangi password"
                    autocomplete="new-password"
                    required
                >

            </div>


            <div class="wa-info">

                <strong>
                    📱 Notifikasi WhatsApp
                </strong>

                <br><br>

                Setelah registrasi berhasil,
                sistem akan otomatis mengirimkan
                notifikasi WhatsApp ke nomor HP
                yang kamu masukkan.

            </div>


            <button
                type="submit"
                class="btn"
            >
                🎉 Daftar Sekarang
            </button>

        </form>


        <div class="login-link">

            Sudah punya akun?

            <a href="login.php">
                Login di sini
            </a>

        </div>


        <div class="footer">

            ASTS GALAXY SYSTEM © <?= date("Y") ?>

        </div>

    </div>

</div>

</body>

</html>