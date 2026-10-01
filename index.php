<?php

session_start();
require_once "koneksi.php";

/* =====================================================
   CEK LOGIN
===================================================== */
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: login.php");
    exit;
}

$namaUser     = $_SESSION["nama"] ?? "Pengguna";
$usernameUser = $_SESSION["username"] ?? "";

/* =====================================================
   STATISTIK
===================================================== */
$totalSiswa     = 0;
$totalLaki      = 0;
$totalPerempuan = 0;

$queryTotal = $conn->query("
    SELECT
        COUNT(*) AS total,

        SUM(
            CASE
                WHEN LOWER(TRIM(gender))
                IN ('laki-laki', 'laki laki', 'male', 'l')
                THEN 1 ELSE 0
            END
        ) AS laki,

        SUM(
            CASE
                WHEN LOWER(TRIM(gender))
                IN ('perempuan', 'female', 'p')
                THEN 1 ELSE 0
            END
        ) AS perempuan

    FROM users
");

if ($queryTotal) {
    $statistik = $queryTotal->fetch_assoc();

    $totalSiswa     = (int)($statistik["total"] ?? 0);
    $totalLaki      = (int)($statistik["laki"] ?? 0);
    $totalPerempuan = (int)($statistik["perempuan"] ?? 0);
}

/* =====================================================
   DATA SISWA
===================================================== */
$dataSiswa = [];

$querySiswa = $conn->query("
    SELECT
        id,
        name,
        nisn,
        ttl,
        gender,
        email,
        address,
        foto
    FROM users
    ORDER BY id DESC
");

if ($querySiswa) {

    while ($row = $querySiswa->fetch_assoc()) {

        $dataSiswa[] = [
            "id"      => (int)$row["id"],
            "name"    => $row["name"] ?? "",
            "nisn"    => $row["nisn"] ?? "",
            "ttl"     => $row["ttl"] ?? "",
            "gender"  => $row["gender"] ?? "",
            "email"   => $row["email"] ?? "",
            "address" => $row["address"] ?? "",
            "foto"    => $row["foto"] ?? ""
        ];
    }
}

/* =====================================================
   NOTIFIKASI LOGIN / WHATSAPP
===================================================== */
$loginWa = $_SESSION["login_wa"] ?? null;

if ($loginWa !== null) {
    unset($_SESSION["login_wa"]);
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

<title>ASTS Galaxy System | Dashboard</title>

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

    color: #eef2ff;

    background:
        radial-gradient(
            circle at 10% 10%,
            rgba(99,102,241,.22),
            transparent 28%
        ),

        radial-gradient(
            circle at 85% 15%,
            rgba(168,85,247,.18),
            transparent 25%
        ),

        radial-gradient(
            circle at 70% 90%,
            rgba(14,165,233,.14),
            transparent 30%
        ),

        linear-gradient(
            135deg,
            #050816,
            #0a1024 45%,
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

    background-size: 42px 42px;

    z-index: -3;
}

body::after {

    content: "";

    position: fixed;

    width: 550px;
    height: 550px;

    right: -220px;
    top: 25%;

    border-radius: 50%;

    background:
        rgba(99,102,241,.12);

    filter: blur(110px);

    pointer-events: none;

    z-index: -2;
}

/* =====================================================
   SCROLLBAR
===================================================== */

::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #050816;
}

::-webkit-scrollbar-thumb {

    background:
        linear-gradient(
            180deg,
            #6366f1,
            #a855f7
        );

    border-radius: 20px;
}

::-webkit-scrollbar-thumb:hover {
    background: #818cf8;
}

/* =====================================================
   SIDEBAR
===================================================== */

.sidebar {

    position: fixed;

    left: 18px;
    top: 18px;
    bottom: 18px;

    width: 250px;

    padding: 22px 15px;

    display: flex;
    flex-direction: column;

    background:

        linear-gradient(
            180deg,
            rgba(15,23,42,.96),
            rgba(7,12,28,.98)
        );

    border:
        1px solid
        rgba(129,140,248,.20);

    border-radius: 26px;

    box-shadow:

        0 30px 80px
        rgba(0,0,0,.55),

        inset 0 1px 0
        rgba(255,255,255,.04);

    backdrop-filter: blur(25px);

    z-index: 100;
}

/* =====================================================
   LOGO
===================================================== */

.logo {

    display: flex;

    align-items: center;

    gap: 12px;

    padding:
        4px 9px 24px;

    border-bottom:
        1px solid
        rgba(255,255,255,.06);
}

.logo-icon {

    width: 48px;
    height: 48px;

    display: grid;
    place-items: center;

    border-radius: 15px;

    color: white;

    background:

        linear-gradient(
            135deg,
            #6366f1,
            #8b5cf6,
            #a855f7
        );

    box-shadow:

        0 0 30px
        rgba(99,102,241,.35);

    font-size: 16px;

    font-weight: 800;

    animation:
        logoFloat 3s ease-in-out infinite;
}

@keyframes logoFloat {

    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-4px);
    }
}

