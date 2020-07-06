<?php namespace Sheba\Logistics;

use App\Models\Vendor;

trait ValidatesApiRequest
{
    /**
     * @return array|bool
     */
    public function hasErrorAccessingApiFromLogistic()
    {
        if (!$this->request->hasHeader('app-key') || !$this->request->hasHeader('app-secret')) {
            return ['code' => 400, 'message' => 'Authorization headers missing'];
        }

        $vendor = Vendor::where([
            ['app_key', $this->request->header('app-key')],
            ['app_secret', $this->request->header('app-secret')],
            ['is_active', 1]
        ])->first();

        if(!$vendor) return ['code' => 403, 'message' => 'Unauthorized request'];

        return false;
    }
}