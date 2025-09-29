<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\FormManage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //boot strap
        Paginator::useBootstrap();

        View::composer('layouts.sidebar', function ($view) {
            $formManages = FormManage::orderBy('form_name')->get();
            $view->with('formManages', $formManages);
        });
    }
}
