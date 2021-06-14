<?php


namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Dal\PushNotificationMonitoring\PushNotificationMonitoringItem;
use Sheba\Dal\PushNotificationMonitoring\PushNotificationMonitoringItemRepository;

class PushNotificationMonitoringController extends Controller
{
    function store(Request $request, PushNotificationMonitoringItemRepository $pushNotificationMonitoringItemRepository)
    {
        try {
            $this->validate($request,
                [
                    'notification_monitoring_id' => 'required',
                    'sent_time'                  => 'required',
                    'receive_time'               => 'required',
                    'priority'                   => 'required',
                    'original_priority'          => 'required',
                    'manufacturer'               => 'required',
                    'model'                      => 'required',
                    'message_id'                 => 'required'
                ]
            );
            $data = $request->only(['sent_time', 'receive_time', 'priority', 'original_priority', 'manufacturer', 'model', 'message_id']);
            /** @var PushNotificationMonitoringItem $monitoringItem */
            $monitoringItem = $pushNotificationMonitoringItemRepository->find($request->notification_monitoring_id);
            if ($monitoringItem) {
                $monitoringItem->receiveInfos()->create($data);
                return api_response($request, null, 200);
            }
            return api_response($request, null, 404);

        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }

    }
}
