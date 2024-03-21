<div>
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            {{ session()->get('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong>
            {{ session()->get('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    {{-- ************************************ Request Withdrawal Model ************************************ --}}
    <div class="modal fade" id="requestWithdrawModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="withdrawRequest" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Request Withdrawal</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="number" class="form-control" placeholder="Enter amount" wire:model.defer="amount" max="{{ auth()->user()->pending_withdraw }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Request Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="content-header">
            <div class="container pt-4">
                <form wire:submit.prevent="render">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-8 col-sm-12 col-md-8">
                                    <div class="row">
                                        <div class="col-12 col-sm-4">
                                            <input type="number" wire:model.defer="amount" class="form-control mb-2" placeholder="Search by Amount">
                                        </div>
                                        <div class="col-12 col-sm-4">
                                            <select wire:model.defer="search" class="form-control mb-2">
                                                <option>Select Status</option>
                                                <option value="Completed">Completed</option>
                                                <option value="Cancelled">Cancelled</option>
                                                <option value="Pending">Pending</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-sm-4">
                                            <input type="date" wire:model.defer="created_at" class="form-control mb-2">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-12 col-md-4 d-flex flex-sm-row">
                                    <button type="submit" class="btn btn-site-primary p-1 w-100 mx-1" wire:target="search" wire:loading.class="btn-dark" wire:loading.class.remove="btn-site-primary" wire:loading.attr="disabled" title="Search">
                                        <span class="fas fa-search" wire:target="search" wire:loading.remove=""></span>
                                        <span wire:target="search" wire:loading="">
                                            <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                        </span>
                                    </button>
                                    <button type="button" class="btn btn-primary p-1 w-100 mx-1" wire:click="resetThisPage" wire:target="resetThisPage" wire:loading.class="btn-dark" wire:loading.class.remove="btn-primary" wire:loading.attr="disabled" title="Reset Withdrawal Requests">
                                        <span class="fas fa-sync" wire:target="resetThisPage" wire:loading.remove=""></span>
                                        <span wire:target="resetThisPage" wire:loading="">
                                            <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                                        </span>
                                    </button>
                                    <button type="button" class="btn btn-info p-1 w-100 mx-1 text-white" data-bs-toggle="modal" data-bs-target="#requestWithdrawModal" title="Withdrawal">
                                        <i class="fas fa-hand-holding-usd"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-lg-12 col-sm-12 col-md-12">
                        <h4 class="py-4 my-1">Withdrawal Requests</h4>
                    </div>
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>£{{ auth()->user()->total_withdraw }}</h3>
                                <p>Total Withdrawals </p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-pie-graph"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>£{{ auth()->user()->pending_withdraw * 0.9 }}</h3>
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
                                <h3>£{{ auth()->user()->pending_withdraw * 0.1 }}</h3>
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
                        <table class="table table-hover table-responsive-sm border-bottom">
                            <thead>
                                <tr class="bg-primary text-white">
                                    <th scope="col">#</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Transaction ID</th>
                                    <th scope="col">Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $single_index)
                                    <tr>
                                        <td>{{ $single_index->id }}</td>
                                        <td>{{ $single_index->amount }}</td>
                                        @if ($single_index->status == 'Completed')
                                            <td><span class="bg-success py-1 px-3 rounded-3 text-white text-bold">{{ $single_index->status }}</span></td>
                                        @elseif($single_index->status == 'Pending')
                                            <td><span class="bg-warning py-1 px-3 rounded-3 text-white text-bold">{{ $single_index->status }}</span></td>
                                        @elseif($single_index->status == 'Cancelled')
                                            <td><span class="bg-danger py-1 px-3 rounded-3 text-white text-bold">{{ $single_index->status }}</span></td>
                                        @endif
                                        <td>{{ $single_index->transaction_id }}</td>
                                        <td>{{ $single_index->created_at }}</td>
                                    </tr>
                                @empty
                                    <td colspan="5">
                                        No Records Found
                                    </td>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-12">
                                {{ $data->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
</div>