.logo-text strong {

    display: block;

    color: white;

    font-size: 15px;

    letter-spacing: 1px;
}

.logo-text small {

    display: block;

    margin-top: 4px;

    color: #7c89a8;

    font-size: 9px;

    letter-spacing: 1px;
}

/* =====================================================
   MENU TITLE
===================================================== */

.menu-title {

    padding:
        23px 10px 10px;

    color: #64748b;

    font-size: 9px;

    font-weight: 700;

    letter-spacing: 2px;

    text-transform: uppercase;
}

/* =====================================================
   MENU
===================================================== */

.menu {

    position: relative;

    display: flex;

    align-items: center;

    gap: 12px;

    margin-bottom: 7px;

    padding: 12px 13px;

    color: #94a3b8;

    text-decoration: none;

    border-radius: 12px;

    font-size: 11px;

    transition:
        .25s ease;
}

.menu-icon {

    width: 31px;
    height: 31px;

    display: grid;
    place-items: center;

    flex-shrink: 0;

    color: #94a3b8;

    background:
        rgba(255,255,255,.035);

    border:
        1px solid
        rgba(255,255,255,.04);

    border-radius: 9px;

    font-size: 12px;

    transition: .25s ease;
}

.menu:hover {

    color: white;

    background:
        rgba(99,102,241,.10);

    transform:
        translateX(4px);
}

.menu:hover .menu-icon {

    color: #a5b4fc;

    background:
        rgba(99,102,241,.16);

    border-color:
        rgba(129,140,248,.25);
}

.menu.active {

    color: white;

    background:

        linear-gradient(
            90deg,
            rgba(99,102,241,.22),
            rgba(139,92,246,.07)
        );

    border:
        1px solid
        rgba(129,140,248,.22);

    box-shadow:

        inset 3px 0 0 #818cf8,

        0 10px 25px
        rgba(99,102,241,.08);
}

.menu.active .menu-icon {

    color: white;

    background:

        linear-gradient(
            135deg,
            #6366f1,
            #8b5cf6
        );

    border-color:
        transparent;

    box-shadow:
        0 5px 18px
        rgba(99,102,241,.25);
}

/* =====================================================
   LOGOUT
===================================================== */

.menu.logout {

    margin-top: auto;

    color: #fb7185;

    border:
        1px solid
        rgba(244,63,94,.10);
}

.menu.logout:hover {

    color: #fda4af;

    background:
        rgba(244,63,94,.08);

    border-color:
        rgba(244,63,94,.20);
}

.menu.logout .menu-icon {

    color: #fb7185;
}

/* =====================================================
   MAIN
===================================================== */

.main {

    margin-left: 285px;

    padding:
        28px 32px 45px 15px;
}

/* =====================================================
   TOPBAR
===================================================== */

.topbar {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 25px;

    margin-bottom: 25px;
}

.title-area h1 {

    color: white;

    font-size: 31px;

    line-height: 1.2;

    letter-spacing: -.8px;
}

.title-area h1 span {

    color: #818cf8;

    text-shadow:
        0 0 25px
        rgba(129,140,248,.25);
}

.title-area p {

    margin-top: 8px;

    color: #71809d;

    font-size: 11px;
}

/* =====================================================
   TOP ACTIONS
===================================================== */

.top-actions {

    display: flex;

    align-items: center;

    gap: 12px;
}

.clock {

    padding:
        11px 15px;

    color: #a5b4fc;

    background:
        rgba(99,102,241,.08);

    border:
        1px solid
        rgba(129,140,248,.18);

    border-radius: 12px;

    font-size: 10px;

    box-shadow:
        inset 0 1px 0
        rgba(255,255,255,.03);
}

.profile {

    display: flex;

    align-items: center;

    gap: 10px;

    padding:
        6px 15px 6px 6px;

    background:
        rgba(15,23,42,.82);

    border:
        1px solid
        rgba(129,140,248,.17);

    border-radius: 999px;

    box-shadow:
        0 10px 25px
        rgba(0,0,0,.20);
}

.avatar {

    width: 40px;
    height: 40px;

    display: grid;
    place-items: center;

    border-radius: 50%;

    color: white;

    background:

        linear-gradient(
            135deg,
            #6366f1,
            #a855f7
        );

    font-weight: 800;

    box-shadow:
        0 0 20px
        rgba(99,102,241,.25);
}

.profile b {

    display: block;

    color: #f8fafc;

    font-size: 11px;
}

.profile small {

    display: block;

    margin-top: 3px;

    color: #71809d;

    font-size: 9px;
}

/* =====================================================
   WHATSAPP NOTIFICATION
===================================================== */

.wa-alert {

    position: relative;

    display: flex;

    align-items: center;

    gap: 13px;

    margin-bottom: 20px;

    padding: 15px 18px;

    border-radius: 15px;

    animation:
        alertIn .5s ease;
}

