<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Categories;
use App\Models\Products;
use App\Models\SellersForCompany;
use App\Enums\OrderByEnum;
use App\Enums\ProductStatusEnum;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class AddSellersForCompanyLivewire extends Component
{
    use WithPagination;

    public int $companyId;

    public ?int $sellerId = null;

    public string $search = '';

    public ?int $categoryId = null;

    public ?OrderByEnum $orderBy = null;

    public ?string $orderByPrice = null;

    public array $selectedProducts = [];

    public Collection $categories;

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

    protected function rules(): array
    {
        return [
            'companyId' => 'required|integer|exists:users,id',
            'sellerId' => 'required|integer|exists:users,id',
            'selectedProducts' => 'required|array|min:1',
        ];
    }

    /*
     * Livewire Lifecycle Hooks
     */
    public function mount(int $companyId): void
    {
        $this->companyId = $companyId;
        $this->categories = Categories::all(['id', 'category_name']);
    }

    public function updatedSellerId(): void
    {
        $this->selectedProducts = [];
    }

    /*
     * Custom Helpers
     */
    public function resetComponent(): void
    {
        $this->resetValidation();

        $this->reset([
            'sellerId',
            'categoryId',
            'search',
            'orderBy',
            'orderByPrice',
            'selectedProducts',
        ]);
    }

    public function addSellerAndProducts(): void
    {
        $this->validate();

        try {
            /* Perform some operation */
            DB::transaction(function () {
                SellersForCompany::deleteByCompanyAndSellerId($this->companyId, $this->sellerId);

                /* Bulk insert newly selected products */
                $now = now();
                $groupedData = [];
                // $productIds = array_unique(array_filter(array_map('intval', $this->selectedProducts)));
                
                foreach ($this->selectedProducts as $productId) {
                    $groupedData[] = [
                        'company_id' => $this->companyId,
                        'seller_id' => $this->sellerId,
                        'product_id' => (int) $productId,
                        'created_at' => $now,
                    ];
                }

                SellersForCompany::addBulk($groupedData);
            });
            /* Operation finished */
            sleep(1);
            $this->resetComponent();

            session()->flash('success', config('constants.DATA_INSERTION_SUCCESS'));
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('constants.INSERTION_FAILED'));
        }
    }

    public function render(): View
    {
        $sellers = User::getParentAndChildSellers(
            columns: ['id', 'business_name']
        );

        $products = ($this->sellerId) ? Products::getParentOrChildSellerProductsForView(
            sellerId: $this->sellerId,
            search: $this->search,
            categoryId: $this->categoryId,
            status: ProductStatusEnum::ENABLE,
            orderByPrice: $this->orderByPrice,
            orderBy: $this->orderBy ?? OrderByEnum::DESC
        ) : null;

        return view('livewire.admin.add-sellers-for-company-livewire', compact('sellers', 'products'));
    }
}
