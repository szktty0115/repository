<!doctype html>
<html lang="ja">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!-- オリジナル CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <title>一般ユーザー編集</title>
</head>

<body class="text-center bg-light">
    <h1 class="h10 my-10">一般ユーザー編集</h1>
    @extends('layouts.app') @section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">ユーザ編集</div>
                    <div class="card-body">
                        <!-- 重要な箇所ここから -->
                        <form action="" method="post">
                            @csrf
                            <p>ID: {{ $user->id }}</p>
                            <input type="hidden" name="id" value="{{ $user->id }}" />
                            <p>名前</p>
                            <input type="text" name="name" value="{{ $user->name }}" />
                            <p>メール</p>
                            <input type="text" name="email" value="{{ $user->email }}" /><br />
                            <input type="submit" value="更新" />
                        </form>
                        <!-- 重要な箇所ここまで -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
</body>

</html>

<!-- <form class="border rounded bg-white form-login">
    <div class="form-group">
        <p>氏名</p>
        <input type="name" class="form-control my3" placeholder="お名前">
    </div>
    <div class="form-group">
        <p>生年月日</p>
        <input type="" class="form-control my3" placeholder="生年月日">
    </div>
    <div class="form-group">
        <p>電話番号</p>
        <input type="tel" class="form-control my3" placeholder="電話版号">
    </div>
    <div class="form-group pt-3">
        <p>メールアドレス</p>
        <input type="email" class="form-control my-3" placeholder="メールアドレス">
    </div>
    <div class="my-3 form-check">
        <button type="submit" class="btn btn-primary">更新</button>
    </div>
</form> -->