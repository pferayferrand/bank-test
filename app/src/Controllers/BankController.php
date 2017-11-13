<?php

namespace App\Controllers;

use mysqli;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class BankController
{
    private $view;
    private $logger;
    private $flash;

    public function __construct($view, LoggerInterface $logger, $flash)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->flash = $flash;
    }

    public function dispatch(Request $request, Response $response, $args)
    {
        $this->logger->info("Validation action dispatched");

        $sortcode = $request->getParam('shortcode');
        $bankaccount = $request->getParam('bank_account');

        // Define end point URL
                $url     = "http://www.bankaccountchecker.com/";
                $service = "listener.php";

        // Define parameters for all tests
                $key         = "cdc36c2ed80e0a3fc30c9fe61eab1b66";
                $password    = "M@r10n76300";
                $output      = "json";
                $type        = "uk";

        // Call with Post with url encoded string post parameters
        $ch = curl_init() ;
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL,$url . $service);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "key=$key&password=$password&output=$output&type=$type&sortcode=$sortcode&bankaccount=$bankaccount");

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch) . "\r\n";
            $info = curl_getinfo($ch);
            print_r($info);
            curl_close($ch);
            return;
        }

        curl_close($ch);

        $resultJson = json_decode($result);

        $ipAddress = $request->getAttribute('ip_address');

        $mysqli = new mysqli("localhost", "root", "", "bank-test");


        $mysqli->query("INSERT INTO `requests` (`id`, `short_code`, `bank_account`, `ip`) VALUES (NULL, '".$sortcode."', '".$bankaccount."', '".$ipAddress."');");
        $insert_id = $mysqli->insert_id;
        $mysqli->query("INSERT INTO `responses` (`id`, `request_id`, `reponse_json`) VALUES (NULL, '".$insert_id."', '".$result."');");

        if ($resultJson->resultCode == '01') {
            $success = true;
        } else if  ($resultJson->resultCode == '02') {
            $success = false;
        } else {
            $success = false;
        }

        $this->view->render($response, 'validated.twig', array('success' => $success));

        return $response;
    }
}