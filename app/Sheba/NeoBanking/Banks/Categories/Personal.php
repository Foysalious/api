<?php namespace Sheba\NeoBanking\Banks\Categories;


use Sheba\NeoBanking\Banks\CategoryGetter;
use Sheba\NeoBanking\DTO\BankFormCategory;

class Personal extends BankFormCategory
{
    protected $code = 'personal';

    public function completion()
    {
        return [
            'en' => 75,
            'bn' => '৭৫'
        ];
    }

    public function get()
    {
        // TODO: Implement get() method.
    }

    public function post()
    {
        // TODO: Implement post() method.
    }

    public function getLastUpdated()
    {
        return $this->last_updated;
    }

    public function getDummy()
    {
        $this->setData(json_decode('[
    {
      "field_type": "header",
      "title": "সাধারণ তথ্য",
	  "input_type": "",
      "list_type": null,
      "name": "",
      "id": "",
      "value": null,
      "hint": "",
      "error_message": "",
      "mandatory": false,
      "is_editable": false,
      "image_url": null,
      "views": [],
      "list": [],
      "check_list": []
    },
    {
      "field_type": "editText",
      "title": "আবেদনকারীর নাম (বড় অক্ষরে)  *",
      "input_type": "text",
      "list_type": null,
      "name": "applicant_name",
      "id": "applicant_name",
      "value": null,
      "hint": "ABUL KALAM AZAD",
      "error_message": "আবেদনকারীর নাম পূরণ আবশ্যক",
      "mandatory": true,
      "is_editable": false,
      "image_url": null,
      "views": [],
      "list": [],
      "check_list": []
    },
    {
      "field_type": "date",
      "title": "জন্ম তারিখ *",
      "input_type": null,
      "list_type": null,
      "name": "birth_date",
      "id": "birth_date",
      "value": null,
      "hint": "উদাহরণ: 01/01/2000",
      "error_message": "জন্ম তারিখ  পূরণ আবশ্যক",
      "mandatory": true,
      "is_editable": true,
      "image_url": null,
      "views": [],
      "list": [],
      "check_list": []
    },
    {
      "field_type": "editText",
      "title": "বাবার নাম  *",
      "input_type": "text",
      "list_type": null,
      "name": "father_name",
      "id": "father_name",
      "value": null,
      "hint": "ABUL KALAM",
      "error_message": "বাবার নাম পূরণ আবশ্যক",
      "mandatory": true,
      "is_editable": false,
      "image_url": null,
      "views": [],
      "list": [],
      "check_list": []
    },
    {
      "field_type": "editText",
      "title": "মায়ের নাম  *",
      "input_type": "text",
      "list_type": null,
      "name": "mother_name",
      "id": "mother_name",
      "value": null,
      "hint": "Mrs. ABUL",
      "error_message": "মায়ের নাম পূরণ আবশ্যক",
      "mandatory": true,
      "is_editable": false,
      "image_url": null,
      "views": [],
      "list": [],
      "check_list": []
    },
    {
      "field_type": "editText",
      "title": "স্বামী/ স্ত্রীর নাম (যদি থাকে)",
      "input_type": "text",
      "list_type": null,
      "name": "husband_or_wife_name",
      "id": "husband_or_wife_name",
      "value": null,
      "hint": "ABUILLA AZAD",
      "error_message": "স্বামী/ স্ত্রীর নাম পূরণ আবশ্যক",
      "mandatory": false,
      "is_editable": false,
      "image_url": null,
      "views": [],
      "list": [],
      "check_list": []
    },
    {
      "field_type": "editText",
      "title": "পেশা  *",
      "input_type": "text",
      "list_type": null,
      "name": "occupation_name",
      "id": "occupation_name",
      "value": null,
      "hint": "The Boss",
      "error_message": "পেশার ধরণ পূরণ আবশ্যক",
      "mandatory": true,
      "is_editable": false,
      "image_url": null,
      "views": [],
      "list": [],
      "check_list": []
    },
    {
      "field_type": "editText",
      "title": "প্রতিষ্ঠান এর নাম লিখুন",
      "input_type": "text",
      "list_type": null,
      "name": "company_name",
      "id": "company_name",
      "value": null,
      "hint": "Sheba.xyz",
      "error_message": "প্রতিষ্ঠান এর নাম পূরণ আবশ্যক",
      "mandatory": false,
      "is_editable": false,
      "image_url": null,
      "views": [],
      "list": [],
      "check_list": []
    },
    {
      "field_type": "editText",
      "title": "ই-টিন নাম্বার  *",
      "input_type": "text",
      "list_type": null,
      "name": "etin_number",
      "id": "etin_number",
      "value": null,
      "hint": "4654453",
      "error_message": "ই-টিন নাম্বার পূরণ আবশ্যক",
      "mandatory": true,
      "is_editable": false,
      "image_url": null,
      "views": [],
      "list": [],
      "check_list": []
    },
    {
      "field_type": "editText",
      "title": "জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার  *",
      "input_type": "text",
      "list_type": null,
      "name": "NID_passpost_birth_cer_number",
      "id": "NID_passpost_birth_cer_number",
      "value": null,
      "hint": "654564544645464",
      "error_message": "জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার পূরণ আবশ্যক",
      "mandatory": true,
      "is_editable": false,
      "image_url": null,
      "views": [],
      "list": [],
      "check_list": []
    },
    {
      "field_type": "header",
      "title": "আপনার বর্তমান ঠিকানা",
	  "input_type": "",
      "list_type": null,
      "name": "",
      "id": "",
      "value": null,
      "hint": "",
      "error_message": "",
      "mandatory": false,
      "is_editable": false,
      "image_url": null,
      "views": [],
      "list": [],
      "check_list": []
    },
    {
      "field_type": "MultipleView",
      "name": "present_address",
      "id": "present_address",
      "value": null,
	  "input_type": "",
      "list_type": null,
      "hint": "",
	  "title": "",
      "error_message": "",
      "mandatory": false,
      "is_editable": false,
      "image_url": null,
      "list": [],
      "check_list": [],
      "views": [
        {
          "field_type": "editText",
          "input_type": "text",
          "name": "street_village_present_address",
          "id": "street_village_present_address",
          "value": null,
          "title": "স্ট্রিট নং / গ্রামের নাম *",
          "hint": "স্ট্রিট নং / গ্রামের নাম",
          "error_message": "স্ট্রিট নং / গ্রামের নাম  পূরণ আবশ্যক ",
          "mandatory": true,
          "is_editable": true,
		  "list_type": null,
		  "image_url": null,
		  "views": [],
          "list": [],
          "check_list": []
        },
        {
          "field_type": "editText",
          "input_type": "text",
          "name": "postcode_present_address",
          "id": "postcode_present_address",
          "value": null,
          "title": "পোস্ট কোড *",
          "hint": "পোস্ট কোড",
          "error_message": "পোস্ট কোড  পূরণ আবশ্যক",
          "mandatory": true,
          "is_editable": true,
		  "list_type": null,
		  "image_url": null,
		  "views": [],
          "list": [],
          "check_list": []
        },
        {
          "field_type": "dropdown",
          "name": "district_present_address",
          "id": "district_list_present_address",
          "value": null,
          "title": "জেলা *",
          "hint": "জেলা",
          "error_message": "জেলার নাম পূরণ আবশ্যক",
          "mandatory": true,
          "is_editable": true,
		  "list_type": "new_page_radio",
		  "image_url": null,
		  "views": [],
          "list": [],
          "check_list": []
        },
        {
          "field_type": "dropdown",
          "list_type": "new_page_radio",
          "name": "sub_district_present_address",
          "id": "Sub_district_list_present_address",
          "value": null,
          "title": "থানা / উপজেলা *",
          "hint": "থানা / উপজেলা",
          "error_message": "থানা / উপজেলা নাম পূরণ আবশ্যক",
          "mandatory": true,
          "is_editable": true,
		  "image_url": null,
		  "views": [],
          "list": [],
          "check_list": []
        },
        {
          "field_type": "editText",
          "input_type": "text",
          "name": "country_present_address",
          "id": "country_present_address",
          "value": null,
          "title": "দেশ *",
          "hint": "দেশ",
          "error_message": "দেশের পূরণ আবশ্যক",
          "mandatory": true,
          "is_editable": true,
		  "list_type": null,
		  "image_url": null,
		  "views": [],
          "list": [],
          "check_list": []
        }
      ]
    },
	{
      "field_type": "MultipleView",
      "name": "permanent_address",
      "id": "present_parmanent_address_check",
      "value": null,
	  "title": "",
	  "input_type": "",
      "list_type": null,
      "hint": "",
      "error_message": "",
      "mandatory": false,
      "is_editable": false,
      "image_url": null,
      "list": [],
      "check_list": [],
      "views": [
        {
          "field_type": "checkbox",
          "name": "present_parmanent_same_address_checkbox",
          "id": "present_parmanent_same_address",
		  "value": "0",
		  "title": "",
	      "input_type": "",
          "list_type": null,
          "hint": "",
          "error_message": "",
          "mandatory": false,
          "is_editable": false,
          "image_url": null,
          "list": [],
          "check_list": [],
          "views": []
        },
        {
          "field_type": "textView",
          "input_type": "text",
          "name": "present_parmanent_same_address_text",
          "id": "present_parmanent_same_address_text",
          "value": "বর্তমান ঠিকানা এবং স্থায়ী ঠিকানা একই",
		  "title": "",
          "list_type": null,
          "hint": "",
          "error_message": "",
          "mandatory": false,
          "is_editable": false,
          "image_url": null,
          "list": [],
          "check_list": [],
          "views": []
        }
      ]
    },
    {
      "field_type": "header",
      "title": "আপনার স্থায়ী ঠিকানা ",
	  "input_type": "",
      "list_type": null,
      "name": "",
      "id": "",
      "value": null,
      "hint": "",
      "error_message": "",
      "mandatory": false,
      "is_editable": false,
      "image_url": null,
      "views": [],
      "list": [],
      "check_list": []
    },
    {
      "field_type": "MultipleView",
      "name": "permanent_address",
      "id": "present_address",
      "value": null,
	  "input_type": "",
      "list_type": null,
      "hint": "",
      "error_message": "",
      "mandatory": false,
      "is_editable": false,
	  "title": "",
      "image_url": null,
      "list": [],
      "check_list": [],
      "views": [
        {
          "field_type": "editText",
          "input_type": "text",
          "name": "street_village_permanent_address",
          "id": "street_village_permanent_address",
          "value": null,
          "title": "স্ট্রিট নং / গ্রামের নাম *",
          "hint": "স্ট্রিট নং / গ্রামের নাম",
          "error_message": "স্ট্রিট নং / গ্রামের নাম  পূরণ আবশ্যক ",
          "mandatory": true,
          "is_editable": true,
          "image_url": null,
          "list": [],
		  "list_type": null,
          "check_list": [],
          "views": []
        },
        {
          "field_type": "editText",
          "input_type": "text",
          "name": "postcode_permanent_address",
          "id": "postcode_permanent_address",
          "value": null,
          "title": "পোস্ট কোড *",
          "hint": "পোস্ট কোড",
          "error_message": "পোস্ট কোড  পূরণ আবশ্যক",
          "mandatory": true,
          "is_editable": true,
          "image_url": null,
          "list": [],
		  "list_type": null,
          "check_list": [],
          "views": []
        },
        {
          "field_type": "dropdown",
          "list_type": "new_page_radio",
          "name": "district_permanent_address",
          "id": "district_list_permanent_address",
          "value": null,
          "title": "জেলা *",
          "hint": "জেলা",
          "error_message": "জেলার নাম পূরণ আবশ্যক",
          "mandatory": true,
          "is_editable": true,
          "image_url": null,
          "list": [],
		  "list_type": null,
          "check_list": [],
          "views": []
        },
        {
          "field_type": "dropdown",
          "list_type": "new_page_radio",
          "name": "sub_district_permanent_address",
          "id": "Sub_district_list_permanent_address",
          "value": null,
          "title": "থানা / উপজেলা *",
          "hint": "থানা / উপজেলা",
          "error_message": "থানা / উপজেলা নাম পূরণ আবশ্যক",
          "mandatory": true,
          "is_editable": true,
          "image_url": null,
          "list": [],
		  "list_type": null,
          "check_list": [],
          "views": []
        },
        {
          "field_type": "editText",
          "input_type": "text",
          "name": "country_permanent_address",
          "id": "country_permanent_address",
          "value": null,
          "title": "দেশ *",
          "hint": "দেশ",
          "error_message": "দেশের পূরণ আবশ্যক",
          "mandatory": true,
          "is_editable": true,
          "image_url": null,
          "list": [],
		  "list_type": null,
          "check_list": [],
          "views": []
        }
      ]
    }
  ]',0));
        return (new CategoryGetter())->setCategory($this)->toArray();
    }
}
