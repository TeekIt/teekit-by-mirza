@extends('layouts.shopkeeper.app')
@section('content')
    <div class="content">

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark text-center">Withdrawal Requests</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">

                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>£{{auth()->user()->total_withdraw}}</h3>

                                <p>Total Withdrawals </p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-4">

                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>£{{auth()->user()->pending_withdraw*0.9}}</h3>

                                <p>Balance</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-4">

                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>£{{auth()->user()->pending_withdraw*0.1}}</h3>

                                <p>Teekit Charges</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="row">
<div class="col-md-12">
    @if(auth()->user()->pending_withdraw>0)
    <a href="#" class="float-right btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Request Withdrawal </a>
  @endif
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{route('withdraw_request')}}" method="post">
{{csrf_field()}}
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Request Withdraw</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">
                            Amount to Withdraw
                        </label>

                        <input type="number" step="0.1" name="amount" class="form-control" max="{{auth()->user()->pending_withdraw*0.9}}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>

                </form>
            </div>
        </div>
    </div>
</div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table text-center table-hover  border-bottom">
                                    <thead>
                                    <tr class="bg-primary text-white">
                                        <th scope="col">#</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Transaction ID</th>
                                        <th scope="col">Date</th>
                                    </tr>
                                    </thead>
                                    <?php
                                    $total = 0;
                                    ?>
                                    @foreach($transactions as $transaction)
                                        <?php
                                        $total = 1;
                                        ?>
                                        <tr>
                                            <td>{{$transaction->id}}</td>
                                            <td>{{$transaction->amount}}</td>
                                            <td>{{$transaction->status}}</td>
                                            <td>{{$transaction->transaction_id}}</td>
                                            <td>{{$transaction->created_at}}</td>
                                        </tr>
                                    @endforeach
                                    @if($total==0)
                                        <tr class="bg-secondary-light">
                                            <td colspan="5" >There is No data</td>
                                        </tr>
                                    @endif
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>

@endsection
