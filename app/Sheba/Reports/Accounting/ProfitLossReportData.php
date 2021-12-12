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
        $list = array();
        $balance = 0;
        $required_fields = ['key', 'value', 'type'];

        foreach ($data as $d)
            if(isset($d['additional_type']) && $d['additional_type'] === "list_view")
                foreach ($d['value'] as $value) {
                    $list[] = $this->makeListData($value);
                }
            else {
                if($d["type"] === "bottom") $balance = $d['value'];
                $list[] = array_only($d, $required_fields);
            }

        return ["list" => $list, "balance" => $balance];
    }

    private function makeListData($list_data): array
    {
        $inside_list["key"] = $list_data["name_bn"];
        $inside_list["value"] = $list_data["balance"];
        $inside_list["type"] = "item";
        return $inside_list;
    }
}