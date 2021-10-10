<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en" >
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>Loan Application Form</title>
    <link rel="stylesheet" href="{{resource_path('assets/css/dana_classic.css')}}">
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
                <td style="width: 30%;font-size: 46px;font-weight: 600;text-align: right;">
                    <p>{{ $groups }}</p>
                </td>
                <td style="width: 20%;text-align: right;">
                    <img width="100px" height="100px" src="{{ $final_information_for_loan['document']['picture'] }}"
                         alt="">
                </td>
            </tr>
        </table>
    </div>
    <div class="title">Dana Classic</div>
    <div class="cover-letter">
        Dear Sir/Madam<br>
        I request you to grant me/us the term loan facility of BDT <span>{{ $loan_amount }}</span> {{--for a tenure
        of <span>{{ $total_installment }}</span> months --}} <span>{{ $purpose }}</span> purpose.To enable you to
        consider the proposal, my following information are given for your kind consideration.
    </div>
    <div class="static-node">Note : This applicant has already agreed for the terms and condition (Including providing
                             authorization robi to share info, CIB report)
    </div>
    @include('reports.pdfs.partials._application_data')
    <div style="page-break-before:always">&nbsp;</div>

    <div>
        <h4 class="heading">For official use only</h4>
        <p style="font-size: 12px;">Last 12 months sale (‘000 TK)</p>
        @include('reports.pdfs.partials._last_12_month')
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
        @include('reports.pdfs.partials._month')
    </div>

    <div>
        <div style="font-size: 14px;">End Month</div>
        @include('reports.pdfs.partials._month')
    </div>


    <div class="rules" style="margin-top: 20px">
        @include('reports.pdfs.partials._rules')
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

