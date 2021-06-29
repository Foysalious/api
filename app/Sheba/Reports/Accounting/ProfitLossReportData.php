<?php

namespace Sheba\Reports\Accounting;

class ProfitLossReportData
{
    /**
     * @param $data
     * @return array
     */
    public function format_data($data) : array
    {
        $real_data = array();
        $required_fields = ['key', 'value', 'type'];
        foreach ($data as $d)
            if(isset($d['additional_type']) && $d['additional_type'] === "list_view")
                foreach ($d['value'] as $value) {
                    $a["key"] = $value["name_bn"];
                    $a["value"] = $value["balance"];
                    $a["type"] = "item";
                    $real_data[] = $a;
                }
            else
                $real_data[] = array_only($d, $required_fields);

        return $real_data;
    }

}