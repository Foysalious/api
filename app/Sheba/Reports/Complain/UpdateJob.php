<?php namespace Sheba\Reports\Complain;

use Sheba\Dal\Complain\Model as Complain;
use Sheba\Reports\UpdateJob as BaseUpdateJob;

class UpdateJob extends BaseUpdateJob
{
    /** @var Complain */
    private $complain;

    /**
     * Create a new job instance.
     *
     * @param Complain $complain
     */
    public function __construct(Complain $complain)
    {
        $this->complain = $complain;
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @param Generator $generator
     * @return void
     */
    public function handle(Generator $generator)
    {
        $generator->createOrUpdate($this->complain);
    }
}