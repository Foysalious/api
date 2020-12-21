<?php namespace Sheba\Cache\Category\Children\Services;

use App\Http\Controllers\CategoryController;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;
use Sheba\Cache\Exceptions\CacheGenerationException;
use Sheba\Checkout\DeliveryCharge;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\Service\MinMaxPrice;
use Sheba\Services\ServiceSubscriptionDiscount;
use Sheba\Subscription\ApproximatePriceCalculator;
use Throwable;

class ServicesDataStore implements DataStoreObject
{
    /** @var ServicesCacheRequest */
    private $servicesCacheRequest;


    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->servicesCacheRequest = $cache_request;
        return $this;
    }

    /**
     * @inheritDoc
     * @throws CacheGenerationException
     */
    public function generate()
    {
        try {
            /*
            $client = new Client();
            $url = config('sheba.api_url') . '/v2/categories/' . $this->servicesCacheRequest->getCategoryId();
            $url .= '/services?location=' . $this->servicesCacheRequest->getLocationId();
            $response = $client->get($url);
            */

            $request = new Request();
            $request->replace(['location' => $this->servicesCacheRequest->getLocationId()]);

            /** @var CategoryController $controller */
            $controller = app(CategoryController::class);
            $response = $controller->getServices($this->servicesCacheRequest->getCategoryId(), $request, app(PriceCalculation::class),
                app(DeliveryCharge::class), app(JobDiscountHandler::class), app(UpsellCalculation::class),
                app(MinMaxPrice::class), app(ApproximatePriceCalculator::class), app(ServiceSubscriptionDiscount::class)
            );

        } catch (Throwable $e) {
            throw new CacheGenerationException();
        }

        $data = $response->getData();
        if (!$data || $data->code != 200) return null;
        return ['category' => $data->category];
    }
}
