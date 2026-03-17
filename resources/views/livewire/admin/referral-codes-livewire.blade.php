<div class="container-xxl flex-grow-1 container-p-y">

    <x-session-messages />

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-lg-12 col-sm-12 col-md-12">
                    <h4 class="py-4 my-1 text-site-primary">Referrals</h4>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table  table-hover table-responsive-sm border-bottom">
                                <thead>
                                    <tr class="bg-primary text-white">
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Referral Code</th>
                                        <th scope="col">Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $singleIndex)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $singleIndex->name . ' ' . $singleIndex->l_name }}</td>
                                            <td>{{ $singleIndex->email }}</td>
                                            <td>{{ $singleIndex->referral_code }}</td>
                                            <td>{{ $singleIndex->created_at }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-md-12">
                                    {{ $data->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </div>
    
</div>