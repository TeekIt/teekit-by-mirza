<?php

namespace App\Http\Livewire\Sellers;

use App\Models\User;
use App\Models\WithdrawalRequests;
use Livewire\Component;
use Livewire\WithPagination;

class WithdrawalLivewire extends Component
{
    use WithPagination;

    public $search;

    public $amount;

    public $created_at;

    public $status;

    public $seller_id;

    public $page = 1;

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'amount' => 'numeric|between:0,999999.99',
    ];

    /*
     * Lifecycle Hooks
     */
    public function mount()
    {
        $this->seller_id = User::getSellerID();
        $this->resetAllPaginators();
    }

    /*
     * Helpers
     */
    public function updatedAmount($value)
    {
        $this->validateOnly('amount');
    }

    public function resetAllErrors()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function resetAllPaginators()
    {
        $this->resetPage('sap_products_page');
    }

    public function resetThisPage()
    {
        $this->reset([
            'search',
            'amount',
            'created_at'
        ]);
    }

    public function isAmountByIdSet()
    {
        $amount = (int) $this->amount;
        if ($amount != 0) {
            $this->resetPage();
        }

        return $amount;
    }

    /*
     * CRUD Methods
     */
    public function withdrawRequest()
    {
        $user = User::find(auth()->user()->id);
        // Check if withdrawal amount is valid
        if ($this->amount <= 0) {
            session()->flash('error', 'Withdrawal amount is not valid');
            $this->dispatchBrowserEvent('close-modal', ['id' => 'requestWithdrawModal']);

            return;
        }

        if ($this->amount > $user->pending_withdraw) {
            $this->addError('amount', 'Withdrawal amount exceeds pending balance');
            $this->dispatchBrowserEvent('close-modal', ['id' => 'requestWithdrawModal']);

            return;
        }
        // Proceed with withdrawal
        $user->pending_withdraw -= $this->amount;
        $user->total_withdraw += $this->amount;
        $user->save();

        $status = 'Pending';
        // Create withdrawal request
        $withdrawalRequest = WithdrawalRequests::add($user->id, $this->amount, $status, $user->bank_details);
        if ($withdrawalRequest) {
            session()->flash('success', 'Amount Withdrawal SuccessFull');
            $this->dispatchBrowserEvent('close-modal', ['id' => 'requestWithdrawModal']);
        } else {
            session()->flash('error', 'Amount Withdrawal Field');
            $this->dispatchBrowserEvent('close-modal', ['id' => 'requestWithdrawModal']);
        }
    }

    public function render()
    {
        $data = WithdrawalRequests::getWithdrawalRequests(
            User::getSellerID(),
            $this->search,
            $this->isAmountByIdSet(),
            $this->created_at
        )->paginate(9);

        return view('livewire.sellers.withdrawal-livewire', compact('data'));
    }
}
