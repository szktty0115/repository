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

    <title>一般ユーザーTOP</title>
</head>

<body class="text-center bg-light">
    <form class="border rounded bg-white form-login">
        <h1 class="h10 my-10">予約済み大会一覧</h1>
        <select class="form-control rouded-pill mb-3" id="exampleFormControlSelect">
            <option>2022/12</option>
        </select>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">ゲーム名</th>
                    <th scope="col">大会名</th>
                    <th scope="col">募集期間</th>
                    <th scope="col">上限人数</th>
                    <th scope="col">大会日時</th>
                    <th scope="col">募集要項</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col"></th>
                    <th scope="col">削除</th>
                </tr>
            </tbody>
        </table>

        <div class="my-3 form-check">
            <button type="submit" class="btn btn-primary">大会一覧</button>
            <button type="submit" class="btn btn-primary">ユーザー情報編集</button>
            <button type="submit" class="btn btn-primary">ユーザー情報削除</button>
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