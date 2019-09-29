<?php namespace Sheba\AppSettings\HomePageSetting;

use Illuminate\Console\Command as BaseCommand;

class Command extends BaseCommand
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'sheba:update-setting-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update app home page setting cache.';

    private $cacher;

    /**
     * Create a new migration install command instance.
     *
     * @param Cacher $cacher
     */
    public function __construct(Cacher $cacher)
    {
        parent::__construct();
        $this->cacher = $cacher;
    }

    public function handle()
    {
        $this->cacher->update();
        $this->info('Done.');
    }
}