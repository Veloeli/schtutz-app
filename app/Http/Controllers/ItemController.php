<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        return Item::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'amount' => 'required|numeric'
        ]);
        Item::create($request->only('name', 'amount'));
        return ['success' => true];
    }

    public function update(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'required',
            'amount' => 'required|numeric'
        ]);
        $item->update($request->only('name', 'amount'));
        return ['success' => true];
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return ['success' => true];
    }
}
