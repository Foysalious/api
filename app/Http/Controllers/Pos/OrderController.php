<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\PosOrder;

use App\Transformers\JobTransformer;
use App\Transformers\PosOrderTransformer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
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

    public function show(Request $request)
    {
        try {
            $order = PosOrder::with('items')->find($request->order)->calculate();
            $manager = new Manager();
            $manager->setSerializer(new ArraySerializer());
            $resource = new Item($order, new PosOrderTransformer());
            $order = $manager->createData($resource)->toArray();

            return api_response($request, null, 200, ['order' => $order]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store(Request $request, Creator $creator)
    {
        try {
            $this->validate($request, []);
            $this->setModifier($request->manager_resource);

            $order = $creator->setData($request->all())->create();

            return api_response($request, null, 200, ['msg' => 'Order Created Successfully', 'order' => $order]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getSelectColumnsOfItem()
    {
        return ['service_name', 'app_thumb', 'app_banner', 'price', 'stock'];
    }
}
