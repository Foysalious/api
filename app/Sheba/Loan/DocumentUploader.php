<?php namespace Sheba\Loan;

use App\Models\PartnerBankLoan;
use App\Repositories\FileRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ReflectionException;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\Loan\DS\PartnerLoanRequest;
use Sheba\Loan\Exceptions\InvalidFileName;
use Sheba\ModificationFields;

class DocumentUploader
{
    use CdnFileManager, FileManager, ModificationFields;
    private $repo;
    private $for;
    private $loanRequest;
    private $user;
    private $fileRepository;


    public function __construct(PartnerBankLoan $loan)
    {
        $this->loanRequest    = (new PartnerLoanRequest($loan));
        $this->repo           = new LoanRepository();
        $this->fileRepository = app(FileRepository::class);
    }

    /**
     * @param mixed $user
     * @return DocumentUploader
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param mixed $for
     * @return DocumentUploader
     */
    public function setFor($for)
    {
        $this->for = $for;
        return $this;
    }


    /**
     * @param Request $request
     * @throws ReflectionException
     */
    public function update(Request $request)
    {
        $file      = $request->file('picture');
        $name      = $request->name;
        $forMethod = $this->getProcessMethod();
        list($formatted_name, $url) = $this->$forMethod($name, $file);
        $detail = $this->loanRequest->details();
        if (isset($detail['final_information_for_loan']['document'][$this->for][$formatted_name])) {
            $this->deleteOld($detail['final_information_for_loan']['document'][$this->for][$formatted_name]);
        }
        $detail['final_information_for_loan']['document'][$this->for][$formatted_name] = $url;
        $user                                                                          = $this->user;
        $loan                                                                          = $this->loanRequest->partnerBankLoan;
        $this->setModifier($this->user);
        DB::transaction(function () use ($loan, $detail, $formatted_name, $user, $name) {
            $loan->update($this->withUpdateModificationField([
                'final_information_for_loan' => json_encode($detail['final_information_for_loan'])
            ]));
            $this->loanRequest->storeChangeLog($user, "images_$this->for -> $name" , 'none', $formatted_name, $name);
        });
    }

    /**
     * @return string
     */
    private function getProcessMethod()
    {
        return "upload" . implode('', array_map(function ($item) { return ucfirst($item); }, explode('_', $this->for)));
    }

    /**
     * @param $image
     */
    private function deleteOld($image)
    {
        if (basename($image) != 'default.jpg') {
            $filename = substr($image, strlen(config('sheba.s3_url')));
            $this->deleteOldImage($filename);
        }
    }

    /**
     * @param $filename
     */
    private function deleteOldImage($filename)
    {
        $base_name        = basename($filename);
        $old_image_folder = preg_replace("/$base_name/", '', $filename);
        if ($old_image_folder == getLoanFolder()) {
            $this->fileRepository->deleteFileFromCDN($filename);
        }
    }

    /**
     * @param $name
     * @param $file
     * @return array
     */
    private function uploadExtras($name, $file)
    {
        $formatted_name = $this->formatName($name);
        list($extra_file, $extra_file_name) = $this->makeLoanFile($file, $formatted_name);
        $url = $this->saveFileToCDN($extra_file, getLoanFolder(), $extra_file_name);
        return [
            $formatted_name,
            $url
        ];
    }

    /**
     * @param $name
     * @return string
     */
    private function formatName($name)
    {
        return strtolower(preg_replace("/ /", "_", $name));
    }

    /**
     * @param $name
     * @param $file
     * @return array
     * @throws InvalidFileName
     */
    private function uploadGrantorDocument($name, $file)
    {
        return $this->uploadBasicDocument($name, $file);
    }

    /**
     * @param $name
     * @param $file
     * @return array
     * @throws InvalidFileName
     */
    private function uploadNomineeDocument($name, $file){
        return $this->uploadBasicDocument($name, $file);
    }
    /**
     * @param $name
     * @param $file
     * @return array
     * @throws InvalidFileName
     */
    private function uploadBasicDocument($name, $file)
    {
        $formatted_name = $this->formatName($name);
        $url            = null;
        if (!in_array($formatted_name, ['picture'])) {
            throw new InvalidFileName();
        }
        list($file, $filename) = $this->makeLoanFile($file, $formatted_name);
        $url = $this->saveFileToCDN($file, getLoanFolder(), $filename);
        return [
            $formatted_name,
            $url
        ];
    }
}
