<?php namespace Sheba\Reports\PartnerOrder\Getters;

use App\Models\PartnerOrder;
use Sheba\Reports\PartnerOrder\Presenter;
use Sheba\Reports\PartnerOrder\Repositories\RawRepository;

class RawDataGetter extends Getter
{
    public function __construct(RawRepository $repo, Presenter $presenter)
    {
        parent::__construct($repo, $presenter);
    }

    /**
     * @param PartnerOrder $item
     * @return array
     */
    protected function mapForView($item)
    {
        return $this->presenter->setPartnerOrder($item)->getForView();
    }
}