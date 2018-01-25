<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organizations;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    public function index(){
        return view("org/manage",['organizations' => Auth::user()->organizations()->get()]);
    }
    public function addOrg(Request $request){
        $org =  Organizations::where('name',$request->input('name'))->get();
        Auth::user()->organizations()->attach($org[0]->id);
        return view("org/manage",['organizations' => Auth::user()->organizations()->get()]);
    }
}
