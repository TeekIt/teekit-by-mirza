<?php

namespace App\Livewire\Admin;

use App\Enums\OrderByEnum;
use App\Models\Categories;
use App\Models\VanProduct;
use App\Services\ImageServices;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class VanInventoriesLivewire extends Component
{
    use WithFileUploads;

    public string $search = '';

    public ?int $inventoryId = null;

    public int $sellerId = 0;

    public int $categoryId = 0;

    public int $productVanId = 0;

    public string $productName = '';

    public string $sku = '';

    public float $price = 0;

    public int $featured = 0;

    public string $discountPercentage = '';

    public ?float $weight = null;

    public ?string $brand = null;

    public ?string $size = null;

    public string $productStatus = '1';

    public string $contact = '';

    public ?string $colors = null;

    public ?int $bike = null;

    public ?int $car = null;

    public ?int $van = null;

    public string $featureImg = '';

    public $featureImgUpload = null;

    public ?float $height = null;

    public ?float $width = null;

    public ?float $length = null;

    public ?string $jobReference = null;

    public int $quantity = 0;

    public int $threshold = 0;

    public int $vanId = 0;

    protected function rules(): array
    {
        return [
            'sellerId' => ['required', 'integer', 'exists:users,id'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'productVanId' => ['required', 'integer', 'exists:vans,id'],
            'productName' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'featured' => ['required', 'integer', Rule::in([0, 1, '0', '1'])],
            'discountPercentage' => ['required', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'brand' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:255'],
            'productStatus' => ['required', Rule::in(['active', 'inactive', 'out_of_stock', 'low_stock', 'critical'])],
            'contact' => ['required', 'string', 'max:255'],
            'colors' => ['nullable', 'json'],
            'bike' => ['nullable', 'integer', Rule::in([0, 1, '0', '1'])],
            'car' => ['nullable', 'integer', Rule::in([0, 1, '0', '1'])],
            'van' => ['nullable', 'integer', Rule::in([0, 1, '0', '1'])],
            'featureImg' => ['required', 'string'],
            'featureImgUpload' => ['nullable', 'image', 'max:1024', 'mimes:jpeg,jpg,png'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'jobReference' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
            'threshold' => ['required', 'integer', 'min:0'],
        ];
    }

    /*
    * Lifecycle Hooks
    */
    public function mount(): void
    {
        $this->vanId = request()->query('vanId');
    }

    /**
     * Reset to the first page in future if pagination is introduced.
     */
    public function updatingSearch(): void
    {
        // Intentionally left blank for now. Kept to mirror other admin components.
    }

    public function resetComponent(): void
    {
        $this->resetValidation();

        $this->reset([
            'inventoryId',
            'sellerId',
            'categoryId',
            'productVanId',
            'productName',
            'sku',
            'price',
            'featured',
            'discountPercentage',
            'weight',
            'brand',
            'size',
            'productStatus',
            'contact',
            'colors',
            'bike',
            'car',
            'van',
            'featureImg',
            'featureImgUpload',
            'height',
            'width',
            'length',
            'jobReference',
            'quantity',
            'threshold',
        ]);
    }

    public function renderEditVanInventoryModal(int $id): void
    {
        $inventory = VanProduct::getById($id, $this->vanId);

        $this->authorize('update', $inventory);

        $this->inventoryId = $inventory->id;
        $this->sellerId = (int) $inventory->seller_id;
        $this->categoryId = (int) $inventory->category_id;
        $this->productVanId = (int) $inventory->van_id;
        $this->productName = $inventory->product_name;
        $this->sku = $inventory->sku;
        $this->price = (float) $inventory->price;
        $this->featured = (int) $inventory->featured;
        $this->discountPercentage = $inventory->discount_percentage;
        $this->weight = $inventory->weight;
        $this->brand = $inventory->brand;
        $this->size = $inventory->size;
        $this->productStatus = (string) $inventory->getRawOriginal('status');
        $this->contact = $inventory->contact;
        $this->colors = $inventory->getRawOriginal('colors');
        $this->bike = isset($inventory->bike) ? (int) $inventory->bike : null;
        $this->car = isset($inventory->car) ? (int) $inventory->car : null;
        $this->van = isset($inventory->van) ? (int) $inventory->van : null;
        $this->featureImg = $inventory->feature_img;
        $this->height = $inventory->height;
        $this->width = $inventory->width;
        $this->length = $inventory->length;
        $this->jobReference = $inventory->job_reference;
        $this->quantity = (int) $inventory->quantity;
        $this->threshold = (int) $inventory->min_threshold;
    }

    public function updateVanInventory(): void
    {
        $vanProduct = VanProduct::with('van')->findOrFail($this->inventoryId);

        // $this->authorize('update', $vanProduct);

        $this->validate();

        try {
            if ($this->featureImgUpload) {
                $uploadedImage = ImageServices::uploadLivewireImg($this->featureImgUpload, $this->vanId);
                if (! $uploadedImage) {
                    throw new Exception(config('constants.INTERNAL_SERVER_ERROR'));
                }

                $this->featureImg = $uploadedImage;
            }

            $updated = $vanProduct->update([
                'seller_id' => $this->sellerId,
                'category_id' => $this->categoryId,
                'van_id' => $this->productVanId,
                'product_name' => $this->productName,
                'sku' => $this->sku,
                'price' => $this->price,
                'featured' => $this->featured,
                'discount_percentage' => $this->discountPercentage,
                'weight' => $this->weight,
                'brand' => $this->brand,
                'size' => $this->size,
                'status' => $this->productStatus,
                'contact' => $this->contact,
                'colors' => $this->colors,
                'bike' => $this->bike,
                'car' => $this->car,
                'van' => $this->van,
                'feature_img' => $this->featureImg,
                'height' => $this->height,
                'width' => $this->width,
                'length' => $this->length,
                'job_reference' => $this->jobReference,
                'quantity' => $this->quantity,
                'min_threshold' => $this->threshold,
            ]);

            sleep(1);
            $this->resetComponent();
            $this->dispatch('close-modal', ['id' => 'editVanInventoryModal']);

            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', $error->getMessage());
        }
    }

    public function render(): View
    {
        $data = VanProduct::getAll(
            orderBy: OrderByEnum::DESC,
            search: $this->search,
            vanId: $this->vanId
        );

        $categories = Categories::getAll(['id', 'category_name']);

        return view('livewire.admin.van-inventories-livewire', compact('data', 'categories'));
    }
}
