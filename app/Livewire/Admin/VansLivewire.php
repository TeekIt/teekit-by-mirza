<?php

namespace App\Livewire\Admin;

use App\Enums\OrderByEnum;
use App\Models\Van;
use Exception;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class VansLivewire extends Component
{
    use WithPagination;

    public $vanId;


    public $username;

    public $operative;

    public $numberPlate;

    public $payload;

    public $width;

    public $height;

    public $length;

    public $password;

    public $search = '';


    protected function rules(): array
    {
        return [
            'username' => 'required|string|max:20',
            'operative' => 'required|string|max:20',
            'numberPlate' => 'required|string|max:20',
            'payload' => 'required|integer|min:1',
            'width' => 'required|numeric|min:1.0',
            'height' => 'required|numeric|min:1.0',
            'length' => 'required|numeric|min:1.0',
            'password' => 'required|string|min:6',
        ];
    }

    /**
     * Reset to the first page in future if pagination is introduced.
     */
    public function updatingSearch(): void
    {
        // Intentionally left blank for now. Kept to mirror other admin components.
    }

    public function resetComponent(): void
    {
        $this->resetValidation();

        $this->reset([
            'vanId',
            'search',
            'username',
            'operative',
            'numberPlate',
            'payload',
            'width',
            'height',
            'length',
            'password',
        ]);
    }

    public function renderEditVanModal(int $id): void
    {
        $van = Van::find($id);
        $this->vanId = $van->id;
        $this->username = $van->username;
        $this->operative = $van->operative;
        $this->numberPlate = $van->number_plate;
        $this->payload = $van->payload ?? '';
        $this->width = $van->width ?? '';
        $this->height = $van->height ?? '';
        $this->length = $van->length ?? '';
        $this->password = $van->password;
    }

    public function addVan(): void
    {
        $this->validate();

        try {
            /* Perform some operation */
            $inserted = Van::add(
                $this->username,
                $this->operative,
                $this->numberPlate,
                $this->payload,
                $this->width,
                $this->height,
                $this->length,
                $this->password
            );
            /* Operation finished */
            sleep(1);
            $this->resetComponent();
            $this->dispatch('close-modal', ['id' => 'addVanModal']);

            if ($inserted) {
                session()->flash('success', config('constants.DATA_INSERTION_SUCCESS'));
            } else {
                session()->flash('error', config('constants.INSERTION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('constants.INVALID_DATA'));
        }
    }

    public function updateVan(): void
    {
        $this->validate();

        try {
            /* Perform some operation */
            $updated = Van::updateInfo(
                $this->vanId,
                $this->username,
                $this->operative,
                $this->numberPlate,
                $this->payload,
                $this->width,
                $this->height,
                $this->length,
                $this->password
            );
            /* Operation finished */
            sleep(1);
            $this->resetComponent();
            $this->dispatch('close-modal', ['id' => 'editVanModal']);

            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            report($error);
            session()->flash('error', config('constants.INVALID_DATA'));
        }
    }

    public function render(): View
    {
        $data = Van::getAll(OrderByEnum::DESC, $this->search);

        return view('livewire.admin.vans-livewire', compact('data'));
    }
}
