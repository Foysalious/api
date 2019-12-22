<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ProcurementItemField extends Model
{
    protected $guarded = ['id'];

    public function isRadio()
    {
        return $this->input_type == "radio";
    }

    public function isCheckBox()
    {
        return $this->input_type == "checkbox";
    }

    public function isText()
    {
        return $this->input_type == "text";
    }

    public function isNumber()
    {
        return $this->input_type == "number";
    }

    public function isTextArea()
    {
        return $this->input_type == "textarea";
    }

    public function getVariables()
    {
        return json_decode($this->variables);
    }

    public function getOptions()
    {
        return $this->getVariables()->options;
    }
}
