<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use Sheba\ModificationFields;
use App\Models\FormTemplate;
use Illuminate\Http\Request;


class FormTemplateController extends Controller
{
    use ModificationFields;

    public function store(Request $request)
    {
        return api_response($request, null, 200, ['id' => 1]);
    }

    public function get($form_template, Request $request)
    {
        $data = [
            'id' => 1,
            'title' => 'Contrary to popular be',
            'short_description' => 'simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since',
            'items' => [
                [
                    'id' => 2,
                    'title' => 'Contrary to popular be',
                    'short_description' => 'Cimply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standar',
                    'type' => 'text',
                ],
                [
                    'id' => 3,
                    'title' => 'Contrary to popular be',
                    'short_description' => 'Cimply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standar',
                    'type' => 'number',
                ],
                [
                    'id' => 4,
                    'title' => 'Contrary to popular be',
                    'short_description' => 'Cimply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standar',
                    'type' => 'radio'
                ],
            ]
        ];
        return api_response($request, null, 200, ['form_template' => $data]);
    }

    public function index($business, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $this->setModifier($member);
            $form_templates = FormTemplate::where('owner_id', $business->id)->published()->orderBy('id', 'DESC')->get();
            $templates = [];
            foreach ($form_templates as $template) {

                $template = [
                    'id' => $template->id,
                    'title' => $template->title,
                    'long_description' => $template->long_description,
                ];
                array_push($templates, $template);
            }
            if (count($templates) > 0) return api_response($request, $templates, 200, ['templates' => $templates]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}