<?php

function kirimWhatsApp($target, $message)
{
    $token = "TkaoqfXfFrPGTGJ6UbLZ";

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => [
            "target" => $target,
            "message" => $message,
            "countryCode" => "62"
        ],
        CURLOPT_HTTPHEADER => [
            "Authorization: " . $token
        ],
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        $error = curl_error($curl);
        curl_close($curl);

        return [
            "status" => false,
            "message" => $error
        ];
    }

    curl_close($curl);

    return json_decode($response, true);
}