<?php namespace Sheba\Repositories\Interfaces;

interface BusinessMemberRepositoryInterface extends BaseRepositoryInterface
{
    public function checkExistingMobile($mobile);
}