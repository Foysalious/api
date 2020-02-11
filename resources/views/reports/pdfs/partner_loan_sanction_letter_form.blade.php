<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sanction Letter form</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <style >
        .body{
            font-family:  Siyamrupali,sans-serif!important;
        }
        .sanction-letter{
            font-size: 12px;
        }
        .bank-contact-info{
            margin-top: 10px;
        }
        .bank-contact-info p{
            line-height: .3;
        }

        .cover-letter .header {
            margin-top: 10px;
            line-height: .3;

        }
        .cover-letter span{

        }
        .margin-top-10 {
            margin-top: 10px;
        }
        .margin-top-30 {
            margin-top: 30px;
        }
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            padding: 5px;
            text-align: left;
        }
    </style>
</head>
<body>
<?php $today = \Carbon\Carbon::today()->format('d-m-y')?>
<?php $current_year = \Carbon\Carbon::today()->format('Y')?>

<div class="sanction-letter">
    <div style="padding: 5px">
        <div>
            <p>{{$bank['name']}}/INPE/{{$current_year}}/{{$final_information_for_loan['sanction_letter_info']['reference_no']}}</p>
            <p>Date: <span>January 6, 2020</span></p>
        </div>
        <div class="bank-contact-info">
            <p>To:</p>
            <p>{{$partner['profile']['name']}}</p>
            <p>Proprietor</p>
            <p>{{$partner['name']}}</p>
            <p> {{$final_information_for_loan['personal']['present_address']['street']}},
                {{$final_information_for_loan['personal']['present_address']['thana']}}</p>
            <p>{{$final_information_for_loan['personal']['present_address']['zilla']}}-
                {{$final_information_for_loan['personal']['present_address']['post_code']}}.</p>
        </div>
        <div class="cover-letter">
            <div class="header">
                <p>Dear Sir,</p>
                <p>Subject: Offer Letter for {{$final_information_for_loan['proposal_info']['loan_type']}} Facility.</p>
            </div>
            <p>All conditions of the {{$final_information_for_loan['proposal_info']['loan_type']}} Facility will be provided in formal Loan Agreement. The most
                important conditions of the Agreement will be:</p>
        </div>
    </div>
    <div class="sanction-letter-table">
        <table>
            <tr>
                <td style="width:20%">Facility Type</td>
                <td style="width:20%">{{$final_information_for_loan['proposal_info']['loan_type']}}</td>
                <td style="width:35%">Amount: BDT {{$loan_amount}}</td>
                <td style="width:25%">Term: {{$duration}} months</td>
            </tr>
            <tr>
                <td style="width:20%">Purpose</td>
                <td style="width:80%" colspan="3">To meet additional fund requirement for {{$purpose}}.</td>
            </tr>
            <tr>
                <td style="width:40%">Interest Rate</td>
                <td style="width:60%" colspan="3">{{$interest_rate}}% p.a. on outstanding principal balance. However, IPDC will have
                    sole discretion to re-fix the interest rate based on market condition.</td>
            </tr>
            <tr>
                <td style="width:40%">Availability Period</td>
                <td style="width:60%" colspan="3">{{$final_information_for_loan['proposal_info']['availability']}} months from the date of this letter.</td>
            </tr>
            <tr>
                <td style="width:40%">Repayment</td>
                <td style="width:60%" colspan="3">{{$final_information_for_loan['proposal_info']['existing_debt_repayment']}} equal monthly installments/ structured payment.</td>
            </tr>
            <tr>
                <td style="width:40%">Penal Interest</td>
                <td style="width:60%" colspan="3">{{$final_information_for_loan['sanction_letter_info']['panel_interest']}}% p.a. on and above the regular interest rate on all overdue
                    amounts.</td>
            </tr>
            <tr>
                <td style="width:40%">Prepayment</td>
                <td style="width:60%" colspan="3">In case of prepayment, an unwinding interest will be applicable @ 2.00%
                    on the prepaid principal amount.</td>
            </tr>
            <tr>
                <td style="width:40%">Documentation & Processing Fee</td>
                <td style="width:60%" colspan="3">BDT {{$final_information_for_loan['sanction_letter_info']['documentation_and_processing_fee']}} only plus 15% VAT.</td>
            </tr>
            <tr>
                <td style="width:40%">Security</td>
                <td style="width:60%;padding: 5px" colspan="3">
                    <p style="padding:0">The security package will include but is not limited to:</p>
                    <div>
                        <ul>
                            <li>Assignment of payment from Sheba Platform Limited in favor of IPDC.</li>
                            <li>Personal guarantee-
                                <ul>
                                    <li>
                                        {{$final_information_for_loan['nominee_granter']['grantor']['name']}}
                                        ({{$final_information_for_loan['nominee_granter']['grantor']['grantor_relation']}} of the proprietor)
                                    </li>
                                    <li>
                                        {{$final_information_for_loan['nominee_granter']['nominee']['name']}} ({{$final_information_for_loan['nominee_granter']['nominee']['nominee_relation']}} of the proprietor)
                                    </li>
                                </ul>
                            </li>
                            <li>Other usual charge documents.</li>
                        </ul>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width:40%">Tax, Commissions and other charges</td>
                <td style="width:60%" colspan="3">All payments falling due to IPDC to be made without deduction of any
                    tax, commissions and other charges.</td>
            </tr>
            <tr>
                <td style="width:40%">Excise Duty</td>
                <td style="width:60%" colspan="3">Every year, in December, Excise Duty will be charged to loan account
                    as per NBR circular. The client to be provide account payee cheque
                    favoring IPDC Finance Limited for equivalent amount of the excise
                    Duty.</td>
            </tr>
            <tr>
                <td style="width:40%">Disbursement</td>
                <td style="width:60%" colspan="3">Subject to completion of documentation to the satisfaction of IPDC,
                    fund will be disbursed in single or multiple tranches directly to client/
                    proprietor.</td>
            </tr>
            <tr>
                <td style="width:40%">Restrictions</td>
                <td style="width:60%" colspan="3">Except with prior written consent of IPDC you will not sell, lease,
                    transfer, let-out for hire, sub-let alienate, or otherwise dispose of any
                    of your assets including assets of INP Engineering.</td>
            </tr>
            <tr>
                <td style="width:40%">Payment Modality</td>
                <td style="width:60%" colspan="3">Through submission of {{$final_information_for_loan['sanction_letter_info']['no_of_checks_submitted']}} no. of postdated cheques (PDCs) for
                    installment payments and another cheque covering full principal
                    amount along with submission of Memorandum of Deposit of Cheques.</td>
            </tr>
            <tr>
                <td style="width:40%">Other Clauses</td>
                <td style="width:60%" colspan="3">
                    <ol>
                        <li>Your and guarantors’ latest personal net worth statements with
                            details of assets and liabilities duly signed by you and the
                            respective guarantors will be provided to IPDC before disbursement
                            of fund.</li>
                        <li>All registration fees, Government charges, documentation fees,
                            taxes and other related expenses as and when required in
                            connection with the loan facility will be on your account.</li>
                        <li>Disbursement is subject to completion of all documentation to the
                            satisfaction of IPDC including obtaining personal guarantees,
                            postdated cheques for installment payment and one cheque for full
                            principal amount.</li>
                        <li>If the project is not eligible for Bangladesh Bank Refinance Scheme
                            and accordingly does not approved by Bangladesh Bank at any point
                            of time during the loan period, the interest rate of the facility will
                            be revised to 15.00% p. a. from the date of disbursement.</li>
                        <li>IPDC will have sole discretion to change the rate of interest based
                            on market condition. In case of any change of interest rate during
                            the tenure of the facility, for repayment convenience, such rate
                            change may be affected in any of the following methods:
                            <ul>
                                <li>Changing the size of all the remaining installments;</li>
                                <li>Increasing the number of installments while keeping installment
                                    size unchanged; and</li>
                                <li>Changing only the last installment amount to account for the
                                    impact of interest rate change.</li>
                            </ul>
                        </li>
                        <li>Disbursement is subject to availability of IPDC’s liquidity with
                            matching maturity.</li>
                    </ol>
                </td>
            </tr>

        </table>
    </div>
    <div style="padding: 5px">
        <div class="margin-top-10">
            The effectiveness of this Letter of Offer is subject to the fulfillment of all the terms and conditions
            stated herein to the satisfaction of IPDC.
        </div>
        <div class="margin-top-10">
            Please sign and return the enclosed duplicate of this letter along with a cheque of BDT
            {{$final_information_for_loan['sanction_letter_info']['documentation_and_processing_fee']}} as
            indication of your acceptance of the terms and conditions contained herein. Please also Note that,
            by way of your acceptance to this letter of offer, this will be treated as final “Sanction Letter” to
            you and our offer shall lapse should this not be accepted within 15 (fifteen) days of the date hereof.
        </div>
        <div class="margin-top-10">
            Yours sincerely,
        </div>
        <div class="margin-top-30" >
            <table  style="width: 100%;border: none !important;" >
                <tr style="border: none !important; padding: 0">
                    <td style="border: none !important; padding: 0">
                        <div>
                            <span>_____________________________</span> <br>
                            Md. Ashique Hossain <br>
                            DGM & Head of Credit Risk Management
                        </div>
                    </td>
                    <td style="border: none !important; padding: 0">
                        <div style="margin-left: 30px">
                            <span>_____________________________</span> <br>
                            Rizwan Dawood Shams <br>
                            DMD & Head of Business Finance
                        </div>

                    </td>
                </tr>
            </table>

        </div>
        <div class="margin-top-10">
            Accepted by
        </div>
        <div class="margin-top-30">
            <table style="width: 100%;border: none !important;">
                <tr style="border: none !important;padding: 0">
                    <td style="border: none !important;padding: 0">
                        <div>
                            <span>_____________________________</span> <br>
                            {{$partner['profile']['name']}} <br>
                            Proprietor <br>
                            {{$bank['name']}}
                        </div>
                    </td>
                </tr>
            </table>

        </div>
        <div class="margin-top-30">
            Date:
        </div>
    </div>
</div>
</div>

</body>
</html>
