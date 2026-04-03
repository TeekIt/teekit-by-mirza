<?php

namespace App\Livewire\Admin;

use App\Actions\VanInventoryOrder\ProcessVanInventoryOrderAction;
use App\Enums\OrderTypeEnum;
use App\Enums\ProductStatusEnum;
use App\Models\Products;
use App\Models\User;
use App\Services\GoogleMapServices;
use Exception;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class AddVanInventoryLivewire extends Component
{
    use WithPagination;

    public int $userId = 0;

    public string $vanLocation = '';

    public string $vanCity = '';

    public float $vanLat = 0.0;

    public float $vanLon = 0.0;

    public int $vanId = 0;

    public int $nearBySellerId = 0;

    public array $nearbySellers = [];

    public string $search = '';

    public bool $showInventoryGrid = false;

    public const CART_SESSION_KEY = 'add_van_inventory_cart';

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

    /*
    * Lifecycle Hooks
    */
    public function mount(): void
    {
        $this->userId = User::getAuthUser()->id;
        $this->vanId = request()->query('vanId', 0);
    }

    /*
     * Custom Helpers
     */
    public function updatedVanLocation(): void
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
            $this->vanLocation = $data['pickupAddress'];
        }

        if (isset($data['pickupCity'])) {
            $this->vanCity = $data['pickupCity'];
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

    public function vanLocationChanged(): void
    {
        $sellersOfTheSameCity = $this->getSellersOfSameCity();
        $this->nearbySellers = GoogleMapServices::getNearBySellers(
            $this->vanLat,
            $this->vanLon,
            $sellersOfTheSameCity,
            $this->userId,
        );
    }

    public function performSearch(): void
    {
        $this->validate([
            'vanLocation' => 'required|string',
            'nearBySellerId' => 'required|integer|exists:users,id',
        ]);

        // $this->resetPage();

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
    public function checkout(OrderTypeEnum $vanInventoryOrderType): void
    {
        try {
            $cartItems = $this->getCartItemsValues();

            if (empty($cartItems)) {
                session()->flash('error', 'Cart is empty.');
                return;
            }

            /* Perform checkout operation */
            $vanInventoryOrderPlaced = (new ProcessVanInventoryOrderAction())->execute(
                $this->userId,
                $this->vanId,
                $this->vanLocation,
                $vanInventoryOrderType,
                $cartItems,
                $this->getCartTotal()
            );
            /* Operation finished */
            sleep(1);

            if ($vanInventoryOrderPlaced) {
                session()->forget(self::CART_SESSION_KEY);
                session()->flash('success', config('constants.ORDER_PLACED_SUCCESSFULLY'));
            } else {
                session()->flash('error', config('constants.ORDER_PLACED_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('constants.ORDER_PLACED_FAILED'));
        }
    }

    public function render(): View
    {
        $inventory = ($this->showInventoryGrid) ? Products::getParentOrChildSellerProductsForView(
            (int) $this->nearBySellerId,
            search: $this->search,
            status: ProductStatusEnum::ENABLE,
            orderBy: 'desc'
        ) : null;

        $cartItems = $this->getCartItemsValues();
        $cartItemsCount = $this->getCartItemsCount();
        $cartTotal = $this->getCartTotal();

        return view('livewire.admin.add-van-inventory-livewire', compact(
            'inventory',
            'cartItems',
            'cartItemsCount',
            'cartTotal'
        ));
    }
}
