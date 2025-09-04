<div class="content">

    @php
        use App\Enums\PackageWeightEnum;
        use App\Enums\PackageTransportTypeEnum;
        use App\Enums\DeliveryProviderEnum;
    @endphp

    <x-session-messages />

    <style>
        .table {
            --bs-table-bg: transparent !important;
        }

        .cursor-pointer {
            cursor: pointer !important;
        }
    </style>

    <!-- Main content -->
    <div class="content">
        <div class="content-header">
            <div class="container pt-4">
                <form wire:submit.prevent="render">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-sm-0 col-md-10"></div>
                                <div class="col-sm-12 col-md-2 d-flex flex-sm-row">
                                    <a type="button" href="{{ route('seller.requested.deliveries') }}"
                                        class="btn btn-primary py-3 px-0 w-100 mx-1" title="View Requested Deliveries">
                                        <span class="fas fa-eye"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="offset-xl-2 col-lg-12 col-xl-8 py-4">
                    <div class="card-body">
                        <div class="d-block">
                            <div class="card-text">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 class="text-center text-site-primary">Request Delivery For Buyer</h4>
                                    </div>
                                    <div class="col-md-12">
                                        <form wire:submit.prevent="requestDelivery" method="POST"
                                            enctype="multipart/form-data">
                                            {{ csrf_field() }}
                                            <div class="my-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control"
                                                                wire:model.defer="pickupAddress"
                                                                wire:change="inputFieldChanged" id="pickupAddress"
                                                                placeholder="Pickup Address*" value="" required>
                                                        </div>
                                                        <small class="text-danger">
                                                            @error('pickupAddress')
                                                                {{ $message }}
                                                            @enderror
                                                        </small>
                                                    </div>
                                                    <div class="col-md-12 mt-3">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control"
                                                                wire:model.defer="dropoffAddress"
                                                                wire:change="inputFieldChanged" id="dropoffAddress"
                                                                placeholder="Dropoff Address*" value="" required>
                                                        </div>
                                                        <small class="text-danger">
                                                            @error('dropoffAddress')
                                                                {{ $message }}
                                                            @enderror
                                                        </small>
                                                    </div>
                                                    <div class="col-md-12 mb-3">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control"
                                                                wire:model.defer="unitAddress"
                                                                placeholder="Unit Address (e.g Flat#)" value="" required>
                                                        </div>
                                                        <small class="text-danger">
                                                            @error('unitAddress')
                                                                {{ $message }}
                                                            @enderror
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="my-3">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control"
                                                                wire:model.defer="receiverName"
                                                                placeholder="Buyer Name*" value="" required>
                                                        </div>
                                                        <small class="text-danger">
                                                            @error('receiverName')
                                                                {{ $message }}
                                                            @enderror
                                                        </small>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <input type="number" class="form-control"
                                                                wire:model.defer="receiverPhone"
                                                                placeholder="Buyer Contact*" value="" required>
                                                        </div>
                                                        <small class="text-danger">
                                                            @error('receiverPhone')
                                                                {{ $message }}
                                                            @enderror
                                                        </small>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <input type="email" class="form-control"
                                                                wire:model.defer="receiverEmail"
                                                                placeholder="Buyer Email*" value="" required>
                                                        </div>
                                                        <small class="text-danger">
                                                            @error('receiverEmail')
                                                                {{ $message }}
                                                            @enderror
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="my-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <select class="form-control"
                                                                wire:model.defer="packageTransportType"
                                                                wire:change="changePackageWeight" required>
                                                                <option value="">
                                                                    Package Transport Type*
                                                                </option>
                                                                <option vlaue="{{ PackageTransportTypeEnum::MOPED }}">
                                                                    Moped
                                                                </option>
                                                                <option
                                                                    vlaue="{{ PackageTransportTypeEnum::CAR_BOOT }}">
                                                                    Car Boot
                                                                </option>
                                                                <option
                                                                    vlaue="{{ PackageTransportTypeEnum::SMALL_VAN }}">
                                                                    Small Van
                                                                </option>
                                                                <option
                                                                    vlaue="{{ PackageTransportTypeEnum::BIG_VAN }}">
                                                                    Big Van
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <small class="text-danger">
                                                            @error('packageTransportType')
                                                                {{ $message }}
                                                            @enderror
                                                        </small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <select class="form-control"
                                                                wire:model.defer="packageWeight"
                                                                wire:change="inputFieldChanged" required>
                                                                <option value="">Package Weight (Kg)*</option>
                                                                <optgroup label="Small">
                                                                    <option value="{{ PackageWeightEnum::SMALL }}">
                                                                        W20cm x D15cm x H40cm <b>(Up to 8kg)</b>
                                                                    </option>
                                                                </optgroup>
                                                                <optgroup label="Medium">
                                                                    <option value="{{ PackageWeightEnum::MEDIUM }}">
                                                                        W30cm x D30cm x H50cm <b>(Up to 12kg)</b>
                                                                    </option>
                                                                </optgroup>
                                                                <optgroup label="Large">
                                                                    <option value="{{ PackageWeightEnum::LARGE }}">
                                                                        W65cm x D50cm x H90cm <b>(Up to 40kg)</b>
                                                                    </option>
                                                                </optgroup>
                                                                <optgroup label="Extra Large">
                                                                    <option
                                                                        value="{{ PackageWeightEnum::EXTRA_LARGE }}">
                                                                        W90cm x D50cm x H100cm <b>(Up to 70kg)</b>
                                                                    </option>
                                                                </optgroup>
                                                            </select>
                                                        </div>
                                                        <small class="text-danger">
                                                            @error('packageWeight')
                                                                {{ $message }}
                                                            @enderror
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 offset-md-3 text-center my-3">
                                                {{-- <button type="button"
                                                    class="btn site-primary-yellow-bg rounded-pill px-5 py-2 font-weight-bold dropdown-toggle"
                                                    data-bs-toggle="dropdown" aria-expanded="false"
                                                    wire:click="calculateDeliveryCost"
                                                    wire:target="calculateDeliveryCost" wire:loading.class="btn-dark"
                                                    wire:loading.class.remove="site-primary-yellow-bg"
                                                    wire:loading.attr="disabled">
                                                    <span wire:target="calculateDeliveryCost" wire:loading.remove>
                                                        Calculate Delivery Charges
                                                    </span>
                                                    <span wire:target="calculateDeliveryCost" wire:loading>
                                                        <span class="spinner-border spinner-border-sm text-light"
                                                            role="status" aria-hidden="true"></span>
                                                    </span>
                                                </button> --}}

                                                <div class="btn-group">
                                                    <button type="button"
                                                        class="btn site-primary-yellow-bg rounded-pill px-5 py-2 font-weight-bold dropdown-toggle"
                                                        data-bs-toggle="dropdown" aria-expanded="false"
                                                        wire:loading.class="btn-dark"
                                                        wire:loading.class.remove="site-primary-yellow-bg"
                                                        wire:loading.attr="disabled">
                                                        <span wire:target="calculateDeliveryCost" wire:loading.remove>
                                                            Calculate Delivery Charges
                                                        </span>
                                                        <span wire:target="calculateDeliveryCost" wire:loading>
                                                            <span class="spinner-border spinner-border-sm text-light"
                                                                role="status" aria-hidden="true"></span>
                                                        </span>
                                                    </button>
                                                    <ul class="dropdown-menu col-12">
                                                        <li class="dropdown-item cursor-pointer p-3 border-bottom"
                                                            wire:click="calculateDeliveryCost('{{ DeliveryProviderEnum::STUART }}')">
                                                            Stuart Delivery Charges
                                                        </li>
                                                        <li class="dropdown-item cursor-pointer p-3"
                                                            wire:click="calculateDeliveryCost('{{ DeliveryProviderEnum::GOPHR }}')">
                                                            Gophr Delivery Charges
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="d-flex justify-content-center">
                                                    <div class="col-12 col-sm-6 table-responsive">
                                                        <table class="table">
                                                            <tbody>
                                                                <tr class="p-3 border-bottom">
                                                                    <td class="fs-5 py-2 text-start">
                                                                        Delivery Charges:
                                                                    </td>
                                                                    <td class="fs-5 py-2 text-end">
                                                                        {{ $currency }} {{ $deliveryCharges }}
                                                                    </td>
                                                                </tr>
                                                                <tr class="p-3 border-bottom">
                                                                    <td class="fs-5 py-2 text-start">
                                                                        Service Charges:
                                                                    </td>
                                                                    <td class="fs-5 py-2 text-end">
                                                                        {{ $currency }} {{ $serviceCharges }}
                                                                    </td>
                                                                </tr>
                                                                <tr class="p-3 border-bottom">
                                                                    <td class="fs-5 py-2 text-start">
                                                                        Tax:
                                                                    </td>
                                                                    <td class="fs-5 py-2 text-end">
                                                                        {{ $currency }} {{ $tax }}
                                                                    </td>
                                                                </tr>
                                                                <tr class="p-3">
                                                                    <td class="fs-5 py-2 text-start fw-bold">
                                                                        Total Cost:
                                                                    </td>
                                                                    <td class="fs-5 py-2 text-end fw-bold">
                                                                        {{ $currency }} {{ $totalCost }}
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 offset-md-3 text-center my-3">
                                                <button type="submit"
                                                    class="btn site-primary-yellow-bg rounded-pill px-5 py-2 font-weight-bold"
                                                    wire:target="requestDelivery" wire:loading.class="btn-dark"
                                                    wire:loading.class.remove="site-primary-yellow-bg"
                                                    wire:loading.attr="disabled"
                                                    @if ($disableRequestDeliveryButton) disabled @endif>
                                                    <span wire:target="requestDelivery" wire:loading.remove>
                                                        {{ $requestDeliveryButtonTxt }}
                                                    </span>
                                                    <span wire:target="requestDelivery" wire:loading>
                                                        <span class="spinner-border spinner-border-sm text-light"
                                                            role="status" aria-hidden="true"></span>
                                                    </span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="my-5">
                                            <h4 class="text-center text-site-primary my-3">
                                                Package Size and Transport Types
                                            </h4>

                                            <p>
                                                All packages have different dimensions and weights. However, the main
                                                sizes are:
                                            </p>

                                            <ul>
                                                <li>
                                                    <strong>S:</strong> W20cm x D15cm x H40cm (1/2 of a thermal bag),
                                                    8/12 kg, depending on bag*
                                                </li>
                                                <li>
                                                    <strong>M:</strong> W30cm x D30cm x H50cm (1 thermal bag), 8/12 kg,
                                                    depending on bag*
                                                </li>
                                                <li>
                                                    <strong>L:</strong> W65cm x D50cm x H90cm (2 thermal bags), 40kg
                                                </li>
                                                <li>
                                                    <strong>XL:</strong> W90cm x D50cm x H100cm (4 Thermal bags), 70kg
                                                </li>
                                            </ul>

                                            <p>
                                                <strong>S/M</strong> packages can fit on moped (motorbike).<br>
                                                <strong>L/XL</strong> packages can be delivered only by car boot/van.
                                            </p>
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
    <style>
        .card-body {
            padding: 30px 50px !important;
        }
    </style>
</div>
