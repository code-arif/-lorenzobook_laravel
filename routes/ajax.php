<?php

use App\Http\Controllers\Web\Ajax\SubcategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/subcategory/{category_id}', [SubcategoryController::class, 'index'])->name('subcategory');