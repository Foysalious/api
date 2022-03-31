<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;

class LiveTrackingSettingChangeLogsTransformer extends TransformerAbstract
{
    /*** @var MemberRepositoryInterface $memberRepository*/
    private $memberRepository;

    public function __construct()
    {
        $this->memberRepository = app(MemberRepositoryInterface::class);
    }

    public function transform($tracking_logs)
    {
        $created_at = $tracking_logs->created_at;
        $member = $this->memberRepository->find($tracking_logs->created_by);
        $business_member = $member->businessMember;
        return [
            'id' => $tracking_logs->id,
            'logs' => $tracking_logs->log,
            'date' => $created_at->format('j M, Y'),
            'time' => $created_at->format('h:i A'),
            'created_by_profile' => [
                'business_member_id' => $business_member ? $business_member->id : null,
                'name' => str_replace('Member-', '', $tracking_logs->created_by_name),
            ]
        ];
    }
}
