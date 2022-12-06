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

    <title>大会一覧</title>
</head>
<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">大会予約サイト
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav">
                    <a class="nav-link" href="#">1</a>
                    <a class="nav-link" href="#">2</a>
                    <a class="nav-link" href="#">3</a>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            ログイン/ログアウト
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <li><a class="dropdown-item" href="#">ログイン</a></li>
                            <li><a class="dropdown-item" href="#">ログアウト</a></li>
                        </ul>
                    </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>

<body class="text-center bg-light">
    <form class="border rounded bg-white form-login">
        <h1 class="h10 mx-10 my-10">募集中の大会一覧</h1>
        <div class="input-group my-5">
            <input type="text" class="form-control" placeholder="キーワードを入力">
            <button class="btn btn-outline-success" type="button" id="button-addon2"><i class="fas fa-search"></i>検索</button>
        </div>
        <table class="table table-bordered my-5">
            <tr>
                <th scope="col">大会イメージ画像</th>
                <td scope="col"><img src="img/sample1.jpg"></td>
            </tr>
            <tr>
                <th scope="col">ゲーム名</th>
                <th scope="col">大会名</th>
                <th scope="col">募集期間</th>
                <th scope="col">上限人数</th>
                <th scope="col">大会日時</th>
                <th scope="col">募集要項</th>
            </tr>
            <tr>
                <th scope="col">a</th>
                <th scope="col">a</th>
                <th scope="col">a</th>
                <th scope="col">a</th>
                <th scope="col">a</th>
                <th scope="col">a</th>
            </tr>
        </table>
        <div class="my-4 form-check">
            <button type="submit" class="btn btn-primary">応募</button>
        </div>
    </form>
    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    -->
</body>

</html>