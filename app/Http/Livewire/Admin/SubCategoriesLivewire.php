<?php

namespace App\Http\Livewire\Admin;

use App\Models\SubCategory;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class SubCategoriesLivewire extends Component
{
    public
        $subCategoryId,
        $name;

    public $subCategories;

    public function mount(Collection $subCategories)
    {
        $this->subCategories = $subCategories;
    }
    /* 
     * Custom Helpers
     */
    public function resetModal()
    {
        $this->resetValidation();

        $this->reset([
            'subCategoryId',
            'name',
        ]);
    }

    public function renderEditSubCategoryModal($id)
    {
        $subCategory = SubCategory::find($id);
        $this->subCategoryId = $subCategory->id;
        $this->name = $subCategory->name;
    }
     /* 
     * CRUD Methods
     */
    public function addSubCategory()
    {
        $validatedData = $this->validate([
            'name' => 'required|string|max:255',
        ]);
        $validatedData = (object) $validatedData;

        try {
            /* Perform some operation */
            $inserted = SubCategory::add(
                $validatedData->name,
                1
            );
            /* Operation finished */
            sleep(1);
            $this->resetModal();
            $this->dispatchBrowserEvent('close-modal', ['id' => 'addSubCategoryModal']);

            if ($inserted) {
                session()->flash('success', config('constants.DATA_INSERTION_SUCCESS'));
            } else {
                session()->flash('error', config('constants.INSERTION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function updateSubCategoryName()
    {
        $this->validate([
            'name' => 'required|string',
        ]);

        try {
            /* Perform some operation */
            $updated = SubCategory::updateInfo(
                $this->subCategoryId,
                $this->name
            );
            /* Operation finished */
            sleep(1);
            $this->dispatchBrowserEvent('close-modal', ['id' => 'editSubCategoryModal']);

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
        return view('livewire.admin.sub-categories-livewire');
    }
}
