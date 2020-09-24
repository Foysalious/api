<?php namespace Sheba\Loan;


use App\Models\PartnerBankLoan;
use App\Repositories\FileRepository;
use Illuminate\Support\Facades\DB;
use ReflectionException;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\Loan\DS\PartnerLoanRequest;
use Sheba\ModificationFields;

class DocumentDeleter
{
    use CdnFileManager, FileManager, ModificationFields;

    /**
     * @var PartnerLoanRequest
     */
    private $loanRequest;
    private $for;
    private $key;
    private $user;

    public function __construct(PartnerBankLoan $loan)
    {
        $this->loanRequest = new PartnerLoanRequest($loan);
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param $for
     * @return $this
     */
    public function setFor($for)
    {
        $this->for = $for;
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    private function deleteOld($image)
    {
        if (basename($image) != 'default.jpg') {
            $filename = substr($image, strlen(config('sheba.s3_url')));
            self::deleteOldImage($filename);
        }
    }

    /**
     * @throws ReflectionException
     */
    public function delete()
    {
        $detail = $this->loanRequest->details();
        $this->setModifier($this->user);
        if (isset($detail['final_information_for_loan']['document'][$this->for][$this->key])) {
            $url  = $detail['final_information_for_loan']['document'][$this->for][$this->key];
            $loan = $this->loanRequest->partnerBankLoan;
            $this->deleteOld($url);
            $detail['final_information_for_loan']['document'][$this->for][$this->key] = null;
            DB::transaction(function () use ($loan, $detail, $url) {
                $loan->update($this->withUpdateModificationField([
                    'final_information_for_loan' => json_encode($detail['final_information_for_loan'])
                ]));
                $this->loanRequest->storeChangeLog($this->user, "images_$this->for -> $this->key", $url, 'none', $this->key);
            });
        }
        return true;
    }

    /**
     * @param $filename
     */
    public static function deleteOldImage($filename)
    {
        /** @var FileRepository $fileRepository */
        $fileRepository = app(FileRepository::class);
        $fileRepository->deleteFileFromCDN($filename);
    }
}
