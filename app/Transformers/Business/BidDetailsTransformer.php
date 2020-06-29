<?php namespace App\Transformers\Business;

use App\Models\Bid;
use League\Fractal\TransformerAbstract;
use Sheba\Business\Bid\StatusCalculator;
use App\Transformers\AttachmentTransformer;

class BidDetailsTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['attachments'];

    /** @var Bid $bid */
    private $bid;

    public function transform($bid)
    {
        $this->bid = $bid;
        $default_terms = "<p>After clicking on <b>Hire</b> button admin will be redirected to this page. In this page, admin can check the procurement title, vendor name, total price.Admin can also see a table where there will be Sl no.</p><ol><li>In economics, a service is a transaction in which</li><li>The benefits of such a service are held to be demonstrated</li><li>Buyer's willingness to make the exchange</li></ol>";
        $default_policies = "<p>After clicking on <b>Hire</b> button admin will be redirected to this page. In this page, admin can check the procurement title, vendor name, total price.Admin can also see a table where there will be Sl no.</p><ol><li>In economics, a service is a transaction in which</li><li>The benefits of such a service are held to be demonstrated</li><li>Buyer's willingness to make the exchange</li></ol>";

        $price_quotation = $this->generatePriceQuotationItemData();
        $technical_evaluation = $this->formatItemData('technical_evaluation');
        $company_evaluation = $this->formatItemData('company_evaluation');

        return [
            'id' => $bid->id,
            'status' => StatusCalculator::resolveStatus($bid),
            'price' => $bid->price,
            'title' => $bid->procurement->title,
            'type' => $bid->procurement->type,
            'is_awarded' => $bid->canNotSendHireRequest(),
            'terms' => $bid->terms ?: $default_terms,
            'policies' => $bid->policies ?: $default_policies,
            'proposal' => $bid->proposal,
            'start_date' => $bid->procurement->procurement_start_date->format('d/m/y'),
            'end_date' => $bid->procurement->procurement_end_date->format('d/m/y'),
            'created_at' => $bid->created_at->format('d/m/y'),
            'price_quotation' => $price_quotation,
            'technical_evaluation' => $technical_evaluation,
            'company_evaluation' => $company_evaluation,
            'vendor' => [
                'name' => $bid->bidder->name,
                'logo' => $bid->bidder->logo,
                'domain' => $bid->bidder->sub_domain,
                'rating' => round($bid->bidder->reviews->avg('rating'), 2),
                'total_rating' => $bid->bidder->reviews->count()
            ],
        ];
    }

    public function includeAttachments($bid)
    {
        $collection = $this->collection($bid->attachments, new AttachmentTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }

    /**
     * @param $type
     * @return array
     */
    private function formatItemData($type)
    {
        $item_type = $this->bid->items->where('type', $type)->first();
        if (!$item_type) return null;

        $item_fields = [];
        foreach ($item_type->fields as $field) {
            $result = json_decode($field->result);
            array_push($item_fields, [
                'id' => $field->id,
                'title' => $field->title,
                'result' => is_array($result) ? implode(", ", $result) : $field->result
            ]);
        }

        return $item_fields;
    }

    /**
     * @return array|null
     */
    public function generatePriceQuotationItemData()
    {
        $type_data = $this->bid->items->where('type', 'price_quotation')->first();
        return $type_data ? $type_data->fields->map(function ($field) {
            $unit = $field->variables ? json_decode($field->variables)->unit ? json_decode($field->variables)->unit : 0 : 0;
            return [
                'id' => $field->bid_item_id ? $field->bid_item_id : $field->procurement_item_id,
                'field_id' => $field->id,
                'title' => $field->title,
                'input_type' => $field->input_type,
                'short_description' => $field->short_description,
                'long_description' => $field->long_description,
                'unit' => (double)$unit,
                'result' => (double)$field->result
            ];
        }) : null;
    }
}
