<?php

namespace App\Repositories;

use Validator;

class PartnerOrderRepository
{

    public function _validateShowRequest($request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|string',
            'filter' => 'sometimes|required|string|in:ongoing,history'
        ]);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

    private function resolveStatus($filter)
    {
        if ($filter == 'ongoing') {
            return array(constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Schedule_Due'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Served']);
        }
    }

    public function getStatusFromRequest($request)
    {
        if ($request->has('status')) {
            return explode(',', $request->status);
        } elseif ($request->has('filter')) {
            return $this->resolveStatus($request->filter);
        }else{
            constants('JOB_STATUSES');
        }
    }
}