<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class pay extends Controller
{

    public function index(){
        $payment = PaymentMethod::all();
        return view('payment.pay',compact('payment'));
    }
}
