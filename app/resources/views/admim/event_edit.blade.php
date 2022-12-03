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

    <title>大会内容編集（主催者）</title>
</head>

<body class="text-center bg-light">
    <h1 class="h10 my-10">大会内容編集</h1>

    <form class="border rounded bg-white form-login">
        <div class="form-group">
            <p>ゲーム名</p>
            <input type="name" class="form-control my3" placeholder="ゲーム名">
        </div>
        <div class="form-group">
            <p>大会名</p>
            <input type="" class="form-control my3" placeholder="大会名">
        </div>
        <div class="form-group">
            <p>募集期間</p>
            <input type="tel" class="form-control my3" placeholder="募集期間">
        </div>
        <div class="form-group">
            <p>上限人数</p>
            <input type="tel" class="form-control my3" placeholder="上限人数">
        </div>
        <div class="form-group">
            <p>大会日時</p>
            <input type="tel" class="form-control my3" placeholder="大会日時">
        </div>
        <div class="form-group">
            <p>募集要項</p>
            <input type="tel" class="form-control my3" placeholder="募集要項">
        </div>

        <div class="my-3 form-check">
            <button type="submit" class="btn btn-primary">更新</button>
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