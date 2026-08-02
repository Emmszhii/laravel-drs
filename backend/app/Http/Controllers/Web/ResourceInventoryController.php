<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\ResourceType;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Stock + resource-type views (Inertia page: Pages/Resources/Index.tsx,
 * subtabs "Stock" and "Resource types").
 */
class ResourceInventoryController extends Controller
{
    public function index(): Response
    {
        $stock = Resource::with(['depot', 'resourceType'])->get()->map(fn (Resource $resource) => [
            'id' => $resource->id,
            'type' => $resource->resourceType->name,
            'depot' => $resource->depot->name,
            'available' => $resource->quantity_available,
            'reserved' => $resource->quantity_reserved,
        ]);

        return Inertia::render('Resources/Index', [
            'stock' => $stock,
            'resourceTypes' => ResourceType::all(['id', 'name', 'unit']),
        ]);
    }
}
