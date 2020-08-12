<!DOCTYPE html>
<html lang="en">
<head>
    <title>Loan application form</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body{
            font-family: 'kalpurush',sans-serif;
        }
        .table {
            width: 100%;
            max-width: 100%;
            margin-bottom: 1rem;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #eceeef;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #eceeef;
        }

        .table tbody + tbody {
            border-top: 2px solid #eceeef;
        }

        .table .table {
            background-color: #fff;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.3rem;
        }

        .table-bordered {
            border: 1px solid #eceeef;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #eceeef;
        }

        .table-bordered thead th,
        .table-bordered thead td {
            border-bottom-width: 2px;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.075);
        }

        .table-active,
        .table-active > th,
        .table-active > td {
            background-color: rgba(0, 0, 0, 0.075);
        }

        .table-hover .table-active:hover {
            background-color: rgba(0, 0, 0, 0.075);
        }

        .table-hover .table-active:hover > td,
        .table-hover .table-active:hover > th {
            background-color: rgba(0, 0, 0, 0.075);
        }

        .table-success,
        .table-success > th,
        .table-success > td {
            background-color: #dff0d8;
        }

        .table-hover .table-success:hover {
            background-color: #d0e9c6;
        }

        .table-hover .table-success:hover > td,
        .table-hover .table-success:hover > th {
            background-color: #d0e9c6;
        }

        .table-info,
        .table-info > th,
        .table-info > td {
            background-color: #d9edf7;
        }

        .table-hover .table-info:hover {
            background-color: #c4e3f3;
        }

        .table-hover .table-info:hover > td,
        .table-hover .table-info:hover > th {
            background-color: #c4e3f3;
        }

        .table-warning,
        .table-warning > th,
        .table-warning > td {
            background-color: #fcf8e3;
        }

        .table-hover .table-warning:hover {
            background-color: #faf2cc;
        }

        .table-hover .table-warning:hover > td,
        .table-hover .table-warning:hover > th {
            background-color: #faf2cc;
        }

        .table-danger,
        .table-danger > th,
        .table-danger > td {
            background-color: #f2dede;
        }

        .table-hover .table-danger:hover {
            background-color: #ebcccc;
        }

        .table-hover .table-danger:hover > td,
        .table-hover .table-danger:hover > th {
            background-color: #ebcccc;
        }

        .thead-inverse th {
            color: #fff;
            background-color: #292b2c;
        }

        .thead-default th {
            color: #464a4c;
            background-color: #eceeef;
        }

        .table-inverse {
            color: #fff;
            background-color: #292b2c;
        }

        .table-inverse th,
        .table-inverse td,
        .table-inverse thead th {
            border-color: #fff;
        }

        .table-inverse.table-bordered {
            border: 0;
        }

        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -ms-overflow-style: -ms-autohiding-scrollbar;
        }

        .table-responsive.table-bordered {
            border: 0;
        }

        .loan-summery p {
            font-size: 14px;
            font-weight: bold;
        }

        .loan-summery span {
            font-size: 14px;
            font-weight: 600;
            color: #121212;
        }

        .account-info p {
            font-size: 14px;
            font-weight: bold;
        }

        .inner-div {
            padding: 5px;
        }

        .inner-div p {
            font-size: 14px;
        }

        .form-control {
            background-color: rgba(243, 247, 250, 0.7) !important;
            height: 20px;
            display: block;
            line-height: .8;
            width: 100%;
            padding: 0 5px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 5px;
        }

        th {
            vertical-align: baseline;
        }

        td, th {
            text-align: left;
            padding: 0px;
            font-size: 12px;
            font-weight: normal;
        }

        .cover-letter {
            font-size: 12px;
        }

        .cover-letter span {
            text-decoration: underline;
            font-weight: 600;
        }

        .sheba-logo {
            margin-right: 30px;
            height: 25px;
        }

        .bank-logo {
            height: 25px;
        }

        .horizontal {
            border: none;
            border-left: 2px solid hsla(200, 10%, 50%, 100);
            height: 25px;
            width: 5px;
            color: #121212;
        }

        .empty_table {
            border: 1px dashed black;
            margin: 3px;
            border-radius: 3px;
        }

        .bottom-letter {
            font-size: 13px;
        }

        .static-node {
            color: red;
            font-size: 12px;
            margin-top: 10px;
        }

        .title {
            text-align: center;
            font-size: 18px;
            color: #FB0973;
            font-weight: 600;
        }

        .month-div {
            padding: 5px;
            text-align: center;
        }

        .rules table {
            border: 1px solid black;
            border-collapse: collapse;
        }

        .rules td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        .rules tr {
            border: 1px solid black;
            border-collapse: collapse;
        }
    </style>
