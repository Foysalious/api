<!DOCTYPE html>
<html lang="en">
<head>
    <title>Loan application form</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <style >
        .image-container {
            position: relative;
            width: 50%;
        }

        .image {
            opacity: 1;
            display: block;
            width: 100%;
            height: auto;
            transition: .5s ease;
            backface-visibility: hidden;
        }

        .middle {
            transition: .5s ease;
            opacity: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            text-align: center;
        }

        .image-container:hover .image {
            opacity: 0.3;
            border: 2px solid;
        }

        .image-container:hover .middle {
            opacity: 1;
        }

        .loader-container {
            position: absolute;
            top: 0;
            bottom: 0;
            right: 0;
            left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .custom_button {
            padding: 6px 79px;
            font-size: 18px;
        }

        .status {
            display: list-item;
            list-style-type: disc;
            list-style-position: inside;
        }

        .btn-discard {
            padding: 6px 100px;
            color: #979797;
            border: 2px solid #979797;
            font-weight: 600;
            margin-left: 10px;
            font-size: 18px;
        }

        .panel-title span:after {
            font-family: "Font Awesome 5 Free";
            content: "\f0d8";
            display: inline-block;
            padding-right: 3px;
            vertical-align: middle;
            font-weight: 900;
            margin-left: 20px;
        }

        .panel-title span.collapsed:after {
            font-family: "Font Awesome 5 Free";
            margin-left: 20px;
            content: "\f0d7";
        }

        .background {
            background-color: #eef1f5;
        }

        .btn-extra {
            border-color: rgb(0, 145, 255) !important;
            background: white !important;
            color: rgb(0, 145, 255) !important;
        }

        .padding-20 {
            padding: 20px 0;
        }

        .margin-left-20 {
            margin-left: 20px;
        }

        .Loan-status {
            padding: 10px;
        }

        .loan-summery {
            background-color: #ffffff;
            padding: 30px;
            margin: 20px 0;
        }

        .loan-summery p {
            font-size: 18px;
            font-weight: bold;
        }

        .account-info {
            background-color: #ffffff;
            padding: 30px;
            margin: 20px 0;
        }

        .loan-summery span {
            font-family: OpenSans;
            font-size: 16px;
            font-weight: 600;
            color: #121212;
        }

        .account-info p {
            font-size: 18px;
            font-weight: bold;
        }

        .comment {
            background-color: #ffffff;
            padding: 30px;
            margin: 20px 0;
        }

        .comment p {
            font-size: 18px;
            font-weight: bold;
            cursor: pointer
        }

        .change-logs {
            background-color: #ffffff;
            padding: 30px;
            margin: 20px 0;
        }

        .change-logs p {
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
        }

        .item-align-center {
            align-items: center;
        }

        .inner-div {
            padding: 20px;
        }

        .inner-div p {
            font-size: 24px;
        }

        .form-control {
            padding: 25px !important;
            background-color: rgba(243, 247, 250, 0.7) !important;
        }

        .row {
            margin-bottom: 20px !important;
        }

        .doc-div {
            max-width: 20%;
            padding: 15px;
        }

        .edit-button {
            position: absolute;
            right: 40px;
            cursor: pointer;
        }

        .bodered {
            border: 1px solid #cecece;
        }

 /*       .uploader {
            width: 100%;
            color: #cecece;
            padding: 40px 15px;
            text-align: center;
            font-size: 18px;
            border: 2px dotted #cecece;
            position: relative;

        &.dragging {
             background: #fff;
             color: #2196F3;
             border: 3px dashed #2196F3;

        .file-input label {
            background: #2196F3;
            color: #fff;
        }
        }

        .file-input {
            width: 110px;
            margin: auto;
            height: 50px;
            position: relative;

        label,
        input {
            width: 100%;
            position: absolute;
            color: #121212;
            left: 0;
            top: 0;
            padding: 3px;
            margin-top: 5px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid #cecece;
            background-color: #e2e2e2
        }

        input {
            opacity: 0;
            z-index: -2;
        }
        }

        .images-preview {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 56px;

        .img-wrapper {
            width: 160px;
            display: flex;
            flex-direction: column;
            margin: 10px;
            height: 150px;
            justify-content: space-between;
            background: #fff;
            box-shadow: 5px 5px 20px #3e3737;

        img {
            max-height: 105px;
        }
        }

        .details {
            font-size: 12px;
            background: #fff;
            color: #000;
            display: flex;
            flex-direction: column;
            align-items: self-start;
            padding: 3px 6px;

        .name {
            overflow: hidden;
            height: 18px;
        }
        }
        }

        .upload-control {
            position: absolute;
            width: 100%;
            background: #fff;
            bottom: 0;
            left: 0;
            border-top-left-radius: 7px;
            border-top-right-radius: 7px;
            padding: 10px;
            padding-bottom: 4px;
            text-align: right;

        button, label {
            background: #2196F3;
            border: 2px solid #03A9F4;
            border-radius: 3px;
            color: #fff;
            font-size: 15px;
            cursor: pointer;
        }

        label {
            padding: 2px 5px;
            margin-right: 10px;
        }
        }
        }*/
    </style>
</head>
<body align="center">
<?php $today = \Carbon\Carbon::today()->format('d-m-y')?>

<div>
    <a  class="navbar-brand margin-left-300">
        <img width="100%" src="{{asset('assets/images/logo.jpg')}}" ></a>
    <span class="vertical"></span>
    <a class="navbar-brand" >
        <img  class="bank-logo" height="35" width="135" src="{{$bank['logo']}}">
    </a>
    <h1> {{$today}}</h1>
    <img width="100%" height="220px"
         src="{{$final_information_for_loan['document']['picture']}}" alt="">
</div>
<p>
    Dear Sir/Madam,<br><br><br>
    I request you to grant me/us the term loan facility of BDT <span>{{round($loan_amount,0)}}</span> for a tenure of <span>{{$duration}}</span><br>
    years for {{$purpose}} purpose. To enable you to consider the proposal, my following information<br>
    are given for your kind consideration.
</p>


<div class="background" {{--v-cloak--}}>

    <div class="row" {{--v-if="data && !loader"--}}>
        <div class="col-md-10 offset-md-1">
            {{--<div class="padding-20">

            </div>--}}


            <div class="account-info">

                <div id="accountInfo" {{--class="collapse"--}}>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="inner-div">
                                <p>Proprietors Details</p>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Name
                                    </div>
                                    <div class="col-md-8">
                                        <div {{--:disabled="data.partner.profile.is_nid_verified"--}} class="form-control" type="text"
                                                {{--v-model="data.final_information_for_loan.personal.name"--}}>{{$final_information_for_loan['personal']['name']}}</div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Gender
                                    </div>
                                    <div class="col-md-8">
                                        <div {{--:disabled="is_input_disable"--}} class="form-control" type="text"
                                                {{--v-model="data.final_information_for_loan.personal.gender"--}}>{{$final_information_for_loan['personal']['gender']}}</div>
                                    </div>
                                </div>

                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Date of Birth
                                    </div>
                                    <div class="col-md-8">
                                        <div {{--:disabled="data.partner.profile.is_nid_verified"--}} class="form-control" type="text"
                                                {{--v-model="data.final_information_for_loan.personal.birthday"--}}>{{$final_information_for_loan['personal']['birthday']}}</div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Place of Birth
                                    </div>
                                    <div class="col-md-8">
                                        <div {{--:disabled="is_input_disable"--}} class="form-control" type="text"
                                                {{--v-model="data.final_information_for_loan.personal.birth_place"--}}>{{$final_information_for_loan['personal']['birth_place']}}</div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Fathers Name
                                    </div>
                                    <div class="col-md-8">
                                        <div {{--:disabled="is_input_disable"--}} class="form-control" type="text"
                                                {{-- v-model="data.final_information_for_loan.personal.father_name"--}}>{{$final_information_for_loan['personal']['father_name']}}</div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Mothers Name
                                    </div>
                                    <div class="col-md-8">
                                        <div {{--:disabled="is_input_disable"--}} class="form-control" type="text"
                                                {{--v-model="data.final_information_for_loan.personal.mother_name"--}}>{{$final_information_for_loan['personal']['mother_name']}}</div>
                                    </div>
                                </div>

                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Spouse Name
                                    </div>
                                    <div class="col-md-8">
                                        <div {{--:disabled="is_input_disable"--}} class="form-control" type="text"
                                                {{--v-model="data.final_information_for_loan.personal.spouse_name"--}}>{{$final_information_for_loan['personal']['spouse_name']}}</div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        E-mail
                                    </div>
                                    <div class="col-md-8">
                                        <div {{--:disabled="is_input_disable"--}} class="form-control" type="text"
                                                {{--v-model="data.final_information_for_loan.personal.email"--}}>{{$final_information_for_loan['personal']['email']}}</div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        NID/Passport no.
                                    </div>
                                    <div class="col-md-8">
                                        <div {{--:disabled="data.partner.profile.is_nid_verified"--}} class="form-control" type="text"
                                                {{--v-model="data.final_information_for_loan.personal.nid_no"--}}>{{$final_information_for_loan['personal']['nid_no']}}</div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        NID Issue date
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                                {{-- v-model="data.final_information_for_loan.personal.nid_issue_date"--}}>{{$final_information_for_loan['personal']['nid_issue_date']}}</div>
                                    </div>
                                </div>

                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Other ID (If any)
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                                {{-- v-model="data.final_information_for_loan.personal.other_id"--}}>{{$final_information_for_loan['personal']['other_id']}}</div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Other ID Issue date
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                                {{-- v-model="data.final_information_for_loan.personal.other_id_issue_date"--}}>{{$final_information_for_loan['personal']['other_id_issue_date']}}</div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        E-TIN number
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                             {{-- v-model="data.final_information_for_loan.business.tin_no"--}}>{{$final_information_for_loan['business']['tin_no']}}</div>
                                    </div>
                                </div>
                            </div>
                            {{--???--}}
                            <div class="inner-div">
                                <p>Present Address</p>

                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Street
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                              {{--v-model="data.final_information_for_loan.personal.present_address.street"--}}>{{$final_information_for_loan['personal']['present_address']['street']}}</div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        PS/Upozilla
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.personal.present_address.thana"--}}>
                                            {{$final_information_for_loan['personal']['present_address']['thana']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        District
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.personal.present_address.zilla"--}}>
                                            {{$final_information_for_loan['personal']['present_address']['zilla']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Postal Code
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.personal.present_address.post_code"--}}>
                                            {{$final_information_for_loan['personal']['present_address']['post_code']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Country
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.personal.present_address.country"--}}>
                                            {{$final_information_for_loan['personal']['present_address']['country']}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="inner-div">
                                <p>Permanent Address</p>

                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Street
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.personal.permanent_address.street"--}}>
                                            {{$final_information_for_loan['personal']['permanent_address']['street']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        PS/Upozilla
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.personal.permanent_address.thana"--}}>
                                            {{$final_information_for_loan['personal']['permanent_address']['thana']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        District
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.personal.permanent_address.zilla"--}}>
                                            {{$final_information_for_loan['personal']['permanent_address']['zilla']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Postal Code
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.personal.permanent_address.post_code"--}}>
                                            {{$final_information_for_loan['personal']['permanent_address']['post_code']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Country
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.personal.permanent_address.street"--}}>
                                            {{$final_information_for_loan['personal']['permanent_address']['country']}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{--???--}}
                        <div class="col-md-6">
                            <div class="inner-div">
                                <p>Business Details</p>

                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Business Name
                                    </div>
                                    <div class="col-md-8">
                                            <textarea  class="form-control" cols="10"
                                                      rows="4"
                                                      {{--v-model="data.final_information_for_loan.business.business_name"--}}>
                                                {{$final_information_for_loan['business']['business_name']}}
                                            </textarea>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Nature of Business
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.business.smanager_business_type"--}}>
                                            {{$final_information_for_loan['business']['smanager_business_type']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Legal Status
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="legal_status"--}}>
                                            {{$final_information_for_loan['business']['ownership_type']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Business Type
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.business.business_type"--}}>
                                            {{$final_information_for_loan['business']['business_type']}}
                                        </div>
                                    </div>
                                </div>

                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        E-TIN number (company)
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.business.tin_no"--}}>
                                            {{$final_information_for_loan['business']['tin_no']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Issue Date
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="e_tin_company_issue_date"--}}>
                                            {{$final_information_for_loan['business']['tin_no']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Date of Starting with Sheba
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.business.establishment_year"--}}>
                                            {{$final_information_for_loan['business']['establishment_year']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Number of online order serve
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                             {{--v-model="number_online_order_serve"--}}>
                                            {{$final_information_for_loan['business']['online_order']}}

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="inner-div">
                                <p>Bank Details</p>

                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Account Title
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.finance.acc_name"--}}>
                                            {{$final_information_for_loan['finance']['acc_name']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Account Number
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.finance.acc_no"--}}>
                                            {{$final_information_for_loan['finance']['acc_no']}}

                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Acc Type
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.finance.acc_type"--}}>
                                            {{$final_information_for_loan['finance']['acc_type']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Bank Name
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.finance.bank_name"--}}>
                                            {{$final_information_for_loan['finance']['bank_name']}}

                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Branch
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.finance.branch_name"--}}>
                                            {{$final_information_for_loan['finance']['branch_name']}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="inner-div">
                                <p>Business Performance</p>

                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Total 6 Month Sale
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.business.last_six_month_sales_information.min_sell"--}}>
                                            {{$final_information_for_loan['business']['last_six_month_sales_information']['min_sell']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Total 6 Month Cost
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.business.business_additional_information.other_cost"--}}>
                                            {{$final_information_for_loan['business']['business_additional_information']['other_cost']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Total Fixed Asset
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.business.fixed_asset"--}}>
                                            {{$final_information_for_loan['business']['fixed_asset']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Number of Employee
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.business.full_time_employee"--}}>
                                            {{$final_information_for_loan['business']['full_time_employee']}}
                                        </div>
                                    </div>
                                </div>
                                <div class="row item-align-center">
                                    <div class="col-md-4">
                                        Value of Stock
                                    </div>
                                    <div class="col-md-8">
                                        <div  class="form-control" type="text"
                                               {{--v-model="data.final_information_for_loan.business.stock_price"--}}>
                                            {{$final_information_for_loan['business']['stock_price']}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
