<?php

namespace App\Http\Controllers;

use App\Models\NoteArticle;
use App\Services\NoteArticleGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

class NoteDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(NoteArticleGeneratorService $generator)
    {
        $drafts = NoteArticle::where('status', NoteArticle::STATUS_DRAFT)
            ->latest()->get();
        $published = NoteArticle::where('status', NoteArticle::STATUS_PUBLISHED)
            ->latest('published_at')->limit(30)->get();

        return view('note.dashboard', [
            'drafts' => $drafts,
            'published' => $published,
            'generatorConfigured' => $generator->isConfigured(),
            'genre' => config('services.note.genre'),
            'targetChars' => (int) (config('services.note.target_chars') ?: 2500),
        ]);
    }

    public function generate(Request $request, NoteArticleGeneratorService $generator)
    {
        $count = max(1, min(3, (int) $request->input('count', 1)));
        $topic = trim((string) $request->input('topic', '')) ?: null;

        // 1本あたり30秒〜程度かかるため、PHPの実行時間制限を解除する。
        @set_time_limit(0);

        try {
            for ($i = 0; $i < $count; $i++) {
                $article = $generator->generateArticle($topic);
                NoteArticle::create([
                    'title' => $article['title'],
                    'body' => $article['body'],
                    'tags' => $article['tags'],
                    'status' => NoteArticle::STATUS_DRAFT,
                    'source' => 'ai',
                ]);
            }
        } catch (Throwable $e) {
            return back()->with('error', '記事の生成に失敗しました: ' . $e->getMessage());
        }

        return back()->with('status', $count . ' 件の記事を生成しました。');
    }

    public function update(Request $request, NoteArticle $article)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'tags' => 'nullable|string|max:255',
        ]);

        $article->update($data);

        return back()->with('status', '保存しました。');
    }

    public function markPublished(NoteArticle $article)
    {
        $article->update([
            'status' => NoteArticle::STATUS_PUBLISHED,
            'published_at' => $article->published_at ?: Carbon::now(),
        ]);

        return back()->with('status', '公開済みにマークしました。');
    }

    public function markDraft(NoteArticle $article)
    {
        $article->update([
            'status' => NoteArticle::STATUS_DRAFT,
        ]);

        return back()->with('status', '下書きに戻しました。');
    }

    public function destroy(NoteArticle $article)
    {
        $article->delete();

        return back()->with('status', '削除しました。');
    }
}
