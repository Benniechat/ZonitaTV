<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ChannelCatalogueService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected ChannelCatalogueService $catalogueService;

    public function __construct(ChannelCatalogueService $catalogueService)
    {
        $this->catalogueService = $catalogueService;
    }

    public function show(Request $request, string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $country = $request->header('CF-IPCountry', 'NGA');
        $channels = $this->catalogueService->getCatalogue(['category' => $slug], 18, $country);
        $allCategories = Category::orderBy('name')->get();

        return view('categories.show', compact('category', 'channels', 'allCategories'));
    }
}
