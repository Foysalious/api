<?php namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class PaymentLinkArrayTransform extends TransformerAbstract
{
    public function transform($link)
    {
        return [
            'id' => $link['linkId'],
            'code' => '#' . $link['linkId'],
            'purpose' => $link['reason'],
            'status' => $link['isActive'] == 1 ? 'active' : 'inactive',
            'amount' => $link['amount'],
            'created_at' => date('Y-m-d h:i a', $link['createdAt'] / 1000),
        ];
    }
}