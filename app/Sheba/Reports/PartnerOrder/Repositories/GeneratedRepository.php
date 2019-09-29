<?php namespace Sheba\Reports\PartnerOrder\Repositories;

use App\Models\PartnerOrderReport;

class GeneratedRepository extends Repository
{
    protected function getQuery()
    {
        return PartnerOrderReport::query();
    }

    protected function getPartnerIdField()
    {
        return 'sp_id';
    }

    protected function getCancelledDateField()
    {
        return 'cancelled_date';
    }

    protected function getClosedDateField()
    {
        return 'closed_date';
    }
}