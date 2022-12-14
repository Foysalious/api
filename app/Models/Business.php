<?php namespace App\Models;

use App\Sheba\Business\Attendance\HalfDaySetting\HalfDayType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Sheba\Business\AttendanceActionLog\TimeByBusiness;
use Sheba\Business\CoWorker\Statuses;
use Sheba\Dal\Announcement\Announcement;
use Sheba\Dal\BaseModel;
use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;
use Sheba\Dal\BusinessPayslip\BusinessPayslip;
use Sheba\Dal\BusinessShift\BusinessShift;
use Sheba\Dal\LeaveType\Model as LeaveTypeModel;
use Sheba\Dal\LiveTrackingSettings\Contract;
use Sheba\Dal\LiveTrackingSettings\LiveTrackingSettings;
use Sheba\Dal\OfficePolicy\OfficePolicy;
use Sheba\Dal\OfficePolicy\Type;
use Sheba\Dal\OfficePolicyRule\OfficePolicyRule;
use Sheba\Dal\PayrollSetting\PayrollSetting;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Helpers\TimeFrame;
use Sheba\ModificationFields;
use Sheba\Payment\PayableUser;
use Sheba\Reward\Rewardable;
use Sheba\Transactions\Types;
use Sheba\Wallet\Wallet;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpTrait;
use Sheba\TopUp\TopUpTransaction;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Sheba\Dal\BusinessAttendanceTypes\Model as BusinessAttendanceType;
use Sheba\Dal\BusinessOffice\Model as BusinessOffice;
use Sheba\Dal\BusinessOfficeHours\Model as BusinessOfficeHour;

class Business extends BaseModel implements TopUpAgent, PayableUser, HasWalletTransaction, Rewardable
{
    use Wallet, ModificationFields, TopUpTrait, HasFactory;

    protected $guarded = ['id'];
    const BUSINESS_FISCAL_START_MONTH = 7;

    public function offices()
    {
        return $this->hasMany(BusinessOffice::class)->where('is_location', 0);
    }

