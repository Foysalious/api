<?php namespace Sheba\Pos\Repositories;

use App\Models\PosOrderDiscount;
use Sheba\Pos\Repositories\Interfaces\PosDiscountRepositoryInterface;
use Sheba\Repositories\BaseRepository;

class PosDiscountRepository extends BaseRepository implements PosDiscountRepositoryInterface
{
    /**
     * PosDiscountRepository constructor.
     * @param PosOrderDiscount $pos_order_discount
     */
    public function __construct(PosOrderDiscount $pos_order_discount)
    {
        parent::__construct();
        $this->setModel($pos_order_discount);
    }
}