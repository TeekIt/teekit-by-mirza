@extends('layouts.shopkeeper.app')
@section('content')
<div class="content">

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 text-dark text-center">Payment Settings</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">

                <div class="offset-md-2 col-md-8 pl-4 pr-4 pb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class=" d-block text-right">
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h4 class="text-center text-primary">Bank Details</h4>
                                        </div>
                                        <div class="col-md-12">
                                            <form action="{{route('payment_settings_update')}}" method="POST" enctype="multipart/form-data">
                                                {{csrf_field()}}
                                                <?php
                                                $ps = json_decode($payment_settings, true);
                                                if (!isset($ps['one'])) {
                                                    $ps['one']['bank_name'] = "";
                                                    $ps['one']['branch'] = "";
                                                    $ps['one']['account_number'] = "";
                                                }
                                                if (!isset($ps['two'])) {
                                                    $ps['two']['bank_name'] = "";
                                                    $ps['two']['branch'] = "";
                                                    $ps['two']['account_number'] = "";
                                                }
                                                ?>
                                                <div class="bank-info">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <select class="form-control" name="bank[one][bank_name]" required>
                                                                    <option value="">Bank Name</option>
                                                                    <?php
                                                                    $myarr = [
                                                                        'Al Rayan Bank Plc.',
                                                                        'Aldermore Bank Plc.',
                                                                        'Alpha Bank London Limited.',
                                                                        'Atom Bank Plc.',
                                                                        'Axis Bank UK Limited.',
                                                                        'Bank and Clients Plc.',
                                                                        'Bank of Ireland (UK) Plc.',
                                                                        'Bank of Scotland.',
                                                                        'Barclays Bank UK Plc.',
                                                                        'CAF Bank Ltd.',
                                                                        'Canara Bank.',
                                                                        'Chetwood Financial Limited.',
                                                                        'Guaranty Trust Bank (UK) Limited.',
                                                                        'Habib Bank Ltd.',
                                                                        'HBL BANK UK LIMITED.',
                                                                        'HSBC UK Bank Plc.',
                                                                        'ICICI Bank UK Ltd.',
                                                                        'IKANO BANK AB (PUBL).',
                                                                        'Lloyds Bank Plc.',
                                                                        'Marks & Spencer Financial Services Ltd.',
                                                                        'Metro Bank Plc.',
                                                                        'Monument Bank Limited.',
                                                                        'Monzo Bank Ltd.',
                                                                        'NatWest Markets NV.',
                                                                        'NATWEST MARKETS PLC.',
                                                                        'Paragon Bank Plc.',
                                                                        'Punjab National Bank (International) Limited.',
                                                                        'Sainsbury\'s Bank plc.',
                                                                        'Santander Financial Services plc.',
                                                                        'Santander UK.',
                                                                        'Secure Trust Bank plc.',
                                                                        'Shawbrook Bank Limited.',
                                                                        'Starling Bank Limited.',
                                                                        'Tesco Personal Finance PLC.',
                                                                        'The Co-operative Bank plc.',
                                                                        'TSB Bank Plc.',
                                                                        'Turkish Bank (UK) Ltd.',
                                                                        'Turkiye Is Bankasi AS.',
                                                                        'United National Bank Limited.',
                                                                        'United Trust Bank Limited.',
                                                                        'Virgin Money Plc.',
                                                                        'Wesleyan Bank Limited.',
                                                                        'Wyelands Bank Plc.'
                                                                    ];
                                                                    ?>
                                                                    @foreach($myarr as $m)
                                                                    <option @if($m==$ps['one']['bank_name']) selected @endif value="{{$m}}">{{$m}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <input type="text" required class="form-control" name="bank[one][account_number]" placeholder="Account Number" value="{{$ps['one']['account_number']}}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <input type="text" required class="form-control" name="bank[one][branch]" placeholder="Branch" value="{{$ps['one']['branch']}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <select class="form-control" name="bank[two][bank_name]">
                                                                    <option value="">Bank Name</option>
                                                                    @foreach($myarr as $m)
                                                                    <option @if($m==$ps['two']['bank_name']) selected @endif value="{{$m}}">{{$m}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" name="bank[two][account_number]" placeholder="Account Number" value="{{$ps['two']['account_number']}}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <input type="text" class="form-control" name="bank[two][branch]" placeholder="Branch" value="{{$ps['two']['branch']}}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 offset-md-3 text-center mt-3">
                                                            <button style="background: #ffcf42;color:black;font-weight: 600" class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill" type="submit">{{__('Update')}}</button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </form>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /.row -->

        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<style>
    .card-body {
        padding: 30px 50px !important;
    }
</style>
@endsection