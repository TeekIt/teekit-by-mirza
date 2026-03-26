<?php

namespace App\Livewire\Admin;

use App\Enums\ProductStatusEnum;
use App\Models\Products;
use App\Models\User;
use App\Services\GoogleMapServices;
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
        $product = Products::query()
            ->select(['id', 'product_name', 'feature_img', 'price'])
            ->findOrFail($productId);

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

    /*
     * CRUD Methods
     */
    // Coming soon...

    public function render(): View
    {
        $inventory = ($this->showInventoryGrid) ? Products::getParentOrChildSellerProductsForView(
            (int) $this->nearBySellerId,
            search: $this->search,
            status: ProductStatusEnum::ENABLE,
            orderBy: 'desc'
        ) : null;

        $cartItems = array_values($this->getCart());
        $cartItemsCount = array_sum(array_column($cartItems, 'qty'));
        $cartTotal = array_reduce(
            $cartItems,
            fn(float $carry, array $item): float => $carry + ((float) $item['price'] * (int) $item['qty']),
            0.0
        );

        return view('livewire.admin.add-van-inventory-livewire', compact('inventory', 'cartItems', 'cartItemsCount', 'cartTotal'));
    }
}
