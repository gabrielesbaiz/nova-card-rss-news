<?php

use Illuminate\Support\Facades\Route;
use Gabrielesbaiz\NovaCardRssNews\Http\Controllers\NewsController;

Route::get('news', [NewsController::class, 'index']);
