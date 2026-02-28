<?php

namespace App\View\Components;

use Illuminate\View\Component;

class PageTitle extends Component
{
    public string $title;
    public array $breadcrumbs;

    public function __construct(string $title, array $breadcrumbs = [])
    {
        $this->title = $title;
        $this->breadcrumbs = $breadcrumbs;
    }

    public function render()
    {
        return view('components.page-title');
    }
}
