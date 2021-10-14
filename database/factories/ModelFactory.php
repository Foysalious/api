<?php

use Factory\Factory;
use Factory\CategoryFactory;
use Factory\PartnerBonus;
use Factory\PartnerSubscriptionPackageFactory;
use Factory\JobFactory;
use Factory\PartnerFactory;
use Factory\PartnerOrderFactory;
use Factory\PartnerResourceFactory;
use Factory\ResourceScheduleFactory;
use Factory\ServiceFactory;
use Factory\LocationFactory;
use Factory\ProfileFactory;
use Factory\AffiliateFactory;
use Factory\AuthorizationRequestFactory;
use Factory\AuthorizationTokenFactory;
use Factory\TopupBlacklistNumbersFactory;
use Factory\TopUpVendorFactory;
use Factory\TopUpVendorCommissionFactory;
use Factory\TopUpOTFSettingsFactory;
use Factory\TopUpVendorOTFFactory;
use Factory\TopUpVendorOTFChangeLogFactory;
use Factory\CustomerFactory;
use Factory\ResourceFactory;
use Factory\MemberFactory;
use Factory\OrderFactory;
use Factory\PartnerOrderRequestFactory;
use Factory\CustomerDeliveryAddressFactory;
use Factory\ScheduleSlotsFactory;
use Factory\JobServiceFactory;
use Factory\CategoryPartnerFactory;
use Factory\BusinessFactory;
use Factory\BusinessMemberFactory;
use Factory\BusinessHolidayFactory;
use Factory\CategoryLocationFactory;
use Factory\PosOrderFactory;
use Factory\PosCustomerFactory;
use Factory\PartnerDeliveryInfoFactory;
use Factory\PartnerPosServiceFactory;
use Factory\PartnerPosCategoryFactory;
use Factory\PosCategoriesFactory;
use Factory\PosOrderPaymentFactory;
use Factory\InfoCallFactory;
use Factory\InfoCallRejectReasonFactory;
use Factory\InfoCallStatusLogFactory;
use Factory\ResourceTransactionFactory;
use \Factory\SubscriptionWisePaymentGatewaysFactory;
use \Factory\LocationServiceFactory;
use Factory\AttendanceFactory;
use Factory\BusinessOfficeHoursFactory;
use Factory\BusinessWeekend;
use Factory\PayrollSettingFactory;
use Factory\PayrollComponentFactory;
use Factory\ComponentPackageTargetFactory;
use Factory\AttendanceActionLogFactory;
use Factory\PayrollComponentPackageFactory;
use Factory\LeaveFactory;
use Factory\LeaveTypeFactory;
use Factory\BusinessMemberLeaveTypeFactory;
use Factory\OfficePolicyRuleFactory;
use Factory\DivisionFactory;
use Factory\DistrictFactory;
use Factory\ThanaFactory;
use Factory\PaymentGatewayFactory;

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
    TopupBlacklistNumbersFactory::class,
    PartnerSubscriptionPackageFactory::class,
    PartnerFactory::class,
    PartnerResourceFactory::class,
    OrderFactory::class,
    PartnerOrderFactory::class,
    JobFactory::class,
    PartnerOrderRequestFactory::class,
    CustomerDeliveryAddressFactory::class,
    ScheduleSlotsFactory::class,
    JobServiceFactory::class,
    CategoryPartnerFactory::class,
    ResourceScheduleFactory::class,
    TopupBlacklistNumbersFactory::class,
    PartnerSubscriptionPackageFactory::class,
    BusinessFactory::class,
    BusinessMemberFactory::class,
    BusinessHolidayFactory::class,
    PartnerBonus::class,
    CategoryLocationFactory::class,
    PosOrderFactory::class,
    PosCustomerFactory::class,
    PartnerDeliveryInfoFactory::class,
    PartnerPosServiceFactory::class,
    PartnerPosCategoryFactory::class,
    PosCategoriesFactory::class,
    PosOrderPaymentFactory::class,
    InfoCallFactory::class,
    InfoCallRejectReasonFactory::class,
    InfoCallStatusLogFactory::class,
    ResourceTransactionFactory::class,
    SubscriptionWisePaymentGatewaysFactory::class,
    LocationServiceFactory::class,
    AttendanceFactory::class,
    BusinessOfficeHoursFactory::class,
    BusinessWeekend::class,
    PayrollSettingFactory::class,
    PayrollComponentFactory::class,
    ComponentPackageTargetFactory::class,
    AttendanceActionLogFactory::class,
    PayrollComponentPackageFactory::class,
    LeaveFactory::class,
    LeaveTypeFactory::class,
    BusinessMemberLeaveTypeFactory::class,
    OfficePolicyRuleFactory::class,
    DivisionFactory::class,
    DistrictFactory::class,
    ThanaFactory::class,
    PaymentGatewayFactory::class

];

foreach ($factory_classes as $factory_class) {
    /** @var Factory $f */
    $f = new $factory_class($factory);
    $f->handle();
}
