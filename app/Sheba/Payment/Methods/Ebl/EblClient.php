<?php


namespace Sheba\Payment\Methods\Ebl;


use ReflectionException;
use Sheba\Payment\Methods\Ebl\Stores\EblStore;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPProxyServerError;
use Sheba\TPProxy\TPProxyTestClient;
use Sheba\TPProxy\TPRequest;

class EblClient
{

    /**
     * @var TPProxyClient
     */
    private $client;
    /** @var EblStore $store */
    private $store;

    public function __construct(TPProxyTestClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param EblStore $store
     * @return EblClient
     */
    public function setStore($store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * @param EblInputs $eblInputs
     * @throws ReflectionException|TPProxyServerError
     */
    public function init(EblInputs $eblInputs)
    {
        $payload = $eblInputs->toArray();
        dd($payload(()));
        $request = (new TPRequest())->setInput($payload)->setMethod(TPRequest::METHOD_POST)->setUrl($this->store->getBaseUrl() . '/token/create');
        $res     = $this->client->call($request);
        dd($res);
    }

    /**
     * @return EblClient
     */
    public static function get()
    {

        return app(EblClient::class);
    }

}

