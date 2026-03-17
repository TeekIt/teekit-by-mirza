<?php

namespace App\Livewire\Admin;

use App\Enums\OrderByEnum;
use App\Exports\VansExport;
use App\Imports\VansImport;
use App\Models\Van;
use Exception;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelConstants;

class VansLivewire extends Component
{
    use WithFileUploads, WithPagination;

    public $vanId;

    public $userName;

    public $oldUserName;

    public $operative;

    public $numberPlate;

    public $oldNumberPlate;

    public $payload;

    public $width;

    public $height;

    public $length;

    public $password;

    public $excelFile;

    public $search = '';

    protected function rules(): array
    {
        return (new Van)->getValidationRules($this->vanId);
    }

    protected function messages(): array
    {
        return [
            'userName.regex' => 'Blank spaces are not allowed.',
        ];
    }

    /*
     * Custom Helpers
     */
    public function resetComponent(): void
    {
        $this->resetValidation();

        $this->reset([
            'vanId',
            'userName',
            'operative',
            'numberPlate',
            'payload',
            'width',
            'height',
            'length',
            'password',
            'excelFile',
            'search',
        ]);
    }

    public function redirectToVanInventories(int $vanId)
    {
        $this->authorize('view', Van::find($vanId));

        return $this->redirectRoute('admin.vans.inventories', ['vanId' => $vanId]);
    }
    /*
     * CRUD Methods
     */
    public function renderEditVanModal(int $id): void
    {
        $van = Van::find($id);

        $this->authorize('view', $van);

        $this->vanId = $van->id;
        $this->userName = $van->user_name;
        $this->oldUserName = $van->user_name;
        $this->operative = $van->operative;
        $this->numberPlate = $van->number_plate;
        $this->oldNumberPlate = $van->number_plate;
        $this->payload = $van->payload ?? '';
        $this->width = $van->width ?? '';
        $this->height = $van->height ?? '';
        $this->length = $van->length ?? '';
    }

    public function addVan(): void
    {
        $this->authorize('create', Van::class);

        $this->validate();

        try {
            /* Perform some operation */
            $inserted = Van::add(
                $this->userName,
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
            session()->flash('error', $error->getMessage());
        }
    }

    public function updateVan(): void
    {
        $van = Van::find($this->vanId);

        $this->authorize('update', $van);

        $this->validate();

        try {
            /* Perform some operation */
            $updated = Van::updateInfo(
                $this->vanId,
                $this->userName,
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
            session()->flash('error', $error->getMessage());
        }
    }

    public function importVans()
    {
        $this->validate([
            'excelFile' => 'required|file|mimes:csv|max:2048',
        ]);

        try {
            /* Perform some operation */
            Excel::import(new VansImport, $this->excelFile, ExcelConstants::CSV);
            /* Operation finished */
            sleep(1);
            $this->resetComponent();
            $this->dispatch('close-modal', ['id' => 'importVansModal']);

            session()->flash('success', config('constants.DATA_INSERTION_SUCCESS'));
        } catch (ExcelValidationException $error) {
            $this->dispatch('close-modal', ['id' => 'importVansModal']);

            session()->flash('error', $error->getMessage());
        } catch (Exception $error) {
            logger()->channel('importExport')->error($error->getMessage());

            $this->dispatch('close-modal', ['id' => 'importVansModal']);

            session()->flash('error', config('constants.IMPORT_FAILED'));
        }
    }

    public function exportVans()
    {
        try {
            /* Perform some operation */
            $fileName = 'vans-' . now()->format('m-d-Y:H:i:s') . '.csv';
            /* Operation finished */
            return Excel::download(new VansExport, $fileName, ExcelConstants::CSV);
        } catch (Exception $error) {
            logger()->channel('importExport')->error($error->getMessage());

            $this->dispatch('close-modal', ['id' => 'importVansModal']);

            session()->flash('error', config('constants.EXPORT_FAILED'));
        }
    }

    public function render(): View
    {
        $this->authorize('viewAny', Van::class);

        $data = Van::getAll(OrderByEnum::DESC, $this->search);

        return view('livewire.admin.vans-livewire', compact('data'));
    }
}
