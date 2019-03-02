<?php namespace Sheba\MovieTicket\Vendor;
use App\Models\MovieTicketVendor;
use App\Sheba\MovieTicketRechargeHistory;
use Carbon\Carbon;
use Sheba\MovieTicket\Response\MovieResponse;

abstract class Vendor
{
    protected $model;

    abstract public function init();

    abstract public function generateURIForAction($action, $params = []);

    abstract function buyTicket($movieTicketResponse): MovieResponse;

    public function  setModel(MovieTicketVendor $model)
    {
        $this->model = $model;
        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function isPublished()
    {
        return $this->model->is_published;
    }

    public function deductAmount($amount)
    {
        $this->model->amount -= $amount;
        $this->model->update();
    }

    public function refill($amount)
    {
        $this->model->amount += $amount;
        $this->model->update();
        // $this->createNewRechargeHistory($amount);
    }

    protected function createNewRechargeHistory($amount, $vendor_id = null)
    {
        $recharge_history = new MovieTicketRechargeHistory();
        $recharge_history->recharge_date = Carbon::now();
        $recharge_history->vendor_id = $vendor_id ?: $this->model->id;
        $recharge_history->amount = $amount;
        $recharge_history->save();
    }
}