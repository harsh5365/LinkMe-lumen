<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>{{ $site_title }}</title>
        <meta name="description" content="The telemedicine platform that will change healthcare." />

        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <script type="text/javascript">
        </script>
    </head>
    <body>


        <div class="app-content-body ">


            <div class="">
                <div class="wrapper text-center">
                    <h2 class="text-center">Error Logs</h2>
                </div>
                <p class="text-center"></p>
            </div>

            <form class="form-horizontal" id="send_validation" role="form" data-toggle="validator" enctype="multipart/form-data"  method="get" action="{{ url('/errorlogs') }}">

                <div class="row">
                    <div class="col-md-12">
                        Enter date (YYYY-MM-Dd): <input type="text" placeholder="YYY-MM-DD" name="indate" value="{{ $indate }}" class="form-control m-b-sm">

                        &nbsp;

                        <input type="submit" value="View" />

                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12">
                        {!! $filedata !!}

                    </div>
                </div>
            </form>
        </div>

    </body>
</html>
