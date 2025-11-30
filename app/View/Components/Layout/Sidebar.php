<?php

namespace App\View\Components\Layout;

use App\Models\Member;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class Sidebar extends Component
{
    public ?Member $member;
    public string $activeRoute;

    /**
     * Create a new component instance.
     */
    public function __construct(string $activeRoute = '')
    {
        $this->activeRoute = $activeRoute;
        
        // Load authenticated member
        $this->member = auth()->user();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.layout.sidebar');
    }
}

