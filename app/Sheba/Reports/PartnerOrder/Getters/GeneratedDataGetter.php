<?php namespace Sheba\Reports\PartnerOrder\Getters;

use App\Models\PartnerOrderReport;
use Sheba\Reports\PartnerOrder\Presenter;
use Sheba\Reports\PartnerOrder\Repositories\GeneratedRepository;

class GeneratedDataGetter extends Getter
{
    protected $field = "created_date";

    public function __construct(GeneratedRepository $repo, Presenter $presenter)
    {
        parent::__construct($repo, $presenter);
    }

    /**
     * @param PartnerOrderReport $item
     * @return array
     */
    protected function mapForView($item)
    {
        return $this->presenter->setPartnerOrderReport($item)->getForView();
    }
}