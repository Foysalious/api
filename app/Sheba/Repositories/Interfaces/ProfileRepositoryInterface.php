<?php namespace Sheba\Repositories\Interfaces;

interface ProfileRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param $mobile
     * @return $this
     */
    public function findByMobile($mobile);
}