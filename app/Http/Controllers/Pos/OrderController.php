<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;
use Sheba\Pos\Order\Creator;

class OrderController extends Controller
{
    use ModificationFields;

    public function index(Request $request)
    {
        $orders = [
            [
                'date' => '12 Feb 2019',
                'orders' => [
                    [
                        'code' => '156412',
                        'time' => '5:20PM',
                        'status' => 'paid',
                    ],
                    [
                        'code' => '156412',
                        'time' => '5:20PM',
                        'status' => 'due',
                    ]
                ]
            ],
            [
                'date' => '14 Feb 2019',
                'orders' => [
                    [
                        'code' => '156412',
                        'time' => '5:20PM',
                        'status' => 'paid',
                    ],
                    [
                        'code' => '156412',
                        'time' => '5:20PM',
                        'status' => 'due',
                    ]
                ]
            ]
        ];
        //To do => Filter Collection By Query
        try {
            return api_response($request, $orders, 200, ['orders' => $orders]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request, Creator $creator)
    {
        try {
            $this->validate($request, []);
            $this->setModifier($request->partner);

            $order = $creator->setData($request->all())->create();

            return api_response($request, null, 200, ['msg' => 'Order Created Successfully', 'order' => $order]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