    public function geoOffices()
    {
        return $this->hasMany(BusinessOffice::class)->where('is_location', 1);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function departments()
    {
        return $this->hasMany(BusinessDepartment::class);
    }

    public function members()
    {
        return $this->belongsToMany(Member::class)->withTimestamps();
    }

    public function membersWithProfileAndAccessibleBusinessMember()
    {
        return $this->members()->select('members.id', 'profile_id', 'social_links')->with([
            'profile' => function ($q) {
                $q->select('profiles.id', 'name', 'mobile', 'email', 'dob', 'blood_group', 'pro_pic');
            }, 'businessMember' => function ($q) {
                $q->select('business_member.id', 'business_id', 'member_id', 'type', 'business_role_id', 'status')->with([
                    'role' => function ($q) {
                        $q->select('business_roles.id', 'business_department_id', 'name')->with([
                            'businessDepartment' => function ($q) {
                                $q->select('business_departments.id', 'business_id', 'name');
                            }
                        ]);
                    }
                ]);
            }
        ])->wherePivot('status', '<>', Statuses::INACTIVE);
    }

    /**
     * @return mixed
     */
    public function membersWithProfile()
    {
        return $this->members()->select('members.id', 'profile_id',
            'emergency_contract_person_name', 'emergency_contract_person_number', 'emergency_contract_person_relationship')->with([
            'profile' => function ($q) {
                $q->select('profiles.id', 'name', 'mobile', 'email', 'dob', 'address', 'nationality', 'nid_no', 'tin_no')->with('banks');
            },
            'businessMember' => function ($q) {
                $q->select('business_member.id', 'business_id', 'member_id', 'type', 'business_role_id', 'status')->with([
                    'role' => function ($q) {
                        $q->select('business_roles.id', 'business_department_id', 'name')->with([
                            'businessDepartment' => function ($q) {
                                $q->select('business_departments.id', 'business_id', 'name');
                            }
                        ]);
                    }
                ]);
            }
        ])->wherePivot('status', '<>', Statuses::INACTIVE);
    }

    public function getAllBusinessMember()
    {
        return BusinessMember::where('business_id', $this->id)->with([
            'member' => function ($q) {
                $q->select('members.id', 'profile_id')->with([
                    'profile' => function ($q) {
                        $q->select('profiles.id', 'name', 'mobile', 'email', 'pro_pic', 'dob', 'address', 'nationality', 'nid_no', 'tin_no');
                    }
                ]);
            }, 'role' => function ($q) {
                $q->select('business_roles.id', 'business_department_id', 'name')->with([
                    'businessDepartment' => function ($q) {
                        $q->select('business_departments.id', 'business_id', 'name');
                    }
                ]);
            }
        ]);
    }

    public function getActiveBusinessMember()
    {
        return BusinessMember::where('business_id', $this->id)->where('status', Statuses::ACTIVE)->with([
            'member' => function ($q) {
                $q->select('members.id', 'profile_id')->with([
                    'profile' => function ($q) {
                        $q->select('profiles.id', 'name', 'mobile', 'email', 'pro_pic');
                    }
                ]);
            }, 'role' => function ($q) {
                $q->select('business_roles.id', 'business_department_id', 'name')->with([
                    'businessDepartment' => function ($q) {
                        $q->select('business_departments.id', 'business_id', 'name');
                    }
                ]);
            }
        ]);
    }

    public function getAccessibleBusinessMember()
    {
        return BusinessMember::where('business_id', $this->id)->where('status', '<>', Statuses::INACTIVE)->with([
            'member' => function ($q) {
                $q->select('members.id', 'profile_id')->with([
                    'profile' => function ($q) {
                        $q->select('profiles.id', 'name', 'mobile', 'email', 'pro_pic');
                    }
                ]);
            }, 'role' => function ($q) {
                $q->select('business_roles.id', 'business_department_id', 'name')->with([
                    'businessDepartment' => function ($q) {
                        $q->select('business_departments.id', 'business_id', 'name');
                    }
                ]);
            }
        ]);
    }

    public function getAccessibleInactiveBusinessMember()
    {
        return BusinessMember::where('business_id', $this->id)->where('status', Statuses::INACTIVE)->with([
            'member' => function ($q) {
                $q->select('members.id', 'profile_id')->with([
                    'profile' => function ($q) {
                        $q->select('profiles.id', 'name', 'mobile', 'email', 'pro_pic');
                    }
                ]);
            }, 'role' => function ($q) {
                $q->select('business_roles.id', 'business_department_id', 'name')->with([
                    'businessDepartment' => function ($q) {
                        $q->select('business_departments.id', 'business_id', 'name');
                    }
                ]);
            }
        ]);
    }

    /**
     * @return mixed
     */
    public function getAllBusinessMemberExceptInvited()
    {
        return BusinessMember::where('business_id', $this->id)->where('status', '<>', Statuses::INVITED)->with([
            'member' => function ($q) {
                $q->select('members.id', 'profile_id')->with([
                    'profile' => function ($q) {
                        $q->select('profiles.id', 'name', 'mobile', 'email', 'pro_pic', 'address');
                    }
                ]);
            }, 'role' => function ($q) {
                $q->select('business_roles.id', 'business_department_id', 'name')->with([
                    'businessDepartment' => function ($q) {
                        $q->select('business_departments.id', 'business_id', 'name');
                    }
                ]);
            }, 'leaves' => function ($q) {
                $q->select('id', 'title', 'business_member_id', 'leave_type_id', 'start_date', 'end_date', 'note', 'total_days', 'left_days', 'status')->with([
                    'leaveType' => function ($query) {
                        $query->withTrashed()->select('id', 'business_id', 'title', 'total_days', 'deleted_at');
                    }]);
            }, 'attendances' => function ($q) {
                $q->with('actions');
            },'shifts'
        ]);
    }

    /**
     * @return array
     */
    public function getBusinessMemberProrate()
    {
        $business_member_leave_types = [];
        $this->getAccessibleBusinessMember()->get()->each(function ($business_member) use (&$business_member_leave_types) {
            $leave_types = [];
            if (!$business_member->leaveTypes->isEmpty()) {
                $business_member->leaveTypes->each(function ($leave_type) use (&$leave_types) {
                    $leave_types[$leave_type->leave_type_id] = ['total_days' => $leave_type->total_days];
                });
                $business_member_leave_types[$business_member->id] = ['leave_types' => $leave_types];
            }
        });
        return $business_member_leave_types;
    }

    public function businessSms()
    {
        return $this->hasMany(BusinessSmsTemplate::class);
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class, 'business_partners');
    }

