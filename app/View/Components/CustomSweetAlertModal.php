<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CustomSweetAlertModal extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $alertIconHTML,
        public string $alertHeading,
        public string $msg,
        public string $confirmButtonText = 'Yes',
        public string $cancelButtonText = 'No',
        public string $confirmButtonFunction,
        public string $cancelButtonFunction,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.custom-sweet-alert-modal', [
            'alertIconHTML' => $this->alertIconHTML,
            'alertHeading' => $this->alertHeading,
            'msg' => $this->msg,
            'confirmButtonText' => $this->confirmButtonText,
            'cancelButtonText' => $this->cancelButtonText,
            'confirmButtonFunction' => $this->confirmButtonFunction,
            'cancelButtonFunction' => $this->cancelButtonFunction,
        ]);
    }
}
