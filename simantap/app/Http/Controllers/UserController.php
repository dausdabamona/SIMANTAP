<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()   { return view('coming-soon', ['page' => 'UserController']); }
    public function create()  { return view('coming-soon', ['page' => 'UserController']); }
    public function store(Request $r)  { return back(); }
    public function show($id)   { return view('coming-soon', ['page' => 'UserController']); }
    public function edit($id)   { return view('coming-soon', ['page' => 'UserController']); }
    public function update(Request $r, $id) { return back(); }
    public function destroy($id) { return back(); }
}
