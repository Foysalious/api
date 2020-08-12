<!DOCTYPE html>
<html lang="en">
<head>
    <title>proposal Letter form</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <style>
        .proposal-letter {
            font-size: 10px;
        }

        .bank-contact-info p {
            line-height: .3;
        }

        .cover-letter .header {
            margin-top: 10px;
            line-height: .3;

        }

        .cover-letter span {

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
        }

        th, td {
            text-align: left;
            padding: 0 3px;
        }

        ul {
            list-style: none;
            padding-left: 20px;
            margin-bottom: 0;
        }

        ul li {
            display: block;
            line-height: 1;
            position: relative;
        }
        ul li:before{
            content: '•';
            position: absolute;
            width: 20px;
            left: -20px;
            height: 100%;

        }
        .border-less-td {
            border-bottom: none;
            border-left: none;
            border-top: none;
        }

        .text-center {
            text-align: center;
        }

        @font-face {
            font-family: 'Rupali';
            src: url({{ storage_path('fonts\Siyamrupali.ttf') }}) format("truetype");
            font-weight: 400;
            font-style: normal;
        }

        body {
            font-family: 'Rupali', sans-serif !important;
        }
    </style>
</head>
<body>
<?php $today = \Carbon\Carbon::today()->format('d-M-y')?>
<?php $current_year = \Carbon\Carbon::today()->format('Y')?>

