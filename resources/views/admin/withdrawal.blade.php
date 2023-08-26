@extends('layouts.admin.app')
@section('content')
    <div class="content">

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark text-center">Sellers Withdrawal Requests</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">

                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table text-center table-hover  border-bottom">
                                    <thead>
                                    <tr class="bg-primary text-white">
                                        <th scope="col">#</th>
                                        <th scope="col">Seller</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Transaction ID</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Operation</th>
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
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{$transaction->user->name}}</td>
                                            <td>{{$transaction->amount}}</td>
                                            <td>{{$transaction->status}}</td>
                                            <td>{{$transaction->transaction_id}}</td>
                                            <td>{{$transaction->created_at}}</td>
                                            <td>
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#bankModal{{$transaction->id}}" class="btn btn-xs btn-warning">View Bank Detail</a>
                                                <a href="#" data-bs-toggle="modal" data-bs-target="#transactionModal{{$transaction->id}}"  class="btn btn-primary btn-xs">Update Transaction ID</a>
                                            </td>
                                        </tr>
                                            <!-- Modal -->
                                            <div class="modal fade" id="bankModal{{$transaction->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
{{--                                                        <form action="">--}}
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalLabel">User Bank Detail</h5>
                                                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                @foreach(json_decode($transaction->bank_detail) as $key=>$bank)
<h1>Bank {{$key}}</h1>
                                                                    <div class="row text-left">
                                                                        @foreach($bank as $key1=>$b)
                                                                        <div class="col-md-6 font-weight-bold text-capitalize">
                                                                            {{str_replace('_',' ',$key1)}}
                                                                        </div>
                                                                        <div class="col-md-6">
{{$b}}
                                                                        </div>
                                                                        @endforeach
                                                                    </div>
                                                                    <hr>
                                                                @endforeach

                                                            </div>
{{--                                                        </form>--}}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="transactionModal{{$transaction->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <form action="{{route('withdraw_request')}}" method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalLabel">Update</h5>
                                                                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">
                                                                                Transaction ID
                                                                            </label>
                                                                            <input type="text" value="{{$transaction->transaction_id}}" name="t_id" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                    {{csrf_field()}}
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">
                                                                                Status
                                                                            </label>
                                                                            <select name="status" id=""
                                                                                    class="form-control">
                                                                                <option @if($transaction->status=='Pending') SELECTED @endif value="Pending">Pending</option>
                                                                                <option @if($transaction->status=='Cancelled') SELECTED @endif  value="Cancelled">Cancelled</option>
                                                                                <option @if($transaction->status=='Completed') SELECTED @endif  value="Completed">Completed</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <input type="hidden" name="id" value="{{$transaction->id}}">
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
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
