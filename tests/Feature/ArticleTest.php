<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Support\ArticleTextImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_articles_are_visible_and_article_content_is_escaped(): void
    {
        $article = Article::create([
            'title' => 'Panduan Belajar Web',
            'slug' => 'panduan-belajar-web',
            'excerpt' => 'Mulai belajar secara bertahap.',
            'body' => '<script>alert("xss")</script>',
            'author_name' => 'Tim RuangKelas',
            'published_at' => now()->subDay(),
        ]);
        $draft = Article::create([
            'title' => 'Artikel Draf',
            'slug' => 'artikel-draf',
            'excerpt' => 'Belum diterbitkan.',
            'body' => 'Konten draf.',
            'author_name' => 'Tim RuangKelas',
        ]);
        $scheduled = Article::create([
            'title' => 'Artikel Terjadwal',
            'slug' => 'artikel-terjadwal',
            'excerpt' => 'Terbit nanti.',
            'body' => 'Konten terjadwal.',
            'author_name' => 'Tim RuangKelas',
            'published_at' => now()->addDay(),
        ]);

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertSee($article->title)
            ->assertDontSee($draft->title)
            ->assertDontSee($scheduled->title);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false);

        $this->get(route('articles.show', $draft))->assertNotFound();
        $this->get(route('articles.show', $scheduled))->assertNotFound();
    }

    public function test_article_listing_searches_published_titles_and_excerpts(): void
    {
        $matchingArticle = $this->article('Dasar Laravel', 'Belajar routing dan Blade.');
        $otherArticle = $this->article('Portofolio Digital', 'Dokumentasikan proyek pertamamu.');

        $this->get(route('articles.index', ['search' => 'routing']))
            ->assertOk()
            ->assertSee($matchingArticle->title)
            ->assertDontSee($otherArticle->title);
    }

    public function test_admin_can_upload_a_text_file_and_publish_its_content_as_an_article(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->get(route('admin.articles.create'))
            ->assertOk()
            ->assertSee('Upload dokumen artikel')
            ->assertSee('.TXT atau .DOCX');

        $this->post(route('admin.articles.store'), [
            'title' => 'Panduan Belajar Web',
            'excerpt' => 'Ringkasan dari dokumen.',
            'author_name' => 'Tim RuangKelas',
            'status' => 'published',
            'article_file' => UploadedFile::fake()->createWithContent(
                'panduan.txt',
                "\xEF\xBB\xBFParagraf pertama.\r\n\r\nParagraf kedua."
            ),
        ])->assertRedirect(route('admin.articles.index'));

        $article = Article::where('title', 'Panduan Belajar Web')->firstOrFail();
        $this->assertSame("Paragraf pertama.\n\nParagraf kedua.", $article->body);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Paragraf pertama.')
            ->assertSee('Paragraf kedua.');
    }

    public function test_admin_can_upload_and_replace_article_thumbnails_shown_on_public_pages(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->get(route('admin.articles.create'))
            ->assertOk()
            ->assertSee('Thumbnail artikel')
            ->assertSee('name="thumbnail"', false);

        $this->post(route('admin.articles.store'), [
            'title' => 'Panduan Belajar Web',
            'excerpt' => 'Ringkasan panduan belajar web.',
            'body' => 'Isi artikel.',
            'author_name' => 'Tim RuangKelas',
            'status' => 'published',
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg'),
        ])->assertRedirect(route('admin.articles.index'));

        $article = Article::where('title', 'Panduan Belajar Web')->firstOrFail();
        $originalThumbnailPath = $article->thumbnail_path;

        $this->assertNotNull($originalThumbnailPath);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'thumbnail_path' => $originalThumbnailPath]);
        Storage::disk('public')->assertExists($originalThumbnailPath);

        $thumbnailUrl = Storage::disk('public')->url($originalThumbnailPath);

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertSee($thumbnailUrl, false)
            ->assertSee('background-size: cover', false);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee($thumbnailUrl, false)
            ->assertSee('background-size: cover', false);

        $this->get(route('admin.articles.edit', $article))
            ->assertOk()
            ->assertSee('Thumbnail saat ini');

        $this->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'excerpt' => $article->excerpt,
            'body' => $article->body,
            'author_name' => $article->author_name,
            'status' => 'published',
        ])->assertRedirect(route('admin.articles.index'));

        $this->assertSame($originalThumbnailPath, $article->refresh()->thumbnail_path);

        $this->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'excerpt' => $article->excerpt,
            'body' => $article->body,
            'author_name' => $article->author_name,
            'status' => 'published',
            'thumbnail' => UploadedFile::fake()->image('thumbnail-baru.png'),
        ])->assertRedirect(route('admin.articles.index'));

        $replacementThumbnailPath = $article->refresh()->thumbnail_path;

        $this->assertNotSame($originalThumbnailPath, $replacementThumbnailPath);
        Storage::disk('public')->assertMissing($originalThumbnailPath);
        Storage::disk('public')->assertExists($replacementThumbnailPath);
    }

    public function test_deleting_an_article_also_deletes_its_thumbnail(): void
    {
        Storage::fake('public');
        $thumbnailPath = 'articles/thumbnails/article.png';
        Storage::disk('public')->put($thumbnailPath, 'image contents');
        $article = $this->article('Artikel dengan Thumbnail', 'Ringkasan artikel.');
        $article->update(['thumbnail_path' => $thumbnailPath]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->delete(route('admin.articles.destroy', $article))
            ->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        Storage::disk('public')->assertMissing($thumbnailPath);
    }

    public function test_admin_cannot_upload_a_non_image_as_an_article_thumbnail(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Artikel dengan Thumbnail Salah',
                'excerpt' => 'Ringkasan artikel.',
                'body' => 'Isi artikel.',
                'author_name' => 'Admin',
                'status' => 'draft',
                'thumbnail' => UploadedFile::fake()->createWithContent('thumbnail.txt', 'bukan gambar'),
            ])
            ->assertSessionHas('errors', fn ($errors): bool => $errors->getBag('default')->first('thumbnail')
                === 'Thumbnail harus berupa file gambar yang valid.');

        $this->assertDatabaseCount('articles', 0);
        Storage::disk('public')->assertDirectoryEmpty('articles/thumbnails');
    }

    public function test_admin_can_upload_a_word_document_and_import_its_paragraphs_as_text(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Artikel dari Word',
                'excerpt' => 'Ringkasan artikel Word.',
                'author_name' => 'Tim RuangKelas',
                'status' => 'published',
                'article_file' => UploadedFile::fake()->createWithContent(
                    'artikel.docx',
                    file_get_contents(base_path('tests/Fixtures/articles/sample-article.docx'))
                ),
            ])
            ->assertRedirect(route('admin.articles.index'));

        $article = Article::where('title', 'Artikel dari Word')->firstOrFail();
        $this->assertSame(
            "Baris pertama dari Word.\nBaris kedua\tdengan tab.\nBaris baru.",
            $article->body
        );

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Baris pertama dari Word.')
            ->assertSee('Baris kedua')
            ->assertSee('Baris baru.');

        $this->put(route('admin.articles.update', $article), [
            'title' => 'Artikel dari Word',
            'excerpt' => 'Ringkasan artikel yang diperbarui.',
            'author_name' => 'Tim RuangKelas',
            'status' => 'published',
            'article_file' => UploadedFile::fake()->createWithContent(
                'artikel-baru.txt',
                "Konten baru dari TXT.\nBaris kedua."
            ),
        ])->assertRedirect(route('admin.articles.index'));

        $this->assertSame("Konten baru dari TXT.\nBaris kedua.", $article->refresh()->body);
    }

    public function test_admin_cannot_upload_an_invalid_word_document_or_unsupported_file(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        $articleData = [
            'title' => 'Artikel Unggahan',
            'excerpt' => 'Ringkasan artikel.',
            'author_name' => 'Admin',
            'status' => 'draft',
        ];

        $this->post(route('admin.articles.store'), [
            ...$articleData,
            'article_file' => UploadedFile::fake()->createWithContent('rusak.docx', 'bukan dokumen Word'),
        ])->assertSessionHasErrors('article_file');

        $this->post(route('admin.articles.store'), [
            ...$articleData,
            'article_file' => UploadedFile::fake()->createWithContent('artikel.pdf', 'konten PDF'),
        ])->assertSessionHasErrors('article_file');

        $this->post(route('admin.articles.store'), [
            ...$articleData,
            'article_file' => UploadedFile::fake()->createWithContent('kosong.txt', ''),
        ])->assertSessionHas('errors', fn ($errors): bool => $errors->getBag('default')->first('article_file')
            === 'Dokumen tidak berisi teks artikel yang dapat dibaca.');

        $this->post(route('admin.articles.store'), [
            ...$articleData,
            'article_file' => UploadedFile::fake()->createWithContent('terlalu-panjang.txt', str_repeat('a', 50001)),
        ])->assertSessionHas('errors', fn ($errors): bool => $errors->getBag('default')->first('article_file')
            === 'Isi artikel maksimal 50.000 karakter.');

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_invalid_word_archive_returns_a_validation_error_instead_of_a_server_error(): void
    {
        try {
            (new ArticleTextImporter)->extract(
                UploadedFile::fake()->createWithContent('rusak.docx', 'bukan arsip Word')
            );
            $this->fail('Invalid Word documents should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'File DOCX tidak valid atau tidak dapat dibaca.',
                $exception->errors()['article_file'][0]
            );
        }
    }

    private function article(string $title, string $excerpt): Article
    {
        return Article::create([
            'title' => $title,
            'slug' => str($title)->slug(),
            'excerpt' => $excerpt,
            'body' => 'Isi artikel.',
            'author_name' => 'Tim RuangKelas',
            'published_at' => now()->subDay(),
        ]);
    }
}
