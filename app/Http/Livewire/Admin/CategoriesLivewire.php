<?php

namespace App\Http\Livewire\Admin;

use App\Categories;
use App\Services\ImageServices;
use Exception;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CategoriesLivewire extends Component
{
    use WithPagination, WithFileUploads;

    public
        $image,
        $name;

    protected $paginationTheme = 'bootstrap';
    /* 
     * Custom Helpers
     */
    public function resetModal()
    {
        $this->resetValidation();

        $this->reset([
            'image',
            'name',
        ]);
    }
    /* 
     * CRUD Methods
     */
    public function addCategory()
    {
        $validatedData = $this->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png|max:1024',
            'name' => 'required|string',
        ]);

        try {
            /* Perform some operation */
            $inserted = Categories::add(
                $validatedData['name'],
                ImageServices::uploadLivewireImg($this->image)
            );
            /* Operation finished */
            sleep(1);
            $this->resetModal();
            $this->dispatchBrowserEvent('close-modal', ['id' => 'addCategoryModal']);

            if ($inserted) {
                session()->flash('success', config('constants.DATA_INSERTION_SUCCESS'));
            } else {
                session()->flash('error', config('constants.INSERTION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function updateCategory()
    {
        $this->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png|max:1024',
            'name' => 'required|string',
        ]);

        try {
            /* Perform some operation */
            // $updated = User::updateInfo(
            //     $this->user_id,
            //     name: $this->name
            // );

            $updated = true;
            /* Operation finished */
            $this->resetModal();
            sleep(1);

            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function render()
    {
        $data = Categories::getCategoriesForView(columns: ['*'], orderBy: 'desc');

        return view('livewire.admin.categories-livewire', compact('data'));
    }
}
