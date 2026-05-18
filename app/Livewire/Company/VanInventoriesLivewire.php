<?php

namespace App\Livewire\Company;

use App\Enums\OrderByEnum;
use App\Models\Categories;
use App\Models\User;
use App\Models\Van;
use App\Models\VanProduct;
use App\Services\ImageServices;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class VanInventoriesLivewire extends Component
{
    use WithFileUploads, WithPagination;

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

    public $vanId = 0;

    public int $companyId = 0;

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

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
        // $this->vanId = request()->query('vanId');
        $this->companyId = User::getAuthUser()->id;
    }

    /**
     * Reset to the first page
     */
    public function updatingSearch(): void
    {
        /* No function should be kept blank. */
    }

    public function updatingVanId(): void
    {
        $this->resetPage();
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
            vanId: (int) $this->vanId
        );

        $vans = Van::getByCompanyId(
            $this->companyId,
            ['id', 'company_id', 'number_plate']
        );

        $categories = Categories::getAll(['id', 'category_name']);

        return view('livewire.admin.van-inventories-livewire', compact('data', 'vans', 'categories'));
    }
}
