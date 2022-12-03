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

    <title>新規ユーザー登録（主催者）</title>
</head>

<body class="text-center bg-light">
    <h1 class="h10 my-10">新規ユーザー登録（主催者）</h1>
    <form class="border rounded bg-white form-login">
        <div class="form-group">
            <p>会社名</p>
            <input type="name" class="form-control my3" placeholder="会社名">
        </div>
        <div class="form-group">
            <p>主催者住所</p>
            <input type="" class="form-control my3" placeholder="住所">
        </div>
        <div class="form-group">
            <p>主催者電話番号</p>
            <input type="tel" class="form-control my3" placeholder="電話版号">
        </div>
        <div class="form-group pt-3">
            <p>主催者メールアドレス</p>
            <input type="email" class="form-control my-3" placeholder="メールアドレス">
        </div>
        <div class="my-3 form-check">
            <button type="submit" class="btn btn-primary">確認</button>
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