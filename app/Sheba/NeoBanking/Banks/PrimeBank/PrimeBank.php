<?php namespace Sheba\NeoBanking\Banks\PrimeBank;

use Sheba\NeoBanking\Banks\Bank;
use Sheba\NeoBanking\DTO\BankFormCategory;

class PrimeBank extends Bank
{
    public function __construct()
    {
        parent::__construct();
        $this->setBank();
    }

    private function setBank()
    {
        $bank = $this->bankRepo->getPrimeBank();
        if (!empty($bank)) {
            $this->mapBank($bank);
        }
    }

    public function categories()
    {
        // TODO: Implement categories() method.
    }

    public function accountInfo(): array
    {
        // TODO: Implement accountInfo() method.
    }

    public function categoryDetails(BankFormCategory $category): array
    {
        // TODO: Implement categoryDetails() method.
    }

    private function mapBank($bank)
    {
        $this->id      = $bank->id;
        $this->name    = $bank->name;
        $this->name_bn = $bank->name_bn;
        $this->logo    = $bank->logo;
    }
}
