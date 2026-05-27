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

    protected $paginationTheme = 'bootstrap';

    public function mount(int $companyId): void
    {
        $this->companyId = $companyId;
        $this->categories = Categories::all(['id', 'category_name']);
    }

    public function resetComponent()
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
        $this->validate([
            'companyId' => 'required|integer|exists:users,id',
            'sellerId' => 'required|integer|exists:users,id',
            'selectedProducts' => 'required|array|min:1',
        ]);

        try {
            DB::transaction(function () {
                // Delete existing relationships for this company and seller pair
                SellersForCompany::where('company_id', '=',$this->companyId)
                    ->where('seller_id', '=',$this->sellerId)
                    ->delete();

                // Bulk insert newly selected products
                $now = now();
                $insertData = [];
                $productIds = array_unique(array_filter(array_map('intval', $this->selectedProducts)));

                foreach ($productIds as $productId) {
                    $insertData[] = [
                        'company_id' => $this->companyId,
                        'seller_id' => $this->sellerId,
                        'product_id' => $productId,
                        'created_at' => $now,
                    ];
                }

                SellersForCompany::insert($insertData);
            });

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
