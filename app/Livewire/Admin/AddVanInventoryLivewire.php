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

    public function checkout(): void
    {
        /**
         * * Create a Single Action class for this whole procedure named "ProcessVanOrderAction" and call it from here by passing the cart items and the van details.
         * STEP 1:
         * Create a new "Van Order" table with the following details:
         * - 'company_id' (bigincrements)
         * - 'van_id' (bigincrements)
         * - 'order_total' (float)
         * - 'status' (string)
         * - 'van_location' (string)
         * 
         * Note: we also have to create a new table for "Van Order Items" with the following columns:
         * - 'van_order_id' (bigincrements)
         * - 'product_id' (bigincrements)
         * - 'seller_id' (bigincrements)
         * - 'price' (float)
         * - 'qty' (integer)
         * 
         * * STEP 2:
         * Send the following product details as emails to all unique sellers in the cart:
         * - 'title'
         * - 'image'
         * - 'price'
         * - 'qty'
         */
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

        $cartItemsCount = $this->getCartItemsCount();
        $cartTotal = $this->getCartTotal();

        return view('livewire.admin.add-van-inventory-livewire', compact('inventory', 'cartItems', 'cartItemsCount', 'cartTotal'));
    }
}
