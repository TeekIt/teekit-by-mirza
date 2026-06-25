<?php

namespace App\Livewire\Admin;

use Illuminate\Contracts\View\View;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ReferralCodesLivewire extends Component
{
    use WithPagination;

    /*
    * Livewire Built-in Properties
    */
    protected $paginationTheme = 'bootstrap';

    public function render(): View
    {
        $data = User::getBuyersWithReferralCode();

        return view('livewire.admin.referral-codes-livewire', compact('data'));
    }
}
