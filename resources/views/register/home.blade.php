@extends('layouts.form')

@section('style')
    <style>
        body {
            background-color: #d8e8b0;
        }

        .bg-light2 {
            background-color: #fff;
        }

        .card-header {
            background-image: url("{{ asset('images/school_309241295.jpg') }}");
            height: 420px;
            background-repeat: no-repeat;
        }

        hr {
            margin-top: 1rem;
            margin-bottom: 1rem;
            border: 0;
            border-top: 1px solid #112d53;
        }

        .div_center {
            margin: auto;
            text-align: center;

        }
    </style>
@endsection


@section('content')
    <div id="loader"></div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card bg-light2 mb-3 ">
                    <div class="card-header" style="background-color: #fff;"><div class="div_center avatar mx-auto white">
                        <img class="rounded-circle img-responsive bg-white p-3 image" style="margin-top: 300px; border:solid #eeeded 1px ;" src="{{ asset('images/infinite.jpg') }}" alt="..."
                        height="200"></div></div> 

                
                    <div class="card-body">
                        <br>
                        <div class="text-center">
                            <br>
                            <br>
                            <h3 class="card-title form_header">School Information</h3>
                        </div>
                        <br>
                       
                    </div>
                    <div class="card-footer text-center" style="background-color: #fff">

                    </div>
                </div>


            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {

        });
    </script>
@endsection
