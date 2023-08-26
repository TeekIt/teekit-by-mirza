@extends('layouts.shopkeeper.app')
@section('content')
    <div class="content">

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1 class="m-0 text-dark text-center">Inventory</h1>

                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <?php
                     ?>
                    <div class="offset-md-3 col-md-6 pl-4 pr-4 pb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class=" d-block text-right">
                                <div class="card-text">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h4 class="text-center text-primary">Edit Product</h4>
                                        </div>
                                       <div class="col-md-12">
                                           <form action="{{route('update_inventory',$inventory->id)}}" method="POST" enctype="multipart/form-data">
                                               {{csrf_field()}}
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="product_name" placeholder="Title*" required id="" value="{{$inventory->product_name}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="sku" placeholder="SKU*" required id="" value="{{$inventory->sku}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <select class="form-control"  required name="category_id" id="">

                                                            <option value="">Category*</option>
                                                            @foreach($categories as $cat)

                                                                <option @if($cat->id==$inventory->category_id)
                                                                    selected
                                                                        @endif
                                                                        value="{{$cat->id}}">{{$cat->category_name}}</option>
                                                                @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control" name="qty" placeholder="Stock*" required id="" value="{{$inventory->qty}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="number" step="0.01" class="form-control" name="price" placeholder="Price*" required id="" value="{{$inventory->price}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="number" step="0.01" class="form-control" name="discount_percentage" placeholder="Discounted Price*" required id="" value="{{$inventory->discount_percentage}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="dimension" placeholder="Dimension" id="" value="{{$inventory->dimension}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="weight" placeholder="Weight" id="" value="{{$inventory->weight}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="brand" placeholder="Brand" id="" value="{{$inventory->brand}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="size" placeholder="Size" id="" value="{{$inventory->size}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <select class="form-control"  required name="status" id="">
                                                            <option value="">Status</option>
                                                            <option  <option @if($inventory->status==1)
                                                                             selected
                                                                             @endif value="1">Published</option>
                                                            <option @if($inventory->status==0)
                                                                    selected
                                                                    @endif value="0">Un Published</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="tel" class="form-control" name="contact" placeholder="Contact*" required id="" value="{{$inventory->contact}}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-left">
                                                    <p for="">Colors: &emsp;
                                                        <?php
//                                                        echo $inventory->colors;die;
                                                        if ($inventory->colors){
                                                            $colors1=[
                                                                'blue'=>false,'green'=>false,'red'=>false,'yellow'=>false
                                                            ];

                                                            $colors=json_decode($inventory->colors,true);
                                                            $colors=array_merge($colors1,$colors);
                                                        }else{
                                                            $colors=[
                                                                'blue'=>false,'green'=>false,'red'=>false,'yellow'=>false
                                                            ];
                                                        }
                                                        ?>
                                                        <input <?php if ($colors['blue']){ echo "checked";} ?> type="checkbox" name="color[]" value="blue" > Blue
                                                         <input <?php if ($colors['green']){ echo "checked";} ?> type="checkbox" name="color[]" value="green" > Green
                                                         <input <?php if ($colors['red']){ echo "checked";} ?> type="checkbox" name="color[]" value="Red" > Red
                                                         <input <?php if ($colors['yellow']){ echo "checked";} ?> type="checkbox" name="color[]" value="yellow" > Yellow
                                                    </p>
                                                    <div class="row">

                                                        <div class="col-md-12 text-left">

                                                            <p for="">Upload Image Gallery: &emsp;
                                                                <input type="file" accept="image/*" name="gallery[]" multiple>
                                                            </p>
                                                            <div class="img-to-del-container">
                                                                @if($inventory->images)
                                                                    @foreach($inventory->images as $img)
                                                                        <div class="img-to-del d-inline-block position-relative" style="max-width: 80px">
                                                                            <a href="/inventory/image/delete/{{$img->id}}" class="text-sm position-absolute"><i class="fas fa-trash"></i></a>
                                                                            <img class="img-fluid" src="{{asset($img->product_image)}}" alt="">
                                                                        </div>
                                                                    @endforeach
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-left">
                                                    <p for="">Upload Feature Image: &emsp;
                                                        <input type="file" accept="image/*" name="feature_img">
                                                    </p>

                                                    <div class="img-to-del d-inline-block position-relative" style="max-width: 150px">
                                                        <img class="img-fluid" src="{{asset($inventory->feature_img)}}" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 offset-md-3 text-center">
                                                    <p for="">
                                                        <input @if($inventory->bike==1)
                                                               checked
                                                               @endif type="checkbox" name="bike" value="1" > Bike &emsp;
                                                        <input  @if($inventory->van==1)
                                                                checked
                                                                @endif  type="checkbox" name="van" value="1" > Van

                                                    </p>
                                                </div>
                                                <div class="col-md-6 offset-md-3 text-center">
                                                    <button style="background: #ffcf42;color:black;font-weight: 600" class="pl-5 pr-5 pt-2 pb-2 border-0 btn btn-secondary rounded-pill" type="submit" >{{__('Update')}}</button>
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
                <!-- /.row -->
                </div>
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
    <style>
        .card-body {
            padding: 30px 50px!important;
        }
    </style>
@endsection
