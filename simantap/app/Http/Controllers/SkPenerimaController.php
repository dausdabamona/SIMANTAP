<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SkPenerimaController extends Controller
{
    public function index()   { return view('coming-soon', ['page' => 'SkPenerimaController']); }
    public function create()  { return view('coming-soon', ['page' => 'SkPenerimaController']); }
    public function store(Request $r)  { return back(); }
    public function show($id)   { return view('coming-soon', ['page' => 'SkPenerimaController']); }
    public function edit($id)   { return view('coming-soon', ['page' => 'SkPenerimaController']); }
    public function update(Request $r, $id) { return back(); }
    public function destroy($id) { return back(); }
}
