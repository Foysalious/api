<?php namespace Sheba\Repositories;

use App\Models\Job;

class JobRepository extends BaseRepository
{
    public function update(Job $job, $data)
    {
        $job->update($this->withUpdateModificationField($data));
    }
}