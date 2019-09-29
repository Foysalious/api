<?php namespace Sheba\Transport\Bus\Generators;

use Sheba\Transport\Bus\ClientCalls\BdTickets;

class CompanyList
{
    /** @var BdTickets $bdTicketsClient */
    private $bdTicketsClient;

    public function __construct(BdTickets $bdTickets)
    {
        $this->bdTicketsClient = $bdTickets;
    }

    public function getCompanies()
    {
        $bd_ticket_companies = $pekhom_companies = [];

        $bd_ticket_companies = $this->bdTicketsClient->get('/companies')['data'];
        $pekhom_companies = [];
        $merged_companies = array_merge($bd_ticket_companies,$pekhom_companies);

        return $merged_companies;
    }
}