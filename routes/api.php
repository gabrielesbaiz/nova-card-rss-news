<?php

use Illuminate\Support\Facades\Route;
use Gabrielesbaiz\NovaCardRssNews\Http\Controllers\NewsController;
use Gabrielesbaiz\NovaCardRssNews\Http\Controllers\SourcesController;

Route::get('news', [NewsController::class, 'index']);
Route::get('sources', [SourcesController::class, 'index']);
