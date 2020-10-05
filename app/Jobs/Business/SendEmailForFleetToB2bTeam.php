<?php namespace App\Jobs\Business;

use App\Models\Business;
use App\Models\HyperLocal;
use App\Models\Procurement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Job;
use Mail;

class SendEmailForFleetToB2bTeam extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var Business $business */
    private $business;
    private $toMail;

    /**
     * SendEmailForFleetToB2bTeam constructor.
     * @param Business $business
     * @param $to_mail
     */
    public function __construct(Business $business, $to_mail)
    {
        $this->business = $business;
        $this->toMail = $to_mail;
    }

    public function handle()
    {
        $location = null;
        $geo_information = json_decode($this->business->geo_informations, 1);
        $hyperLocation = HyperLocal::insidePolygon((double)$geo_information['lat'], (double)$geo_information['lng'])->with('location')->first();
        if (!is_null($hyperLocation)) $location = $hyperLocation->location;

        $company_name = $this->business->name;
        $contact_person_name = $this->business->getContactPerson();
        $contact_person_email = $this->business->getContactEmail();
        $contact_person_mobile = $this->business->getContactNumber();
        $address = $location->name;
        $subject = $company_name . " has shown interest in Fleet management.";

        Mail::send('emails.fleet-mail', [
            'company_name' => $company_name,
            'contact_person_name' => $contact_person_name,
            'contact_person_email' => $contact_person_email,
            'contact_person_mobile' => $contact_person_mobile,
            'address' => $address
        ], function ($m) use ($subject) {
            $m->to($this->toMail)->subject($subject);
        });
    }
}
