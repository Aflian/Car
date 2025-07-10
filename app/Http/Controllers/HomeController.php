<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;

class HomeController extends Controller
{
   public function index(){
     $head = 'Beranda';
     $mobil = Car::all();
     return view('welcome',compact('mobil'));
   }

}
