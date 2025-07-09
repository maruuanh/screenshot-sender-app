<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="{{ asset('plugins/datatables/datatables.min.css') }}" rel="stylesheet"type="text/css" />
    <link href="{{ asset('plugins/bootstrap-5.2.3-dist/css/bootstrap.min.css') }}" rel="stylesheet"type="text/css" />
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>{{ $title }}</h1>

                <div class="card">
                    <div class="card-body">
                    {!! $dataTable->table() !!}
                </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('plugins/datatables/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap-5.2.3-dist/js/bootstrap.min.js') }}"></script>
    {{ $dataTable->scripts() }}
</body>
</html>