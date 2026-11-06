<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $searchInput = $request->query('search');
        $search = is_string($searchInput) ? trim($searchInput) : '';

        return view('articles.index', [
            'articles' => Article::query()
                ->published()
                ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                }))
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->paginate(9)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->published_at !== null && $article->published_at->lte(now()), 404);

        return view('articles.show', ['article' => $article]);
    }
}
