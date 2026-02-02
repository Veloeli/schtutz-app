<?php

namespace App\Http\Controllers;

use App\Services\ItemService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct(private ItemService $service) {}

    public function index()
    {
        $data = $this->service->getItemsForUser(auth()->user());
        return view('items.index', $data);
    }

    public function store(Request $request)
    {
        $this->service->createItem(auth()->user(), $request->all());
        return redirect()->route('items.index');
    }

    public function update(Request $request, $itemId)
    {
        $item = Item::findOrFail($itemId);
        $this->service->updateItem($item, $request->all());
        return redirect()->route('items.index');
    }
}
