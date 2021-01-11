<?php

use Factories\Factory;
use Factories\CategoryFactory;
use Factories\ServiceFactory;
use Factories\LocationFactory;
use Factories\ProfileFactory;
use Factories\AffiliateFactory;
use Factories\AuthorizationRequestFactory;
use Factories\AuthorizationTokenFactory;
use Factories\TopUpVendorFactory;
use Factories\TopUpVendorCommissionFactory;
use Factories\TopUpOTFSettingsFactory;
use Factories\TopUpVendorOTFFactory;
use Factories\TopUpVendorOTFChangeLogFactory;
use Factories\CustomerFactory;
use Factories\ResourceFactory;
use Factories\MemberFactory;

$factory_classes = [
    CategoryFactory::class,
    ServiceFactory::class,
    LocationFactory::class,
    ProfileFactory::class,
    AffiliateFactory::class,
    AuthorizationRequestFactory::class,
    AuthorizationTokenFactory::class,
    TopUpVendorFactory::class,
    TopUpVendorCommissionFactory::class,
    TopUpOTFSettingsFactory::class,
    TopUpVendorOTFFactory::class,
    TopUpVendorOTFChangeLogFactory::class,
    CustomerFactory::class,
    ResourceFactory::class,
    MemberFactory::class

];

foreach ($factory_classes as $factory_class) {
    /** @var Factory $f */
    $f = (new $factory_class($factory));
    $f->handle();
}
