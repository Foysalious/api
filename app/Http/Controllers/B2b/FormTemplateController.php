<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\FormTemplateRepositoryInterface;
use Illuminate\Http\Request;


class FormTemplateController extends Controller
{

    public function store(Request $request, FormTemplateRepositoryInterface $formTemplateRepository)
    {
        try {
            $formTemplateRepository->create(['ad'=>44]);
            return api_response($request, null, 200, ['id' => 1]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
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
}