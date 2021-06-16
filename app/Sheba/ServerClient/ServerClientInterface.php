<?php


namespace App\Sheba\ServerClient;


interface ServerClientInterface
{
    public function get($uri);
    public function call($method, $uri, $data = null, $multipart = false);
}