@keyframes alertIn {

    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.wa-alert.success {

    color: #bbf7d0;

    background:
        rgba(34,197,94,.08);

    border:
        1px solid
        rgba(34,197,94,.20);
}

.wa-alert.error {

    color: #fecaca;

    background:
        rgba(239,68,68,.08);

    border:
        1px solid
        rgba(239,68,68,.20);
}

.wa-icon {

    width: 38px;
    height: 38px;

    display: grid;
    place-items: center;

    flex-shrink: 0;

    border-radius: 11px;

    background:
        rgba(255,255,255,.06);

    font-size: 17px;
}

.wa-alert strong {

    display: block;

    margin-bottom: 3px;

    font-size: 11px;
}

.wa-alert small {

    color: #94a3b8;

    font-size: 9px;
}

/* =====================================================
   API PANEL
===================================================== */

.api-panel {

    overflow: hidden;

    margin-bottom: 20px;

    background:

        linear-gradient(
            135deg,
            rgba(15,23,42,.95),
            rgba(8,13,30,.96)
        );

    border:
        1px solid
        rgba(129,140,248,.18);

    border-radius: 18px;

    box-shadow:
        0 25px 60px
        rgba(0,0,0,.25);
}

.api-top {

    height: 40px;

    display: flex;

    align-items: center;

    gap: 7px;

    padding:
        0 15px;

    background:
        rgba(0,0,0,.18);

    border-bottom:
        1px solid
        rgba(255,255,255,.05);
}

.dot {

    width: 9px;
    height: 9px;

    border-radius: 50%;
}

.dot.red {
    background: #fb7185;
}

.dot.yellow {
    background: #facc15;
}

.dot.green {

    background: #4ade80;

    box-shadow:
        0 0 12px
        rgba(74,222,128,.75);
}

.api-file {

    margin-left: auto;

    color: #64748b;

    font-size: 9px;
}

.api-content {

    padding: 20px;
}

.api-line {

    display: flex;

    align-items: center;

    gap: 10px;
}

.method {

    padding:
        6px 10px;

    color: #a5b4fc;

    background:
        rgba(99,102,241,.10);

    border:
        1px solid
        rgba(129,140,248,.20);

    border-radius: 7px;

    font-size: 10px;

    font-weight: 800;
}

.endpoint {

    color: #e0e7ff;

    font-size: 12px;
}

.api-description {

    margin-top: 10px;

    color: #71809d;

    font-size: 10px;
}

.api-status {

    display: flex;

    align-items: center;

    gap: 8px;

    margin-top: 15px;

    color: #86efac;

    font-size: 9px;

    font-weight: 600;
}

.status-dot {

    width: 7px;
    height: 7px;

    border-radius: 50%;

    background: #4ade80;

    box-shadow:
        0 0 12px
        #4ade80;
}

/* =====================================================
   STATISTICS
===================================================== */

.stats {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 14px;

    margin-bottom: 20px;
}

.stat-card {

    position: relative;

    overflow: hidden;

    min-height: 150px;

    padding: 19px;

    background:

        linear-gradient(
            145deg,
            rgba(15,23,42,.95),
            rgba(8,13,28,.97)
        );

    border:
        1px solid
        rgba(129,140,248,.14);

    border-radius: 17px;

    transition:
        .25s ease;
}

.stat-card::before {

    content: "";

    position: absolute;

    left: 0;
    right: 0;
    top: 0;

    height: 2px;

    background:

        linear-gradient(
            90deg,
            transparent,
            #6366f1,
            #a855f7,
            transparent
        );
}

.stat-card::after {

    content: "";

    position: absolute;

    width: 120px;
    height: 120px;

    right: -50px;
    bottom: -50px;

    border-radius: 50%;

    background:
        rgba(99,102,241,.09);

    filter: blur(10px);
}

.stat-card:hover {

    transform:
        translateY(-6px);

    border-color:
        rgba(129,140,248,.32);

    box-shadow:

        0 20px 45px
        rgba(0,0,0,.35),

        0 0 30px
        rgba(99,102,241,.06);
}

.stat-top {

    display: flex;

    align-items: center;

    justify-content: space-between;
}

.stat-label {

    color: #7c89a8;

    font-size: 9px;

    letter-spacing: 1px;

    text-transform: uppercase;
}

.stat-icon {

    width: 36px;
    height: 36px;

    display: grid;
    place-items: center;

    color: #a5b4fc;

    background:
        rgba(99,102,241,.09);

    border:
        1px solid
        rgba(129,140,248,.16);

    border-radius: 10px;

    font-size: 13px;

    font-weight: 800;
}

.stat-number {

    margin-top: 16px;

    color: #e0e7ff;

    font-size: 31px;

    font-weight: 800;

    letter-spacing: -1px;

    text-shadow:
        0 0 20px
        rgba(129,140,248,.15);
}

.stat-number.ok {

    color: #86efac;

    font-size: 17px;

    letter-spacing: 1px;
}

.stat-sub {

    margin-top: 7px;

    color: #53627c;

    font-size: 9px;
}

/* =====================================================
   CONTENT CARD
===================================================== */

.content-card {

    padding: 22px;

    background:

        linear-gradient(
            145deg,
            rgba(15,23,42,.97),
            rgba(7,12,26,.98)
        );

    border:
        1px solid
        rgba(129,140,248,.16);

    border-radius: 19px;

    box-shadow:
        0 25px 65px
        rgba(0,0,0,.28);
}

.card-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 18px;

    margin-bottom: 18px;
}

