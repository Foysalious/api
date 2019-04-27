<?php namespace Sheba\Pos\Order\RefundNatures;

use App\Models\PosOrder;
use Sheba\Pos\Log\Creator as LogCreator;
use Sheba\Pos\Log\Supported\Types;
use Sheba\Pos\Order\Creator;
use Sheba\Pos\Order\Updater;
use Sheba\Pos\Product\StockManager;
use Sheba\Pos\Repositories\PosServiceRepository;

class ExchangePosItem extends RefundNature
{
    /** @var false|string */
    private $details;
    /** @var PosOrder $old_order */
    private $newOrder;
    /** @var StockManager $stockManager */
    private $stockManager;
    /** @var PosServiceRepository $serviceRepo */
    private $serviceRepo;

    public function __construct(LogCreator $log_creator, Updater $updater, PosServiceRepository $service_repo, StockManager $stock_manager)
    {
        parent::__construct($log_creator, $updater);
        $this->stockManager = $stock_manager;
        $this->serviceRepo = $service_repo;
    }

    public function update()
    {
        $creator = app(Creator::class);
        $this->stockRefill();
        $this->data['previous_order_id'] = $this->order->id;
        $this->newOrder = $creator->setData($this->data)->create();
        $this->generateDetails();
        $this->saveLog();
    }

    protected function saveLog()
    {
        $this->logCreator->setOrder($this->order)->setType(Types::EXCHANGE)->setLog("New Order Created for Exchange, old order id: {$this->order->id}")->setDetails($this->details)->create();
    }

    /**
     * GENERATE LOG DETAILS DATA
     */
    protected function generateDetails()
    {
        $details['orders']['changes'] = ['new' => $this->newOrder, 'old' => $this->order];
        $this->details = json_encode($details);
    }

    private function stockRefill()
    {
        $this->order->items->each(function ($item) {
            $partner_pos_service = $this->serviceRepo->find($item->service_id);
            $is_stock_maintainable = $this->stockManager->setPosService($partner_pos_service)->isStockMaintainable();
            if ($is_stock_maintainable) $this->stockManager->increase($item->quantity);
        });
    }
}