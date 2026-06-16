<?php

namespace App\Livewire\Company;

use App\Actions\VanInventoryOrder\ProcessVanInventoryOrderAction;
use App\Actions\VanInventoryOrder\ProcessVanInventoryPayAsYouGoOrderAction;
use App\Enums\OrderByEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\ProductStatusEnum;
use App\Models\Categories;
use App\Models\Products;
use App\Models\User;
use App\Models\Van;
use App\Services\GoogleMapServices;
use Exception;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class OrderVanInventoryLivewire extends Component
{
    use WithPagination;

    public User $authUser;

    public int $companyId;

    public ?string $vanAddress = null;

    public ?string $vanCountry = null;

    public ?string $vanState = null;

    public ?string $vanCity = null;

    public ?string $vanPostcode = null;

    public ?float $vanLat = null;

    public ?float $vanLon = null;

    public ?int $vanId = null;

    public ?int $nearBySellerId = null;

    public array $nearbySellers = [];

    public ?string $search = null;

    public ?int $categoryId = null;

    public ?OrderByEnum $orderBy = null;

    public ?string $orderByPrice = null;

    public bool $showInventoryGrid = false;

    public bool $isPayAsYouGoRoute = false;

    public Collection $vans;

    public Collection $categories;

    public const CART_SESSION_KEY = 'add_van_inventory_cart';

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

    protected function rules(): array
    {
        return [
            'vanId' => 'required|integer|exists:vans,id',
            'vanAddress' => 'required|string',
            'nearBySellerId' => 'required|integer|exists:users,id',
        ];
    }
    /*
    * Lifecycle Hooks
    */
    public function mount(): void
    {
        $this->authUser = User::getAuthUser();
        $this->companyId = $this->authUser->id;
        $this->isPayAsYouGoRoute = request()->is('*pay-as-you-go*');
        $this->categories = Categories::all(['id', 'category_name']);

        $this->vans = Van::getByCompanyId(
            companyId: $this->companyId,
            columns: ['id', 'company_id', 'number_plate']
        );
    }

    /*
     * Custom Helpers
     */
    public function resetComponent()
    {
        $this->resetValidation();

        $this->reset([
            'vanId',
            'vanAddress',
            'vanCountry',
            'vanState',
            'vanCity',
            'vanPostcode',
            'vanLat',
            'vanLon',
            'nearBySellerId',
            'nearbySellers',
            'search',
            'categoryId',
            'orderBy',
            'orderByPrice',
            'showInventoryGrid',
        ]);
    }

    public function updatedVanAddress(): void
    {
        $this->resetSearchResults();
    }

    public function updatedNearBySellerId(): void
    {
        $this->resetSearchResults();
    }

    protected function resetSearchResults(): void
    {
        $this->showInventoryGrid = false;
        $this->resetPage();
    }

    public function updateLivewireProperties(array $data = []): void
    {
        if (isset($data['pickupAddress'])) {
            $this->vanAddress = $data['pickupAddress'];
        }

        if (isset($data['pickupCountry'])) {
            $this->vanCountry = $data['pickupCountry'];
        }

        if (isset($data['pickupState'])) {
            $this->vanState = $data['pickupState'];
        }

        if (isset($data['pickupCity'])) {
            $this->vanCity = $data['pickupCity'];
        }

        if (isset($data['pickupPostcode'])) {
            $this->vanPostcode = $data['pickupPostcode'];
        }

        if (isset($data['pickupLat']) && isset($data['pickupLon'])) {
            $this->vanLat = $data['pickupLat'];
            $this->vanLon = $data['pickupLon'];
        }
    }

    public function getSellersOfSameCity(): Collection
    {
        return Cache::remember(
            'getSellersOfSameCityForAddVanInventoryLivewire' . $this->vanCity,
            Carbon::now()->addDay(),
            fn() => User::getActiveParentAndChildSellersByCity(
                $this->vanCity,
            )
        );
    }

    public function vanAddressChanged(): void
    {
        $sellersOfTheSameCity = $this->getSellersOfSameCity();
        $this->nearbySellers = GoogleMapServices::getNearBySellers(
            $this->vanLat,
            $this->vanLon,
            $sellersOfTheSameCity,
            $this->companyId,
        );
    }

    public function performSearch(): void
    {
        $this->validate();

        $this->showInventoryGrid = true;
    }

    public function getCart(): array
    {
        return (array) session()->get(self::CART_SESSION_KEY, []);
    }

    public function putCart(array $cart): void
    {
        session()->put(self::CART_SESSION_KEY, $cart);
    }

    public function addToCart(int $productId): void
    {
        $product = Products::getProductInfoWithoutRelationsById($productId, [
            'id',
            'product_name',
            'feature_img',
            'price',
        ]);

        $cart = $this->getCart();
        $cartKey = (string) $productId;

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['qty']++;
        } else {
            $image = str_contains($product->feature_img, 'https://')
                ? $product->feature_img
                : config('constants.BUCKET') . $product->feature_img;

            $cart[$cartKey] = [
                'id' => $product->id,
                'sellerId' => $this->nearBySellerId,
                'title' => $product->product_name,
                'image' => $image,
                'price' => (float) $product->price,
                'qty' => 1,
            ];
        }

        $this->putCart($cart);
    }

    public function increaseCartItemQty(int $productId): void
    {
        $cart = $this->getCart();
        $cartKey = (string) $productId;

        if (!isset($cart[$cartKey])) {
            return;
        }

        $cart[$cartKey]['qty']++;
        $this->putCart($cart);
    }

    public function decreaseCartItemQty(int $productId): void
    {
        $cart = $this->getCart();
        $cartKey = (string) $productId;

        if (!isset($cart[$cartKey])) {
            return;
        }

        $cart[$cartKey]['qty']--;

        if ($cart[$cartKey]['qty'] <= 0) {
            unset($cart[$cartKey]);
        }

        $this->putCart($cart);
    }

    public function updateCartItemQty(int $productId, string $qty): void
    {
        $cart = $this->getCart();
        $cartKey = (string) $productId;

        if (!isset($cart[$cartKey])) {
            return;
        }

        $normalizedQty = max(0, (int) $qty);

        if ($normalizedQty === 0) {
            unset($cart[$cartKey]);
        } else {
            $cart[$cartKey]['qty'] = $normalizedQty;
        }

        $this->putCart($cart);
    }

    public function removeCartItem(int $productId): void
    {
        $cart = $this->getCart();
        $cartKey = (string) $productId;

        if (!isset($cart[$cartKey])) {
            return;
        }

        unset($cart[$cartKey]);

        $this->putCart($cart);
    }

    public function getCartItemsValues(): array
    {
        return array_values($this->getCart());
    }

    public function getCartItemsCount(): int
    {
        return array_sum(array_column($this->getCartItemsValues(), 'qty'));
    }

    public function getCartTotal(): float
    {
        return array_reduce(
            $this->getCart(),
            fn(float $carry, array $item): float => $carry + ((float) $item['price'] * (int) $item['qty']),
            0.0
        );
    }

    /*
     * CRUD Methods
     */
    public function addDirectlyToVan(): void
    {
        $this->validate([
            'vanId' => 'required|integer|exists:vans,id',
        ]);

        try {
            $cartItems = $this->getCartItemsValues();

            if (empty($cartItems)) {
                session()->flash('error', 'Cart is empty.');

                $this->dispatch('close-cart', ['id' => 'cartDrawer']);

                return;
            }

            /* Perform checkout operation */
            $vanInventoryPayAsYouGoOrderPlaced = (new ProcessVanInventoryPayAsYouGoOrderAction())->execute(
                companyId: $this->companyId,
                vanId: $this->vanId,
                orderItems: $cartItems,
            );
            /* Operation finished */
            sleep(1);
            $this->dispatch('close-cart', ['id' => 'cartDrawer']);
            $this->resetComponent();

            if ($vanInventoryPayAsYouGoOrderPlaced) {
                session()->forget(self::CART_SESSION_KEY);
                session()->flash('success', config('constants.PAY_AS_YOU_GO_ORDER_PLACED_SUCCESSFULLY'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('constants.ORDER_PLACED_FAILED'));
        }
    }

    public function checkout(OrderTypeEnum $vanInventoryOrderType): void
    {
        $this->validate([
            'vanId' => 'required|integer|exists:vans,id',
        ]);

        try {
            $cartItems = $this->getCartItemsValues();

            if (empty($cartItems)) {
                session()->flash('error', 'Cart is empty.');

                $this->dispatch('close-cart', ['id' => 'cartDrawer']);

                return;
            }

            /* Perform checkout operation */
            $vanInventoryOrderPlaced = (new ProcessVanInventoryOrderAction())->execute(
                companyId: $this->companyId,
                vanId: $this->vanId,
                customerName: $this->authUser->name,
                customerLat: $this->authUser->lat,
                customerLon: $this->authUser->lon,
                customerCountryCode: $this->authUser->country_code,
                customerPhoneNumber: $this->authUser->phone,
                vanAddress: $this->vanAddress,
                vanCountry: $this->vanCountry,
                vanState: $this->vanState,
                vanCity: $this->vanCity,
                vanPostcode: $this->vanPostcode,
                vanLat: $this->vanLat,
                vanLon: $this->vanLon,
                orderType: $vanInventoryOrderType,
                orderItems: $cartItems
            );
            /* Operation finished */
            sleep(1);
            $this->dispatch('close-cart', ['id' => 'cartDrawer']);

            if ($vanInventoryOrderPlaced) {
                session()->forget(self::CART_SESSION_KEY);
                session()->flash('success', config('constants.ORDER_PLACED_SUCCESSFULLY'));
            } else {
                session()->flash('error', config('constants.ORDER_PLACED_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('constants.ORDER_PLACED_FAILED'));

            throw $error;
        }
    }

    public function render(): View
    {
        $data = ($this->showInventoryGrid) ? Products::getParentOrChildSellerProductsForView(
            $this->nearBySellerId,
            search: $this->search,
            categoryId: $this->categoryId,
            status: ProductStatusEnum::ENABLE,
            orderBy: $this->orderBy ?? OrderByEnum::DESC,
            orderByPrice: $this->orderByPrice
        ) : null;

        $vans = $this->vans;
        $categories = $this->categories;

        $cartItems = $this->getCartItemsValues();
        $cartItemsCount = $this->getCartItemsCount();
        $cartTotal = $this->getCartTotal();

        return view('livewire.company.order-van-inventory-livewire', compact(
            'data',
            'vans',
            'categories',
            'cartItems',
            'cartItemsCount',
            'cartTotal'
        ));
    }
}
