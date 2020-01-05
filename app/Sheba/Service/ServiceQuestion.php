<?php namespace App\Sheba\Service;


use App\Models\Service;

class ServiceQuestion
{
    /** @var Service */
    private $service;

    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    public function get()
    {
        if ($this->service->isFixed()) return null;
        $options = (json_decode($this->service->variables))->options;
        foreach ($options as &$option) {
            $option->answers = explode(',', $option->answers);
            $contents = [];
            foreach ($option->answers as $answer) {
                array_push($contents, [
                    'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/74/600.jpg',
                    'description' => [
                        "We have more than 300 services",
                        "Verified experts all arround the country"
                    ],
                    'images' => [
                        'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/74/600.jpg',
                        'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/74/600.jpg',
                        'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/74/600.jpg',
                        'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Services/74/600.jpg'
                    ]
                ]);
            }
            $option->contents = $contents;
        }
        return $options;
    }
}