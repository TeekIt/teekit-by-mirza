<?php

namespace App\Livewire\Common;

use App\Enums\TransportVehicleEnum;
use App\Enums\UserRoleEnum;
use App\Models\Categories;
use App\Models\ProductImage;
use App\Models\Products;
use App\Models\Qty;
use App\Models\User;
use App\Models\Van;
use App\Models\VanProduct;
use App\Services\ImageServices;
use App\Services\ProductServices;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ProductFormLivewire extends Component
{
    use WithFileUploads;

    public int $authUserId;

    public bool $isAuthUserParentSeller;

    public bool $isAuthUserChildSeller;

    public bool $isAuthUserCompany;

    public bool $shouldLoadVans = false;

    #[Url]
    public ?int $productId = null;

    public string $productName = '';

    public string $sku = '';

    public int $categoryId = 0;

    public int $qty = 0;

    public float $price = 0.0;

    public string $discountPercentage = '';

    public ?float $height = null;

    public ?float $width = null;

    public ?float $length = null;

    public ?float $weight = 0.0;

    public ?string $brand = null;

    public ?string $size = null;

    public string $status = '';

    public string $contact = '';

    public array $colors = [];

    public string $vehicle = '';

    public ?int $vanId = null;

    public int $minThreshold = 0;

    public ?TemporaryUploadedFile $featureImgUpload = null;

    public string $featureImg = '';

    public array $galleryUploads = [];

    public array $existingImages = [];

    protected function rules(): array
    {
        return [
            'productName'        => ['required', 'string', 'max:255'],
            'sku'                => ['required', 'string', 'max:255'],
            'categoryId'         => ['required', 'integer', 'exists:categories,id'],
            'qty'                => ['required', 'integer', 'min:0'],
            'price'              => ['required', 'numeric', 'min:0'],
            'discountPercentage' => ['nullable', 'numeric'],
            'minThreshold'       => ['required', 'integer', 'min:0'],
            'height'             => ['nullable', 'numeric', 'min:0'],
            'width'              => ['nullable', 'numeric', 'min:0'],
            'length'             => ['nullable', 'numeric', 'min:0'],
            'weight'             => ['required', 'numeric', 'min:0'],
            'brand'              => ['nullable', 'string', 'max:255'],
            'status'             => ['required', 'in:0,1'],
            'contact'            => ['required', 'string', 'min:10', 'max:10'],
            'colors'             => ['nullable', 'array'],
            'featureImgUpload'   => array_filter([
                $this->productId ? 'nullable' : 'required',
                'image',
                'max:1024',
                'mimes:jpeg,jpg,png',
            ]),
            'galleryUploads.*'   => ['nullable', 'image', 'max:1024', 'mimes:jpeg,jpg,png'],
            'vehicle'            => ['required', 'in:bike,car,van'],
            'vanId'              => array_filter([
                $this->isAuthUserCompany ? 'required' : 'nullable',
                'integer',
                'exists:vans,id',
            ]),
        ];
    }

    /*
    * Lifecycle Hooks
    */
    public function mount(?int $productId = null): void
    {
        $authUser = User::getAuthUser();
        $this->authUserId = $authUser->id;
        $this->isAuthUserParentSeller = ($authUser->role_id === UserRoleEnum::SELLER->value);
        $this->isAuthUserChildSeller = ($authUser->role_id === UserRoleEnum::CHILD_SELLER->value);
        $this->isAuthUserCompany = ($authUser->role_id === UserRoleEnum::COMPANY->value);
        $this->shouldLoadVans = request()->is('*vans*');

        if ($productId) {
            $this->productId = $productId;

            $this->populateComponentVariables();
        }
    }

    /*
     * Custom Helpers
     */
    public function resetComponent()
    {
        $this->resetValidation();

        $this->reset([
            'productName',
            'sku',
            'categoryId',
            'qty',
            'price',
            'discountPercentage',
            'minThreshold',
            'height',
            'width',
            'length',
            'weight',
            'brand',
            'size',
            'status',
            'contact',
            'colors',
            'vehicle',
            'vanId',
            'featureImgUpload',
            'featureImg',
            'galleryUploads',
            'existingImages',
        ]);
    }

    public function populateComponentVariables(): void
    {
        if (request()->route()->getName() === 'vans.inventory.edit.manually') {
            $product = VanProduct::getById($this->productId);
            $this->productName        = $product->product_name;
            $this->sku                = $product->sku;
            $this->categoryId         = $product->category_id;
            $this->qty                = $product->quantity;
            $this->price              = $product->price;
            $this->discountPercentage = $product->discount_percentage;
            $this->height             = $product->height ? $product->height : null;
            $this->width              = $product->width ? $product->width : null;
            $this->length             = $product->length ? $product->length : null;
            $this->weight             = $product->weight;
            $this->brand              = $product->brand;
            $this->size               = $product->size;
            $this->status             = (string) $product->getRawOriginal('status');
            $contact                  = $product->contact ?? '';
            $this->contact            = str_starts_with($contact, '+44') ? substr($contact, 3) : $contact;
            $this->colors             = $product->colors
                ? array_keys(json_decode($product->colors, true) ?? [])
                : [];
            $this->vehicle            = match (true) {
                (bool) $product->bike => TransportVehicleEnum::BIKE->value,
                (bool) $product->car  => TransportVehicleEnum::CAR->value,
                (bool) $product->van  => TransportVehicleEnum::VAN->value,
                default               => '',
            };
            $this->vanId              = $product->van_id;
            $this->featureImg         = $product->feature_img ?? '';
            $this->minThreshold       = $product->min_threshold;
        }
    }

    public function removeGalleryImage(int $imageId): void
    {
        ProductImage::deleteById($imageId);

        $this->existingImages = array_values(
            array_filter($this->existingImages, fn($img) => $img['id'] !== $imageId)
        );
    }

    public function uploadProductGalleryImages(): void
    {
        foreach ($this->galleryUploads as $galleryImage) {
            $fileName = ImageServices::uploadLivewireImg($galleryImage, $this->productId);
            if ($fileName) {
                ProductImage::add($this->productId, $fileName);
            }
        }

        /* Refresh images without redirecting */
        $this->existingImages   = Products::find($this->productId)->images->toArray();
        $this->galleryUploads   = [];
    }

    public function addOrUpdateProduct(): void
    {
        $this->validate();

        try {
            /* Perform some operation */
            if ($this->featureImgUpload) {
                $uploadedFilePath = ImageServices::uploadLivewireImg($this->featureImgUpload, $this->authUserId);
                $this->featureImg = $uploadedFilePath;
            }

            $data = [
                'seller_id'           => $this->authUserId,
                'category_id'         => $this->categoryId,
                'product_name'        => $this->productName,
                'sku'                 => $this->sku,
                'price'               => $this->price,
                'discount_percentage' => $this->discountPercentage !== '' ? (float) $this->discountPercentage : 0.00,
                'height'              => $this->height,
                'width'               => $this->width,
                'length'              => $this->length,
                'weight'              => $this->weight,
                'brand'               => $this->brand,
                'size'                => $this->size,
                'status'              => $this->status,
                'contact'             => '+44' . $this->contact,
                'colors'              => ! empty($this->colors)
                    ? ProductServices::jsonEncodeColors($this->colors)
                    : null,
                'bike'                => $this->vehicle === TransportVehicleEnum::BIKE->value ? 1 : 0,
                'car'                 => $this->vehicle === TransportVehicleEnum::CAR->value ? 1 : 0,
                'van'                 => $this->vehicle === TransportVehicleEnum::VAN->value ? 1 : 0,
                'feature_img'         => $this->featureImg,
            ];

            if ($this->isAuthUserCompany) {
                $data['seller_id'] = null;
                $data['van_id'] = $this->vanId;
                $data['quantity'] = $this->qty;
                $data['min_threshold'] = $this->minThreshold;
            }

            /* Update Product */
            if ($this->productId) {
                if ($this->isAuthUserCompany) {
                    $product = VanProduct::find($this->productId)->update($data);
                }

                if ($this->isAuthUserParentSeller || $this->isAuthUserChildSeller) {
                    $product = Products::find($this->productId);
                    $product->update($data);
                    Qty::updateQty($this->productId, $this->authUserId, $this->qty);
                    $this->uploadProductGalleryImages();
                    // foreach ($this->galleryUploads as $galleryImage) {
                    //     $fileName = ImageServices::uploadLivewireImg($galleryImage, $product->id);
                    //     if ($fileName) {
                    //         ProductImage::add($product->id, $fileName);
                    //     }
                    // }

                    // /* Refresh images without redirecting */
                    // $this->existingImages   = $product->load('images')->images->toArray();
                    // $this->featureImgUpload = null;
                    // $this->galleryUploads   = [];
                }

                $message = config('constants.DATA_UPDATED_SUCCESS');
            } else {
                /* Add Product */
                if ($this->isAuthUserCompany) {
                    VanProduct::add($data);
                }

                if ($this->isAuthUserParentSeller || $this->isAuthUserChildSeller) {
                    $product = Products::add($data);
                    Qty::add($this->authUserId, $product->id, $this->categoryId, $this->qty);
                    // $this->productId = $product->id;
                }

                $message = config('constants.DATA_INSERTION_SUCCESS');
            }
            /* Operation finished */
            sleep(1);
            if (!$this->productId) {
                $this->resetComponent();
            }

            session()->flash('success', $message);
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('constants.INTERNAL_SERVER_ERROR'));
        }
    }

    public function render(): View
    {
        $vans = [];
        $categories = Categories::getAll(['id', 'category_name']);
        $commonColors = Products::getCommonColors();

        if ($this->shouldLoadVans) {
            $vans = Van::getByCompanyId($this->authUserId, ['id', 'number_plate']);
        }

        return view('livewire.common.product-form-livewire', compact('categories', 'commonColors', 'vans'));
    }
}
