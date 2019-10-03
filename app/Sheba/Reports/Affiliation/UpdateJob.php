<?php namespace Sheba\Reports\Affiliation;

use App\Models\Affiliation;
use Sheba\Reports\UpdateJob as BaseUpdateJob;

class UpdateJob extends BaseUpdateJob
{
    /** @var Affiliation */
    private $affiliation;

    /**
     * Create a new job instance.
     *
     * @param Affiliation $affiliation
     */
    public function __construct(Affiliation $affiliation)
    {
        $this->affiliation = $affiliation;
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
        $generator->createOrUpdate($this->affiliation);
    }
}