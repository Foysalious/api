<?php namespace App\Sheba\Business\CoWorker\ProfileInformation;


use App\Models\Member;

class SocialLink
{
    /*** @var Member $member*/
    private $member;

    public function __construct(Member $member)
    {
        $this->member = $member;
    }

    public function get()
    {
        $social_links = json_decode($this->member->social_links, 1);
        if (!$social_links) return null;
        $data = [];
        foreach ($social_links as $type => $link){
            array_push($data, [
                'link' =>  $link,
                'type' => $type
            ]);
        }
        return $data;
    }

}
