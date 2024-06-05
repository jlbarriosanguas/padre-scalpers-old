<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
	@include('includes.head')
    <style>
    @media (min-width: 1200px) {
        .container, .container-lg, .container-md, .container-sm, .container-xl {
            max-width: 100%;
            padding: 0 5%;
        }
    }
    </style>
</head>

<body>
	<div class="container mt-5">
		{{--
		@if (Route::has('login'))
		<div class="top-right links">
			@auth
			<a href="{{ url('/home') }}">Home</a>
			@else
			<a href="{{ route('login') }}">Login</a>
			<a href="{{ route('register') }}">Register</a>
			@endauth
		</div>
		@endif
		--}}

		<div class="content">
			<div id="applicant-view">
                <p class="h4">{{$applicant->name}} {{$applicant->surname}}<span style="float: right;"><button id="remove-applicant" type="submit" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></button></span></p>
                <div><span class="badge badge-dark">Creación: {{$applicant->created_at->format('d-m-Y')}}</span> <span class="badge badge-primary">Actualizado: {{$applicant->updated_at->format('d-m-Y')}}</span></div>

                <div class="row">
                    <div class="col-md-9">
                        <div class="card my-4">
                            <div class="card-header" style="font-weight: 400;">
                            Datos personales
                            </div>
                            <div class="card-body">
                            <blockquote class="blockquote mb-0" style="font-size: .85em; font-weight: 400;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p>Nombre: {{$applicant->name}}</p>
                                        <p>Email: {{$applicant->email}}</p>
                                        <p>Cumpleaños: {{$applicant->birthday->format('d-m-Y')}}</p>
                                        <p>País: {{$applicant->country}}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p>Apellidos: {{$applicant->surname}}</p>
                                        <p>Teléfono: {{$applicant->phone}}</p>
                                        <p>Edad: {{$applicant->birthday->age}}</p>
                                        <p>Ciudad: {{$applicant->city}}</p>
                                    </div>
                                </div>
                            </blockquote>
                            </div>
                        </div>
                        <div class="card my-4">
                            <div class="card-header" style="font-weight: 400;">
                                Oferta solicitada
                            </div>
                            <div class="card-body">
                                <blockquote class="blockquote mb-0" style="font-size: .85em; font-weight: 400;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p class="mb-0"><a href="#" class="offer-link" data-offer="{{$applicant->job_id}}">{{$applicant->job}}</a></p>
                                        </div>
                                    </div>
                                </blockquote>
                            </div>
                        </div>
                        <div class="card my-4">
                            <div class="card-header" style="font-weight: 400;">
                                Datos laborales
                            </div>
                            <div class="card-body">
                                <blockquote class="blockquote mb-0" style="font-size: .85em; font-weight: 400;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p>Estudios: {{$applicant->studies}}</p>
                                            <p>Experiencia Retail: {{$applicant->retail_exp}}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p>Disp. Desplazamiento: {{$applicant->travel_availability}}</p>
                                            <p>Disp. Horaria: {{$applicant->time_availability}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p>Última experiencia laboral: {{$applicant->last_exp}}</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p><button id="get-cv" class="btn btn-dark btn-sm" data-endpoint="{{$applicant->curriculum}}">Descargar CV</button> @if($applicant->motivation_letter) <button id="get-letter" class="btn btn-dark btn-sm" data-endpoint="{{$applicant->motivation_letter}}">Descargar Carta Motivacional</button> @endif</p>
                                        </div>
                                    </div>

                                </blockquote>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                    <img id="applicant-photo" class="img-fluid private-img-load my-4" src="{{'data:image/png;base64,'.base64_encode(Storage::disk('applicants')->get($applicant->photo))}}" />
                    </div>
                </div>
			</div>
			{{--
			<div class="links">
				<a href="https://laravel.com/docs">Documentation</a>
				<a href="https://laracasts.com">Laracasts</a>
				<a href="https://laravel-news.com">News</a>
				<a href="https://forge.laravel.com">Forge</a>
				<a href="https://github.com/laravel/laravel">GitHub</a>
			</div>
			--}}
		</div>
    </div>

    <div class="modal fade" id="remove-applicant-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border: unset;">
                <h5 class="modal-title">Eliminar candidato</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="font-weight: 400; text-align: center;">
                    Los candidatos eliminados no se pueden recuperar. ¿Estás seguro?
                </div>
                <div class="remove-modal-alert" style="font-weight: 400; padding: 0 2rem;">

                </div>
                <div class="modal-footer" style="border: unset;">
                    <button id="confirm-applicant-remove" type="button" class="btn btn-danger">Si, eliminar candidato</button>
                </div>
            </div>
        </div>
    </div>

	@include('includes.appbridge')
    <script>
    const breadcrumb = Button.create(app, {label: 'RRHH'});
    breadcrumb.subscribe(Button.Action.CLICK, () => {
        app.dispatch(Redirect.toApp({path: '/rrhh/view'}));
    });

    var myTitleBar = TitleBar.create(app, {
        title: "{{$applicant->name}} {{$applicant->surname}}",
        breadcrumbs: breadcrumb,
		buttons: {
            secondary: [feedsButton, rrhhGroupButton],
        },
    });
	</script>
	@include('includes.scripts')
	<script>
    $('#remove-applicant').click(function() {
        $('#remove-applicant-modal').modal();
    });
    $('#confirm-applicant-remove').click(function() {
        destroy_data('{{$applicant->id}}');
    });
    $('#get-cv, #get-letter').click(function() {
        event.preventDefault();
        var loc = window.location.toString();
        var params = loc.split('?')[1];
        fetch_private_file($(this).data('endpoint'), params);
    });
    $('.offer-link').click(function() {
        var offerId = $(this).attr('data-offer');
        redirect.dispatch(Redirect.Action.APP, '/rrhh/offer/' + offerId);
    });

    function fetch_private_file(filename, params)
    {
        $.ajax({
            url:"/private/" + filename + '?' + params,
            method: 'GET',
            xhrFields: {
                responseType: 'blob'
            },
            success: function (data) {
                var a = document.createElement('a');
                var url = window.URL.createObjectURL(data);
                a.href = url;
                a.download = filename;
                document.body.append(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            }
        });
    }

    function destroy_data(applicant_id)
    {
        $.ajax({
            method: 'POST',
            url:"/rrhh/remove-applicant/" + applicant_id,
            data: {
                "_token": "{{ csrf_token() }}"
            },
            success:function()
            {
                //redirect.dispatch(Redirect.Action.APP, '/rrhh/view');
                $('.remove-modal-alert').append('<div class="alert alert-primary">Candidato borrado con éxito</div>');
                $('.modal-footer > button').prop("disabled", true);
                setInterval(function(){
                    redirect.dispatch(Redirect.Action.APP, '/rrhh/applicants');
                }, 2000);
            }
        });
    }
	</script>
</body>

</html>
