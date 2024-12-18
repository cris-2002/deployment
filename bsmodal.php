<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class bsmodal extends Component
{
    /**
     * Create a new component instance.
     */
    public $id;

    public $title;

    public function __construct($id, $title)
    {
        //
        $this->id = $id;
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.bsmodal');
    }
}
