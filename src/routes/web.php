<?php

use Illuminate\Support\Facades\Route;

Route::get('arpoone/webhook', function (\Illuminate\Http\Request $request) {
    dd($request->all());
});