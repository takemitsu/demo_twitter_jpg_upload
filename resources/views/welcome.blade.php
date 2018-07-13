<!DOCTYPE html>
<html>
    <head>
        <title>Twitter Upload Demo</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">
            <div class="content">


                @if (count($errors) > 0)
                <!-- upload file にエラーが有った場合 -->
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <!-- 終わったよをアレ -->
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="post" action="/upload" enctype="multipart/form-data">

                    <div class="form-group">
                        <label>アップロードファイル</label>
                        <input type="file" name="uploadfile">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">送信</button>
                    </div>
                </form>
            </div>
        </div>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    </body>
</html>
