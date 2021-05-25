<?php

namespace App\View\Components;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NoPosts extends Component
{
   /**
    * Create a new component instance.
    *
    * @return void
    */
   public function __construct()
   {
      //
   }

   /**
    * Get the view / contents that represent the component.
    *
    * @return \Illuminate\Contracts\View\View|\Closure|string
    */
   public function render(): Factory | View | Application
   {
      return view('components.no-posts');
   }
}