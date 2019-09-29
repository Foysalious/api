<?php
/**
 * Created by PhpStorm.
 * User: arnab
 * Date: 11/13/17
 * Time: 6:06 PM
 */

namespace App\Repositories;

use Pap_Api_Session;
use Pap_Api_Transaction;

class PapRepository
{

    /**
     * @param $order_code
     */
    public function refund($order_code)
    {
        $pap_url = env('PAP_URL'); // URL to PAP
        $pap_user = env('PAP_USERNAME'); // Merchant username
        $pap_password = env('PAP_PASSWORD'); // Merchant password
        $session = new Pap_Api_Session($pap_url . "scripts/server.php");

//        if (@!$session->login($pap_user, $pap_password)) {
//            die("Cannot login. Message: " . $session->getMessage());
//        }
        $transaction = new Pap_Api_Transaction($session);
        $transaction->setOrderId($order_code);

        $result = $transaction->refundByOrderId();
        //$result = $transaction->chargeBackByOrderId();
        //$result = $transaction->refundByOrderId('affiliate note', '0.1');
        //$result = $transaction->chargeBackByOrderId('affiliate note', '0.1');

//        if ($result->isError()) {
//            echo 'Error: ' . $result->getErrorMessage();
//        } else {
//            echo 'Success: ' . $result->getInfoMessage();
//        }
    }
}