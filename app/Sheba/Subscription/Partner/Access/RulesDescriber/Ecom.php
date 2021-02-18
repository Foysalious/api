<?php namespace Sheba\Subscription\Partner\Access\RulesDescriber;

/**
 * @property string $PRODUCT_PUBLISH
 * @property string $WEBSTORE_PUBLISH
 */
class Ecom extends BaseRule
{
    protected $PRODUCT_PUBLISH = "product_publish";
    protected $WEBSTORE_PUBLISH = "webstore_publish";

    protected function register($name, $prefix)
    {
        // TODO: Implement register() method.
    }
}