<?php
/**
 * Created by PhpStorm.
 * User: sabbir
 * Date: 3/18/18
 * Time: 7:44 PM
 */

namespace App\Http\Controllers;

use Sheba\Dal\Accessor\Contract as AccessorRepo;
use Sheba\Dal\ComplainPreset\Contract as ComplainPresetRepo;

use Illuminate\Http\Request;

class ComplainController extends Controller
{
    private $accessorRepo;
    private $complainPresetRepo;

    public function __construct(AccessorRepo $accessorRepo, ComplainPresetRepo $complain_preset_repo)
    {
        $this->accessorRepo = $accessorRepo;
        $this->complainPresetRepo = $complain_preset_repo;
    }

    public function get(Request $request)
    {
        return $this->accessorRepo->findByNameWithPublishedCategoryAndPreset('Customer'); // Partner, //Customer
        return $this->accessorRepo->findByIdWithPublishedCategoryAndPreset(2); // 1 , 2
    }

    public function store(Request $request)
    {
        $this->processData($request);
    }

    protected function processData(Request $request)
    {
        $preset_id = (int) $request->complain_preset;
        $preset = $this->complainPresetRepo->find($preset_id);
        $follow_up_time = Carbon::now()->addMinutes($preset->complainType->sla);

        return [
            'complain'           => $request->complain,
            'complain_preset_id' => $preset_id,
            'source'             => $request->complain_source,
            'follow_up_time'     => $follow_up_time,
            'accessor_id'        => $request->accessor_id,
            'assigned_to_id'     => empty($request->assigned_to_id)? null : (int) $request->assigned_to_id,
            'job_id'             => empty($request->job_id) ? null : $request->job_id,
            'customer_id'        => isset($request->customer_id) ? $request->customer_id :  null,
            'partner_id'         => empty($request->partner_id) ? null : $request->partner_id
        ];
    }
}