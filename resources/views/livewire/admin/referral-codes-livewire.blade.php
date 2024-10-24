<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Content Header (Page header) -->

    <x-session-messages />

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-lg-12 col-sm-12 col-md-12">
                    <h4 class="py-4 my-1">Referrals</h4>
                </div>
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
                                    @foreach ($data as $single_index)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $single_index->name . ' ' . $single_index->l_name }}</td>
                                            <td>{{ $single_index->email }}</td>
                                            <td>{{ $single_index->referral_code }}</td>
                                            <td>{{ $single_index->created_at }}</td>
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
    <!-- /.content -->
</div>