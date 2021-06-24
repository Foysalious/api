<?php

namespace Sheba\Reports\Accounting;

use Carbon\Carbon;

class JournalReportData
{
    /**
     * @param $data
     * @return array
     */
    public function format_data($data): array
    {
        $end_journal_data = array();
        $journal_data = collect($data);
        $journal_data = $journal_data->groupBy('identifier')->toArray();
        foreach ($journal_data as $data)
            $end_journal_data[] = $this->make_single_journal_data($data);

        return $end_journal_data;
    }

    /**
     * @param $data
     * @return array
     */
    private function make_single_journal_data($data): array
    {
        $journal_single_data = [
            "key" => $data[0]['identifier'],
            "date" => Carbon::parse($data[0]['entry_at'])->format('d M Y'),
            "source_type" => $data[0]['source_type']
        ];
        foreach ($data as $d)
            $journal_single_data['entries'][] = $d;

        return $journal_single_data;
    }
}
