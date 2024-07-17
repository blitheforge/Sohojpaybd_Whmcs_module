<?php
/* Sohojpaybd WHMCS Plugin
 *
 * Copyright (c) 2023 blitheforge
 * Website: https://sohojpaybd.com/
 * Developer: https://github.com/blitheforge
 * 
 */

/* 
How to use ?

Go to Module of your file - then go to gateway and upload file in root folder 
then again upload it on Callback file.

 */


if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function sohojpaybd_MetaData()
{
    return array(
        'DisplayName' => 'sohojpaybd',
        'APIVersion' => '1.0',
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}




function sohojpaybd_link($params)
{
    $host_config = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $host_config = pathinfo($host_config, PATHINFO_FILENAME);

    if (isset($_POST['pay'])) {
        $response = sohojpaybd_payment_url($params);
        if ($response->status) {
            return '<form action="' . $response->payment_url . ' " method="GET">
            <input class="btn btn-primary" type="submit" value="' . $params['langpaynow'] . '" />
            </form>';
        }

        return $response->message;
    }


    if ($host_config == "viewinvoice") {
        return '<form action="" method="POST">
        <input class="btn btn-primary" name="pay" type="submit" value="' . $params['langpaynow'] . '" />
        </form>';
    } else {
        $response = sohojpaybd_payment_url($params);

        
        if ($response->status) {
            return '<form action="' . $response->payment_url . ' " method="GET">
            <input class="btn btn-primary" type="submit" value="' . $params['langpaynow'] . '" />
            </form>';
        }

        return $response->message;
    }
}


function sohojpaybd_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'sohojpaybd',
        ),
        'apiKey' => array(
            'FriendlyName' => 'API Key',
            'Type' => 'text',
            'Size' => '150',
            'Default' => '',
            'Description' => 'Enter Your Api Key',
        ),

        'currency_rate' => array(
            'FriendlyName' => 'Currency Rate',
            'Type' => 'text',
            'Size' => '150',
            'Default' => '85',
            'Description' => 'Enter Dollar Rate',
        )
    );
}

function sohojpaybd_payment_url($params)
{
    $cus_name = $params['clientdetails']['firstname'] . " " . $params['clientdetails']['lastname'];
    $cus_email = $params['clientdetails']['email'];

    $apikey = $params['apiKey'];

    $currency_rate = $params['currency_rate'];

    $invoiceId = $params['invoiceid'];

    if ($params['currency'] == "USD") {
        $amount = $params['amount'] * $currency_rate;
    } else {
        $amount = $params['amount'];
    }
    $hostname = $params['hostName'];


    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        $url = "https://";
    else
        $url = "http://";
    // Append the host(domain name, ip) to the URL.   
    $url .= $_SERVER['HTTP_HOST'];

    $systemUrl = $url;

    $webhook_url = $systemUrl . '/modules/gateways/callback/sohojpaybd.php?api=' . $apikey . '&invoice=' . $invoiceId;
    $success_url = $systemUrl . '/viewinvoice.php?id=' . $invoiceId;
    $cancel_url = $systemUrl . '/viewinvoice.php?id=' . $invoiceId;


    $data   = array(
        "cus_name"          => $cus_name,
        "cus_email"         => $cus_email,
        "amount"            => $amount,
        "webhook_url"       => $webhook_url,
        "success_url"       => $success_url,
        "cancel_url"        => $cancel_url,
    );

    $header   = array(
        "api"               => $apikey,
        "url"               => 'https://secure.sohojpaybd.com/api/payment/create',
    );


    $headers = array(
        'Content-Type: application/json',
        'SOHOJPAY-API-KEY: ' . $header['api'],
    );
    $url = $header['url'];
    $curl = curl_init();
    $data = json_encode($data);

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_VERBOSE => true
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    echo $response;
}
