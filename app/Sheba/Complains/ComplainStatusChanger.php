<?php namespace Sheba\Complains;

use Carbon\Carbon;
use Sheba\Dal\Complain\Model as Complain;
use Sheba\Dal\Complain\EloquentImplementation as ComplainRepo;
use Sheba\ModificationFields;
use Sheba\Notification\ComplainNotification;
use Sheba\Dal\ComplainLog\Model as ComplainLog;

class ComplainStatusChanger
{
    use ModificationFields;

    private $complainRepo;
    private $statuses;

    private $complain;
    private $data;

    public function __construct(ComplainRepo $complain)
    {
        $this->complainRepo = $complain;
        $this->statuses = constants('COMPLAIN_STATUSES');
    }

    public function setModifierForModificationFiled($entity)
    {
        $this->setModifier($entity);
        return $this;
    }

    public function setComplain(Complain $complain)
    {
        $this->complain = $complain;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function hasError()
    {
        if (!$this->complain) return "No Complain Set";
        if ($this->complain->status == $this->statuses['Resolved']) return "You can't change a resolved complain!";
        if ($this->complain->status == $this->data['status']) return "You can't change complain status to current status!";
        if ($this->isInvalidResolvingCategory()) return "Invalid Resolving Category!";
        if (!in_array($this->data['status'], $this->statuses)) return "Invalid Status!";
        return false;
    }

    /**
     *
     * @throws \Exception
     */
    public function change()
    {
        $old_status = $this->complain->status;
        $this->complainRepo->update($this->complain, $this->withUpdateModificationField($this->statusChangeData()));
        $this->saveLog($old_status, $this->data['status']);
        if($this->data['status'] == "Resolved") (new ComplainNotification($this->complain))->notifyOnResolve();
    }

    private function statusChangeData()
    {
        $data['status'] = $this->data['status'];
        $data['is_satisfied'] = null;
        if($this->data['status'] == $this->statuses["Resolved"]) {
            $data['resolved_time'] = Carbon::now();
            $data['resolved_category'] = $this->data['resolved_category'];
        }
        return $data;
    }

    private function isInvalidResolvingCategory()
    {
        $complain_resolve_cats = array_keys(constants('COMPLAIN_RESOLVE_CATEGORIES'));
        return $this->data['status'] == $this->statuses['Resolved']
            && !in_array($this->data['resolved_category'], $complain_resolve_cats);
    }

    private function saveLog($old_status, $new_status)
    {
        $data = [
            'complain_id' => $this->complain->id,
            'field'       => 'status',
            'from'        => $old_status,
            'to'          => $new_status
        ];
        ComplainLog::create($this->withCreateModificationField($data));
    }
}