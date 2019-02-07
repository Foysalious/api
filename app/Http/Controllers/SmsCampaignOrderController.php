<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\SmsCampaignOrder;
use App\Sheba\SmsCampaign\InfoBip\SmsHandler;
use Illuminate\Http\Request;

use App\Http\Requests;
use Sheba\SmsCampaign\SmsCampaign;
use Sheba\SmsCampaign\SmsLogs;
use Sheba\UrlShortener\ShortenUrl;

class SmsCampaignOrderController extends Controller
{
    public function getSettings(Request $request)
    {
        try{
            return api_response($request, null, 200, ['settings' => constants('SMS_CAMPAIGN')]);
        }   catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function create($partner_id, Requests\SmsCampaignRequest $request, SmsCampaign $campaign)
    {
        try {
            $requests = $request->all();
            if($campaign->formatRequest($requests)->createOrder())
                return api_response($request, null, 200, ['message' => "Campaign created successfully"]);
            else
                return api_response($request, null, 500, ['message' =>'Failed to create campaign', 'code' => 500]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            $code = $e->getCode();
            return api_response($request, null, 500, ['message' => $e->getMessage(), 'code' => $code ? $code : 500]);
        }
    }

    public function getTemplates($partner, ShortenUrl $shortenUrl, Request $request)
    {
        try {
            $partner = Partner::find($partner);
            $url_to_shorten = config('sheba.front_url').'/'.$partner->sub_domain;
            $deep_link = $shortenUrl->shorten('bit.ly',$url_to_shorten)['link'];
            $templates =  config('sms_campaign_templates');
            foreach ($templates as $key=>$template) {
                $template = (object) $template;
                $template->deeplink = $deep_link;
                $templates[$key]=$template;
            }
            return api_response($request, null, 200, ['templates' => $templates]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            $code = $e->getCode();
            return api_response($request, null, 500, ['message' => $e->getMessage(), 'code' => $code ? $code : 500]);
        }
    }

    public function getHistory($partner, Request $request)
    {
        try {
           $history = SmsCampaignOrder::where('partner_id',$partner)->with('order_receivers')->get();
           $total_history = [];
           foreach ($history as $item) {
                $current_history = [
                    'id'=>$item->id,
                    'name' => $item->title,
                    'cost' => $item->total_cost,
                    'created_at' => $item->created_at->format('Y-m-d H:i:s')
                ];
                array_push($total_history, $current_history);
           }
            return api_response($request, null, 200, ['history' => $total_history]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            $code = $e->getCode();
            return api_response($request, null, 500, ['message' => $e->getMessage(), 'code' => $code ? $code : 500]);
        }
    }

    public function getHistoryDetails($partner, $history, Request $request)
    {
        try{
            $details = SmsCampaignOrder::find($history);
            $data = [
                'id' => $details->id,
                'total_cost' => $details->total_cost,
                'message' => $details->message,
                'total_messages_requested' => $details->total_messages,
                'successfully_sent' => $details->successful_messages,
                'sms_count' => $details->order_receivers[0]->sms_count,
                'sms_rate' => $details->rate_per_sms,
                'created_at' => $details->created_at->format('Y-m-d H:i:s')
            ];
            return api_response($request, null, 200, ['details' => $data]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            $code = $e->getCode();
            return api_response($request, null, 500, ['message' => $e->getMessage(), 'code' => $code ? $code : 500]);
        }
    }
    
    public function processQueue(SmsLogs $smsLogs, SmsHandler $smsHandler)
    {
        $smsLogs->processLogs($smsHandler);
    }
}
