<?php

namespace App\Sheba\Pap;

use Gpf_Data_Filter;
use Gpf_Rpc_GridRequest;
use http\Exception;
use Pap_Api_Session;

class Pap
{
    public function getAffiliateId($visitorId)
    {
        try {
            if (empty($visitorId)) {
                return null;
            }
            $session = new Pap_Api_Session(env('PAP_URL') . '/scripts/server.php');
            if (!@$session->login(env('PAP_USERNAME'), env('PAP_PASSWORD'))) {
                return null;
                die("Cannot login. Message: " . $session->getMessage());
            }
            if (strlen($visitorId) > 32) {
                $visitorId = substr($visitorId, -32);
            }
            $request = new Gpf_Rpc_GridRequest('Pap_Merchants_Tools_VisitorAffiliatesGrid', 'getRows', $session);
            // set filter
            $request->addFilter("visitorid", Gpf_Data_Filter::EQUALS, $visitorId);
            $request->addFilter("rtype", Gpf_Data_Filter::EQUALS, 'A');  //remove this line if you are using 'Split Commissions' feature
            $request->addFilter("validto", Gpf_Data_Filter::DATE_EQUALS_GREATER, date('Y-m-d'));
            //in PAN insert here your merchant network accountid
            //$request->addFilter("accountid", Gpf_Data_Filter::EQUALS, 'default1');
            $request->setLimit(0, 1);

            try {
                $request->sendNow();
            } catch (Exception $e) {
                return null;
                die("API call error: " . $e->getMessage());
            }

            $grid = $request->getGrid();

            $recordset = $grid->getRecordset();

            if ($recordset->getSize() > 0) {
                return $recordset->get(0)->get('userid');
            }
        } catch (\Throwable $e) {
            return null;
        }
    }
}