<?php

namespace App\Http\Livewire\Admin;

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

    public function render()
    {
        $data = User::getBuyersWithReferralCode();

        return view('livewire.admin.referral-codes-livewire', compact('data'));
    }
}