    public function officeHour()
    {
        return $this->hasOne(BusinessOfficeHour::class);
    }

    public function payrollSetting()
    {
        return $this->hasOne(PayrollSetting::class);
    }

    public function payslipSummary()
    {
        return $this->hasMany(BusinessPayslip::class);
    }

    public function activePartners()
    {
        return $this->partners()->where('is_active_for_b2b', 1);
    }

    public function deliveryAddresses()
    {
        return $this->hasMany(BusinessDeliveryAddress::class);
    }

    public function bankInformations()
    {
        return $this->hasMany(BusinessBankInformations::class);
    }

    public function joinRequests()
    {
        return $this->morphMany(JoinRequest::class, 'organization');
    }

    public function businessCategory()
    {
        return $this->belongsTo(BusinessCategory::class);
    }

    public function businessTrips()
    {
        return $this->hasMany(BusinessTrip::class);
    }

    public function businessTripRequests()
    {
        return $this->hasMany(BusinessTripRequest::class);
    }

    public function bonusLogs()
    {
        return $this->morphMany(BonusLog::class, 'user');
    }

    public function topups()
    {
        return $this->hasMany(TopUpOrder::class, 'agent_id')->where('agent_type', self::class);
    }

    public function movieTicketOrders()
    {
        return $this->morphMany(MovieTicketOrder::class, 'agent');
    }

    public function shebaCredit()
    {
        return $this->wallet + $this->shebaBonusCredit();
    }

    public function shebaBonusCredit()
    {
        return (double)$this->bonuses()->where('status', 'valid')->sum('amount');
    }

    public function bonuses()
    {
        return $this->morphMany(Bonus::class, 'user');
    }

    public function transactions()
    {
        return $this->hasMany(BusinessTransaction::class);
    }

    public function vehicles()
    {
        return $this->morphMany(Vehicle::class, 'owner');
    }

    public function businessSmsTemplates()
    {
        return $this->hasMany(BusinessSmsTemplate::class, 'business_id');
    }

    public function procurements()
    {
        return $this->morphMany(Procurement::class, 'owner');
    }

    public function hiredVehicles()
    {
        return $this->morphMany(HiredVehicle::class, 'hired_by');
    }

    public function hiredDrivers()
    {
        return $this->morphMany(HiredDriver::class, 'hired_by');
    }

    public function getCommission()
    {
        return new \Sheba\TopUp\Commission\Business();
    }

    public function topUpTransaction(TopUpTransaction $transaction)
    {
        (new WalletTransactionHandler())->setModel($this)
            ->setAmount($transaction->getAmount())
            ->setType(Types::debit())->setLog($transaction->getLog())
            ->setSource(TransactionSources::TOP_UP)->dispatch();
    }

    public function getMobile()
    {
        return '+8801678242934';
    }

    public function getContactPerson()
    {
        if ($super_admin = $this->getAdmin()) return $super_admin->profile->name;
        return null;
    }

    public function getContactNumber()
    {
        if ($super_admin = $this->getAdmin()) return $super_admin->profile->mobile;
        return null;
    }

    public function getContactEmail()
    {
        if ($super_admin = $this->getAdmin()) return $super_admin->profile->email;
        return null;
    }

    public function getAdmin()
    {
        if ($super_admin = $this->superAdmins()->first()) return $super_admin;
        return null;
    }

