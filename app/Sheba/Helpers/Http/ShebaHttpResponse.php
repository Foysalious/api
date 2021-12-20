<?php namespace Sheba\Helpers\Http;


use Throwable;

class ShebaHttpResponse
{
    public function __get($name)
    {
        return ['message' => self::getMessage((int)$name)];
    }

    /**
     * @param $name
     * @return string
     */
    private static function getMessage($name)
    {
        $preserve_response = [
            200 => 'Successful',
            202 => 'Successful',
            303 => 'Partial Updates Successful',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            409 => 'Conflict',
            420 => 'Not Allowed',
            421 => 'Misdirected.',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
            0   => 'Internal Server Error'
        ];

        try {
            return $preserve_response[$name];
        } catch (Throwable $e) {
            return 'Something Went Wrong';
        }
    }
}