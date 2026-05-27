<?php

namespace App\Livewire\Admin;

use Illuminate\Contracts\View\View;
use App\Models\Categories;
use App\Models\SubCategory;
use Exception;
use Livewire\Component;

class SubCategoriesLivewire extends Component
{
    public $subCategoryId;

    public $name;

    public $category;

    /*
    * Livewire Built-in Properties
    */
    protected $listeners = [
        'refreshThisComponent' => '$refresh',
    ];

    public function mount(Categories $category)
    {
        $this->category = $category;
    }

    /*
     * Custom Helpers
     */
    public function resetComponent()
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
                $this->category->id
            );
            /* Operation finished */
            sleep(1);
            $this->dispatch(event: 'refreshThisComponent')->self();
            $this->resetComponent();
            $this->dispatch('close-modal', ['id' => 'addSubCategoryModal'.$this->category->id]);

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
            $this->dispatch(event: 'refreshThisComponent')->self();
            $this->dispatch('close-modal', ['id' => 'editSubCategoryModal'.$this->category->id]);

            if ($updated) {
                session()->flash('success', config('constants.DATA_UPDATED_SUCCESS'));
            } else {
                session()->flash('error', config('constants.UPDATION_FAILED'));
            }
        } catch (Exception $error) {
            session()->flash('error', $error);
        }
    }

    public function render(): View
    {
        return view('livewire.admin.sub-categories-livewire');
    }
}