    public function superAdmins()
    {
        return $this->belongsToMany(Member::class)->where('is_super', 1);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function leaveTypes()
    {
        return $this->hasMany(LeaveTypeModel::class);
    }

    public function attendanceTypes()
    {
        return $this->hasMany(BusinessAttendanceType::class);
    }

    public function getBusinessFiscalPeriod()
    {
        $business_fiscal_start_month = $this->fiscal_year ?: Business::BUSINESS_FISCAL_START_MONTH;
        $time_frame = new TimeFrame();
        return $time_frame->forAFiscalYear(Carbon::now(), $business_fiscal_start_month);
    }

    public function isRemoteAttendanceEnable($business_member_id = null)
    {
        if ($this->isShebaTech($business_member_id)) return true;
        return in_array(AttendanceTypes::REMOTE, $this->attendanceTypes->pluck('attendance_type')->toArray());
    }

    public function isGeoLocationAttendanceEnable()
    {
        return in_array(AttendanceTypes::GEO_LOCATION_BASED, $this->attendanceTypes->pluck('attendance_type')->toArray());
    }

    public function isShebaTech($business_member_id)
    {
        $sheba_tech = [574, 586, 847, 922, 1031, 4493, 6885, 7102, 2111, 585, 587, 588, 592, 593, 594, 596, 597, 600, 604,
            606, 611, 614, 615, 616, 620, 634, 636, 641, 642, 645, 654, 687, 696, 731, 736, 816, 841, 844, 907, 910, 911, 919, 1091, 1093,
            1826, 1838, 1858, 1859, 1860, 1861, 1862, 1961, 1963, 2030, 2031, 2032, 2033, 2034, 2108, 2109, 3122, 3128, 3130, 3171, 3368,
            3369, 3370, 3371, 3660, 3661, 3662, 3666, 3667, 3668, 3669, 3674, 3935, 3936, 4487, 4488, 4489, 4808, 4809, 4810, 4913, 4931,
            5089, 5125, 6886, 7105, 7360, 7540, 7543, 8301, 8899, 9278, 9635, 10053, 10054, 10581, 10986, 12032, 12034, 13371,578,648,
            6880,10056,10591,13373,577,583,586,750,2499,2794,2795,4493,6439,6824,6832,7545,7546,8308,8897,10582,10988,11186,11652,11653,13522];
        return in_array($business_member_id, $sheba_tech);
    }

    public function getBusinessHalfDayConfiguration()
    {
        return json_decode($this->half_day_configuration, 1);
    }

    public function halfDayStartEnd($which_half)
    {
        return $this->getBusinessHalfDayConfiguration()[$which_half];
    }

    public function halfDayStartTimeUsingWhichHalf($which_half)
    {
        return $this->getBusinessHalfDayConfiguration()[$which_half]['start_time'];
    }

    public function halfDayEndTimeUsingWhichHalf($which_half)
    {
        return $this->getBusinessHalfDayConfiguration()[$which_half]['end_time'];
    }

    public function halfDayStartEndTime($which_half)
    {
        $half_day_configuration = $this->halfDayStartEnd($which_half);
        $start_time = Carbon::parse($half_day_configuration['start_time'])->format('h:i');
        $end_time = Carbon::parse($half_day_configuration['end_time'])->format('h:i');
        return $start_time . '-' . $end_time;
    }

    public function fullDayStartEndTime()
    {
        $full_day_configuration = $this->officeHour;
        $start_time = Carbon::parse($full_day_configuration->start_time)->format('h:i');
        $end_time = Carbon::parse($full_day_configuration->end_time)->format('h:i');
        return $start_time . '-' . $end_time;
    }

    public function calculationTodayLastCheckInTime($which_half_day)
    {
        if ($which_half_day) {
            if ($which_half_day == HalfDayType::FIRST_HALF) {
                # If A Employee Has Leave On First_Half, Office Start Time Will Be Second_Half Start_Time
                $last_checkin_time = Carbon::parse($this->halfDayStartTimeUsingWhichHalf(HalfDayType::SECOND_HALF));
                if ($this->officeHour->is_start_grace_time_enable) return $last_checkin_time->addMinutes($this->officeHour->start_grace_time);
                return $last_checkin_time;
            }
            if ($which_half_day == HalfDayType::SECOND_HALF) {
                $last_checkin_time = Carbon::parse($this->halfDayStartTimeUsingWhichHalf(HalfDayType::FIRST_HALF));
                if ($this->officeHour->is_start_grace_time_enable) return $last_checkin_time->addMinutes($this->officeHour->start_grace_time);
                return $last_checkin_time;
            }
        } else {
            $last_checkin_time = (new TimeByBusiness())->getOfficeStartTimeByBusiness();
            if (is_null($last_checkin_time)) return null;
            return Carbon::parse($last_checkin_time);
        }
    }

    public function calculationTodayLastCheckOutTime($which_half_day)
    {
        if ($which_half_day) {
            if ($which_half_day == HalfDayType::FIRST_HALF) {
                $checkout_time = $this->halfDayEndTimeUsingWhichHalf(HalfDayType::SECOND_HALF);
                if ($this->officeHour->is_end_grace_time_enable) {
                    return Carbon::parse($checkout_time)->subMinutes($this->officeHour->end_grace_time)->format('H:i:s');
                }
                return $checkout_time;
            }
            if ($which_half_day == HalfDayType::SECOND_HALF) {
                $checkout_time = $this->halfDayEndTimeUsingWhichHalf(HalfDayType::FIRST_HALF);
                if ($this->officeHour->is_end_grace_time_enable) {
                    return Carbon::parse($checkout_time)->subMinutes($this->officeHour->end_grace_time)->format('H:i:s');
                }
                return $checkout_time;
            }
        } else {
            $checkout_time = (new TimeByBusiness())->getOfficeEndTimeByBusiness();
            if (is_null($checkout_time)) return null;
            return $checkout_time;
        }
    }

    public function isIpBasedAttendanceEnable()
    {
        if (in_array(AttendanceTypes::IP_BASED, $this->attendanceTypes->pluck('attendance_type')->toArray())) return true;
        return false;
    }

    public function policy()
    {
        return $this->hasMany(OfficePolicyRule::class);
    }

    public function gracePolicy()
    {
        return $this->policy()->where('policy_type', Type::GRACE_PERIOD)->orderBy('from_days');
    }

    public function unpaidLeavePolicy()
    {
        return $this->policy()->where('policy_type', Type::UNPAID_LEAVE)->orderBy('from_days');
    }

    public function checkinCheckoutPolicy()
    {
        return $this->policy()->where('policy_type', Type::LATE_CHECKIN_EARLY_CHECKOUT)->orderBy('from_days');
    }

    public function shifts()
    {
        return $this->hasMany(BusinessShift::class);
    }

    public function liveTrackingSettings()
    {
        return $this->hasOne(LiveTrackingSettings::class);
    }

    public function getTrackLocationActiveBusinessMember()
    {
        return BusinessMember::where('business_id', $this->id)->where('is_live_track_enable', 1)->where('status', Statuses::ACTIVE)->with([
            'member' => function ($q) {
                $q->select('members.id', 'profile_id')->with([
                    'profile' => function ($q) {
                        $q->select('profiles.id', 'name', 'mobile', 'email', 'pro_pic');
                    }
                ]);
            }, 'role' => function ($q) {
                $q->select('business_roles.id', 'business_department_id', 'name')->with([
                    'businessDepartment' => function ($q) {
                        $q->select('business_departments.id', 'business_id', 'name');
                    }
                ]);
            }
        ]);
    }

    public function currentIntervalSetting()
    {
        return $this->liveTrackingSettings->intervalSettingLogs()->where('end_date', null)->latest()->first();
    }

    public function isEligibleForLunch(): bool
    {
        return in_array($this->id, config('b2b.BUSINESSES_IDS_FOR_LUNCH'));
    }

    public function isShebaPlatform(): bool
    {
        return in_array($this->id, config('b2b.BUSINESSES_IDS_FOR_REFERRAL'));
    }

    public function isShiftEnabled(): bool
    {
        return (bool) $this->is_shift_enable;
    }

    public function isLiveTrackEnabled(): bool
    {
        return $this->liveTrackingSettings() ? $this->liveTrackingSettings->is_enable : false;
    }

}