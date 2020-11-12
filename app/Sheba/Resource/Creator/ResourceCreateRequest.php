<?php namespace Sheba\Resource\Creator;


use Illuminate\Http\UploadedFile;

class ResourceCreateRequest
{
    private $nidNo;
    /** @var UploadedFile */
    private $nidFrontImage;
    /** @var UploadedFile */
    private $nidBackImage;
    /** @var UploadedFile */
    private $profilePicture;
    private $birthDate;

    /**
     * @return mixed
     */
    public function getNidNo()
    {
        return $this->nidNo;
    }

    /**
     * @param mixed $nidNo
     * @return ResourceCreateRequest
     */
    public function setNidNo($nidNo)
    {
        $this->nidNo = $nidNo;
        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getNidFrontImage()
    {
        return $this->nidFrontImage;
    }

    /**
     * @param UploadedFile $nidFrontImage
     * @return ResourceCreateRequest
     */
    public function setNidFrontImage($nidFrontImage)
    {
        $this->nidFrontImage = $nidFrontImage;
        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getNidBackImage()
    {
        return $this->nidBackImage;
    }

    /**
     * @param UploadedFile $nidBackImage
     * @return ResourceCreateRequest
     */
    public function setNidBackImage($nidBackImage)
    {
        $this->nidBackImage = $nidBackImage;
        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getProfilePicture()
    {
        return $this->profilePicture;
    }

    /**
     * @param UploadedFile $profilePicture
     * @return ResourceCreateRequest
     */
    public function setProfilePicture($profilePicture)
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param mixed $birthDate
     * @return ResourceCreateRequest
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
        return $this;
    }

}