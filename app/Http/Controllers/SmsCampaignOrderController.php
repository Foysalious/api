<?php namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Dal\SmsCampaignOrder\SmsCampaignOrderRepository;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\ModificationFields;
use Sheba\SmsCampaign\SmsCampaign;
use Sheba\SmsCampaign\DTO\SmsCampaignOrderDTO;
use Sheba\SmsCampaign\DTO\SmsCampaignOrderListDTO;
use Sheba\SmsCampaign\SmsExcel;
use Sheba\SmsCampaign\CampaignSmsStatusChanger;
use Sheba\UrlShortener\ShortenUrl;
use DB;
use Excel;
use Sheba\Usage\Usage;

class SmsCampaignOrderController extends Controller
{
    use ModificationFields;

    /** @var SmsCampaignOrderRepository */
    private $orderRepo;

    public function __construct(SmsCampaignOrderRepository $order_repo)
    {
        $this->orderRepo = $order_repo;
    }

    public function getSettings(Request $request)
    {
        return api_response($request, null, 200, ['settings' => constants('SMS_CAMPAIGN')]);
    }

    /**
     * @param $partner_id
     * @param Request $request
     * @param SmsCampaign $campaign
     * @return JsonResponse
     */
    public function create($partner_id, Request $request, SmsCampaign $campaign)
    {
        if ($request->filled('customers') && $request->filled('param_type')) {
            $customers = json_decode(request()->customers, true);
            $request['customers'] = $customers;
        }

        $this->setModifier($request['manager_resource']);

        $rules = ['title' => 'required', 'message' => 'required'];
        if ($request->filled('customers')) $rules += ['customers' => 'required|array', 'customers.*.mobile' => 'required|mobile:bd'];
        if ($request->hasFile('file')) $rules += ['file' => 'required|file'];
        $this->validate($request, $rules);

        $requests = $request->all();
        if ($request->hasFile('file')) {
            $this->pickDataFromExcel($request, $campaign);
        }
        $campaign = $campaign->formatRequest($requests);

        if (!$campaign->partnerHasEnoughBalance()) api_response($request, null, 200, ['message' => 'Insufficient Balance On Partner Wallet', 'error_code' => 'insufficient_balance', 'code' => 200]);

        if (!$campaign->createOrder()) api_response($request, null, 200, ['message' => 'Failed to create campaign', 'error_code' => 'unknown_error', 'code' => 500]);

        (new Usage())->setUser($request->partner)->setType(Usage::Partner()::SMS_MARKETING)->create($request->manager_resource);
        return api_response($request, null, 200, ['message' => "Campaign created successfully"]);
    }

    private function pickDataFromExcel(Request $request, SmsCampaign $campaign)
    {
        $valid_extensions = ["xls", "xlsx", "xlm", "xla", "xlc", "xlt", "xlw"];
        $extension = $request->file('file')->getClientOriginalExtension();

        if (!in_array($extension, $valid_extensions)) {
            return api_response($request, null, 400, ['message' => 'File type not support']);
        }

        $file = Excel::selectSheets(SmsExcel::SHEET)->load($request->file)->save();
        $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;

        $data = Excel::selectSheets(SmsExcel::SHEET)->load($file_path)->get();
        $total = $data->count();

        $data->each(function ($value, $key) use ($file_path, $total, $campaign) {
            $mobile_field = SmsExcel::MOBILE_COLUMN_TITLE;
            $campaign->setMobile(BDMobileFormatter::format($value->$mobile_field))->pushMobileNumber();
        });
    }

    public function getTemplates($partner, Request $request)
    {
        $partner = Partner::find($partner);
        $deep_link = config('sheba.front_url') . '/partners/' . $partner->sub_domain;
        $templates = config('sms_campaign_templates');
        foreach ($templates as $index => $template) {
            $template = (object)$template;
            $template->message .= ' ' . $deep_link;
            $templates[$index] = $template;
        }
        return api_response($request, null, 200, ['templates' => $templates, 'deep_link' => $deep_link]);
    }

    public function getDeepLink($partner, ShortenUrl $shortenUrl)
    {
        $url_to_shorten = config('sheba.front_url') . '/partners/' . $partner->sub_domain;
        if (!$partner->bitly_url) {
            $deep_link = $shortenUrl->shorten('bit.ly', $url_to_shorten)['link'];
            $partner->bitly_url = $deep_link;
            $partner->save();
        }
        return $partner->bitly_url;
    }

    public function getHistory($partner, Request $request)
    {
        $orders = $this->orderRepo->getLatestByPartnerWithReceivers($partner);
        return api_response($request, null, 200, [
            'history' => (new SmsCampaignOrderListDTO($orders))->toArray()
        ]);
    }

    public function getHistoryDetails($partner, $history, Request $request)
    {
        $order = $this->orderRepo->find($history);
        return api_response($request, null, 200, [
            'details' => (new SmsCampaignOrderDTO($order))->toArray()
        ]);
    }

    public function processQueue(CampaignSmsStatusChanger $sms_status_changer)
    {
        $sms_status_changer->processPendingSms();
    }
}