.card-header h2 {

    color: #f8fafc;

    font-size: 16px;
}

.card-header h2 span {

    color: #818cf8;
}

.card-header p {

    margin-top: 5px;

    color: #64748b;

    font-size: 9px;
}

/* =====================================================
   TOOLS
===================================================== */

.tools {

    display: flex;

    align-items: center;

    gap: 9px;
}

.search {

    height: 41px;

    width: 270px;

    display: flex;

    align-items: center;

    gap: 9px;

    padding:
        0 13px;

    background:
        rgba(2,6,23,.80);

    border:
        1px solid
        #25304a;

    border-radius: 10px;

    transition: .2s;
}

.search:focus-within {

    border-color:
        #6366f1;

    box-shadow:
        0 0 0 3px
        rgba(99,102,241,.08);
}

.search span {

    color: #818cf8;

    font-weight: 800;
}

.search input {

    width: 100%;

    border: none;

    outline: none;

    background: transparent;

    color: #e2e8f0;

    font-family: inherit;

    font-size: 10px;
}

.search input::placeholder {
    color: #475569;
}

.btn-tambah {

    height: 41px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    padding:
        0 16px;

    color: white;

    background:

        linear-gradient(
            135deg,
            #6366f1,
            #8b5cf6
        );

    border:
        1px solid
        rgba(165,180,252,.25);

    border-radius: 10px;

    text-decoration: none;

    font-size: 10px;

    font-weight: 700;

    box-shadow:
        0 8px 22px
        rgba(99,102,241,.18);

    transition: .25s;
}

.btn-tambah:hover {

    transform:
        translateY(-2px);

    background:

        linear-gradient(
            135deg,
            #818cf8,
            #a855f7
        );

    box-shadow:
        0 12px 30px
        rgba(99,102,241,.28);
}

/* =====================================================
   TABLE
===================================================== */

.table-container {

    overflow-x: auto;

    background:
        rgba(2,6,23,.75);

    border:
        1px solid
        #1e293b;

    border-radius: 13px;
}

table {

    width: 100%;

    min-width: 1150px;

    border-collapse: collapse;
}

thead {

    background:

        linear-gradient(
            90deg,
            rgba(99,102,241,.11),
            rgba(139,92,246,.05)
        );
}

th {

    padding:
        14px 12px;

    color: #a5b4fc;

    border-bottom:
        1px solid
        #25304a;

    text-align: left;

    font-size: 8px;

    letter-spacing: 1px;

    text-transform: uppercase;
}

td {

    padding:
        12px;

    color: #94a3b8;

    border-bottom:
        1px solid
        rgba(30,41,59,.75);

    font-size: 10px;

    vertical-align: middle;
}

tbody tr {

    transition: .2s;
}

tbody tr:hover {

    background:

        linear-gradient(
            90deg,
            rgba(99,102,241,.08),
            transparent
        );
}

tbody tr:last-child td {
    border-bottom: none;
}

/* =====================================================
   ID
===================================================== */

.id-badge {

    display: inline-block;

    padding:
        5px 8px;

    color: #a5b4fc;

    background:
        rgba(99,102,241,.08);

    border:
        1px solid
        rgba(129,140,248,.15);

    border-radius: 7px;

    font-size: 9px;
}

/* =====================================================
   FOTO
===================================================== */

.foto-cell {

    width: 70px;

    text-align: center;
}

.foto-siswa,
.foto-placeholder {

    width: 45px;
    height: 45px;

    border-radius: 12px;
}

.foto-siswa {

    display: inline-block;

    object-fit: cover;

    border:
        1px solid
        #6366f1;

    box-shadow:
        0 0 18px
        rgba(99,102,241,.15);

    transition: .2s;
}

.foto-siswa:hover {

    transform:
        scale(1.12);

    box-shadow:
        0 0 25px
        rgba(99,102,241,.30);
}

.foto-placeholder {

    display: inline-grid;

    place-items: center;

    color: #c4b5fd;

    background:

        linear-gradient(
            135deg,
            rgba(99,102,241,.16),
            rgba(168,85,247,.08)
        );

    border:
        1px solid
        rgba(129,140,248,.25);

    font-size: 14px;

    font-weight: 800;
}

/* =====================================================
   DATA
===================================================== */

.name {

    color: #f1f5f9;

    font-weight: 700;
}

.email {
    color: #93c5fd;
}

.addr {

    max-width: 230px;

    color: #64748b;
}

/* =====================================================
   GENDER
===================================================== */

