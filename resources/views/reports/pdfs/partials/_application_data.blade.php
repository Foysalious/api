<div style="margin-top: 10px;display: block;width: 100%">
    <table>
        <tr>
            <th width="45%">
                <div class="inner-div">
                    <h4 class="heading">Retailer Details</h4>
                    <table class="details-table">
                        <tr width="100%">
                            <th width="40%" style="width: 35%;padding-top: 5px;">Name</th>
                            <th width="60%" style="width: 60%;position: relative;">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div>{{$final_information_for_loan['personal']['name']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;">Phone number</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div>{{$partner['profile']['mobile']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Date of Birth</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div>{{$final_information_for_loan['personal']['birthday']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Fathers Name</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div>{{$final_information_for_loan['personal']['father_name']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Mothers Name</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div>{{$final_information_for_loan['personal']['mother_name']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">NID no</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div>{{$final_information_for_loan['personal']['nid_no']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                    </table>
                </div>
                <div class="inner-div">
                    <h4 class="heading">Bank Details</h4>
                    <table>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Account holder name</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div>{{$final_information_for_loan['finance']['acc_name']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Account Number</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div>{{$final_information_for_loan['finance']['acc_no']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Bank Name</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div>{{$final_information_for_loan['finance']['bank_name']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Branch</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div>{{$final_information_for_loan['finance']['branch_name']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                    </table>
                </div>
            </th>
            <th width="45%">
                <div class="inner-div">
                    <h4 class="heading">Business Details</h4>
                    <table>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Trade license Number</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div>{{$final_information_for_loan['business']['trade_license']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                    </table>

                    <table>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Trade license Issue date</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div>{{$final_information_for_loan['business']['trade_license_issue_date']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Date of trade license registration</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div> {{$final_information_for_loan['business']['trade_license_issue_date']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">bKash number</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div>{{$final_information_for_loan['finance']['bkash']['bkash_no']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Is retailer bKash agent?</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div >{{$final_information_for_loan['finance']['bkash']['bkash_account_type'] == "agent"?"Yes":"No"}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                    </table>
                </div>
                <div class="inner-div" style="margin-top: 5px">
                    <h4 class="heading">Business address</h4>
                    <table>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Street no/village name</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td>
                                            <div >{{$final_information_for_loan['business']['business_additional_information']['address']['street']}}</div>
                                        </td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Post Code</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td><div >{{$final_information_for_loan['business']['business_additional_information']['address']['post_code']}}</div></td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Thana/Upzilla</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td> <div >{{$final_information_for_loan['business']['business_additional_information']['address']['thana']}}</div></td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Zilla</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td><div >{{$final_information_for_loan['business']['business_additional_information']['address']['zilla']}}</div></td>
                                    </tr>
                                </table>
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
                    <h4 class="heading">Permanent Address</h4>
                    <table>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Street</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td> <div >{{$final_information_for_loan['personal']['permanent_address']['street']}}</div></td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">PS/Upozilla</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td><div >{{$final_information_for_loan['personal']['permanent_address']['thana']}}</div></td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">District</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td><div >{{$final_information_for_loan['personal']['permanent_address']['zilla']}}</div></td>
                                    </tr>
                                </table>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 35%;padding-top: 5px;;">Postal Code</th>
                            <th style="width: 60%">
                                <table class="form-control-l">
                                    <tr>
                                        <td><div >{{$final_information_for_loan['personal']['permanent_address']['post_code']}}</div></td>
                                    </tr>
                                </table>
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
