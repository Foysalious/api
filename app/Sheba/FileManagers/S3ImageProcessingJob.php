<?php namespace Sheba\FileManagers;


use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class S3ImageProcessingJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var S3Image */
    protected $image;

    public function __construct(S3Image $image)
    {
        $this->image = $image;
    }
}