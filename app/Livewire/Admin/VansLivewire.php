<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Collection;
use Livewire\Component;

class VansLivewire extends Component
{
    public string $search = '';

    public ?int $vanId = null;

    public string $vanName = '';

    public string $operative = '';

    public string $numberPlate = '';

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
            'vanName',
            'operative',
            'numberPlate',
        ]);
    }

    public function renderEditModal(int $id): void
    {
        $allVans = $this->getDummyVans();
        $van = $allVans->firstWhere('id', $id);

        if ($van) {
            $this->vanId = $van['id'];
            $this->vanName = $van['name'];
            $this->operative = $van['operative'];
            $this->numberPlate = $van['number_plate'];
        }
    }

    public function addVan(): void
    {
        // Placeholder for add van logic
        // TODO: Implement database insertion when Vans model is created
        $this->resetComponent();
    }

    protected function getDummyVans(): Collection
    {
        return collect([
            [
                'id' => 1,
                'name' => 'Ford Transit Cargo XL',
                'operative' => 'Ali Raza',
                'number_plate' => 'LD23 ABC',
            ],
            [
                'id' => 2,
                'name' => 'Mercedes Sprinter Pro',
                'operative' => 'Usman Tariq',
                'number_plate' => 'MN72 XYZ',
            ],
        ]);
    }

    public function render()
    {
        $searchValue = trim(mb_strtolower($this->search));

        $data = $this->getDummyVans()->filter(function (array $van) use ($searchValue): bool {
            if ($searchValue === '') {
                return true;
            }

            return str_contains(mb_strtolower($van['name']), $searchValue)
                || str_contains(mb_strtolower($van['operative']), $searchValue)
                || str_contains(mb_strtolower($van['number_plate']), $searchValue);
        })->values();

        return view('livewire.admin.vans-livewire', compact('data'));
    }
}
