<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ItemService;
use Illuminate\Http\Request;
use App\Models\Item;

class ItemApiController extends Controller
{
    public function __construct(private ItemService $service) {}

    public function index()
    {
        return response()->json(
            $this->service->getItemsForUser(auth()->user())
        );
    }

    public function store(Request $request)
    {
        $item = $this->service->createItem(auth()->user(), $request->all());
        return response()->json(['success' => true, 'item' => $item]);
    }

    public function update(Request $request, Item $item)
    {
        $this->service->updateItem($item, $request->all());
        return response()->json(['success' => true]);
    }
}
