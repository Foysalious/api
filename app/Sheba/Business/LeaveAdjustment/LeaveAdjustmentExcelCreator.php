<?php namespace Sheba\Business\LeaveAdjustment;

use App\Models\Business;

class LeaveAdjustmentExcelCreator
{
    /** @var Business */
    private $business;

    /** @var LeaveAdjustmentExcel */
    private $excel;

    public function setBusiness(Business $business): LeaveAdjustmentExcelCreator
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function create(): string
    {
        $file_path = $this->loadExcel();
        $this->addLeaves();
        $this->addSuperAdmins();
        $export_path = $this->export();
        unlink($file_path);
        return $export_path;
    }

    private function loadExcel(): string
    {
        $url = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/bulk_upload_template/leave_adjustment_bulk_attachment_file.xlsx';
        $file_path = storage_path('exports') . DIRECTORY_SEPARATOR . basename($url);
        file_put_contents($file_path, file_get_contents($url));
        $this->excel = LeaveAdjustmentExcel::load($file_path);
        return $file_path;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function addLeaves()
    {
        $leave_types = $this->getLeaveTypes();
        foreach ($leave_types as $leave_type) {
            $this->excel->addLeave($leave_type['id'], $leave_type['title'], $leave_type['total_days']);
        }
    }

    private function getLeaveTypes(): array
    {
        $leave_types = [];
        $this->business->leaveTypes()->whereNull('deleted_at')
            ->select('id', 'title', 'total_days', 'deleted_at')->get()
            ->each(function ($leave_type) use (&$leave_types) {
                $leave_type_data = ['id' => $leave_type->id, 'title' => $leave_type->title, 'total_days' => $leave_type->total_days];
                $leave_types[] = $leave_type_data;
            });
        return $leave_types;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function addSuperAdmins()
    {
        $super_admins = $this->business->getAccessibleBusinessMember()->where('is_super', 1)->get();
        foreach ($super_admins as $admin) {
            $profile = $admin->member->profile;
            $this->excel->addSuperAdmin($admin->id, $profile->name);
        }
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    private function export(): string
    {
        $export_path = storage_path('exports') . DIRECTORY_SEPARATOR . "leave_adjustment_bulk_attachment_file_" . now()->timestamp . ".xlsx";
        $this->excel->save($export_path);
        return $export_path;
    }
}