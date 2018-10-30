<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 10/30/2018
 * Time: 4:20 PM
 */

namespace App\Sheba\Bondhu;


class AffiliateHistory
{
    private $from;
    private $to;
    private $type;
    private $parent_id;
    private $histories = array();

    public function setDateRange($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function generateData($affiliate_ids) {
        if($this->type == "affiliates") {
            $this->getData("App\Models\Affiliation","affiliations",$affiliate_ids,$this->from,$this->to);
            return  $this->statuses;
        } else if($this->type === "partner_affiliates"){
            $this->getData("App\Models\PartnerAffiliation","partner_affiliations",$affiliate_ids,$this->from,$this->to);
            return $this->statuses;
        }
    }


    public function getData($model,$tableName, $affiliate_ids, $from, $to) {

    }
}