.gender {

    display: inline-block;

    padding:
        5px 9px;

    border-radius: 7px;

    font-size: 8px;

    font-weight: 700;
}

.gender.male {

    color: #7dd3fc;

    background:
        rgba(14,165,233,.08);

    border:
        1px solid
        rgba(14,165,233,.18);
}

.gender.female {

    color: #f0abfc;

    background:
        rgba(217,70,239,.08);

    border:
        1px solid
        rgba(217,70,239,.18);
}

.gender.other {

    color: #cbd5e1;

    background:
        rgba(148,163,184,.06);

    border:
        1px solid
        rgba(148,163,184,.12);
}

/* =====================================================
   ACTION
===================================================== */

.actions {
    white-space: nowrap;
}

.btn {

    display: inline-block;

    padding:
        6px 10px;

    margin: 2px;

    border-radius: 7px;

    text-decoration: none;

    font-size: 8px;

    font-weight: 700;

    transition: .2s;
}

.edit {

    color: #fde68a;

    background:
        rgba(234,179,8,.07);

    border:
        1px solid
        rgba(234,179,8,.17);
}

.edit:hover {

    background:
        rgba(234,179,8,.15);

    transform:
        translateY(-2px);
}

.hapus {

    color: #fda4af;

    background:
        rgba(244,63,94,.07);

    border:
        1px solid
        rgba(244,63,94,.17);
}

.hapus:hover {

    background:
        rgba(244,63,94,.15);

    transform:
        translateY(-2px);
}

/* =====================================================
   LOADING
===================================================== */

.loading {

    padding: 48px !important;

    color: #818cf8 !important;

    text-align: center;

    font-size: 10px !important;
}

/* =====================================================
   PAGINATION
===================================================== */

.pagination {

    display: flex;

    justify-content: center;

    align-items: center;

    flex-wrap: wrap;

    gap: 6px;

    margin-top: 18px;
}

.page-btn {

    min-width: 35px;

    height: 35px;

    padding:
        0 10px;

    color: #94a3b8;

    background:
        rgba(15,23,42,.75);

    border:
        1px solid
        #263149;

    border-radius: 8px;

    font-family: inherit;

    font-size: 9px;

    cursor: pointer;

    transition: .2s;
}

.page-btn:hover:not(:disabled),
.page-btn.active {

    color: white;

    background:

        linear-gradient(
            135deg,
            #6366f1,
            #8b5cf6
        );

    border-color:
        transparent;

    box-shadow:
        0 5px 18px
        rgba(99,102,241,.20);
}

.page-btn:disabled {

    opacity: .25;

    cursor: not-allowed;
}

.page-info {

    width: 100%;

    margin-top: 6px;

    color: #475569;

    text-align: center;

    font-size: 8px;
}

/* =====================================================
   FOOTER
===================================================== */

.footer {

    margin-top: 20px;

    color: #475569;

    text-align: center;

    font-size: 8px;

    letter-spacing: 1px;
}

.footer span {
    color: #818cf8;
}

