<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 10/30/2018
 * Time: 4:20 PM
 */

namespace App\Sheba\Bondhu;


use Illuminate\Support\Facades\DB;

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

    public function generateData($affiliate_id) {
        if($this->type == "affiliates") {
            $this->getQuery("App\Models\Affiliation","affiliations",$affiliate_id,$this->from,$this->to);
            return $this->histories;
        } else if($this->type === "partner_affiliates"){
            $this->getQuery("App\Models\PartnerAffiliation","partner_affiliations",$affiliate_id,$this->from,$this->to);
            return $this->histories;
        }
    }

    public function getFormattedDate($request)
    {
        switch ($request->filter_type) {
            case "date_range":
                $this->setDateRange($request->from, $request->to);
                break;
            default:
                $formattedDates = (formatDateRange($request->filter_type));
                $this->setDateRange($formattedDates["from"], $formattedDates["to"]);
                break;
        }
        return $this;
    }


    public function getQuery($model,$tableName, $affiliate_id, $from, $to) {
        $this->histories = $model::join('affiliate_transactions','affiliate_transactions.affiliation_id','=',$tableName.".id")
                ->join('affiliates',$tableName.'.affiliate_id','=','affiliates.id')
                ->selectRaw(
                    DB::raw('CONCAT("REF",affiliate_transactions.affiliation_id) as refer_id,status,amount, DATE_FORMAT(DATE(affiliate_transactions.created_at), "%d %b\'%y") as date')
                )
                ->where("affiliate_transactions.affiliate_id",$affiliate_id)
                ->where("is_gifted",1)
                ->whereDate($tableName.'.created_at','>=',$from)
                ->whereDate($tableName.'.created_at','<=',$to);
    }
}