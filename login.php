<?php

session_start();

require_once __DIR__ . "/koneksi.php";

$fonnteAktif = false;

if (file_exists(__DIR__ . "/fonnte.php")) {
    require_once __DIR__ . "/fonnte.php";

    if (function_exists("kirimWhatsApp")) {
        $fonnteAktif = true;
    }
}

/* =========================================================
   VALIDASI KONEKSI
========================================================= */

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Koneksi database tidak valid.");
}

/* =========================================================
   JIKA SUDAH LOGIN
========================================================= */

if (
    isset($_SESSION["login"]) &&
    $_SESSION["login"] === true
) {
    header("Location: index.php");
    exit;
}

/* =========================================================
   VARIABEL
========================================================= */

$username = "";
$error = "";
$success = "";

$waNotif = $_SESSION["wa_notif"] ?? null;
unset($_SESSION["wa_notif"]);

/* =========================================================
   PROSES LOGIN
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        $error = "Username dan password wajib diisi.";

    } else {

        $stmt = $conn->prepare("
            SELECT 
                id,
                nama,
                username,
                email,
                no_hp,
                password
            FROM users_login
            WHERE username = ?
            LIMIT 1
        ");

        if (!$stmt) {

            $error = "Terjadi kesalahan sistem database.";

        } else {

            $stmt->bind_param("s", $username);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {

                $user = $result->fetch_assoc();

                if (password_verify($password, $user["password"])) {

                    session_regenerate_id(true);

                    $_SESSION["login"] = true;
                    $_SESSION["user_id"] = $user["id"];
                    $_SESSION["id"] = $user["id"];
                    $_SESSION["nama"] = $user["nama"];
                    $_SESSION["username"] = $user["username"];
                    $_SESSION["email"] = $user["email"];
                    $_SESSION["no_hp"] = $user["no_hp"];

                    /* =================================================
                       FORMAT NOMOR HP UNTUK WHATSAPP
                    ================================================= */

                    $no_hp = preg_replace("/[^0-9+]/", "", $user["no_hp"] ?? "");

                    if (str_starts_with($no_hp, "+")) {
                        $no_hp = substr($no_hp, 1);
                    }

                    if (str_starts_with($no_hp, "0")) {
                        $no_hp = "62" . substr($no_hp, 1);
                    }

                    /* =================================================
                       KIRIM NOTIFIKASI WHATSAPP
                    ================================================= */

                    if (
                        $fonnteAktif &&
                        $no_hp !== ""
                    ) {

                    $pesan = "🔐 *LOGIN BERHASIL* 🔐\n\n";

$pesan .= "Halo, *" . $user["nama"] . "* 👋\n";
$pesan .= "Kami ingin memberitahukan bahwa akun kamu berhasil melakukan login ke *Sistem Pendataan Siswa ASTS*.\n\n";

$pesan .= "╭───────────────╮\n";
$pesan .= "   🔐 *AKTIVITAS LOGIN*\n";
$pesan .= "╰───────────────╯\n\n";

$pesan .= "👤 *Nama:* " . $user["nama"] . "\n";
$pesan .= "🆔 *Username:* " . $user["username"] . "\n";
$pesan .= "📧 *Email:* " . $user["email"] . "\n";
$pesan .= "📱 *No. HP:* " . $user["no_hp"] . "\n";
$pesan .= "🕐 *Waktu:* " . date("d-m-Y H:i:s") . "\n\n";

$pesan .= "✅ *Status: Login berhasil*\n\n";

$pesan .= "📌 Akun kamu saat ini telah berhasil masuk ke dalam sistem.\n";
$pesan .= "Jika kamu tidak merasa melakukan login ini, segera hubungi admin untuk mengamankan akun kamu.\n\n";

$pesan .= "━━━━━━━━━━━━━━━━━━\n";
$pesan .= "🏫 *SISTEM PENDATAAN SISWA ASTS*\n";
$pesan .= "📱 Notifikasi Otomatis\n";
$pesan .= "━━━━━━━━━━━━━━━━━━\n\n";

$pesan .= "Terima kasih. 🙏\n";
$pesan .= "Semoga proses ASTS kamu berjalan lancar! ✨";

                        try {

                            $hasilWA = kirimWhatsApp(
                                $no_hp,
                                $pesan
                            );

                            if (
                                is_array($hasilWA) &&
                                isset($hasilWA["status"]) &&
                                $hasilWA["status"] === true
                            ) {

                                $_SESSION["login_wa"] =
                                    "Notifikasi WhatsApp berhasil dikirim.";

                            } else {

                                $_SESSION["login_wa"] =
                                    "Login berhasil, tetapi notifikasi WhatsApp tidak terkirim.";
                            }

                        } catch (Throwable $e) {

                            $_SESSION["login_wa"] =
                                "Login berhasil, tetapi WhatsApp sedang tidak tersedia.";
                        }

                    } else {

                        $_SESSION["login_wa"] =
                            "Login berhasil. Nomor WhatsApp belum tersedia.";
                    }

                    header("Location: index.php");
                    exit;

                } else {

                    $error = "Username atau password salah.";
                }

            } else {

                $error = "Username atau password salah.";
            }

            $stmt->close();
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

    <meta
        name="autocomplete"
        content="off"
    >

    <title>Login | ASTS Galaxy System</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --purple: #a855f7;
            --purple-light: #c084fc;
            --purple-dark: #7c3aed;
            --violet: #8b5cf6;
            --pink: #d946ef;

            --bg: #06030d;
            --bg2: #0d0718;

            --text: #ffffff;
            --muted: #a8a0b8;

            --border: rgba(168, 85, 247, .22);
            --glass: rgba(255,255,255,.055);

            --green: #25d366;
            --red: #fb7185;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;

            background:
                radial-gradient(
                    circle at 10% 20%,
                    rgba(124,58,237,.25),
                    transparent 32%
                ),
                radial-gradient(
                    circle at 90% 80%,
                    rgba(217,70,239,.18),
                    transparent 32%
                ),
                linear-gradient(
                    135deg,
                    var(--bg),
                    var(--bg2)
                );

            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
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

        .orb {
            position: fixed;
            width: 350px;
            height: 350px;

            border-radius: 50%;

            filter: blur(100px);
            opacity: .25;

            pointer-events: none;
        }

        .orb-one {
            background: var(--purple);
            top: -150px;
            left: -100px;
        }

        .orb-two {
            background: var(--pink);
            bottom: -150px;
            right: -100px;
        }

        .page {
            width: 100%;
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 30px;
        }

        .login-container {
            width: min(1100px, 100%);

            min-height: 650px;

            display: grid;
            grid-template-columns: 1.05fr .95fr;

            background: rgba(11,6,20,.82);

            border: 1px solid var(--border);

            border-radius: 28px;

            overflow: hidden;

            box-shadow:
                0 35px 100px rgba(0,0,0,.55),
                0 0 80px rgba(124,58,237,.12);

            backdrop-filter: blur(25px);

            position: relative;
            z-index: 2;
        }

        /* =====================================================
           LEFT SIDE
        ===================================================== */

        .left-panel {
            padding: 55px;

            display: flex;
            flex-direction: column;
            justify-content: center;

            background:
                linear-gradient(
                    145deg,
                    rgba(124,58,237,.22),
                    rgba(168,85,247,.05)
                );

            border-right: 1px solid var(--border);

            position: relative;
            overflow: hidden;
        }

        .left-panel::after {
            content: "";

            position: absolute;

            width: 280px;
            height: 280px;

            border-radius: 50%;

            right: -130px;
            top: -130px;

            background: rgba(168,85,247,.14);

            filter: blur(5px);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 14px;

            margin-bottom: 60px;

            position: relative;
            z-index: 2;
        }

        .logo-icon {
            width: 48px;
            height: 48px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 15px;

            background:
                linear-gradient(
                    135deg,
                    var(--purple),
                    var(--violet)
                );

            box-shadow:
                0 0 30px rgba(168,85,247,.45);

            font-size: 23px;
            font-weight: 900;
        }

        .logo-text strong {
            display: block;
            font-size: 17px;
            letter-spacing: 1px;
        }

        .logo-text span {
            display: block;
            color: var(--purple-light);
            font-size: 11px;
            margin-top: 3px;
            letter-spacing: 2px;
        }

        .welcome {
            position: relative;
            z-index: 2;
        }

        .welcome .eyebrow {
            color: var(--purple-light);

            font-size: 12px;
            font-weight: 700;

            letter-spacing: 3px;

            margin-bottom: 18px;
        }

        .welcome h1 {
            font-size: clamp(42px, 5vw, 68px);

            line-height: .95;

            letter-spacing: -3px;

            margin-bottom: 25px;

            background:
                linear-gradient(
                    135deg,
                    #fff,
                    #d8b4fe,
                    #a855f7
                );

            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome p {
            max-width: 430px;

            color: var(--muted);

            line-height: 1.8;

            font-size: 14px;
        }

        .system-card {
            margin-top: 45px;

            display: flex;
            align-items: center;
            gap: 15px;

            padding: 17px 20px;

            width: fit-content;

            border-radius: 16px;

            background: var(--glass);

            border: 1px solid var(--border);

            position: relative;
            z-index: 2;
        }

        .status-dot {
            width: 10px;
            height: 10px;

            border-radius: 50%;

            background: #4ade80;

            box-shadow:
                0 0 15px #4ade80;

            animation: pulse 2s infinite;
        }

        .system-card strong {
            font-size: 13px;
        }

        .system-card span {
            display: block;

            color: var(--muted);

            font-size: 11px;

            margin-top: 3px;
        }

        @keyframes pulse {
            0%,100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .45;
                transform: scale(.75);
            }
        }

        .wa-preview {
            margin-top: 18px;

            padding: 17px;

            max-width: 420px;

            border-radius: 16px;

            border: 1px solid rgba(37,211,102,.15);

            background: rgba(37,211,102,.035);

            position: relative;
            z-index: 2;
        }

        .wa-preview-title {
            display: flex;
            align-items: center;
            gap: 8px;

            color: #86efac;

            font-size: 12px;
            font-weight: 700;

            margin-bottom: 7px;
        }

        .wa-preview p {
            font-size: 11px;
            color: #8f8a99;
            line-height: 1.6;
        }

        /* =====================================================
           RIGHT SIDE
        ===================================================== */

        .right-panel {
            padding: 55px;

            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 32px;
        }

        .form-header h2 {
            font-size: 32px;
            letter-spacing: -1px;

            margin-bottom: 8px;
        }

        .form-header p {
            color: var(--muted);
            font-size: 13px;
        }

        .alert {
            padding: 14px 16px;

            border-radius: 13px;

            margin-bottom: 20px;

            font-size: 12px;
            line-height: 1.5;
        }

        .alert-error {
            background: rgba(251,113,133,.08);

            border: 1px solid rgba(251,113,133,.25);

            color: #fda4af;
        }

        .alert-success {
            background: rgba(74,222,128,.08);

            border: 1px solid rgba(74,222,128,.25);

            color: #86efac;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: flex;
            justify-content: space-between;

            margin-bottom: 9px;

            font-size: 12px;
            font-weight: 700;

            color: #ddd6e7;

            letter-spacing: .4px;
        }

        .form-label span {
            color: #81778e;

            font-size: 10px;

            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper input {
            width: 100%;

            height: 55px;

            padding:
                0 50px 0 17px;

            border-radius: 14px;

            border: 1px solid rgba(168,85,247,.17);

            background: rgba(255,255,255,.035);

            color: white;

            outline: none;

            font-size: 13px;

            transition:
                .25s ease;

            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.025);
        }

        .input-wrapper input::placeholder {
            color: #635b6c;
        }

        .input-wrapper input:focus {
            border-color: rgba(168,85,247,.65);

            background: rgba(168,85,247,.055);

            box-shadow:
                0 0 0 4px rgba(168,85,247,.08),
                0 0 30px rgba(168,85,247,.08);
        }

        .input-icon {
            position: absolute;

            right: 18px;
            top: 50%;

            transform: translateY(-50%);

            color: var(--purple-light);

            font-size: 15px;

            pointer-events: none;
        }

        .password-toggle {
            position: absolute;

            right: 43px;
            top: 50%;

            transform: translateY(-50%);

            border: 0;

            background: transparent;

            color: #8d8398;

            cursor: pointer;

            padding: 5px;
        }

        .login-button {
            width: 100%;

            height: 56px;

            margin-top: 8px;

            border: none;

            border-radius: 14px;

            cursor: pointer;

            color: white;

            font-weight: 800;

            letter-spacing: .8px;

            font-size: 12px;

            background:
                linear-gradient(
                    135deg,
                    var(--purple-dark),
                    var(--purple),
                    var(--pink)
                );

            box-shadow:
                0 15px 35px rgba(124,58,237,.28);

            transition:
                transform .2s ease,
                box-shadow .2s ease,
                opacity .2s ease;
        }

        .login-button:hover {
            transform: translateY(-2px);

            box-shadow:
                0 20px 45px rgba(168,85,247,.35);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button.loading {
            opacity: .7;
            cursor: wait;
        }

        .security {
            display: flex;
            align-items: flex-start;
            gap: 10px;

            margin-top: 20px;

            padding: 14px;

            border-radius: 13px;

            background: rgba(37,211,102,.035);

            border: 1px solid rgba(37,211,102,.10);
        }

        .security-icon {
            color: var(--green);
            font-size: 15px;
        }

        .security-text {
            color: #827a8c;

            font-size: 10px;

            line-height: 1.6;
        }

        .security-text strong {
            color: #b4adb9;
        }

        .register {
            margin-top: 28px;

            text-align: center;

            color: #756d7f;

            font-size: 12px;
        }

        .register a {
            color: var(--purple-light);

            text-decoration: none;

            font-weight: 700;

            margin-left: 4px;
        }

        .register a:hover {
            color: white;
        }

        .footer {
            margin-top: 35px;

            text-align: center;

            color: #514a58;

            font-size: 9px;

            letter-spacing: 1px;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 850px) {

            .login-container {
                grid-template-columns: 1fr;

                max-width: 550px;
            }

            .left-panel {
                display: none;
            }

            .right-panel {
                padding: 40px 30px;
            }
        }

        @media (max-width: 480px) {

            .page {
                padding: 15px;
            }

            .login-container {
                border-radius: 22px;
            }

            .right-panel {
                padding: 32px 22px;
            }

            .form-header h2 {
                font-size: 27px;
            }
        }

    </style>
</head>

<body>

<div class="orb orb-one"></div>
<div class="orb orb-two"></div>

<div class="page">

    <main class="login-container">

        <!-- =================================================
             LEFT PANEL
        ================================================== -->

        <section class="left-panel">

            <div class="logo">

                <div class="logo-icon">
                    ✦
                </div>

                <div class="logo-text">

                    <strong>ASTS GALAXY</strong>

                    <span>SYSTEM</span>

                </div>

            </div>

            <div class="welcome">

                <div class="eyebrow">
                    SECURE ACCESS
                </div>

                <h1>
                    Welcome<br>
                    Back.
                </h1>

                <p>
                    Masuk ke ASTS Galaxy System untuk
                    mengakses dashboard, data pengguna,
                    dan berbagai fitur sistem.
                </p>

            </div>

            <div class="system-card">

                <div class="status-dot"></div>

                <div>

                    <strong>
                        System Operational
                    </strong>

                    <span>
                        Semua layanan berjalan normal
                    </span>

                </div>

            </div>

            <div class="wa-preview">

                <div class="wa-preview-title">
                    <span>●</span>
                    WhatsApp Security Alert
                </div>

                <p>
                    Notifikasi keamanan akan dikirim
                    ke nomor HP yang terdaftar setiap
                    kali akun berhasil login.
                </p>

            </div>

        </section>


        <!-- =================================================
             RIGHT PANEL
        ================================================== -->

        <section class="right-panel">

            <div class="form-header">

                <h2>
                    Sign In
                </h2>

                <p>
                    Masukkan akun Anda untuk melanjutkan.
                </p>

            </div>


            <?php if ($error !== ""): ?>

                <div class="alert alert-error">
                    ⚠️
                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </div>

            <?php endif; ?>


            <?php if ($success !== ""): ?>

                <div class="alert alert-success">
                    ✓
                    <?= htmlspecialchars(
                        $success,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </div>

            <?php endif; ?>


            <?php if ($waNotif): ?>

                <div class="alert alert-success">

                    📱
                    <?= htmlspecialchars(
                        $waNotif,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>

                </div>

            <?php endif; ?>


            <!-- FORM LOGIN -->
            <form
                method="POST"
                action="login.php"
                autocomplete="off"
                id="loginForm"
            >

                <div class="form-group">

                    <label class="form-label">

                        Username

                        <span>
                            Required
                        </span>

                    </label>

                    <div class="input-wrapper">

                        <input
                            type="text"
                            name="username"
                            value=""
                            placeholder="Masukkan username"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="none"
                            spellcheck="false"
                            required
                        >

                        <span class="input-icon">
                            ◉
                        </span>

                    </div>

                </div>


                <div class="form-group">

                    <label class="form-label">

                        Password

                        <span>
                            Protected
                        </span>

                    </label>

                    <div class="input-wrapper">

                        <input
                            type="password"
                            name="password"
                            id="password"
                            value=""
                            placeholder="Masukkan password"
                            autocomplete="new-password"
                            autocorrect="off"
                            autocapitalize="none"
                            spellcheck="false"
                            required
                        >

                        <span class="input-icon">
                            ◆
                        </span>

                        <button
                            type="button"
                            class="password-toggle"
                            id="togglePassword"
                            aria-label="Tampilkan password"
                        >
                            ◉
                        </button>

                    </div>

                </div>


                <button
                    type="submit"
                    class="login-button"
                    id="loginButton"
                >

                    <span id="buttonText">
                        SIGN IN TO GALAXY →
                    </span>

                </button>

            </form>


            <div class="security">

                <div class="security-icon">
                    ✓
                </div>

                <div class="security-text">

                    <strong>
                        Secure Session
                    </strong>
                    <br>

                    Session login dilindungi dan
                    notifikasi keamanan dapat dikirim
                    melalui WhatsApp.

                </div>

            </div>


            <div class="register">

                Belum memiliki akun?

                <a href="register.php">
                    Buat akun →
                </a>

            </div>


            <div class="footer">

                ASTS GALAXY SYSTEM • SECURE LOGIN

            </div>

        </section>

    </main>

</div>


<script>

/* =========================================================
   PASSWORD TOGGLE
========================================================= */

const passwordInput =
    document.getElementById("password");

const togglePassword =
    document.getElementById("togglePassword");

if (togglePassword && passwordInput) {

    togglePassword.addEventListener(
        "click",
        function () {

            if (passwordInput.type === "password") {

                passwordInput.type = "text";

                togglePassword.textContent = "◎";

                togglePassword.setAttribute(
                    "aria-label",
                    "Sembunyikan password"
                );

            } else {

                passwordInput.type = "password";

                togglePassword.textContent = "◉";

                togglePassword.setAttribute(
                    "aria-label",
                    "Tampilkan password"
                );
            }

        }
    );

}


/* =========================================================
   HINDARI AUTOFILL / INPUT TERSISA
========================================================= */

window.addEventListener(
    "pageshow",
    function () {

        const usernameInput =
            document.querySelector(
                'input[name="username"]'
            );

        const passwordInput =
            document.querySelector(
                'input[name="password"]'
            );

        if (usernameInput) {
            usernameInput.value = "";
        }

        if (passwordInput) {
            passwordInput.value = "";
        }

    }
);


/* =========================================================
   LOGIN LOADING
========================================================= */

const loginForm =
    document.getElementById("loginForm");

const loginButton =
    document.getElementById("loginButton");

const buttonText =
    document.getElementById("buttonText");

if (loginForm) {

    loginForm.addEventListener(
        "submit",
        function () {

            if (
                !loginButton ||
                !buttonText
            ) {
                return;
            }

            loginButton.classList.add("loading");

            loginButton.disabled = true;

            buttonText.textContent =
                "AUTHENTICATING...";

        }
    );

}

</script>

</body>
</html>