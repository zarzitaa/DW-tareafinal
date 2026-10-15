<?php

use App\Http\Controllers\AttackOnTitanController;
use Illuminate\Support\Facades\Route;

Route::get('/attack-on-titan/{collection}', [AttackOnTitanController::class, 'index'])
    ->whereIn('collection', AttackOnTitanController::COLLECTIONS);
