<?php


namespace Sheba\NeoBanking\Banks;


use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\Repositories\NeoBankRepository;

abstract class Bank
{
    protected $id;
    protected $name;
    protected $logo;
    protected $name_bn;
    /** @var NeoBankRepository $bankRepo */
    protected $bankRepo;

    public function __construct()
    {
        /** @var NeoBankRepository  */
        $this->bankRepo = app(NeoBankRepository::class);
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    abstract public function categories();

    abstract public function accountInfo(): array;

    abstract public function categoryDetails(BankFormCategory $category): array;
}
