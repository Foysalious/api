<?php namespace Sheba;

use App\Models\Business;
use App\Models\Partner;
use App\Models\User;
use App\Models\Vendor;
use Auth;
use Session;
use Carbon\Carbon;

trait ModificationFields
{
    private $modifier = null;
    private $modifierModelName = null;

    private function isOfValidClass($obj)
    {
        if ($obj == null) return false;

        $this->modifierModelName = substr(strrchr(get_class($obj), '\\'), 1);
        return in_array($this->modifierModelName, ['Customer', 'Resource', 'Partner', 'User', 'Member', 'Affiliate', 'Vendor', 'Business','BankUser','RetailerMember']);
    }

    public function setModifier($entity)
    {
        if ($this->isOfValidClass($entity)) {
            Session::flash('modifier', $entity);
        }
    }

    /**
     * Make the modification fields.
     *
     * @param bool|true $created_fields
     * @param bool|true $updated_fields
     * @return array
     */
    private function modificationFields($created_fields = true, $updated_fields = true)
    {
        list($id, $name, $time) = $this->getData();

        $data = [];
        if ($created_fields) {
            $data['created_by'] = $id;
            $data['created_by_name'] = $name;
            $data['created_at'] = $time;
        }

        if ($updated_fields) {
            $data['updated_by'] = $id;
            $data['updated_by_name'] = $name;
            $data['updated_at'] = $time;
        }

        return $data;
    }

    /**
     * Add the modification fields to an Object.
     *
     * @param $model
     * @param bool|true $created_fields
     * @param bool|true $updated_fields
     */
    private function addModificationFieldsToObject($model, $created_fields = true, $updated_fields = true)
    {
        list($id, $name, $time) = $this->getData();

        if ($created_fields) {
            $model->created_by = $id;
            $model->created_by_name = $name;
            $model->created_at = $time;
        }

        if ($updated_fields) {
            $model->updated_by = $id;
            $model->updated_by_name = $name;
            $model->updated_at = $time;
        }
    }

    /**
     * Merge the data with both(created and updated) modification fields.
     *
     * @param $data
     * @return array
     */
    public function withBothModificationFields($data)
    {
        if (is_array($data)) return array_merge($data, $this->modificationFields());

        $this->addModificationFieldsToObject($data);
    }

    /**
     * Merge the data with only created modification fields.
     *
     * @param $data
     * @return array
     */
    public function withCreateModificationField($data)
    {
        if (is_array($data)) return array_merge($data, $this->modificationFields($create = true, $update = false));

        $this->addModificationFieldsToObject($data, $create = true, $update = false);
    }

    /**
     * Merge the data with only updated modification fields.
     *
     * @param $data
     * @return array
     */
    public function withUpdateModificationField($data)
    {
        if (is_array($data)) return array_merge($data, $this->modificationFields($create = false));

        $this->addModificationFieldsToObject($data, $create = false);
    }

    /**
     * @return array
     */
    private function getData()
    {
        $this->modifier = Session::get('modifier');

        $id = 0;
        $name = app()->runningInConsole() ? "automatic" : "";
        $time = Carbon::now();

        if ($this->modifierModelName == "User" || Auth::user()) {
            $user = Auth::user() ?: $this->modifier;
            $id = $user->id;
            $name = $user->department->name . ' - ' . $user->name;
        } else if ($this->isOfValidClass($this->modifier)) {
            $id = $this->modifier->id;
            $name = $this->modifierModelName . '-' . (($this->modifier instanceof User || $this->modifier instanceof Partner || $this->modifier instanceof Vendor || $this->modifier instanceof Business) ? $this->modifier->name : $this->modifier->profile->name);
        }

        return [$id, $name, $time];
    }

    public function getModifierType()
    {
        if (!empty(class_basename(Session::get('modifier')))) return "App\\Models\\" . class_basename(Session::get('modifier'));
    }
}
