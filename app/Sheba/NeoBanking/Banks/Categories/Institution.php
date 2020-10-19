<?php namespace Sheba\NeoBanking\Banks\Categories;


use Sheba\NeoBanking\Banks\BankCompletionDetail;
use Sheba\NeoBanking\Banks\CategoryGetter;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\DTO\FormItemBuilder;
use Sheba\NeoBanking\Statics\FormStatics;

class Institution extends BankFormCategory
{
    protected $code = 'institution';

    public function completion()
    {
        return [
            'en' => 75,
            'bn' => '৭৫'
        ];
    }

    public function get() : CategoryGetter
    {
        $formItems = FormStatics::institution();
        return $this->getFormData($formItems);
    }

    public function post($data)
    {
        return !!$this->bankAccountData->postByCode($this->code, $data);
    }

    public function getLastUpdated()
    {
        return $this->last_updated;
    }

    public function getDummy()
    {

        $this->setData( json_decode('[
      {
        "field_type": "header",
        "title": "যোগাযোগ এর তথ্য",
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
        "title": "মোবাইল নাম্বার  *",
        "input_type": "text",
        "list_type": null,
        "name": "mobile",
        "id": "mobile",
        "value": null,
        "hint": "+880 1678242900",
        "error_message": "মোবাইল নাম্বার পূরণ আবশ্যক",
        "mandatory": true,
        "is_editable": false,
        "image_url": null,
        "views": [],
        "list": [],
        "check_list": []
      },
      {
        "field_type": "editText",
        "title": "ই-মেইল আইডি",
        "input_type": "email",
        "list_type": null,
        "name": "email",
        "id": "email",
        "value": null,
        "hint": "arafat@gmail.com",
        "error_message": "ই-মেইল আইডি পূরণ আবশ্যক",
        "mandatory": false,
        "is_editable": true,
        "image_url": null,
        "views": [],
        "list": [],
        "check_list": []
      },
      {
        "field_type": "editText",
        "title": "আপনার প্রতিষ্ঠানের নাম (বড় অক্ষরে) *",
        "input_type": "text",
        "list_type": null,
        "name": "company_name",
        "id": "company_name",
        "value": null,
        "hint": "AZAD TELECOM",
        "error_message": "প্রতিষ্ঠানের নাম  পূরণ আবশ্যক",
        "mandatory": true,
        "is_editable": true,
        "image_url": null,
        "views": [],
        "list": [],
        "check_list": []
      },
      {
        "field_type": "header",
        "title": "ট্রেড লাইসেন্স সম্পর্কিত তথ্য",
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
        "title": "ট্রেড লাইসেন্স নং *",
        "input_type": "text",
        "list_type": null,
        "name": "trade_licence_number",
        "id": "trade_licence_number",
        "value": null,
        "hint": "উদাহরণ: AHMED TELECOM",
        "error_message": "ট্রেড লাইসেন্স নং পূরণ আবশ্যক",
        "mandatory": true,
        "is_editable": true,
        "image_url": null,
        "views": [],
        "list": [],
        "check_list": []
      },
      {
        "field_type": "date",
        "title": "নিবন্ধনের তারিখ *",
        "input_type": null,
        "list_type": null,
        "name": "trade_licence_date",
        "id": "trade_licence_date",
        "value": null,
        "hint": "উদাহরণ: 01/01/2000",
        "error_message": "নিবন্ধনের তারিখ  পূরণ আবশ্যক",
        "mandatory": true,
        "is_editable": true,
        "image_url": null,
        "views": [],
        "list": [],
        "check_list": []
      },
      {
        "field_type": "editText",
        "title": "অনুমোদনকারী প্রতিষ্ঠান *",
        "input_type": "text",
        "list_type": null,
        "name": "grantor_organization",
        "id": "grantor_organization",
        "value": null,
        "hint": "উদাহরণ: 5000",
        "error_message": "অনুমোদনকারী প্রতিষ্ঠানের নাম পূরণ আবশ্যক ",
        "mandatory": true,
        "is_editable": true,
        "image_url": null,
        "views": [],
        "list": [],
        "check_list": []
      },
	  {
        "field_type": "header",
        "title": "রেজিস্ট্রেশন সম্পর্কিত তথ্য",
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
        "title": "রেজিস্ট্রেশন নং",
        "input_type": "text",
        "list_type": null,
        "name": "registration_number",
        "id": "registration_number",
        "value": null,
        "hint": "90145",
        "error_message": "রেজিস্ট্রেশন নং পূরণ আবশ্যক",
        "mandatory": false,
        "is_editable": true,
        "image_url": null,
        "views": [],
        "list": [],
        "check_list": []
      },
      {
        "field_type": "date",
        "title": "নিবন্ধনের তারিখ ",
        "input_type": null,
        "list_type": null,
        "name": "registration_date",
        "id": "registration_date",
        "value": null,
        "hint": "উদাহরণ: 01/01/2000",
        "error_message": "নিবন্ধনের তারিখ  পূরণ আবশ্যক",
        "mandatory": false,
        "is_editable": true,
        "image_url": null,
        "views": [],
        "list": [],
        "check_list": []
      },
      {
        "field_type": "editText",
        "title": "অনুমোদনকারী প্রতিষ্ঠান এবং দেশ",
        "input_type": "text",
        "list_type": null,
        "name": "grantor_organization_and_country",
        "id": "grantor_organization_and_country",
        "value": null,
        "hint": "উদাহরণ: Grameen phone, Bangladesh",
        "error_message": "অনুমোদনকারী প্রতিষ্ঠান এবং দেশের নাম পূরণ আবশ্যক ",
        "mandatory": false,
        "is_editable": true,
        "image_url": null,
        "views": [],
        "list": [],
        "check_list": []
      },
      {
        "field_type": "header",
        "title": "ব্যবসা / অফিস - এর ঠিকানা",
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
        "name": "business_office_address",
        "id": "business_office_address",
        "value": null,
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
            "field_type": "editText",
            "input_type": "text",
            "name": "street_village_business",
            "id": "street_village_business",
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
            "name": "postcode_business",
            "id": "postcode_business",
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
            "list_type": "new_page_radio",
            "name": "district_business",
            "id": "district_list_business",
            "value": null,
            "title": "জেলা *",
            "hint": "জেলা",
            "error_message": "জেলার নাম পূরণ আবশ্যক",
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
            "list_type": "new_page_radio",
            "name": "sub_district_business",
            "id": "Sub_district_list_business",
            "value": null,
            "title": "থানা / উপজেলা *",
            "hint": "থানা / উপজেলা",
            "error_message": "থানা / উপজেলা নাম পূরণ আবশ্যক",
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
            "name": "country_business",
            "id": "country_business",
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
        "field_type": "header",
        "title": "অন্যান্য তথ্য",
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
        "input_type": "text",
        "name": "vat_registration_number",
        "id": "vat_registration_number",
        "value": null,
        "title": "ভ্যাট রেজিস্ট্রেশন নাম্বার (যদি থাকে)",
        "hint": "এখানে লিখুন",
        "error_message": "",
        "mandatory": false,
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
        "name": "organization_etin_number",
        "id": "organization_etin_number",
        "value": null,
        "title": "প্রতিষ্ঠানের ই-টিন নাম্বার (যদি থাকে)",
        "hint": "এখানে লিখুন",
        "error_message": "",
        "mandatory": false,
        "is_editable": true,
		"list_type": null,
		"image_url": null,
		"views": [],
        "list": [],
        "check_list": []
      },
      {
        "field_type": "dropdown",
        "list_type": "dialog",
        "name": "organization_type_list",
        "id": "organization_type_list",
        "value": null,
        "title": "প্রতিষ্ঠানের ধরণ",
        "hint": "এখানে লিখুন",
        "error_message": "",
        "mandatory": false,
        "is_editable": true,
		"list_type": null,
		"image_url": null,
		"views": [],
        "list": [],
        "check_list": []
      },
      {
        "field_type": "dropdown",
        "list_type": "dialog",
        "name": "business_type_list",
        "id": "business_type_list",
        "value": null,
        "title": "ব্যবসার ধরণ",
        "hint": "এখানে লিখুন",
        "error_message": "",
        "mandatory": false,
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
        "name": "yearly_earning",
        "id": "yearly_earning",
        "value": null,
        "title": "বাৎসরিক আয়ের পরিমান",
        "hint": "উদাহরণ: 10000",
        "error_message": "",
        "mandatory": false,
        "is_editable": true,
		"list_type": null,
		"image_url": null,
		"views": [],
        "list": [],
        "check_list": []
      }
    ]',0));
        return (new CategoryGetter())->setCategory($this)->toArray();
    }
}
