<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SearchService;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function index(Request $request)
    {
        $query = $request->input('q', '');
        
        if (empty($query)) {
            return view('search.index', [
                'query' => '',
                'results' => null,
            ]);
        }

        $results = $this->searchService->globalSearch($query);

        return view('search.index', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}

