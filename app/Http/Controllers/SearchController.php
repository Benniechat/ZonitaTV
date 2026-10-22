<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ChannelCatalogueService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected ChannelCatalogueService $catalogueService;

    public function __construct(ChannelCatalogueService $catalogueService)
    {
        $this->catalogueService = $catalogueService;
    }

    public function index(Request $request)
    {
        $query = $request->input('q', '');
        $country = $request->header('CF-IPCountry', 'NGA');
        $channels = $this->catalogueService->getCatalogue(['q' => $query], 18, $country);
        $categories = Category::orderBy('name')->get();

        return view('search.index', compact('channels', 'query', 'categories'));
    }
}