<div class="proposal-letter font-face">
    <div class="proposal-letter-table">
        <div style="text-align: right">Management Memo No. <span
                    style="padding-left: 50px">{{$final_information_for_loan['proposal_info']['management_memo_no']}}</span>
        </div>
        <table cellpadding="0" cellspacing="0" width="100%">
            <col width="15%">
            <col width="15%">
            <col width="13%">
            <col width="14%">
            <col width="14%">
            <col width="11%">
            <col width="18%">
            <tr>
                <td rowspan="2" width="16%">
                    <ul style="list-style: disc">
                        <li>APPROVED</li>
                        <li>CONDITIONALLY APPROVED</li>
                        <li>DEFERRED</li>
                        <li>DECLINED</li>
                    </ul>
                </td>
                <td style="text-align: center">APPROVER-1</td>
                <td style="text-align: center">APPROVER-2</td>
                <td style="text-align: center">APPROVER-3</td>
                <td style="text-align: center">APPROVER-4</td>
                <td colspan="2" style="text-align: center">APPROVER-5</td>
            </tr>
            <tr>
                <td style="height: 60px"></td>
                <td style="height: 60px"></td>
                <td style="height: 60px"></td>
                <td style="height: 60px"></td>
                <td colspan="2" style="height: 60px"></td>
            </tr>
            <tr>
                <td colspan="7" style="text-align: center">CREDIT PROPOSAL</td>
            </tr>
            <tr>
                <td>Strategic Partner</td>
                <td colspan="3">{{$final_information_for_loan['business']['strategic_partner']}}</td>
                <td>Date:</td>
                <td class="text-center" colspan="2">{{$today}}</td>
            </tr>
            <tr>
                <td colspan="7" style="text-align: center">Client Details</td>
            </tr>
            <tr>
                <td>Segment</td>
                <td colspan="2">MME Investment</td>
                <td colspan="2">Authority Level</td>
                <td class="text-center" colspan="2">MCC</td>
            </tr>
            <tr>
                <td>Company Name</td>
                <td colspan="2">{{$partner['name']}}</td>
                <td colspan="2">Establishment Date</td>
                <td class="text-center"
                    colspan="2">{{ date('d-M-y', strtotime($final_information_for_loan['business']['establishment_year'])) }}</td>
            </tr>
            <tr>
                <td>Key Sponsor</td>
                <td colspan="2">{{$final_information_for_loan['proposal_info']['key_sponsor_name']}}</td>
                <td colspan="2">CRG Score</td>
                <td class="text-center" colspan="2">N/A</td>
            </tr>
            <tr>
                <td>Trade License No.</td>
                <td colspan="2">{{$final_information_for_loan['business']['trade_license']}}</td>
                <td rowspan="3">Contact Details</td>
                <td>Business Address:</td>
                <td class="text-center bangla-font" colspan="2">{{$final_information_for_loan['business']['location']}}
                </td>
            </tr>
            <tr>
                <td>Legal Status</td>
                <td colspan="2" class="bangla-font">{{$ownership_type}} </td>
                <td>Residential Address:</td>
                <td class="text-center bangla-font" colspan="2">
                    {{$final_information_for_loan['personal']['present_address']['street']}},
                    {{$final_information_for_loan['personal']['present_address']['thana']}},
                    {{$final_information_for_loan['personal']['present_address']['zilla']}}-
                    {{$final_information_for_loan['personal']['present_address']['post_code']}}
                </td>
            </tr>
            <tr>
                <td>Industry & Business
                    Nature
                </td>
                <td colspan="2" class="bangla-font">{{$final_information_for_loan['business']['industry_and_business_nature']}}</td>
                <td>Contact No</td>
                <td class="text-center" colspan="2">{{$partner['profile']['mobile']}}</td>
            </tr>
            <tr>
                <td>Sector</td>
                <td colspan="2">{{$final_information_for_loan['business']['sector']}}</td>
                <td colspan="2">CIB Status</td>
                <td class="text-center" colspan="2">{{$final_information_for_loan['proposal_info']['cib_status']}}</td>
            </tr>
            <tr>
                <td>Human Resource</td>
                <td colspan="2">{{$final_information_for_loan['business']['full_time_employee']}}</td>
                <td colspan="2">Total Asset (excl. Land and Building)</td>
                <td class="text-center" colspan="2">
                    {{
                        $final_information_for_loan['business']['fixed_asset'] ? "BDT " . $final_information_for_loan['business']['fixed_asset'] : "BDT 0.00"
                    }}
                </td>
            </tr>
            <tr>
                <td>Business Category</td>
                <td colspan="2">{{$final_information_for_loan['business']['business_category']}}</td>
                <td colspan="2">Supplier Category: {{$final_information_for_loan['proposal_info']['supplier_category']}}</td>
                <td class="text-center" colspan="2">{{$partner['current_package']}}</td>
            </tr>
            <tr>
                <td colspan="7" style="text-align: center">Client Profile:</td>
            </tr>
            <tr>
                <td colspan="7">{{$final_information_for_loan['proposal_info']['client_profile']}}</td>
            </tr>
            <tr>
                <td colspan="7" style="text-align: center">Liability Position (Brac Bank As on 30-Nov-2019)</td>
            </tr>
            <tr>
                <td>Bank/FI</td>
                <td>Facility</td>
                <td>Limit</td>
                <td>Outstanding</td>
                <td>Overdue</td>
                <td colspan="2">Remarks</td>
            </tr>
            <tr>
                <td>Total</td>
                <td style="text-align: center">{{$final_information_for_loan['proposal_info']['loan_type']}}</td>
                <td style="text-align: center">BDT 0.60 M</td>
                <td style="text-align: center">BDT {{$final_information_for_loan['proposal_info']['outstanding']}}</td>
                <td style="text-align: center">BDT {{$final_information_for_loan['proposal_info']['overdue']}}</td>
                <td colspan="2"
                    style="text-align: center">{{$final_information_for_loan['proposal_info']['remarks']}}</td>
            </tr>
            <tr>
                <td colspan="7" style="text-align: center">Conditions and Purpose of the Proposed Facility</td>
            </tr>
            <tr>
                <td style="text-align: center">{{$final_information_for_loan['proposal_info']['loan_type']}}</td>
                <td style="text-align: center">BDT {{$loan_amount}}</td>
                <td style="text-align: center">{{$purpose}}</td>
                <td style="text-align: center">Interest Rate {{$interest_rate}}% p.a.</td>
                <td style="text-align: center">{{$duration}} Months</td>
                <td colspan="2">
                    <ul style="padding: 0 10px">
                        <li>{{$final_information_for_loan['business']['security_check']}}</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="text-align: center">Financial Highlights (Amount in BDT)</td>
                <td colspan="4" style="text-align: center">Debt-Burden Ratio Calculations</td>
            </tr>
            <tr>
                <td>Annual Turnover (Only Sheba. XYZ)</td>
                <td style="text-align: right"></td>
                <td>(as per SP Report)</td>
                <td>Monthly Operating Income</td>
                <td></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td>Total Annual Turnover</td>
                <td style="text-align: right"></td>
                <td>Overall</td>
                <td></td>
                <td style="text-align: right"></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td>Net Profit</td>
                <td style="text-align: right">{{ $final_information_for_loan['proposal_info']['net_profit'] }}</td>
                <td>{{ $final_information_for_loan['proposal_info']['profit_ratio'] }}%</td>
                <td>Total Existing Monthly
                    Debt Repayment
                </td>
                <td>{{ $final_information_for_loan['proposal_info']['existing_debt_repayment'] }} </td>
                <td colspan="2">(Previous Liability Exposure with
                    Brac Bank which has been setteled)
                </td>
            </tr>
            <tr>
                <td>Total Fixed Assets</td>
                <td style="text-align: right">{{$final_information_for_loan['business']['fixed_asset'] ? $final_information_for_loan['business']['fixed_asset'] : "0.00"}}</td>
                <td></td>
                <td>Proposed EMI</td>
                <td style="text-align: right">{{$monthly_installment}}</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td>Total Current Assets</td>
                <td style="text-align: right">{{ $final_information_for_loan['proposal_info']['total_current_asset'] }}</td>
                <td></td>
                <td>DBR%</td>
                <td style="text-align: right">{{ $final_information_for_loan['proposal_info']['dbr'] }}</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td>Total Equity</td>
                <td style="text-align: right">{{ $final_information_for_loan['proposal_info']['total_equity'] }}</td>
                <td></td>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td colspan="7" style="text-align: center">Banking Summary</td>
            </tr>
            <tr>
                <td class="text-center" width="15%">Account Name</td>
                <td class="text-center" width="15%">Bank Name & Acc Number</td>
                <td class="text-center" width="13%">Account Type</td>
                <td class="text-center" width="14%">Period</td>
                <td class="text-center" width="14%">Debit Sum (BDT)</td>
                <td class="text-center" width="11%">Credit Sum (BDT)</td>
                <td class="text-center" width="18%">Monthly Average Credit Sum (BDT)</td>
            </tr>
            <tr>
                <td class="text-center">{{ $final_information_for_loan['finance']['acc_name'] }}</td>
                <td class="text-center">{{ $final_information_for_loan['finance']['bank_name'] }} (A/C:
                    {{ $final_information_for_loan['finance']['acc_no'] }} )
                </td>
                <td class="text-center">{{ $final_information_for_loan['finance']['acc_type'] }}</td>
                <td class="text-center">{{ $final_information_for_loan['finance']['period'] }} Months</td>
                <td class="text-center">{{ $final_information_for_loan['finance']['debit_sum'] ?
$final_information_for_loan['finance']['debit_sum']:"0.00"}}</td>
                <td class="text-center">{{ $final_information_for_loan['finance']['credit_sum'] ?
$final_information_for_loan['finance']['credit_sum']:"0.00"}}</td>
                <td class="text-center">{{ $final_information_for_loan['finance']['monthly_avg_credit_sum'] ?
$final_information_for_loan['finance']['monthly_avg_credit_sum']:"0.00"}}</td>
            </tr>
            <tr>
                <td style="text-align: center" colspan="7">Personal Guarantor Details</td>
            </tr>
            <tr>
                <td class="text-center">Sl</td>
                <td class="text-center">Name</td>
                <td class="text-center">Age (Yrs)</td>
                <td class="text-center">Occupation</td>
                <td class="text-center">Net Worth</td>
                <td class="text-center">Relationship</td>
                <td class="text-center">Address</td>
            </tr>
            <tr>
                <td class="text-center">1</td>
                <td class="text-center">{{$partner['profile']['name']}}</td>
                <td class="text-center">{{calculateAge($final_information_for_loan['personal']['birthday'])}}</td>
                <td class="text-center">Business</td>
                <td class="text-center">BDT 00000</td>
                <td class="text-center">Self</td>
                <td class="text-center bangla-font">{{$final_information_for_loan['personal']['present_address']['street']}},
                    {{$final_information_for_loan['personal']['present_address']['thana']}},
                    {{$final_information_for_loan['personal']['present_address']['zilla']}}-
                    {{$final_information_for_loan['personal']['present_address']['post_code']}}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td class="text-center">{{$final_information_for_loan['nominee_granter']['grantor']['name']}}</td>
                <td class="text-center">{{calculateAge($final_information_for_loan['nominee_granter']['grantor']['dob'])}}
                </td>
                <td class="text-center">{{$final_information_for_loan['nominee_granter']['grantor']['occupation']}}</td>
                <td class="text-center">
                    BDT {{$final_information_for_loan['nominee_granter']['grantor']['net_worth']}}</td>
                <td class="text-center">{{$final_information_for_loan['nominee_granter']['grantor']['grantor_relation']}}</td>
                <td class="text-center">{{$final_information_for_loan['nominee_granter']['grantor']['address']}}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td class="text-center">{{$final_information_for_loan['nominee_granter']['nominee']['name']}}</td>
                <td class="text-center">{{calculateAge($final_information_for_loan['nominee_granter']['nominee']['dob'])}}</td>
                <td class="text-center">{{$final_information_for_loan['nominee_granter']['nominee']['occupation']}}</td>
                <td class="text-center">
                    BDT {{$final_information_for_loan['nominee_granter']['nominee']['net_worth']}}</td>
                <td class="text-center">{{$final_information_for_loan['nominee_granter']['nominee']['nominee_relation']}}</td>
                <td class="text-center">{{$final_information_for_loan['nominee_granter']['nominee']['address']}}</td>
            </tr>
            <tr>
                <td style="text-align: center" colspan="7">{{$final_information_for_loan['proposal_info']['performance_with_IPDC']}} Performance With IPDC</td>
            </tr>
            <tr>
                <td class="text-center">No. of Client</td>
                <td class="text-center">Approved Amount</td>
                <td class="text-center">Disbursement Amount</td>
                <td class="text-center">Overdue</td>
                <td colspan="3" class="text-center">Repayment Amount</td>
            </tr>
            <tr>
                <td>{{$final_information_for_loan['proposal_info']['no_of_client']}}</td>
                <td class="text-center">BDT {{$final_information_for_loan['proposal_info']['approved_amount']}}</td></td>
                <td class="text-center">BDT {{$final_information_for_loan['proposal_info']['disbursement_amount']}}</td>
                <td class="text-center">BDT {{$final_information_for_loan['proposal_info']['overdue']}}</td>
                <td colspan="3" class="text-center">BDT {{$final_information_for_loan['proposal_info']['repayment_amount']}}</td>
            </tr>
            <tr>
                <td colspan="2">Exposure limit: {{$final_information_for_loan['proposal_info']['exposure_limit']}}</td>
                <td colspan="2">Remarks: {{$final_information_for_loan['proposal_info']['remark']}}</td>
                <td class="text-center">Current</td>
                <td colspan="2" class="text-center">Proposed</td>
            </tr>
            <tr>
                <td colspan="3" rowspan="2">Single Borrower Exposure</td>
                <td>Amount</td>
                <td style="text-align: right"></td>
                <td colspan="2" style="text-align: right">
                    BDT {{$final_information_for_loan['finance']['disbursement_amount']}}</td>
            </tr>
            <tr>
                <td>% of IPDC equity</td>
                <td style="text-align: right"></td>
                <td colspan="2"
                    style="text-align: right">{{$final_information_for_loan['proposal_info']['ipdc_equity'].'%'}}</td>
            </tr>
            <tr>
                <td colspan="3" rowspan="2">Group Exposure</td>
                <td>Amount</td>
                <td style="text-align: right"></td>
                <td colspan="2" style="text-align: right"></td>
            </tr>
            <tr>
                <td>% of IPDC equity</td>
                <td style="text-align: right"></td>
                <td colspan="2" style="text-align: right"></td>
            </tr>
            <tr>
                <td colspan="3">Ind. Exp. (Service)</td>
                <td>Amount</td>
                <td style="text-align: right"></td>
                <td colspan="2" style="text-align: right"></td>
            </tr>
            <tr>
                <td colspan="3">Max 5% of Investment Portfolio</td>
                <td>% of IPDC Investment Portfolio</td>
                <td style="text-align: right"></td>
                <td colspan="2" style="text-align: right"></td>
            </tr>
        </table>

        <div style="page-break-before:always">&nbsp;</div>

        <div style="text-align: right">Management Memo No. <span
                    style="padding-left: 50px">{{$final_information_for_loan['proposal_info']['management_memo_no']}}</span>
        </div>

        <table width="100%">
            <tr>
                <td>FEATURES</td>
                <td colspan="3" class="text-center">PROPOSED TERMS</td>
            </tr>
            <tr>
                <td>Facility Type</td>
                <td colspan="3">{{$final_information_for_loan['proposal_info']['loan_type']}}</td>
            </tr>
            <tr>
                <td>Facility Amount</td>
                <td colspan="3">BDT {{$loan_amount}}</td>
            </tr>
            <tr>
                <td>Purpose</td>
                <td colspan="3">{{$purpose}}</td>
            </tr>
            <tr>
                <td>Tenure</td>
                <td colspan="3">{{$duration}} months</td>
            </tr>
            <tr>
                <td>Interest rate</td>
                <td>{{$interest_rate}}% p.a.</td>
                <td colspan="2" class="text-center">based on client profile and relevant risk parameters. IPDC will
                    have the discretion to re-fix the interest rate.
                </td>
            </tr>
            <tr>
                <td>Disbursement</td>
                <td colspan="2" class="text-center">Disbursement will be made in single or multiple tranches directly to
                    the
                </td>
                <td>{{$final_information_for_loan['proposal_info']['disbursement_to']}}</td>
            </tr>
            <tr>
                <td>Availability Period</td>
                <td>{{$final_information_for_loan['proposal_info']['availability']}}</td>
                <td colspan="2" class="text-center">months from the date of offer</td>
            </tr>
            <tr>
                <td>Repayment</td>
                <td>{{$duration}}</td>
                <td colspan="2" class="text-center">equal monthly installments. (IPDC reserves the right to review
                    and change this instalment amount during the loan period subject
                    to adverse change in money market)
                </td>
            </tr>
            <tr>
                <td>Payment Modality</td>
                <td colspan="3">Through assignment payment and submission of post-dated cheques for instalment payment
                    and
                    another cheque for full principal amount along with submission of memorandum of deposit of
                    cheques.
                </td>
            </tr>
            <tr>
                <td>Penal Charge</td>
                <td colspan="3">2.00% p.a. on and above the regular interest rate on all overdue amounts. IPDC will have
                    sole
                    discretion to change the rate of penal interest at any time during the loan tenure.
                </td>
            </tr>
            <tr>
                <td>Security</td>
                <td colspan="2">
                    <ul>
                        <li>Assignment of Payment from Sheba Platform Limited in favour of IPDC Finance Limited.</li>
                        <li>Other usual charge documents.</li>
                    </ul>
                </td>
                <td>
                    <ul>
                        <li>{{ $final_information_for_loan['business']['security_check'] }}</li>
                    </ul>
                </td>
            </tr>
        </table>

        <table class="margin-top-10" width="100%" style="font-size: 12px">
            <tr>
                <td>
                    <div style="margin: 20px 0">
                        Based on the analysis laid down in this paper, approval is sought for a Term Loan facility up to
                        a limit of <span style="color: red">BDT {{$loan_amount}}</span> for <span style="color: red">{{ $total_installment }}
                        months</span> in favor of <span style="color: red">{{$partner['name']}}</span>.
                    </div>
                    <div>
                        If approved, any disbursement of fund will be made subject to the following:
                        <ul>
                            <li>Completion of all legal & security documentation.</li>
                            <li>Availability of matching Fund.</li>
                        </ul>
                    </div>
                </td>
            </tr>
        </table>

        <table class="margin-top-10" width="100%">
            <tr>
                <td class="text-center">PREPARED BY</td>
                <td class="text-center">PROPOSED BY</td>
                <td class="text-center">RECOMMENDED BY</td>
                <td class="text-center">REVIEWED BY</td>
            </tr>
            <tr>
                <td class="text-center" style="padding: 5px 3px">Financial Analyst</td>
                <td class="text-center" style="padding: 5px 3px">Relationship Manager</td>
                <td class="text-center" style="padding: 5px 3px">Head of SME</td>
                <td class="text-center" style="padding: 5px 3px">In Charge of Corporate & SME - CRM</td>
            </tr>
            <tr>
                <td class="text-center" style="height: 80px"></td>
                <td class="text-center" style="height: 80px"></td>
                <td class="text-center" style="height: 80px"></td>
                <td class="text-center" style="height: 80px"></td>
            </tr>
        </table>

        <div class="margin-top-10" style="font-size: 12px">
            Annexure: <br>
            1) Financial Accounts <br>
            2) Reporting Information for Bangladesh Bank <br>
            3) SP information on Applicant <br>
        </div>
    </div>
</div>
</body>
</html>
