<?php namespace Sheba\Business\Attendance;

use App\Models\Business;
use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Dal\Attendance\EloquentImplementation;
use Sheba\Dal\Attendance\Contract as AttendanceRepositoryInterface;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Dal\AttendanceActionLog\Contract as AttendanceActionLogRepositoryInterface;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\AttendanceActionLog\RemoteMode;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\Leave\Contract as LeaveRepositoryInterface;
use Sheba\Dal\Attendance\Model;
use Sheba\Dal\Attendance\Statuses;
use Sheba\Dal\Leave\Status;
use Sheba\Helpers\TimeFrame;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class AttendanceList
{
    const ALL = 'all';
    const PRESENT = 'present';
    const ABSENT = 'absent';
    const ON_LEAVE = 'on_leave';
    const CHECKIN_TIME = 'checkin_time';
    const CHECKOUT_TIME = 'checkout_time';
    const STAYING_TIME = 'staying_time';

    /** @var Business */
    private $business;
    /** @var Carbon */
    private $startDate;
    /** @var Carbon */
    private $endDate;
    /** @var Model[] */
    private $attendances;
    /** @var AttendanceRepositoryInterface $attendanceRepositoryInterface */
    private $attendanceRepositoryInterface;
    /** @var AttendanceActionLogRepositoryInterface $attendanceActionLogRepositoryInterface */
    private $attendanceActionLogRepositoryInterface;
    /** @var LeaveRepositoryInterface $leaveRepositoryInterface */
    private $leaveRepositoryInterface;
    /** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepository;
    private $businessDepartmentId;
    private $sort;
    private $sortColumn;
    private $search;
    private $checkinStatus;
    private $checkoutStatus;
    private $status;
    private $statusFilter;
    private $businessMemberId;
    private $usersWhoGiveAttendance;
    private $usersWhoOnLeave;
    private $usersLeaveIds;
    /** @var Collection $departments */
    private $departments;
    /** @var BusinessHolidayRepoInterface $businessHoliday */
    private $businessHoliday;
    /** @var BusinessWeekendRepoInterface $businessWeekend */
    private $businessWeekend;
    private $checkoutLocation;
    private $checkinLocation;
    private $checkinOfficeOrRemote;
    private $checkoutOfficeOrRemote;
    private $checkInRemoteMode;
    private $checkOutRemoteMode;

    /**
     * AttendanceList constructor.
     * @param AttendanceRepositoryInterface $attendance_repository_interface
     * @param AttendanceActionLogRepositoryInterface $attendance_action_log_repository_interface
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @param LeaveRepositoryInterface $leave_repository_interface
     * @param BusinessHolidayRepoInterface $business_holiday_repo
     * @param BusinessWeekendRepoInterface $business_weekend_repo
     */
    public function __construct(AttendanceRepositoryInterface $attendance_repository_interface,
                                AttendanceActionLogRepositoryInterface $attendance_action_log_repository_interface,
                                BusinessMemberRepositoryInterface $business_member_repository,
                                LeaveRepositoryInterface $leave_repository_interface,
                                BusinessHolidayRepoInterface $business_holiday_repo,
                                BusinessWeekendRepoInterface $business_weekend_repo)
    {
        $this->attendanceRepositoryInterface = $attendance_repository_interface;
        $this->attendanceActionLogRepositoryInterface = $attendance_action_log_repository_interface;
        $this->businessMemberRepository = $business_member_repository;
        $this->leaveRepositoryInterface = $leave_repository_interface;
        $this->departments = collect();
        $this->usersWhoGiveAttendance = [];
        $this->usersWhoOnLeave = [];
        $this->usersLeaveIds = [];
        $this->businessHoliday = $business_holiday_repo;
        $this->businessWeekend = $business_weekend_repo;
    }

    /**
     * @param Business $business
     * @return AttendanceList
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @param TimeFrame $selected_date
     * @return AttendanceList
     */
    public function setSelectedDate(TimeFrame $selected_date)
    {
        $this->startDate = $selected_date->start;
        $this->endDate = $selected_date->end;
        return $this;
    }

    /**
     * @param $businessDepartmentId
     * @return AttendanceList
     */
    public function setBusinessDepartment($businessDepartmentId)
    {
        $this->businessDepartmentId = $businessDepartmentId;
        return $this;
    }

    /**
     * @param $sort
     * @return $this
     */
    public function setSortKey($sort)
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param $column
     * @return $this
     */
    public function setSortColumn($column)
    {
        $this->sortColumn = $column;
        return $this;
    }

    /**
     * @param $status_filter
     * @return $this
     */
    public function setStatusFilter($status_filter)
    {
        $this->statusFilter = $status_filter;
        return $this;
    }

    /**
     * @param $businessMemberId
     * @return AttendanceList
     */
    public function setBusinessMemberId($businessMemberId)
    {
        $this->businessMemberId = $businessMemberId;
        return $this;
    }

    /**
     * @param $search
     * @return $this
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @param $checkin_status
     * @return $this
     */
    public function setCheckinStatus($checkin_status)
    {
        $this->checkinStatus = $checkin_status;
        return $this;
    }

    /**
     * @param $checkout_status
     * @return $this
     */
    public function setCheckoutStatus($checkout_status)
    {
        $this->checkoutStatus = $checkout_status;
        return $this;
    }

    /**
     * @param $checkin_office_or_remote
     * @return $this
     */
    public function setOfficeOrRemoteCheckin($checkin_office_or_remote)
    {
        $this->checkinOfficeOrRemote = $checkin_office_or_remote;
        return $this;
    }

    /**
     * @param $checkout_office_or_remote
     * @return $this
     */
    public function setOfficeOrRemoteCheckout($checkout_office_or_remote)
    {
        $this->checkoutOfficeOrRemote = $checkout_office_or_remote;
        return $this;
    }

    /**
     * @param $checkin_location
     * @return $this
     */
    public function setCheckinLocation($checkin_location)
    {
        $this->checkinLocation = $checkin_location;
        return $this;
    }

    /**
     * @param $checkout_location
     * @return $this
     */
    public function setCheckoutLocation($checkout_location)
    {
        $this->checkoutLocation = $checkout_location;
        return $this;
    }

    /**
     * @param $checkin_remote_mode
     * @return $this
     */
    public function setCheckInRemoteMode($checkin_remote_mode)
    {
        $this->checkInRemoteMode = $checkin_remote_mode;
        return $this;
    }

    /**
     * @param $checkout_remote_mode
     * @return $this
     */
    public function setCheckOutRemoteMode($checkout_remote_mode)
    {
        $this->checkOutRemoteMode = $checkout_remote_mode;
        return $this;
    }
    /**
     * @return array
     */
    public function get()
    {
        $this->runAttendanceQueryV2();
        return $this->getDataV2();
    }

    private function withMembers($query)
    {
        return $query->select('id', 'member_id', 'business_role_id', 'employee_id')
            ->with([
                'member' => function ($q) {
                    $q->select('id', 'profile_id')
                        ->with([
                            'profile' => function ($q) {
                                $q->select('id', 'name');
                            }]);
                }, 'role']);
    }

    private function runAttendanceQueryV2()
    {

        $business_member_ids = [];
        if ($this->businessMemberId) $business_member_ids = [$this->businessMemberId];
        elseif ($this->business) $business_member_ids = $this->getBusinessMemberIds();
        $attendances = $this->attendanceRepositoryInterface->builder()
            ->select('id', 'business_member_id', 'checkin_time', 'checkout_time', 'staying_time_in_minutes', 'overtime_in_minutes', 'status', 'date')
            ->whereIn('business_member_id', $business_member_ids)
            ->where('date', '>=', $this->startDate->toDateString())
            ->where('date', '<=', $this->endDate->toDateString())
            ->with([
                'actions' => function ($q) {
                    $q->select('id', 'attendance_id', 'note', 'action', 'status', 'ip', 'is_remote', 'remote_mode', 'location', 'created_at');
                },
                'businessMember' => function ($q) {
                    $this->withMembers($q);
                }]);

        if ($this->businessDepartmentId) {
            $role_ids = $this->getBusinessRoleIds();
            $attendances = $attendances->whereHas('businessMember', function ($q) use ($role_ids) {
                $q->whereIn('business_role_id', $role_ids);
            });
        }

        if ($this->checkinStatus) {
            $attendances = $attendances->whereHas('actions', function ($q) {
                $q->where('status', $this->checkinStatus);
            });
        }

        if ($this->checkoutStatus) {
            $attendances = $attendances->whereHas('actions', function ($q) {
                $q->where('status', $this->checkoutStatus);
            });
        }

        if ($this->checkinOfficeOrRemote) {
            $attendances = $attendances->whereHas('actions', function ($q) {
                $q->where([['is_remote', $this->checkinOfficeOrRemote == 'remote' ? 1 : 0],['action', Actions::CHECKIN]]);
            });
        }

        if ($this->checkoutOfficeOrRemote) {
            $attendances = $attendances->whereHas('actions', function ($q) {
                $q->where([['is_remote', $this->checkoutOfficeOrRemote == 'remote' ? 1 : 0],['action', Actions::CHECKOUT]]);
            });
        }

        if ($this->checkinLocation) {
            $attendances = $attendances->whereHas('actions', function ($q) {
                $q->where([['ip', $this->checkinLocation],['action', Actions::CHECKIN]]);
            });
        }

        if ($this->checkoutLocation) {
            $attendances = $attendances->whereHas('actions', function ($q) {
                $q->where([['ip', $this->checkoutLocation],['action', Actions::CHECKOUT]]);
            });
        }

        if ($this->checkInRemoteMode && $this->checkinOfficeOrRemote == 'remote') {
            if ($this->checkInRemoteMode === RemoteMode::HOME) {
                $attendances = $attendances->whereHas('actions', function ($q) {
                    $q->where([['remote_mode', '<>', RemoteMode::FIELD],['action', Actions::CHECKIN]]);
                    $q->whereNotNull('location');
                });
            }
            if ($this->checkInRemoteMode === RemoteMode::FIELD) {
                $attendances = $attendances->whereHas('actions', function ($q) {
                    $q->where([['remote_mode', RemoteMode::FIELD],['action', Actions::CHECKIN]]);
                    $q->whereNotNull('location');
                });
            }
            if ($this->checkInRemoteMode === RemoteMode::NO_LOCATION) {
                $attendances = $attendances->whereHas('actions', function ($q) {
                    $q->where('action', Actions::CHECKIN)
                        ->whereNull('location');
                });
            }
        }

        if ($this->checkOutRemoteMode && $this->checkoutOfficeOrRemote == 'remote') {
            if ($this->checkOutRemoteMode === RemoteMode::HOME) {
                $attendances = $attendances->whereHas('actions', function ($q) {
                    $q->where([['remote_mode', '<>', RemoteMode::FIELD],['action', Actions::CHECKOUT]]);
                    $q->whereNotNull('location');
                });
            }
            if ($this->checkOutRemoteMode === RemoteMode::FIELD) {
                $attendances = $attendances->whereHas('actions', function ($q) {
                    $q->where([['remote_mode', RemoteMode::FIELD],['action', Actions::CHECKOUT]]);
                    $q->whereNotNull('location');
                });
            }
            if ($this->checkOutRemoteMode === RemoteMode::NO_LOCATION) {
                $attendances = $attendances->whereHas('actions', function ($q) {
                    $q->where('action', Actions::CHECKOUT)
                        ->whereNull('location');
                });
            }
        }

        if ($this->sort && $this->sortColumn) {
            $sort_by = $this->sort === 'asc' ? 'ASC' : 'DESC';
            if ($this->sortColumn == self::CHECKIN_TIME) {
                $attendances = $attendances->orderByRaw("UNIX_TIMESTAMP(checkin_time) $sort_by");
            }
            if ($this->sortColumn == self::CHECKOUT_TIME) {
                $attendances = $attendances->orderByRaw("UNIX_TIMESTAMP(checkout_time) $sort_by");
            }
            if ($this->sortColumn == self::STAYING_TIME) {
                $attendances = $attendances->orderByRaw("staying_time_in_minutes $sort_by");
            }
        } else {
            $attendances = $attendances->orderByRaw('id desc');
        }

        $this->attendances = $attendances->get();
    }

    private function getBusinessMemberIds()
    {
        return $this->businessMemberRepository->where('business_id', $this->business->id)->pluck('id')->toArray();
    }

    /**
     * @return array|null
     */
    private function getBusinessRoleIds()
    {
        /** @var Collection $role_ids */
        $role_ids = BusinessRole::select('id', 'business_department_id')->whereHas('businessDepartment', function ($q) {
            $q->where([['business_id', $this->business->id], ['business_departments.id', $this->businessDepartmentId]]);
        })->get();
        return count($role_ids) > 0 ? $role_ids->pluck('id')->toArray() : [];
    }

    /**
     * @return array|Collection
     */
    private function getDataV2()
    {
        $data = [];
        $this->setDepartments();

        $is_weekend_or_holiday = $this->isWeekendHolidayLeave();
        $business_members_in_leave = $this->getBusinessMemberWhoAreOnLeave();

        if ($this->statusFilter != self::ABSENT) {
            foreach ($this->attendances as $attendance) {
                $checkin_data = $checkout_data = null;
                $is_on_leave = $this->isOnLeave($attendance->businessMember->member->id);
                $is_on_half_day_leave = 0;
                $which_half_day = null;
                if ($is_on_leave) {
                    $leave = $this->usersLeaveIds[$attendance->businessMember->member->id];
                    $is_on_half_day_leave = $leave['leave']['is_half_day_leave'];
                    $which_half_day = $leave['leave']['which_half_day'];
                }

                if ($this->statusFilter == self::ON_LEAVE && !$is_on_half_day_leave) continue;
                array_push($this->usersWhoGiveAttendance, $attendance->businessMember->member->id);

                if (
                    !$is_on_half_day_leave &&
                    $is_on_leave && (!!$this->checkinStatus || !!$this->checkoutStatus)
                ) continue;

                foreach ($attendance->actions as $action) {
                    if ($action->action == Actions::CHECKIN) {
                        $checkin_data = collect([
                            'status' => $this->getStatusBasedOnLeaveAction($action, $is_weekend_or_holiday, $is_on_leave, $is_on_half_day_leave),
                            'is_remote' => $action->is_remote ?: 0,
                            'address' => $action->is_remote && $action->location ? json_decode($action->location)->address : null,
                            'checkin_time' => Carbon::parse($attendance->date . ' ' . $attendance->checkin_time)->format('g:i a'),
                            'note' => $action->note,
                            'remote_mode' => $action->remote_mode ?: null
                        ]);
                    }
                    if ($action->action == Actions::CHECKOUT) {
                        $checkout_data = collect([
                            'status' => $this->getStatusBasedOnLeaveAction($action, $is_weekend_or_holiday, $is_on_leave, $is_on_half_day_leave),
                            'is_remote' => $action->is_remote ?: 0,
                            'address' => $action->is_remote && $action->location ? json_decode($action->location)->address : null,
                            'checkout_time' => $attendance->checkout_time ? Carbon::parse($attendance->date . ' ' . $attendance->checkout_time)->format('g:i a') : null,
                            'note' => $action->note,
                            'remote_mode' => $action->remote_mode ?: null
                        ]);
                    }
                }

                array_push($data, $this->getBusinessMemberData($attendance->businessMember) + [
                        'id' => $attendance->id,
                        'check_in' => $checkin_data,
                        'check_out' => $checkout_data,
                        'active_hours' => $attendance->staying_time_in_minutes ? $this->formatMinute($attendance->staying_time_in_minutes) : null,
                        'overtime_in_minutes' => (int) $attendance->overtime_in_minutes ?: 0,
                        'overtime' => (int) $attendance->overtime_in_minutes ? $this->formatMinute((int) $attendance->overtime_in_minutes) : null,
                        'date' => $attendance->date,
                        'is_absent' => $attendance->status == Statuses::ABSENT ? 1 : 0,
                        'is_on_leave' => 0,
                        'is_holiday' => $is_weekend_or_holiday ? 1 : 0,
                        'weekend_or_holiday' => $is_weekend_or_holiday ? $this->isWeekendOrHoliday() : null,
                        'is_half_day_leave' => $is_on_half_day_leave,
                        'which_half_day_leave' => $which_half_day
                    ]);
            }
        }

        foreach ($business_members_in_leave as $index => $business_member_in_leave) {
            if (in_array($business_member_in_leave['member']['id'], $this->usersWhoGiveAttendance)) {
                unset($business_members_in_leave[$index]);
            }
        }

        $present_and_on_leave_business_members = array_merge($data, $business_members_in_leave);
        if ($this->statusFilter == self::ABSENT || $this->statusFilter == self::ALL) {
            $business_members_in_absence = $this->getBusinessMemberWhoAreAbsence($present_and_on_leave_business_members);
            if ($this->statusFilter == self::ABSENT) $present_and_on_leave_business_members = [];
            if ($this->statusFilter == self::ABSENT && $is_weekend_or_holiday) {
                $present_and_on_leave_business_members = [];
                $business_members_in_absence = [];
            }
        } else {
            $business_members_in_absence = [];
        }

        $final_data = array_merge($present_and_on_leave_business_members, $business_members_in_absence);

        if ($this->search)
            $final_data = collect($this->searchWithEmployeeName($final_data))->values();

        return $final_data;
    }

    /**
     * @param $present_and_on_leave_business_members
     * @return array
     */
    private function getBusinessMemberWhoAreAbsence($present_and_on_leave_business_members)
    {
        $is_weekend_or_holiday = $this->isWeekendHolidayLeave();
        $business_member_ids = [];
        $present_and_on_leave_business_member_ids = array_map(function ($business_member) use ($business_member_ids) {
            return $business_member_ids[] = $business_member['business_member_id'];
        }, $present_and_on_leave_business_members);

        $business_member_ids_who_give_attendance = $this->attendances->pluck('business_member_id')->toArray();
        $present_and_on_leave_business_member_ids = array_merge($present_and_on_leave_business_member_ids, $business_member_ids_who_give_attendance);

        $business_members = $this->businessMemberRepository->builder()->select('id', 'member_id', 'business_role_id', 'employee_id')
            ->with([
                'member' => function ($q) {
                    $q->select('id', 'profile_id')
                        ->with([
                            'profile' => function ($q) {
                                $q->select('id', 'name');
                            }]);
                },
                'role' => function ($q) {
                    $q->select('business_roles.id', 'business_department_id', 'name')->with([
                        'businessDepartment' => function ($q) {
                            $q->select('business_departments.id', 'business_id', 'name');
                        }
                    ]);
                }
            ])
            ->where('business_id', $this->business->id)
            ->active()
            ->whereNotIn('id', $present_and_on_leave_business_member_ids);

        if ($this->businessDepartmentId) {
            $business_members = $business_members->whereHas('role', function ($q) {
                $q->whereHas('businessDepartment', function ($q) {
                    $q->where('business_departments.id', $this->businessDepartmentId);
                });
            });
        }

        $business_members = $business_members->get();

        $data = [];
        foreach ($business_members as $business_member) {
                array_push($data, $this->getBusinessMemberData($business_member) + [
                    'id' => $business_member->id,
                    'check_in' => null,
                    'check_out' => null,
                    'active_hours' => null,
                    'is_absent' => $is_weekend_or_holiday ? 0 : 1,
                    'is_on_leave' => 0,
                    'is_holiday' => $is_weekend_or_holiday ? 1 : 0,
                    'weekend_or_holiday' => $is_weekend_or_holiday ? $this->isWeekendOrHoliday() : null,
                    'is_half_day_leave' => 0,
                    'which_half_day_leave' => null,
                    'date' => null
                ]);
        }

        return $data;
    }

    private function getBusinessMemberWhoAreOnLeave()
    {
        if (!($this->statusFilter == self::ON_LEAVE || $this->statusFilter == self::ABSENT || $this->statusFilter == self::ALL)) return [];

        $business_member_ids = [];
        if ($this->businessMemberId) $business_member_ids = [$this->businessMemberId];
        elseif ($this->business) $business_member_ids = $this->getBusinessMemberIds();

        $leaves = $this->leaveRepositoryInterface->builder()
            ->select('id', 'business_member_id', 'end_date', 'status', 'is_half_day', 'half_day_configuration')
            ->whereIn('business_member_id', $business_member_ids)
            ->accepted()
            ->where('start_date', '<=', $this->startDate->toDateString())->where('end_date', '>=', $this->endDate->toDateString())
            ->with(['businessMember' => function ($q) {
                $q->select('id', 'member_id', 'business_role_id', 'employee_id')
                    ->with([
                        'member' => function ($q) {
                            $q->select('id', 'profile_id')
                                ->with([
                                    'profile' => function ($q) {
                                        $q->select('id', 'name');
                                    }]);
                        },
                        'role' => function ($q) {
                            $q->select('business_roles.id', 'business_department_id', 'name')->with([
                                'businessDepartment' => function ($q) {
                                    $q->select('business_departments.id', 'business_id', 'name');
                                }
                            ]);
                        }
                    ]);
            }]);

        if ($this->businessDepartmentId) {
            $leaves = $leaves->whereHas('businessMember', function ($q) {
                $q->whereHas('role', function ($q) {
                    $q->whereHas('businessDepartment', function ($q) {
                        $q->where('business_departments.id', $this->businessDepartmentId);
                    });
                });
            });
        }
        $leaves = $leaves->get();

        $data = [];
        foreach ($leaves as $leave) {
            array_push($this->usersWhoOnLeave, $leave->businessMember->member->id);
            $this->usersLeaveIds[$leave->businessMember->member->id] = [
                'member_id' => $leave->businessMember->member->id,
                'business_member_id' => $leave->businessMember->id,
                'leave' => [
                    'id' => $leave->id,
                    'is_half_day_leave' => (int)$leave->is_half_day,
                    'which_half_day' => $leave->is_half_day ? $leave->half_day_configuration : null
                ]
            ];
            if (!!$this->checkinStatus || !!$this->checkoutStatus) continue;
            array_push($data, $this->getBusinessMemberData($leave->businessMember) + [
                    'id' => $leave->id,
                    'check_in' => null,
                    'check_out' => null,
                    'active_hours' => null,
                    'date' => null,
                    'is_absent' => 0,
                    'is_on_leave' => 1,
                    'is_holiday' => 0,
                    'weekend_or_holiday' => null,
                    'is_half_day_leave' => $leave->is_half_day ? 1 : 0,
                    'which_half_day_leave' => $leave->is_half_day ? $leave->half_day_configuration : null
                ]);
        }

        return $data;
    }

    /**
     * @param BusinessMember $business_member
     * @return array
     */
    private function getBusinessMemberData(BusinessMember $business_member)
    {
        return [
            'employee_id' => $business_member->employee_id ? $business_member->employee_id : 'N/A',
            'business_member_id' => $business_member->id,
            'member' => [
                'id' => $business_member->member->id,
                'name' => $business_member->member->profile->name
            ],
            'department' => $business_member->role ? [
                'id' => $business_member->role->business_department_id,
                'name' => $this->departments->where('id', $business_member->role->business_department_id)->first() ?
                    $this->departments->where('id', $business_member->role->business_department_id)->first()->name :
                    'n/s'
            ] : null
        ];
    }

    /**
     * @param $final_data
     * @return array
     */
    private function searchWithEmployeeName($final_data)
    {
        return array_where($final_data, function ($key, $value) {
            return str_contains(strtoupper($value['member']['name']), strtoupper($this->search));
        });
    }

    private function setDepartments()
    {
        $this->departments = BusinessDepartment::where('business_id', $this->business->id)->select('id', 'name')->get();
        return $this;
    }

    private function formatMinute($minute)
    {
        if ($minute < 60) return "$minute min";
        $hour = $minute / 60;
        $intval_hr = intval($hour);
        $text = "$intval_hr hr ";
        if ($hour > $intval_hr) $text .= ($minute - (60 * intval($hour))) . " min";

        return $text;
    }

    /**
     * @return bool
     */
    private function isWeekendHolidayLeave()
    {
        $business_weekend = $this->businessWeekend->getAllByBusiness($this->business);
        $business_holiday = $this->businessHoliday->getAllByBusiness($this->business);

        $dates_of_holidays_formatted = [];
        $weekend_day = $business_weekend->pluck('weekday_name')->toArray();
        foreach ($business_holiday as $holiday) {
            $start_date = Carbon::parse($holiday->start_date);
            $end_date = Carbon::parse($holiday->end_date);
            for ($d = $start_date; $d->lte($end_date); $d->addDay()) {
                $dates_of_holidays_formatted[] = $d->format('Y-m-d');
            }
        }

        return $this->isWeekend($this->startDate, $weekend_day)
            || $this->isHoliday($this->startDate, $dates_of_holidays_formatted);
    }

    /**
     * @param $action
     * @param $is_weekend_or_holiday
     * @param $is_on_leave
     * @param $is_on_half_day_leave
     * @return null
     */
    private function getStatusBasedOnLeaveAction($action, $is_weekend_or_holiday, $is_on_leave, $is_on_half_day_leave)
    {
        if ($is_on_half_day_leave) return $action->status;
        if ($is_weekend_or_holiday || $is_on_leave) return null;

        return $action->status;
    }

    /**
     * @param Carbon $date
     * @param $weekend_day
     * @return bool
     */
    private function isWeekend(Carbon $date, $weekend_day)
    {
        return in_array(strtolower($date->format('l')), $weekend_day);
    }

    /**
     * @param Carbon $date
     * @param $holidays
     * @return bool
     */
    private function isHoliday(Carbon $date, $holidays)
    {
        return in_array($date->format('Y-m-d'), $holidays);
    }

    /**
     * @param $member_id
     * @return bool
     */
    private function isOnLeave($member_id)
    {
        return in_array($member_id, $this->usersWhoOnLeave);
    }

    private function isWeekendOrHoliday()
    {
        $business_weekend = $this->businessWeekend->getAllByBusiness($this->business);
        $weekend_day = $business_weekend->pluck('weekday_name')->toArray();

        return $this->isWeekend($this->startDate, $weekend_day) ? 'weekend' : 'holiday';
    }

}
