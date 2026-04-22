<?php

namespace App\Livewire\Common;

use App\Enums\TransportVehicleEnum;
use App\Models\Categories;
use App\Models\ProductImage;
use App\Models\Products;
use App\Models\Qty;
use App\Models\User;
use App\Services\ImageServices;
use App\Services\ProductServices;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductFormLivewire extends Component
{
    use WithFileUploads;

    #[Url]
    public ?int $productId = null;

    public string $productName = '';

    public string $sku = '';

    public int $categoryId = 0;

    public int $qty = 0;

    public string $price = '';

    public string $discountPercentage = '';

    public ?float $height = null;

    public ?float $width = null;

    public ?float $length = null;

    public string $weight = '';

    public ?string $brand = null;

    public ?string $size = null;

    public string $status = '';

    public string $contact = '';

    public array $colors = [];

    public string $vehicle = '';

    public $featureImgUpload = null;

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
            'discountPercentage' => ['nullable', 'numeric', 'min:0'],
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
        ];
    }

    protected $validationAttributes = [
        'productName'        => 'product name',
        'categoryId'         => 'category',
        'discountPercentage' => 'discount percentage',
        'featureImgUpload'   => 'feature image',
        'galleryUploads.*'   => 'gallery image',
    ];

    public function mount(): void
    {
        if ($this->productId) {
            $sellerId = User::getAuthUser()->id;
            $product  = Products::getProductInfoEvenDisabled($sellerId, $this->productId);

            $this->productName        = $product->product_name;
            $this->sku                = $product->sku;
            $this->categoryId         = (int) $product->category_id;
            $this->qty                = (int) ($product->qty->first()->qty ?? 0);
            $this->price              = (string) $product->price;
            $this->discountPercentage = (string) ($product->discount_percentage ?? '');
            $this->height             = $product->height ? (float) $product->height : null;
            $this->width              = $product->width ? (float) $product->width : null;
            $this->length             = $product->length ? (float) $product->length : null;
            $this->weight             = (string) $product->weight;
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
            $this->featureImg         = $product->feature_img ?? '';
            $this->existingImages     = $product->images->toArray();
        }
    }

    public function removeGalleryImage(int $imageId): void
    {
        ProductImage::deleteById($imageId);
        $this->existingImages = array_values(
            array_filter($this->existingImages, fn($img) => $img['id'] !== $imageId)
        );
    }

    public function save(): void
    {
        $this->validate();

        try {
            $sellerId = User::getAuthUser()->id;

            if ($this->featureImgUpload) {
                $uploaded = ImageServices::uploadLivewireImg($this->featureImgUpload, $sellerId);
                if (! $uploaded) {
                    throw new Exception(config('constants.INTERNAL_SERVER_ERROR'));
                }
                $this->featureImg = $uploaded;
            }

            $data = [
                'seller_id'           => $sellerId,
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

            if ($this->productId) {
                $product = Products::findOrFail($this->productId);
                $product->update($data);
                Qty::updateQty($this->productId, $sellerId, $this->qty);
                $message = config('constants.DATA_UPDATED_SUCCESS');
            } else {
                $product = Products::add($data);
                Qty::add($sellerId, $product->id, $this->categoryId, $this->qty);
                $this->productId = $product->id;
                $message = config('constants.DATA_INSERTION_SUCCESS');
            }

            foreach ($this->galleryUploads as $galleryImage) {
                $fileName = ImageServices::uploadLivewireImg($galleryImage, $product->id);
                if ($fileName) {
                    ProductImage::add($product->id, $fileName);
                }
            }

            // Refresh images without redirecting
            $this->existingImages   = $product->load('images')->images->toArray();
            $this->featureImgUpload = null;
            $this->galleryUploads   = [];

            session()->flash('success', $message);
        } catch (Exception $e) {
            report($e);
            session()->flash('error', config('constants.INTERNAL_SERVER_ERROR'));
        }
    }

    public function render(): View
    {
        return view('livewire.common.product-form-livewire', [
            'categories'   => Categories::getAll(['id', 'category_name']),
            'commonColors' => Products::getCommonColors(),
        ]);
    }
}
