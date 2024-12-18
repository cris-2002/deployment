<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class swal extends Component
{
    /**
     * Create a new component instance.
     */
    public $icon;

    public function __construct($icon)
    {
        //
        $this->icon = $icon;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.swal');
    }
}
