<?php namespace App\Transformers;

use App\Models\PurchaseRequest;
use League\Fractal\TransformerAbstract;

class PurchaseRequestTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['items', 'questions', 'approvals'];

    public function transform(PurchaseRequest $purchase_request)
    {
        $request_by = null;
        if ($purchase_request->member) {
            $profile = $purchase_request->member->profile;
            $request_by = ['name' => $profile->name, 'image' => $profile->pro_pic, 'mobile' => $profile->mobile];
        }

        return [
            'id' => $purchase_request->id,
            'request_by' => $request_by,
            'request_for' => $purchase_request->type,
            'title' => $purchase_request->title,
            'estimated_price' => $purchase_request->estimated_price,
            'required_date' => $purchase_request->estimated_date ? $purchase_request->estimated_date->format('d/m/Y') : null,
            'message' => $purchase_request->long_description,
            'status' => $purchase_request->status,
            'rejection_reason' => $purchase_request->rejection_note
        ];
    }

    public function includeItems($purchase_request)
    {
        $collection = $this->collection($purchase_request->items, new PurchaseRequestItemTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }

    public function includeQuestions($purchase_request)
    {
        $collection = $this->collection($purchase_request->questions, new PurchaseRequestQuestionTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }

    public function includeApprovals($purchase_request)
    {
        $collection = $this->collection($purchase_request->approvalRequests, new PurchaseRequestApprovalTransformer());
        return $collection->getData() ? $collection : $this->item(null, function () {
            return [];
        });
    }
}