<?php namespace Sheba\Reports\OfferAnalysis;

use App\Models\OfferShowcase;
use Sheba\Reports\Presenter as BasePresenter;

class Presenter extends BasePresenter
{
    private $offer;

    public function setOffer(OfferShowcase $offer)
    {
        $this->offer = $offer;
        return $this;
    }

    /** @return array */
    public function get()
    {
        return [
            'ID' => $this->offer->id,
            'Title' => $this->offer->title,
            'Target Type' => class_basename($this->offer->target_type),
            'Target Id' => $this->offer->target_id,
            'Target Name' => $this->offer->target ? ($this->offer->isVoucher() ? $this->offer->target->code : ($this->offer->target->name ?: "N/S")) : "N/A",
            'Is Flash' => $this->offer->is_flash,
            'Start At' => $this->offer->start_date,
            'End At' => $this->offer->end_date,
            'Order Count' => $this->offer->order_count,
            'Created At' => $this->offer->created_at,
            'Created By' => $this->offer->created_by_name,
            'Updated At' => $this->offer->updated_at,
            'Updated By' => $this->offer->updated_by_name
        ];
    }

    /** @return array */
    public function getForView()
    {
        $data = $this->get();
        $data['Is Flash'] = $data['Is Flash'] ? 'Yes' : 'No';
        $data['Start At'] = $data['Start At']->format('d M Y h:i A');
        $data['End At'] = $data['End At']->format('d M Y h:i A');
        $data['Created At'] = $data['Created At']->format('d M Y h:i A');
        $data['Updated At'] = $data['Updated At']->format('d M Y h:i A');
        return $data;
    }
}