</head>
<body>
<?php $today = \Carbon\Carbon::today()->format('d-m-y')?>

<div class="background">
    <div>
        <table>
            <tr>
                <td style="width: 50%">
                    <table>
                        <tr>
                            <td>
                                <img class="sheba-logo"
                                     src="{{ getCDNAssetsFolder() . 'partner_assets/assets/images/logo_35_135.jpg' }}"
                                     alt="">
                                <span style="padding-bottom: 10px">  </span> <img class="bank-logo"
                                                                                  src="{{ $bank['logo'] }}" alt="">
                                <span style="padding-bottom: 10px">  </span> <img style="height: 32px;"
                                                                                  src="https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/partner/loans/robi-logo.png"
                                                                                  alt="">
                            </td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td style="font-size: 14px">Date: {{ $today }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%;text-align: right;">
                    <img width="100px" height="100px" src="{{ $final_information_for_loan['document']['picture'] }}"
                         alt="">
                </td>
            </tr>
        </table>
    </div>
    <div class="title">Dana Classic</div>
    <div class="cover-letter">
        Dear Sir/Madam<br>
        I request you to grant me/us the term loan facility of BDT <span>{{ $loan_amount }}</span> for a tenure
        of <span>{{ $total_installment }}</span> months for <span>{{ $purpose }}</span> purpose.To enable you to
        consider the proposal, my following information are given for your kind consideration.
    </div>
    <div class="static-node">Note : This applicant has already agreed for the terms and condition (Including providing
                             authorization robi to share info, CIB report)
    </div>
    <div style="margin-top: 10px">
        <table>
            <tr>
                <th width="45%">
                    <div class="inner-div">
                        <p>Retailer Details</p>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;">Name</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['personal']['name']}}</div>
                                </th>
                            </tr>
                        </table>

                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;">Phone number</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$partner['profile']['mobile']}}</div>
                                </th>
                            </tr>
                        </table>

                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Date of Birth</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['personal']['birthday']}}</div>
                                </th>
                            </tr>
                        </table>

                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Fathers Name</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['personal']['father_name']}}</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Mothers Name</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['personal']['mother_name']}}</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">NID no</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['personal']['nid_no']}}</div>
                                </th>
                            </tr>
                        </table>
                    </div>
                    <div class="inner-div">
                        <p>Bank Details</p>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Account holder name</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['finance']['acc_name']}}</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Account Number</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['finance']['acc_no']}}</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Bank Name</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['finance']['bank_name']}}</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Branch</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['finance']['branch_name']}}</div>
                                </th>
                            </tr>
                        </table>
                    </div>
                </th>
                <th width="45%">
                    <div class="inner-div">
                        <p>Business Details</p>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Trade license Number</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['business']['trade_license']}}</div>
                                </th>
                            </tr>
                        </table>

                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Trade license Issue date</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['business']['trade_license_issue_date']}}</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Date of trade license registration</th>
                                <th style="width: 60%">
                                    <div class="form-control"> no data</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">bKash number</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['finance']['bkash']['bkash_no']}}</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Is retailer bKash agent?</th>
                                <th style="width: 60%">
                                    <div class="form-control">no data</div>
                                </th>
                            </tr>
                        </table>
                    </div>
                    <div class="inner-div" style="margin-top: 5px">
                        <p>Business address</p>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Street no/village name</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['business']['business_additional_information']['address']['street']}}</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Post Code</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['business']['business_additional_information']['address']['post_code']}}</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Thana/Upzilla</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['business']['business_additional_information']['address']['thana']}}</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Zilla</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['business']['business_additional_information']['address']['zilla']}}</div>
                                </th>
                            </tr>
                        </table>
                    </div>
                </th>
            </tr>
        </table>
    </div>
    <div>
        <table>
            <tr>
                <td width="50%">
                    <div class="inner-div">
                        <p>Permanent Address</p>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Street</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['personal']['permanent_address']['street']}}</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">PS/Upozilla</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['personal']['permanent_address']['thana']}}</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">District</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['personal']['permanent_address']['zilla']}}</div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Postal Code</th>
                                <th style="width: 60%">
                                    <div class="form-control">{{$final_information_for_loan['personal']['permanent_address']['post_code']}}</div>
                                </th>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="50%">
                </td>
            </tr>
        </table>
    </div>

    <div style="page-break-before:always">&nbsp;</div>

    <div>
        <p style="font-size: 14px;">For official use only</p>
        <p style="font-size: 12px;">Last 12 months sale (‘000 TK)</p>
        <table>
            <tr>
                <td width="16.66%">
                    <div class="inner-div">
                        <table>
                            <tr style="text-align: center">
                                <td>
                                    <div class="form-control"></div>
                                    <div>Month 1</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="16.66%">
                    <div class="inner-div">
                        <table>
                            <tr style="text-align: center">
                                <td>
                                    <div class="form-control"></div>
                                    <div>Month 2</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="16.66%">
                    <div class="inner-div">
                        <table>
                            <tr style="text-align: center">
                                <td>
                                    <div class="form-control"></div>
                                    <div>Month 3</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="16.66%">
                    <div class="inner-div">
                        <table>
                            <tr style="text-align: center">
                                <td>
                                    <div class="form-control"></div>
                                    <div>Month 4</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="16.66%">
                    <div class="inner-div">
                        <table>
                            <tr style="text-align: center">
                                <td>
                                    <div class="form-control"></div>
                                    <div>Month 5</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="16.66%">
                    <div class="inner-div">
                        <table>
                            <tr style="text-align: center">
                                <td>
                                    <div class="form-control"></div>
                                    <div>Month 6</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td width="16.66%">
                    <div class="inner-div">
                        <table>
                            <tr style="text-align: center">
                                <td>
                                    <div class="form-control"></div>
                                    <div>Month 1</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="16.66%">
                    <div class="inner-div">
                        <table>
                            <tr style="text-align: center">
                                <td>
                                    <div class="form-control"></div>
                                    <div>Month 2</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="16.66%">
                    <div class="inner-div">
                        <table>
                            <tr style="text-align: center">
                                <td>
                                    <div class="form-control"></div>
                                    <div>Month 3</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="16.66%">
                    <div class="inner-div">
                        <table>
                            <tr style="text-align: center">
                                <td>
                                    <div class="form-control"></div>
                                    <div>Month 4</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="16.66%">
                    <div class="inner-div">
                        <table>
                            <tr style="text-align: center">
                                <td>
                                    <div class="form-control"></div>
                                    <div>Month 5</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="16.66%">
                    <div class="inner-div">
                        <table>
                            <tr style="text-align: center">
                                <td>
                                    <div class="form-control"></div>
                                    <div>Month 6</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div style="margin: 20px 0">
        <table>
            <tr>
                <td width="50%">
                    <div class="inner-div">
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Average sale</th>
                                <th style="width: 60%">
                                    <div class="form-control"></div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Sanctioned limit (Loan)</th>
                                <th style="width: 60%">
                                    <div class="form-control"></div>
                                </th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Interest Rate (Yearly)</th>
                                <th style="width: 60%">
                                    <div class="form-control"></div>
                                </th>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="50%">
                </td>
            </tr>
        </table>
    </div>

    <div>
        <div style="font-size: 14px;">Start Month</div>
        @include('reports.pdfs._month')
    </div>

    <div>
        <div style="font-size: 14px;">End Month</div>
        @include('reports.pdfs._month')
    </div>


    <div class="rules" style="margin-top: 20px">
        <table class="margin-top-10 table" width="100%">
            <col width="10%">
            <col width="40%">
            <col width="25%">
            <col width="25%">
            <tr>
                <td rowspan="2" style="text-align: center">ক্রমিক নং</td>
                <td rowspan="2" style="text-align: center;">প্রশ্ন সমূহ</td>
                <td colspan="2" style="text-align: center">মন্তব্য</td>
            </tr>
            <tr>
                <td style="height: 20px;text-align: center">আইপিডিসি'র মন্তব্য</td>
                <td style="height: 20px;text-align: center">গ্রাহকের মন্তব্য</td>
            </tr>

            <tr>
                <td style="text-align: center">১</td>
                <td style="text-align: center">মোট কতো টাকা বিতরণ করা হবে?</td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>

            <tr>
                <td style="text-align: center">২ (ক)</td>
                <td style="text-align: center">ঋণ সুবিধার সমুদয় অর্থ কি এককালীন বিতরণ করা হবে?</td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>

            <tr>
                <td style="text-align: center">২ (খ)</td>
                <td style="text-align: center">যদি এককালীন বিতরণ না হয়, তবে কয়টি কিস্তিতে এবং কী পরিমাণে তা বিতরণ করা
                                               হবে?
                </td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>

            <tr>
                <td style="text-align: center">৩</td>
                <td style="text-align: center">কতো বছরে ঋণ পরিশোধ হবে? (পুনঃতফসিলকৃত হিসেবের জন্য পুনঃতফসিলকরণের পর
                                               হতে)
                </td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>

            <tr>
                <td style="text-align: center">৪ (ক)</td>
                <td style="text-align: center">ঋণ পরিশোধের ক্ষেত্রে Grace Period দেয়া হবে কিনা?</td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>
            <tr>
                <td style="text-align: center">৪ (খ)</td>
                <td style="text-align: center">Grace Period দেয়া হলে, তা কতো সময়ের জন্য?</td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>
            <tr>
                <td style="text-align: center">৫</td>
                <td style="text-align: center">কিস্তির টাকা কিভাবে পরিশোধ করতে হবে?</td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>
            <tr>
                <td style="text-align: center">৬</td>
                <td style="text-align: center">প্রতিটি কিস্তির পরিমান কতো হবে?</td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>
            <tr>
                <td style="text-align: center">৭ (ক)</td>
                <td style="text-align: center">ঋণ পরিশোধের মেয়াদকালে কিস্তির পরিমাণ একই থাকবে কিনা?</td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>
            <tr>
                <td style="text-align: center">৭ (খ)</td>
                <td style="text-align: center">না থাকলে, গ্রাহককে সম্পূর্ণ পরিশোধ সূচি সম্পর্কে অবহিত করা হয়েছে কিনা?
                </td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>
            <tr>
                <td style="text-align: center">৮ (ক)</td>
                <td style="text-align: center">সুদের হার কি সব সময় একই থাকবে কি না?</td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>
            <tr>
                <td style="text-align: center">৮ (খ)</td>
                <td style="text-align: center">একই থাকলে সুদের হার কতো হবে?</td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>
            <tr>
                <td style="text-align: center">৮ (গ)</td>
                <td style="text-align: center">সুদের হার পরিবর্তনীয় হলে গ্রাহকে এ সম্পর্কে অবহিত করা হয়েছে কী না?</td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>
            <tr>
                <td style="text-align: center">৯ (ক)</td>
                <td style="text-align: center">বকেয়া ঋণের সাথে ভবিষ্যৎ এ কোন ফী বা চার্জ আদায় করা হবে কী না?</td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>
            <tr>
                <td style="text-align: center"></td>
                <td style="text-align: center">যদি কোন ফী বা চার্জ আদায় করা হয়, তবে কোন পরিস্থিতিতে এবং কি পরিমাণের তা
                                               আদায় করা হবে?
                </td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>
            <tr>
                <td style="text-align: center">১০ (ক)</td>
                <td style="text-align: center">ঋণ হিসাব টি মেয়াদপূর্তির পূর্বে সমন্বয় করা হলে কোন জরিমানা প্রদান করতে
                                               হবে কী না?
                </td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>
            <tr>
                <td style="text-align: center">১০ (খ)</td>
                <td style="text-align: center">যদি প্রদান করতে হয়, তবে তার পরিমাণ কতো?</td>
                <td style="text-align: center"></td>
                <td style="text-align: center">আইপিডিসি'র মন্তব্যের সাথে একমত</td>
            </tr>
        </table>
    </div>

    <div class="bottom-letter" style="margin-top: 40px">
        I, hereby, declare that the information contained herein is correct. I/We shall also submit any additional
        accurate information/documents as and when
        required. You are hereby authorized to obtain and/or verify whatsoever information from any source regarding our
        credit worthiness.
    </div>

    <div style="margin-top: 30px;">
        <table>
            <tr>
                <td width="30%">
                    <div style="margin-top: 30px;font-size: 14px">
                        <span>___________________________</span><br>
                        Authorized Signature
                    </div>
                    <div style="margin-top: 20px;font-size: 14px">
                        <span>Name</span> <br>
                    </div>

                    <div style="margin-top: 10px;font-size: 14px">
                        Date ______/______/______
                    </div>

                </td>
                <td width="30%" style="margin-left: 50px">
                    <div style="margin-top: 30px;font-size: 14px">
                        <span>___________________________</span><br>
                        Client Authorized Signature
                    </div>
                    <div style="margin-top: 20px;font-size: 14px">
                        <span>Name</span> <br>
                    </div>

                    <div style="margin-top: 10px;font-size: 14px">
                        Date ______/______/______
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
