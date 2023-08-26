<?php

namespace App\Http\Livewire\Admin;

use App\User;
use Livewire\Component;
use Livewire\WithPagination;
use Exception;

class ReferralCodes extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $data = User::getBuyersWithReferralCode();
        return view('livewire.admin.referral-codes', compact('data'));
    }
}
