<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveArticleRequest;
use App\Models\Article;
use App\Support\ArticleTextImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $searchInput = $request->query('search');
        $search = is_string($searchInput) ? trim($searchInput) : '';

        return view('admin.articles.index', [
            'articles' => Article::query()
                ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('author_name', 'like', "%{$search}%");
                }))
                ->orderByRaw('published_at IS NULL DESC')
                ->orderByDesc('published_at')
                ->latest('id')
                ->paginate(10)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.articles.form', ['article' => new Article]);
    }

    public function store(SaveArticleRequest $request, ArticleTextImporter $textImporter): RedirectResponse
    {
        $data = $request->safe()->only(['title', 'excerpt', 'body', 'author_name']);
        $data['body'] = $this->articleBody($request, $textImporter);
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['published_at'] = $request->validated('status') === 'published' ? now() : null;
        $thumbnailPath = $this->storeThumbnail($request);

        if ($thumbnailPath !== null) {
            $data['thumbnail_path'] = $thumbnailPath;
        }

        Article::create($data);

        return to_route('admin.articles.index')->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.form', ['article' => $article]);
    }

    public function update(
        SaveArticleRequest $request,
        Article $article,
        ArticleTextImporter $textImporter
    ): RedirectResponse {
        $data = $request->safe()->only(['title', 'excerpt', 'body', 'author_name']);
        $oldThumbnailPath = null;
        $thumbnailPath = $this->storeThumbnail($request);

        if ($thumbnailPath !== null) {
            $oldThumbnailPath = $article->thumbnail_path;
            $data['thumbnail_path'] = $thumbnailPath;
        }

        $article->fill($data);
        $article->body = $this->articleBody($request, $textImporter);
        $article->published_at = $request->validated('status') === 'published'
            ? ($article->published_at ?? now())
            : null;
        $article->save();
        $this->deleteThumbnail($oldThumbnailPath);

        return to_route('admin.articles.index')->with('success', 'Perubahan artikel berhasil disimpan.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $thumbnailPath = $article->thumbnail_path;
        $article->delete();
        $this->deleteThumbnail($thumbnailPath);

        return to_route('admin.articles.index')->with('success', 'Artikel berhasil dihapus.');
    }

    private function storeThumbnail(SaveArticleRequest $request): ?string
    {
        if (! $request->hasFile('thumbnail')) {
            return null;
        }

        $path = $request->file('thumbnail')->store('articles/thumbnails', 'public');

        if (! $path) {
            throw new RuntimeException('Thumbnail artikel gagal disimpan.');
        }

        return $path;
    }

    private function deleteThumbnail(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path) && ! Storage::disk('public')->delete($path)) {
            throw new RuntimeException('Thumbnail artikel gagal dihapus dari penyimpanan.');
        }
    }

    private function articleBody(SaveArticleRequest $request, ArticleTextImporter $textImporter): string
    {
        if ($request->hasFile('article_file')) {
            return $textImporter->extract($request->file('article_file'));
        }

        return $request->validated('body');
    }

    private function uniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'artikel';
        $slug = $baseSlug;
        $suffix = 2;

        while (Article::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
