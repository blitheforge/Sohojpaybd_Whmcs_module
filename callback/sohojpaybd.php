<?php
/* sohojpaybd WHMCS Gateway
     *
     * Copyright (c) 2022 sohojpaybd
     * Website: https://sohojpaybd.com
     * Developer: sohojpaybd LTD
     */

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

use WHMCS\Config\Setting;


$invoiceId = $_REQUEST['invoice'];
$transactionId = $_REQUEST['transactionId'];
$paymentAmount = $_REQUEST['paymentAmount'];
$paymentFee = $_REQUEST['paymentFee'];
$gatewayModuleName = "sohojpaybd";


$transaction_id_sohojpaybd = $transactionId;

$data   = array(
    "transaction_id"          => $transaction_id_sohojpaybd,
);
$apikey = $_GET['api'];
$secretkey = $_GET['secret'];
$hostname = $_GET['host'];

$header   = array(
    "api"               => $apikey,
    "url"               => 'https://secure.sohojpaybd.com/api/payment/verify',
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
$data = json_decode($response, true);

if ($data['status'] == "COMPLETED") {
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        $paymentFee,
        $gatewayModuleName
    );

    $systemUrl = Setting::getValue('SystemURL');
?>
    <script>
        location.href = "<?php echo $systemUrl . '/viewinvoice.php?id=' . $invoiceId; ?>";
    </script>
<?php
} else {
    echo "Failed. Id Not Match";
}
?>