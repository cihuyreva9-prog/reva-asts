<?php

session_start();

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

if (
    !isset($_SESSION["login"]) ||
    $_SESSION["login"] !== true
) {

    http_response_code(401);

    echo json_encode([
        "error" => true,
        "message" => "Session login tidak ditemukan. Silakan login kembali."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

require_once "koneksi.php";


/*
|--------------------------------------------------------------------------
| Pastikan koneksi database tersedia
|--------------------------------------------------------------------------
*/

if (!isset($conn)) {

    http_response_code(500);

    echo json_encode([
        "error" => true,
        "message" => "Variabel koneksi \$conn tidak ditemukan."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| Cek koneksi database
|--------------------------------------------------------------------------
*/

if ($conn->connect_error) {

    http_response_code(500);

    echo json_encode([
        "error" => true,
        "message" => "Koneksi database gagal.",
        "detail" => $conn->connect_error
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| Ambil data siswa
|--------------------------------------------------------------------------
*/

$sql = "
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
";

$result = $conn->query($sql);


/*
|--------------------------------------------------------------------------
| Cek query
|--------------------------------------------------------------------------
*/

if ($result === false) {

    http_response_code(500);

    echo json_encode([
        "error" => true,
        "message" => "Query data siswa gagal.",
        "detail" => $conn->error
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| Masukkan data ke array
|--------------------------------------------------------------------------
*/

$data = [];

while ($row = $result->fetch_assoc()) {

    $data[] = [

        "id" => (int) $row["id"],

        "name" => $row["name"] ?? "",

        "nisn" => $row["nisn"] ?? "",

        "ttl" => $row["ttl"] ?? "",

        "gender" => $row["gender"] ?? "",

        "email" => $row["email"] ?? "",

        "address" => $row["address"] ?? "",

        "foto" => $row["foto"] ?? ""

    ];
}


/*
|--------------------------------------------------------------------------
| Kirim JSON
|--------------------------------------------------------------------------
*/

echo json_encode(
    $data,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);

$conn->close();

?>