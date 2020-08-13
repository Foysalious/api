<!DOCTYPE html>
<html lang="en">
<head>
    <title>Loan application form</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <style >
        .body{
            font-family:  Siyamrupali,sans-serif!important;
        }
        .loan-summery p {
            font-size: 14px;
            font-weight: bold;
        }

        .loan-summery span {
            font-family: OpenSans,sans-serif;
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
            font-family: Siyamrupali,sans-serif!important;
            height: 20px;
            display: block;
            line-height: .8;
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
        .cover-letter span{
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
                            <td style="align-items: center;">
                                <img class="sheba-logo" src="{{ getCDNAssetsFolder() . 'partner_assets/assets/images/logo_35_135.jpg' }}" alt="">
                                <span style="padding-bottom: 10px"> | </span>  <img class="bank-logo" src="{{ $bank['logo'] }}" alt="">
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
                    <img width="100px" height="100px" src="{{ $final_information_for_loan['document']['picture'] }}" alt="">
                </td>
            </tr>
        </table>
    </div>
    <div class="cover-letter">
        Dear Sir/Madam<br>
        I request you to grant me/us the term loan facility of BDT <span>{{ $loan_amount }}</span> for a tenure
        of <span>{{ $total_installment }}</span> months for <span>{{ $purpose }}</span> purpose.To enable you to consider the proposal, my following information are given for your kind consideration.
    </div>
    <div style="margin-top: 10px">
        <table>
            <tr>
                <th width="45%" >
                    <div class="inner-div">
                        <p>Proprietors Details</p>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;">Name</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['name']}}</div></th>
                            </tr>
                        </table>

                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;">Contact Number</th>
                                <th style="width: 60%"><div class="form-control" >{{$partner['profile']['mobile']}}</div></th>
                            </tr>
                        </table>

                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Gender</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['gender']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Date of Birth</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['birthday']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Place of Birth</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['birth_place']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Fathers Name</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['father_name']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Mothers Name</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['mother_name']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Spouse Name</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['spouse_name']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">E-mail</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['email']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">NID/Passport no</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['nid_no']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">NID Issue date</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['nid_issue_date']}}</div></th>
                            </tr>
                        </table>

                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">E-TIN number</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['business']['tin_no']}}</div></th>
                            </tr>
                        </table>
                    </div>
                    <div class="inner-div">
                        <p>Business Performance</p>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Total 6 Month Sale</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['business']['last_six_month_sales_information']['min_sell']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Total 6 Month Cost</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['business']['business_additional_information']['other_cost']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Total Fixed Asset</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['expenses']['total_asset_amount']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Number of Employee</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['business']['full_time_employee']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Value of Stock</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['business']['stock_price']}}</div></th>
                            </tr>
                        </table>
                    </div>
                </th>
                <th width="45%">
                    <div class="inner-div">
                        <p>Business Details</p>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;"> Business Name</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['business']['business_name']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Nature of Business</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['business']['smanager_business_type']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Legal Status</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['business']['ownership_type']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Business Type</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['business']['business_type']}}</div></th>
                            </tr>
                        </table>

                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Trade License</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['business']['trade_license']}}</div></th>
                            </tr>
                        </table>

                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">E-TIN number (company)</th>
                                <th style="width: 60%"><div class="form-control" > {{$final_information_for_loan['business']['tin_no']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Issue Date</th>
                                <th style="width: 60%"><div class="form-control" ></div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Date of Starting with Sheba</th>
                                <th style="width: 60%"><div class="form-control" > {{$final_information_for_loan['business']['establishment_year']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Number of online order serve</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['business']['online_order']}}</div></th>
                            </tr>
                        </table>
                    </div>
                    <div class="inner-div" style="margin-top: 5px">
                        <p>Bank Details</p>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Account Title</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['finance']['acc_name']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Account Number</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['finance']['acc_no']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Acc Type</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['finance']['acc_type']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Bank Name</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['finance']['bank_name']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Branch</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['finance']['branch_name']}}</div></th>
                            </tr>
                        </table>
                    </div>
                    <div class="inner-div" style="margin-top: 5px">
                        <p>Proposed Secuirity Details</p>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Secuirity</th>
                                <th style="width: 60%"><div class="form-control" >{{ $final_information_for_loan['business']['security_check']}}</div></th>
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
                        <p>Present Address</p>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Street</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['present_address']['street']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">PS/Upozilla</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['present_address']['thana']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">District</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['present_address']['zilla']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Postal Code</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['present_address']['post_code']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Country</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['present_address']['country']}}</div></th>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="50%">
                    <div class="inner-div">
                        <p>Permanent Address</p>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Street</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['permanent_address']['street']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">PS/Upozilla</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['permanent_address']['thana']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">District</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['permanent_address']['zilla']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Postal Code</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['permanent_address']['post_code']}}</div></th>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <th style="width: 35%;padding-top: 5px;;">Country</th>
                                <th style="width: 60%"><div class="form-control" >{{$final_information_for_loan['personal']['permanent_address']['country']}}</div></th>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top:40px">
        <p style="font-size: 14px;">Liability Status of the Company</p>
        <table>
            <tr>
                <td width="6%" style="padding-right: 5px">
                    <div style="text-align: center">
                        sl.
                    </div>

                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                </td>
                <td width="26%" style="padding-right: 5px">

                    <div style="text-align: center">
                        Bank Name
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>

                </td>
                <td width="17%" style="padding-right: 5px">

                    <div style="text-align: center">
                        Facility Type
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>

                </td>
                <td width="17%" style="padding-right: 5px">

                    <div style="text-align: center">
                        Limit
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>

                </td>
                <td width="17%" style="padding-right: 5px">

                    <div style="text-align: center">
                        Outstanding
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>

                </td >
                <td width="17%">

                    <div style="text-align: center">
                        Security
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>
                    <div class="empty_table ">
                        <div style="padding: 5px ;color: white">.</div>
                    </div>

                </td>
            </tr>
        </table>
    </div>
    <div class="bottom-letter" style="margin-top: 40px">
        I, hereby, declare that the information contained herein is correct. I/We shall also submit any additional accurate information/documents as and when
        required. You are hereby authorized to obtain and/or verify whatsoever information from any source regarding our credit worthiness.
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
                        <span >___________________________</span><br>
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
</div>

</body>
</html>
