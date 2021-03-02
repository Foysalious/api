<?php namespace App\Http\Requests;


use Sheba\AppVersion\Apps;

class AppVersionRequest extends ApiRequest
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
            'app' => "string|in:" . Apps::implode(),
            'version' => 'required_with:app|integer'
        ];
    }

    public function wantsSingleApp()
    {
        return $this->request->has('version') && $this->request->has('app');
    }
}