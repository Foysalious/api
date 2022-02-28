<?php namespace App\Console\Commands;

use App\Models\Business;
use App\Sheba\Business\AnnouncementV2\AnnouncementNotifications;
use Carbon\Carbon;
use Sheba\Dal\Announcement\AnnouncementRepositoryInterface;
use Sheba\Dal\Announcement\AnnouncementTarget;
use Sheba\Dal\Announcement\ScheduledFor;
use Throwable;

class RunAnnouncementNotifications extends Command
{
    /** @var string The name and signature of the console command. */
    protected $signature = 'sheba:announcement-notifications';

    /** @var string The console command description. */
    protected $description = 'Send Scheduled Announcement Notifications';

    /*** @var AnnouncementRepositoryInterface $announcementRepo */
    private $announcementRepo;

    public function __construct()
    {
        $this->announcementRepo = app(AnnouncementRepositoryInterface::class);
        parent::__construct();
    }

    public function handle()
    {
        $announcements = $this->announcementRepo->where('scheduled_for', ScheduledFor::LATER)->where('start_date', Carbon::now()->toDateString())->whereBetween('start_time', [Carbon::now()->format('H:i') . ':00', Carbon::now()->format('H:i') . ':59'])->select('id', 'business_id', 'title', 'target_type', 'target_id')->orderBy('id', 'DESC')->get();
        
        if ($announcements->isEmpty()) return;
        try {
            foreach ($announcements as $announcement) {
                /** @var Business $business */
                $business = $announcement->business;
                $target = $announcement->target_type;

                if ($target == AnnouncementTarget::ALL) {
                    $members_ids = $business->getActiveBusinessMember()->pluck('member_id')->toArray();
                } else if ($target == AnnouncementTarget::EMPLOYEE) {
                    $members_ids = $business->getActiveBusinessMember()->whereIn('id', json_decode($announcement->target_id, 1))->pluck('member_id')->toArray();
                } else if ($target == AnnouncementTarget::DEPARTMENT) {
                    $members_ids = $business->getActiveBusinessMember()->whereHas('role', function ($q) use ($announcement) {
                        $q->whereHas('businessDepartment', function ($q) use ($announcement) {
                            $q->whereIn('business_departments.id', json_decode($announcement->target_id, 1));
                        });
                    })->pluck('member_id')->toArray();
                } else if ($target == AnnouncementTarget::EMPLOYEE_TYPE) {
                    $members_ids = $business->getActiveBusinessMember()->whereIn('employee_type', json_decode($announcement->target_id, 1))->pluck('member_id')->toArray();
                } else continue;
                $announcement->update(['status'=>'published']);
                (new AnnouncementNotifications($members_ids, $announcement))->shoot();
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
        }
    }
}
