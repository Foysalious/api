<?php namespace App\Http\Requests;

use Carbon\Carbon;
use Sheba\Helpers\TimeFrame;

class TimeFrameReportRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'is_lifetime' => 'in:0,1',
            'start_date' => 'required_without:is_lifetime',
            'end_date' => 'required_with:start_date|after_or_equal:start_date',
        ];
    }

    public function isLifetime()
    {
        return $this->request->has('is_lifetime') && $this->request->get('is_lifetime');
    }

    public function isNotLifetime()
    {
        return !$this->isLifetime();
    }

    /**
     * @return TimeFrame | null
     */
    public function getTimeFrame()
    {
        if ($this->isLifetime()) return null;

        $start = Carbon::parse($this->request->get('start_date'));
        $end = Carbon::parse($this->request->get('end_date'));
        return new TimeFrame($start, $end);
    }

    public function messages()
    {
        $messages = parent::messages();
        $messages['start_date.required_without'] = "Start date is required when not lifetime report.";
        $messages['end_date.required_with'] = "End date is required with start date.";
        $messages['end_date.after_or_equal'] = "End date must be after or equal start date.";
        return $messages;
    }
}
