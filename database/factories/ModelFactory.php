<?php

use Factory\Factory;
use Factory\CategoryFactory;
use Factory\ServiceFactory;
use Factory\LocationFactory;
use Factory\ProfileFactory;
use Factory\AffiliateFactory;
use Factory\AuthorizationRequestFactory;
use Factory\AuthorizationTokenFactory;
use Factory\TopUpVendorFactory;
use Factory\TopUpVendorCommissionFactory;
use Factory\TopUpOTFSettingsFactory;
use Factory\TopUpVendorOTFFactory;
use Factory\TopUpVendorOTFChangeLogFactory;
use Factory\CustomerFactory;
use Factory\ResourceFactory;
use Factory\MemberFactory;
use Factory\BusinessFactory;
use Factory\BusinessMemberFactory;
use Factory\BusinessHolidayFactory;

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
    MemberFactory::class,
    BusinessFactory::class,
    BusinessMemberFactory::class,
    BusinessHolidayFactory::class

];

foreach ($factory_classes as $factory_class) {
    /** @var Factory $f */
    $f = (new $factory_class($factory));
    $f->handle();
}
