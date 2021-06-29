<?php namespace App\Sheba\Business\CoWorker;

use Carbon\Carbon;
use Excel as IncompleteCoWorkerExcel;
use Sheba\Dal\Business\EloquentImplementation as BusinessRepository;

class IncompleteInvitedCoWorkerExcel
{
    /*** @var BusinessRepository */
    private $businessRepo;

    public function __construct()
    {
        $this->businessRepo = app(BusinessRepository::class);
    }

    public function get()
    {
        $businesses = $this->businessRepo->builder()->select('id', 'name')->orderBy('name')->get();
        $total_business_count = $businesses->count();
        IncompleteCoWorkerExcel::create('Invited Incomplete Coworker', function ($excel) use($businesses, $total_business_count){
            $counter = 0;
            foreach ($businesses as $business) {
                $data = $this->getData($business);
                if (empty($data)) {
                    $counter++;
                    continue;
                }
                $excel->sheet(strlen($business->name) < 31 ? $business->name : substr($business->name, 0, 31), function ($sheet) use ($business, $data){
                    $sheet->fromArray($data, null, 'A1', false, false);
                    $sheet->prependRow($this->getHeaders());
                    $sheet->freezePane('C2');
                    $sheet->cell('A1:E1', function ($cells) {
                        $cells->setFontWeight('bold');
                    });
                    $sheet->getDefaultStyle()->getAlignment()->applyFromArray(['horizontal' => 'left']);
                    $sheet->setAutoSize(true);
                });
            }

            if ($counter === $total_business_count){
                $excel->sheet('Sheet 1', function ($sheet){
                    $sheet->fromArray(['No data Found'], null, 'A1', false, false);
                    $sheet->getDefaultStyle()->getAlignment()->applyFromArray(['horizontal' => 'left']);
                    $sheet->setAutoSize(true);
                });
            }
        })->export('xlsx');
    }

    private function getData($business)
    {
        $business_members = $business->getAccessibleBusinessMember()->where('status', 'invited')->get();
        $data = [];
        foreach ($business_members as $business_member){

            $member = $business_member->member;
            $profile = $member->profile;
            if (!$this->isIncomplete($business_member)){
                array_push($data, [
                   'id' =>  $business_member->id,
                   'name' => $profile->name,
                   'employee_id' => $business_member->employee_id,
                   'mobile' => $business_member->mobile,
                   'joining_date' => Carbon::parse($business_member->join_date)->format('Y-m-d')
                ]);
            }
        }
        return [];
    }

    private function getHeaders()
    {
        return ['Business ID', 'Employee Name', 'Employee ID', 'Mobile', 'Joining Date'];
    }

    private function isIncomplete($business_member)
    {
        if (!$business_member->role) return true;
        if (!$business_member->department()) return true;
        return false;
    }

}
