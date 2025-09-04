@extends('layouts.admin.app')
@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <h4 class="py-4 my-1 text-site-primary">Settings</h4>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <div class="container-fluid content">
        <div class="row">
            <div class="offset-md-2 col-md-8 pl-4 pr-4 pb-4">
                <h4 class="text-center text-site-primary">Update Password</h4>
                <div class="card">
                    <div class="card-body-custom">
                        <div class=" d-block text-right">
                            <div class="card-text">
                                <div class="row">
                                    <div class="col-md-12">
                                        <form action="{{ route('password_update') }}" method="POST">
                                            {{ csrf_field() }}
                                            <div class="row form-inline">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="password" class="form-control" name="old_password"
                                                            placeholder="Old Password" required id="old_password"
                                                            minlength="8">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="password" class="form-control" name="new_password"
                                                            placeholder="New Password" required id="new_password"
                                                            minlength="8">
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <button type="submit" class="btn site-primary-yellow-bg rounded-pill mt-3 col-12">
                                                    Update
                                                </button>
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
    <!-- Main content -->
    <form method="post" action="{{ route('admin.update.pages') }}" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="content">
            <div class="container-fluid">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-12">
                                <h4 class="text-center text-site-primary">Help</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card  pl-4 pr-4 pb-4 p-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <textarea
                                                    style="border:0;margin-top: 0px;margin-bottom: 0px;height: 227px;min-height: 279px;max-height: 227px;width: 100%;min-width: 100%;max-width: 100%;background: #f4f6f9;border-radius: 15px;"
                                                    placeholder="Help" name="help" class="form-control" onresize="return 0;">{{ $help_page->page_content }}
                                            </textarea>
                                                <button type="submit"
                                                    class="btn site-primary-yellow-bg rounded-pill mt-3 col-12">
                                                    Update
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-12">
                                <h4 class="text-center text-site-primary">Terms & Conditions</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card  pl-4 pr-4 pb-4 p-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <textarea
                                            style=" border:0;margin-top: 0px;margin-bottom: 0px;height: 127px;min-height: 279px;max-height: 127px;width: 100%;min-width: 100%;max-width: 100%;background: #f4f6f9;border-radius: 15px;"
                                            placeholder="Write your terms & conditions here..." name="tos" class="form-control" onresize="return 0;">
                                            {{ $terms_page->page_content }}
                                        </textarea>
                                        <button type="submit" class="btn site-primary-yellow-bg rounded-pill mt-3 col-12">
                                            Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-12">
                                <h2 class="text-center text-site-primary">FAQ</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card  pl-4 pr-4 pb-4 p-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <textarea
                                            style=" border:0;margin-top: 0px;margin-bottom: 0px;height: 127px;min-height: 279px;max-height: 127px;width: 100%;min-width: 100%;max-width: 100%;background: #f4f6f9;border-radius: 15px;"
                                            placeholder="Please write 'Frequently Asked Questions' here..." name="faq" class="form-control"
                                            onresize="return 0;">{{ $faq_page->page_content }}</textarea>
                                        <button type="submit" class="btn site-primary-yellow-bg rounded-pill mt-3 col-12">
                                            Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </form>
@endsection
