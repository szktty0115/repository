@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">note 記事 自動生成ダッシュボード</h2>
        <span class="text-muted">ジャンル: {{ $genre }} / 目安: 約{{ $targetChars }}文字</span>
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

    <div class="alert alert-info">
        note は公式の投稿 API が無いため、本機能は <strong>記事の下書き生成と管理まで</strong> です。
        生成・編集した本文を「本文をコピー」して、<a href="https://note.com/" target="_blank" rel="noopener">note の編集画面</a>に貼り付けて公開してください。
    </div>

    @unless ($generatorConfigured)
        <div class="alert alert-warning">
            ANTHROPIC_API_KEY が未設定のため、記事の自動生成は利用できません。
            設定手順は <code>docs/note-automation-setup.md</code> を参照してください。
        </div>
    @endunless

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">記事を自動生成（Claude）</h5>
            <form method="POST" action="{{ route('note.generate') }}">
                @csrf
                <div class="form-row">
                    <div class="col-md-8 form-group">
                        <label>テーマ（空欄でジャンルから自動選定）</label>
                        <input type="text" name="topic" class="form-control"
                               placeholder="例: PHP の型システムが提供する3つの安心">
                    </div>
                    <div class="col-md-2 form-group">
                        <label>本数</label>
                        <input type="number" name="count" value="1" min="1" max="3" class="form-control">
                    </div>
                    <div class="col-md-2 form-group d-flex align-items-end">
                        <button class="btn btn-success btn-block" {{ $generatorConfigured ? '' : 'disabled' }}>
                            生成する
                        </button>
                    </div>
                </div>
                <small class="text-muted">1本あたり30秒〜1分ほどかかります。完了までページを離れないでください。</small>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">下書き（{{ count($drafts) }}）</h5>
            @forelse ($drafts as $article)
                <div class="border rounded p-3 mb-3">
                    <form method="POST" action="{{ route('note.articles.update', $article) }}">
                        @csrf
                        <div class="form-group">
                            <label class="font-weight-bold">タイトル</label>
                            <input type="text" name="title" class="form-control"
                                   value="{{ $article->title }}" required>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">本文 <small class="text-muted">（{{ mb_strlen($article->body) }} 文字）</small></label>
                            <textarea name="body" id="article-body-{{ $article->id }}"
                                      class="form-control" rows="14" required>{{ $article->body }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>タグ（カンマ区切り）</label>
                            <input type="text" name="tags" class="form-control"
                                   value="{{ $article->tags }}">
                        </div>
                        <small class="text-muted d-block mb-2">
                            生成: {{ $article->created_at->format('Y/m/d H:i') }} /
                            {{ $article->source === 'ai' ? 'AI生成' : '手動' }}
                        </small>
                        <button class="btn btn-sm btn-primary" type="submit">保存</button>
                        <button class="btn btn-sm btn-info" type="button"
                                data-copy-target="article-body-{{ $article->id }}">本文をコピー</button>
                    </form>
                    <form method="POST" action="{{ route('note.articles.publish', $article) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-success">公開済みにする</button>
                    </form>
                    <form method="POST" action="{{ route('note.articles.destroy', $article) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('削除しますか?');">削除</button>
                    </form>
                </div>
            @empty
                <p class="text-muted mb-0">下書きはありません。上のフォームから生成してください。</p>
            @endforelse
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">公開済み（{{ count($published) }}）</h5>
            @forelse ($published as $article)
                <div class="border rounded p-2 mb-2">
                    <strong>{{ $article->title }}</strong>
                    <small class="text-muted ml-2">
                        公開: {{ optional($article->published_at)->format('Y/m/d') }}
                        / {{ mb_strlen($article->body) }} 文字
                    </small>
                    <textarea id="published-body-{{ $article->id }}" hidden>{{ $article->body }}</textarea>
                    <div class="mt-1">
                        <button class="btn btn-sm btn-outline-info" type="button"
                                data-copy-target="published-body-{{ $article->id }}">本文をコピー</button>
                        <form method="POST" action="{{ route('note.articles.draft', $article) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary">下書きに戻す</button>
                        </form>
                        <form method="POST" action="{{ route('note.articles.destroy', $article) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('削除しますか?');">削除</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">公開済みの記事はまだありません。</p>
            @endforelse
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('[data-copy-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.getElementById(btn.dataset.copyTarget);
            if (!target) return;
            var text = target.value;
            var originalLabel = btn.textContent;
            var done = function () {
                btn.textContent = 'コピーしました';
                setTimeout(function () { btn.textContent = originalLabel; }, 2000);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(done);
            } else {
                target.removeAttribute('hidden');
                target.select();
                document.execCommand('copy');
                target.setAttribute('hidden', '');
                done();
            }
        });
    });
</script>
@endsection
