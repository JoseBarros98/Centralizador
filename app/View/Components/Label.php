<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Label extends Component
{
    /**
     * The input field the label belongs to.
     *
     * @var string|null
     */
    public $for;

    /**
     * The label text.
     *
     * @var string|null
     */
    public $value;

    /**
     * Create a new component instance.
     *
     * @param  string|null  $for
     * @param  string|null  $value
     * @return void
     */
    public function __construct($for = null, $value = null)
    {
        $this->for = $for;
        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.label');
    }
}
