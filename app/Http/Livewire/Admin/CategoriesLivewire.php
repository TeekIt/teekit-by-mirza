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
        $categoryId,
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
            'categoryId',
            'image',
            'name',
        ]);
    }
    /* 
     * CRUD Methods
     */
    public function renderEditCategoryModal($id)
    {
        $category = Categories::find($id);
        $this->categoryId = $category->id;
        $this->name = $category->category_name;
        $this->image = $category->category_image;
    }
    
    public function addCategory()
    {
        $validatedData = $this->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,jpg,png|max:100',
        ]);
        $validatedData = (object) $validatedData;

        try {
            /* Perform some operation */
            $inserted = Categories::add(
                $validatedData->name,
                ImageServices::uploadLivewireImg($validatedData->image)
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

    public function updateCategoryImage()
    {
        $this->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png|max:100',
        ]);

        try {
            /* Perform some operation */
            $updated = Categories::updateInfo(
                $this->categoryId,
                categoryImage: ImageServices::uploadLivewireImg($this->image)
            );
            /* Operation finished */
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

    public function updateCategoryName()
    {
        $this->validate([
            'name' => 'required|string',
        ]);

        try {
            /* Perform some operation */
            $updated = Categories::updateInfo(
                $this->categoryId,
                categoryName: $this->name
            );
            /* Operation finished */
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
        $data = Categories::getCategoriesForView();

        return view('livewire.admin.categories-livewire', compact('data'));
    }
}
