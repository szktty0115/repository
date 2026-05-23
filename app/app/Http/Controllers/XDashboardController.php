<?php

namespace App\Http\Controllers;

use App\Models\XFollowerStat;
use App\Models\XPost;
use App\Services\PostGeneratorService;
use App\Services\XApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;

class XDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(XApiService $x, PostGeneratorService $generator)
    {
        $drafts = XPost::where('status', XPost::STATUS_DRAFT)->latest()->get();
        $scheduled = XPost::where('status', XPost::STATUS_SCHEDULED)
            ->orderBy('scheduled_at')->get();
        $posted = XPost::whereIn('status', [XPost::STATUS_POSTED, XPost::STATUS_FAILED])
            ->latest('updated_at')->limit(20)->get();

        $stats = XFollowerStat::orderBy('recorded_on')->get();
        $latestStat = $stats->last();

        $monthStart = Carbon::now()->startOfMonth();
        $baselineStat = $stats->last(function ($stat) use ($monthStart) {
            return $stat->recorded_on->lt($monthStart);
        });
        if (!$baselineStat) {
            $baselineStat = $stats->first();
        }

        $monthlyGrowth = ($latestStat && $baselineStat)
            ? $latestStat->followers_count - $baselineStat->followers_count
            : 0;
        $goalProgress = (int) max(0, min(100, round($monthlyGrowth / 500 * 100)));

        return view('x.dashboard', [
            'drafts' => $drafts,
            'scheduled' => $scheduled,
            'posted' => $posted,
            'chartLabels' => $stats->map(function ($stat) {
                return $stat->recorded_on->format('m/d');
            })->values(),
            'chartData' => $stats->pluck('followers_count')->values(),
            'latestFollowers' => $latestStat ? $latestStat->followers_count : 0,
            'monthlyGrowth' => $monthlyGrowth,
            'goalProgress' => $goalProgress,
            'canPost' => $x->canPost(),
            'canReadMetrics' => $x->canReadMetrics(),
            'generatorConfigured' => $generator->isConfigured(),
            'genre' => config('services.x.genre'),
            'xUsername' => config('services.x.username'),
            'defaultScheduleAt' => Carbon::now()->addHour()->format('Y-m-d\TH:i'),
        ]);
    }

    public function generate(Request $request, PostGeneratorService $generator)
    {
        $count = (int) $request->input('count', 5);

        try {
            $drafts = $generator->generateDrafts($count, 'X（旧Twitter）', config('services.x.genre'), 120);
        } catch (Throwable $e) {
            return back()->with('error', '投稿案の生成に失敗しました: ' . $e->getMessage());
        }

        foreach ($drafts as $body) {
            XPost::create([
                'body' => $body,
                'status' => XPost::STATUS_DRAFT,
                'source' => 'ai',
            ]);
        }

        return back()->with('status', count($drafts) . ' 件の投稿案を生成しました。');
    }

    public function storeManual(Request $request)
    {
        $data = $request->validate([
            'body' => 'required|string|max:280',
        ]);

        XPost::create([
            'body' => $data['body'],
            'status' => XPost::STATUS_DRAFT,
            'source' => 'manual',
        ]);

        return back()->with('status', '下書きを追加しました。');
    }

    public function schedule(Request $request, XPost $post)
    {
        $data = $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        $post->update([
            'status' => XPost::STATUS_SCHEDULED,
            'scheduled_at' => Carbon::parse($data['scheduled_at']),
            'error' => null,
        ]);

        return back()->with('status', '投稿を予約しました。');
    }

    public function publishNow(XPost $post, XApiService $x)
    {
        if (!$x->canPost()) {
            return back()->with('error', 'X API の認証情報が未設定のため投稿できません。');
        }

        try {
            $tweetId = $x->postTweet($post->body);
        } catch (Throwable $e) {
            $post->update([
                'status' => XPost::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', '投稿に失敗しました: ' . $e->getMessage());
        }

        $post->update([
            'status' => XPost::STATUS_POSTED,
            'posted_at' => Carbon::now(),
            'tweet_id' => $tweetId,
            'error' => null,
        ]);

        return back()->with('status', '投稿しました。');
    }

    public function destroy(XPost $post)
    {
        $post->delete();

        return back()->with('status', '削除しました。');
    }

    public function recordFollowers(Request $request)
    {
        $data = $request->validate([
            'followers_count' => 'required|integer|min:0',
            'recorded_on' => 'nullable|date',
        ]);

        $date = !empty($data['recorded_on'])
            ? Carbon::parse($data['recorded_on'])
            : Carbon::today();

        XFollowerStat::updateOrCreate(
            ['recorded_on' => $date->toDateString()],
            [
                'followers_count' => $data['followers_count'],
                'source' => 'manual',
            ]
        );

        return back()->with('status', 'フォロワー数を記録しました。');
    }
}
