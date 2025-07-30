<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Kasir_MesinKasirController extends Controller
{
    public function index (){
        $title = "Mesin Kasir";
        return view('pages.kasir.mesin-kasir.index', compact('title'));
    }
}
