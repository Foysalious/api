<?php namespace Sheba;


use Illuminate\Database\Eloquent\Model;

class ShebaUser
{
    private $user;
    private $instanceOf;

    public function setUser(Model $user)
    {
        $this->user = $user;
        return $this;
    }
    private function calculateInstance(){
//        $this->instanceOf=$this
    }
    public function getName(){
//        return $this->user->
    }
}