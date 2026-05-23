@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">X SNS 自動運用ダッシュボード</h2>
        <span class="text-muted">ジャンル: {{ $genre }}</span>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @unless ($canPost && $generatorConfigured && $canReadMetrics)
        <div class="alert alert-warning">
            <strong>セットアップが未完了です。</strong>
            <ul class="mb-1">
                @unless ($generatorConfigured)
                    <li>ANTHROPIC_API_KEY が未設定です（投稿案の自動生成に必要）。</li>
                @endunless
                @unless ($canPost)
                    <li>X API の認証情報が未設定です（自動投稿に必要）。</li>
                @endunless
                @unless ($canReadMetrics)
                    <li>フォロワー数の自動取得が未設定です（手動入力でも推移グラフは利用できます）。</li>
                @endunless
            </ul>
            設定手順は <code>docs/x-automation-setup.md</code> を参照してください。
        </div>
    @endunless

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">今月のフォロワー増加（目標 +500）</h5>
            <p class="mb-1">
                現在: <strong>{{ number_format($latestFollowers) }}</strong> フォロワー
                / 今月の増加: <strong>{{ $monthlyGrowth >= 0 ? '+' : '' }}{{ number_format($monthlyGrowth) }}</strong>
            </p>
            <div class="progress" style="height: 24px;">
                <div class="progress-bar bg-info" role="progressbar"
                     style="width: {{ $goalProgress }}%;"
                     aria-valuenow="{{ $goalProgress }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $goalProgress }}%
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8 mb-3 mb-md-0">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">フォロワー数の推移</h5>
                    @if (count($chartLabels) > 0)
                        <canvas id="followerChart" height="120"></canvas>
                    @else
                        <p class="text-muted mb-0">
                            まだ記録がありません。右のフォームから入力するか、
                            <code>php artisan x:snapshot-followers</code> で自動記録できます。
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">フォロワー数を記録</h5>
                    <form method="POST" action="{{ route('x.followers.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>日付</label>
                            <input type="date" name="recorded_on" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label>フォロワー数</label>
                            <input type="number" name="followers_count" class="form-control" min="0" required>
                        </div>
                        <button class="btn btn-primary btn-block">記録する</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">投稿案を自動生成（Claude）</h5>
                    <form method="POST" action="{{ route('x.generate') }}" class="form-inline">
                        @csrf
                        <label class="mr-2">件数</label>
                        <input type="number" name="count" value="5" min="1" max="15"
                               class="form-control mr-2" style="width: 90px;">
                        <button class="btn btn-success" {{ $generatorConfigured ? '' : 'disabled' }}>
                            生成する
                        </button>
                    </form>
                    @unless ($generatorConfigured)
                        <small class="text-muted">ANTHROPIC_API_KEY を設定すると利用できます。</small>
                    @endunless
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">下書きを手動で追加</h5>
                    <form method="POST" action="{{ route('x.posts.store') }}">
                        @csrf
                        <div class="form-group">
                            <textarea name="body" class="form-control" rows="2" maxlength="280"
                                      placeholder="ツイート本文" required></textarea>
                        </div>
                        <button class="btn btn-outline-primary">追加</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">下書き（{{ count($drafts) }}）</h5>
            @forelse ($drafts as $post)
                <div class="border rounded p-2 mb-2">
                    <p class="mb-1" style="white-space: pre-wrap;">{{ $post->body }}</p>
                    <small class="text-muted">
                        {{ mb_strlen($post->body) }} 文字 / {{ $post->source === 'ai' ? 'AI生成' : '手動' }}
                    </small>
                    <div class="d-flex flex-wrap align-items-start mt-2">
                        <form method="POST" action="{{ route('x.posts.schedule', $post) }}"
                              class="form-inline mr-2 mb-1">
                            @csrf
                            <input type="datetime-local" name="scheduled_at"
                                   class="form-control form-control-sm mr-1"
                                   value="{{ $defaultScheduleAt }}" required>
                            <button class="btn btn-sm btn-primary">予約</button>
                        </form>
                        <form method="POST" action="{{ route('x.posts.publish', $post) }}" class="mr-2 mb-1">
                            @csrf
                            <button class="btn btn-sm btn-success" {{ $canPost ? '' : 'disabled' }}>
                                今すぐ投稿
                            </button>
                        </form>
                        <form method="POST" action="{{ route('x.posts.destroy', $post) }}" class="mb-1">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">削除</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">下書きはありません。</p>
            @endforelse
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">予約済み（{{ count($scheduled) }}）</h5>
            @forelse ($scheduled as $post)
                <div class="border rounded p-2 mb-2">
                    <p class="mb-1" style="white-space: pre-wrap;">{{ $post->body }}</p>
                    <small class="text-muted">予約: {{ $post->scheduled_at->format('Y/m/d H:i') }}</small>
                    <div class="mt-2">
                        <form method="POST" action="{{ route('x.posts.publish', $post) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success" {{ $canPost ? '' : 'disabled' }}>
                                今すぐ投稿
                            </button>
                        </form>
                        <form method="POST" action="{{ route('x.posts.destroy', $post) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">取消</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">予約済みの投稿はありません。</p>
            @endforelse
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">投稿履歴</h5>
            @forelse ($posted as $post)
                <div class="border rounded p-2 mb-2">
                    <p class="mb-1" style="white-space: pre-wrap;">{{ $post->body }}</p>
                    @if ($post->status === 'posted')
                        <small class="text-success">
                            投稿済み {{ optional($post->posted_at)->format('Y/m/d H:i') }}
                            @if ($post->tweet_id && $xUsername)
                                ·
                                <a href="https://x.com/{{ $xUsername }}/status/{{ $post->tweet_id }}"
                                   target="_blank" rel="noopener">ツイートを表示</a>
                            @endif
                        </small>
                    @else
                        <small class="text-danger">失敗: {{ $post->error }}</small>
                    @endif
                </div>
            @empty
                <p class="text-muted mb-0">まだ投稿履歴はありません。</p>
            @endforelse
        </div>
    </div>
</div>

@if (count($chartLabels) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    new Chart(document.getElementById('followerChart'), {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'フォロワー数',
                data: @json($chartData),
                borderColor: '#1d9bf0',
                backgroundColor: 'rgba(29, 155, 240, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: false } }
        }
    });
</script>
@endif
@endsection
