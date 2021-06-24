<?php namespace App\Http\Requests;


use App\Models\Affiliate;

class PartnerOnBoardModerationRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->affiliate && is_string($this->affiliate)) {
            $this->affiliate = Affiliate::find($this->affiliate);
        }
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
            'lat' => 'required|numeric',
            'lng' => 'required|numeric'
        ];
    }
}