/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1200px) {

    .stats {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .clock {
        display: none;
    }
}

@media (max-width: 950px) {

    .sidebar {

        width: 72px;

        padding:
            20px 9px;

        align-items: center;
    }

    .logo {
        padding:
            4px 0 22px;
    }

    .logo-text,
    .menu-title,
    .menu-text {
        display: none;
    }

    .menu {

        width: 48px;

        justify-content: center;

        padding: 9px;
    }

    .menu-icon {

        width: 32px;
        height: 32px;
    }

    .main {

        margin-left: 100px;

        padding:
            22px 16px 35px 5px;
    }

    .topbar {

        align-items:
            flex-start;

        flex-direction:
            column;
    }

    .top-actions {
        width: 100%;
    }

    .card-header {

        align-items:
            flex-start;

        flex-direction:
            column;
    }

    .tools {
        width: 100%;
    }

    .search {
        flex: 1;
    }
}

@media (max-width: 600px) {

    .sidebar {

        left: 8px;
        top: 8px;
        bottom: 8px;
    }

    .main {

        margin-left: 88px;

        padding-right: 10px;
    }

    .title-area h1 {
        font-size: 23px;
    }

    .stats {
        grid-template-columns: 1fr;
    }

    .content-card {
        padding: 14px;
    }

    .tools {

        flex-direction:
            column;

        align-items:
            stretch;
    }

    .search {

        width: 100%;
    }

    .btn-tambah {
        width: 100%;
    }

    .profile {
        width: 100%;
    }

    .api-content {
        padding: 15px;
    }
}

</style>

</head>

<body>

<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar">

    <div class="logo">

        <div class="logo-icon">
            ✦
        </div>

        <div class="logo-text">

            <strong>
                ASTS GALAXY
            </strong>

            <small>
                STUDENT MANAGEMENT
            </small>

        </div>

    </div>


    <div class="menu-title">
        Navigation
    </div>


    <a
        href="index.php"
        class="menu active"
    >

        <span class="menu-icon">
            ◈
        </span>

        <span class="menu-text">
            Dashboard
        </span>

    </a>


    <a
        href="data_json.php"
        class="menu"
    >

        <span class="menu-icon">
            { }
        </span>

        <span class="menu-text">
            Data JSON
        </span>

    </a>


    <a
        href="tambah.php"
        class="menu"
    >

        <span class="menu-icon">
            ＋
        </span>

        <span class="menu-text">
            Tambah Data
        </span>

    </a>


    <a
        href="logout.php"
        class="menu logout"
        onclick="return confirm('Yakin ingin logout dari sistem?')"
    >

        <span class="menu-icon">
            ↪
        </span>

        <span class="menu-text">
            Logout
        </span>

    </a>

</aside>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="main">


    <!-- =================================================
         TOPBAR
    ================================================== -->

    <div class="topbar">

        <div class="title-area">

            <h1>
                ASTS
                <span>
                    Dashboard
                </span>
            </h1>

            <p>
                Sistem Pendataan Siswa • Student Management System
            </p>

        </div>


        <div class="top-actions">

            <div class="clock">
                <span id="jam">
                    00:00:00
                </span>
            </div>


            <div class="profile">

                <div class="avatar">

                    <?= htmlspecialchars(
                        strtoupper(
                            mb_substr(
                                $namaUser,
                                0,
                                1
                            )
                        )
                    ) ?>

                </div>


                <div>

                    <b>
                        <?= htmlspecialchars($namaUser) ?>
                    </b>

                    <?php if ($usernameUser !== ""): ?>

                        <small>
                            @<?= htmlspecialchars($usernameUser) ?>
                        </small>

                    <?php else: ?>

                        <small>
                            ASTS USER
                        </small>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>


    <!-- =================================================
         WHATSAPP LOGIN STATUS
    ================================================== -->

    <?php if ($loginWa !== null): ?>

        <?php
            $waStatus = $loginWa["status"] ?? false;
            $waMessage =
                $loginWa["message"]
                ?? $loginWa["response"]
                ?? "";
        ?>

        <div
            class="wa-alert <?= $waStatus ? 'success' : 'error' ?>"
        >

            <div class="wa-icon">
                <?= $waStatus ? "✓" : "!" ?>
            </div>

            <div>

                <strong>
                    <?= $waStatus
                        ? "Login berhasil • WhatsApp terkirim"
                        : "Login berhasil • WhatsApp tidak terkirim"
                    ?>
                </strong>

                <?php if ($waMessage !== ""): ?>

                    <small>
                        <?= htmlspecialchars(
                            is_scalar($waMessage)
                            ? (string)$waMessage
                            : json_encode($waMessage)
                        ) ?>
                    </small>

                <?php else: ?>

                    <small>
                        Status notifikasi login telah diproses oleh sistem.
                    </small>

                <?php endif; ?>

            </div>

        </div>

    <?php endif; ?>


    <!-- =================================================
         API PANEL
    ================================================== -->

    <section class="api-panel">

        <div class="api-top">

            <span class="dot red"></span>
            <span class="dot yellow"></span>
            <span class="dot green"></span>

            <span class="api-file">
                dashboard.php
            </span>

        </div>


        <div class="api-content">

            <div class="api-line">

                <span class="method">
                    GET
                </span>

                <span class="endpoint">
                    /api/siswa
                </span>

            </div>


            <div class="api-description">

                Endpoint untuk mengambil seluruh data siswa
                yang tersimpan pada database ASTS.

            </div>


            <div class="api-status">

                <span class="status-dot"></span>

                DATABASE CONNECTED • API ONLINE

            </div>

        </div>

    </section>


    <!-- =================================================
         STATISTIK
    ================================================== -->

    <section class="stats">


        <div class="stat-card">

            <div class="stat-top">

                <span class="stat-label">
                    Total Siswa
                </span>

                <div class="stat-icon">
                    #
                </div>

            </div>

            <div
                class="stat-number"
                id="totalSiswa"
            >
                <?= $totalSiswa ?>
            </div>

            <div class="stat-sub">
                Total data dalam database
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-top">

                <span class="stat-label">
                    Laki-laki
                </span>

                <div class="stat-icon">
                    ♂
                </div>

            </div>

            <div
                class="stat-number"
                id="totalLaki"
            >
                <?= $totalLaki ?>
            </div>

            <div class="stat-sub">
                Data siswa laki-laki
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-top">

                <span class="stat-label">
                    Perempuan
                </span>

                <div class="stat-icon">
                    ♀
                </div>

            </div>

            <div
                class="stat-number"
                id="totalPerempuan"
            >
                <?= $totalPerempuan ?>
            </div>

            <div class="stat-sub">
                Data siswa perempuan
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-top">

                <span class="stat-label">
                    API Status
                </span>

                <div class="stat-icon">
                    ✓
                </div>

            </div>

            <div class="stat-number ok">
                ONLINE
            </div>

            <div class="stat-sub">
                System operational
            </div>

        </div>


    </section>


    <!-- =================================================
         DATA SISWA
    ================================================== -->

    <section class="content-card">

        <div class="card-header">

            <div>

                <h2>
                    GET
                    <span>
                        /api/siswa
                    </span>
                </h2>

                <p>
                    Daftar data siswa yang tersedia pada sistem
                </p>

            </div>


            <div class="tools">

                <label class="search">

                    <span>
                        ⌕
                    </span>

                    <input
                        type="text"
                        id="cari"
                        placeholder="Cari nama / NISN / email..."
                        autocomplete="off"
                    >

                </label>


                <a
                    href="tambah.php"
                    class="btn-tambah"
                >
                    ＋ Tambah Data
                </a>

            </div>

        </div>


        <!-- =================================================
             TABLE
        ================================================== -->

        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            Foto
                        </th>

                        <th>
                            Nama
                        </th>

                        <th>
                            NISN
                        </th>

                        <th>
                            TTL
                        </th>

                        <th>
                            Gender
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Alamat
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody id="hasil">

                    <tr>

                        <td
                            colspan="9"
                            class="loading"
                        >
                            Memuat data siswa...
                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        <!-- =================================================
             PAGINATION
        ================================================== -->

        <div
            class="pagination"
            id="pagination"
        ></div>

    </section>


    <!-- =================================================
         FOOTER
    ================================================== -->

    <div class="footer">

        ASTS GALAXY SYSTEM

        <span>•</span>

        DATABASE CONNECTED

        <span>•</span>

        PHP + MYSQL

        <span>•</span>

        2026

    </div>

</main>


<script>

/* =====================================================
   DATA PHP
===================================================== */

const allData = <?= json_encode(
    $dataSiswa,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES |
    JSON_HEX_TAG |
    JSON_HEX_AMP |
    JSON_HEX_APOS |
    JSON_HEX_QUOT
) ?>;

let filteredData = allData.slice();

let currentPage = 1;

const rowsPerPage = 5;


/* =====================================================
   JAM DIGITAL
===================================================== */

function updateClock() {

    const now = new Date();

    const jam =
        String(now.getHours())
        .padStart(2, "0");

    const menit =
        String(now.getMinutes())
        .padStart(2, "0");

    const detik =
        String(now.getSeconds())
        .padStart(2, "0");

    const clock =
        document.getElementById("jam");

    if (clock) {

        clock.textContent =
            `${jam}:${menit}:${detik}`;
    }
}

updateClock();

setInterval(
    updateClock,
    1000
);


/* =====================================================
   ESCAPE HTML
===================================================== */

function escapeHTML(value) {

    return String(value ?? "")

        .replace(
            /&/g,
            "&amp;"
        )

        .replace(
            /</g,
            "&lt;"
        )

        .replace(
            />/g,
            "&gt;"
        )

        .replace(
            /"/g,
            "&quot;"
        )

        .replace(
            /'/g,
            "&#039;"
        );
}


/* =====================================================
   GENDER CLASS
===================================================== */

function genderClass(g) {

    g = String(g ?? "")
        .toLowerCase()
        .trim();

    if (
        [
            "laki-laki",
            "laki laki",
            "male",
            "l"
        ].includes(g)
    ) {

        return "male";
    }

    if (
        [
            "perempuan",
            "female",
            "p"
        ].includes(g)
    ) {

        return "female";
    }

    return "other";
}


/* =====================================================
   RENDER TABLE
===================================================== */

function renderTable() {

    const hasil =
        document.getElementById("hasil");

    const totalPages =
        Math.ceil(
            filteredData.length /
            rowsPerPage
        );


    if (
        totalPages > 0 &&
        currentPage > totalPages
    ) {

        currentPage =
            totalPages;
    }


    const start =
        (currentPage - 1) *
        rowsPerPage;


    const pageData =
        filteredData.slice(
            start,
            start + rowsPerPage
        );


    let html = "";


    pageData.forEach(item => {

        const nisn =
            String(item.nisn ?? "")
                .replace(/\D/g, "")
                .substring(0, 10);


        const namaFoto =
            String(item.foto ?? "")
                .trim();


        const nama =
            String(item.name ?? "?")
                .trim();


        const inisial =
            escapeHTML(
                nama.charAt(0)
                    .toUpperCase() || "?"
            );


        let fotoHTML;


        if (namaFoto !== "") {

            fotoHTML = `

                <img
                    class="foto-siswa"
                    src="uploads/${encodeURIComponent(namaFoto)}"
                    alt="Foto ${escapeHTML(item.name)}"
                    loading="lazy"

                    onerror="
                        this.style.display='none';
                        this.nextElementSibling.style.display='inline-grid';
                    "
                >

                <span
                    class="foto-placeholder"
                    style="display:none;"
                >
                    ${inisial}
                </span>

            `;

        } else {

            fotoHTML = `

                <span class="foto-placeholder">
                    ${inisial}
                </span>

            `;
        }


        html += `

            <tr>

                <td>

                    <span class="id-badge">
                        #${escapeHTML(item.id)}
                    </span>

                </td>


                <td class="foto-cell">

                    ${fotoHTML}

                </td>


                <td class="name">

                    ${escapeHTML(item.name)}

                </td>


                <td>

                    ${escapeHTML(nisn)}

                </td>


                <td>

                    ${escapeHTML(item.ttl)}

                </td>


                <td>

                    <span
                        class="gender ${genderClass(item.gender)}"
                    >
                        ${escapeHTML(item.gender)}
                    </span>

                </td>


                <td class="email">

                    ${escapeHTML(item.email)}

                </td>


                <td class="addr">

                    ${escapeHTML(item.address)}

                </td>


                <td class="actions">

                    <a
                        href="edit.php?id=${encodeURIComponent(item.id)}"
                        class="btn edit"
                    >
                        Edit
                    </a>


                    <a
                        href="hapus.php?id=${encodeURIComponent(item.id)}"
                        class="btn hapus"

                        onclick="
                            return confirm(
                                'Yakin ingin menghapus data siswa ini?'
                            );
                        "
                    >
                        Hapus
                    </a>

                </td>

            </tr>

        `;
    });


    if (filteredData.length === 0) {

        html = `

            <tr>

                <td
                    colspan="9"
                    class="loading"
                >

                    ${
                        allData.length === 0
                        ? "Belum ada data siswa di database."
                        : "Data yang dicari tidak ditemukan."
                    }

                </td>

            </tr>

        `;
    }


    hasil.innerHTML =
        html;

    renderPagination(
        totalPages
    );
}


/* =====================================================
   PAGINATION
===================================================== */

function renderPagination(totalPages) {

    const pagination =
        document.getElementById(
            "pagination"
        );

    pagination.innerHTML = "";


    if (totalPages <= 1) {

        if (filteredData.length > 0) {

            const info =
                document.createElement(
                    "div"
                );

            info.className =
                "page-info";

            info.textContent =
                `Menampilkan ${filteredData.length} data`;

            pagination.appendChild(
                info
            );
        }

        return;
    }


    function makeBtn(
        label,
        disabled,
        onClick,
        active = false
    ) {

        const button =
            document.createElement(
                "button"
            );

        button.className =
            "page-btn" +
            (
                active
                ? " active"
                : ""
            );

        button.textContent =
            label;

        button.disabled =
            disabled;

        button.onclick =
            onClick;

        pagination.appendChild(
            button
        );
    }


    makeBtn(
        "‹",
        currentPage === 1,
        () => {

            currentPage--;

            renderTable();
        }
    );


    const maxButtons = 5;


    let startPage =
        Math.max(
            1,
            currentPage -
            Math.floor(
                maxButtons / 2
            )
        );


    let endPage =
        Math.min(
            totalPages,
            startPage +
            maxButtons -
            1
        );


    startPage =
        Math.max(
            1,
            endPage -
            maxButtons +
            1
        );


    for (
        let p = startPage;
        p <= endPage;
        p++
    ) {

        makeBtn(
            p,
            false,
            () => {

                currentPage =
                    p;

                renderTable();

            },
            p === currentPage
        );
    }


    makeBtn(
        "›",
        currentPage === totalPages,
        () => {

            currentPage++;

            renderTable();

        }
    );


    const info =
        document.createElement(
            "div"
        );

    info.className =
        "page-info";


    const mulai =
        (currentPage - 1) *
        rowsPerPage + 1;


    const akhir =
        Math.min(
            currentPage *
            rowsPerPage,
            filteredData.length
        );


    info.textContent =
        `Menampilkan ${mulai}-${akhir} dari ${filteredData.length} data • Halaman ${currentPage}/${totalPages}`;


    pagination.appendChild(
        info
    );
}


/* =====================================================
   UPDATE STATISTICS
===================================================== */

function updateStatistics() {

    document.getElementById(
        "totalSiswa"
    ).textContent =
        allData.length;


    document.getElementById(
        "totalLaki"
    ).textContent =

        allData.filter(
            item =>
                genderClass(
                    item.gender
                ) === "male"
        ).length;


    document.getElementById(
        "totalPerempuan"
    ).textContent =

        allData.filter(
            item =>
                genderClass(
                    item.gender
                ) === "female"
        ).length;
}


/* =====================================================
   SEARCH
===================================================== */

document
    .getElementById("cari")
    .addEventListener(
        "input",
        function() {

            const q =
                this.value
                    .toLowerCase()
                    .trim();


            filteredData =
                allData.filter(
                    item =>

                        [
                            item.name,
                            item.nisn,
                            item.email,
                            item.ttl,
                            item.address,
                            item.gender
                        ].some(
                            value =>

                                String(
                                    value ?? ""
                                )
                                .toLowerCase()
                                .includes(q)
                        )
                );


            currentPage = 1;

            renderTable();
        }
    );


/* =====================================================
   INITIALIZE
===================================================== */

updateStatistics();

renderTable();

</script>

</body>

</html>