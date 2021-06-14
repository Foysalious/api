<?php

namespace Sheba\Reports\Accounting;

class JournalReportData
{
    public function format_data($data): array
    {
        $journal_data = collect($data);
        $journal_data = $journal_data->groupBy('identifier');
        return $journal_data->toArray();
    }
}