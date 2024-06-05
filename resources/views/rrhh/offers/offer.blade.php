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
			<div id="offer-view">
                <p class="h4">{{$offer->title}} <a href="https://scalperscompany.com/pages/careers?offerid={{$offer->id}}" target="_blank" style="font-size: .5em; vertical-align: super;"><i class="fa fa-external-link" aria-hidden="true"></i></a><span style="float: right;"><button id="edit-offer" type="submit" class="btn btn-dark">Editar oferta</button> <button id="remove-offer" type="submit" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></button></span></p>
                <div><span class="badge badge-dark">Creación: {{$offer->created_at->format('d-m-Y')}}</span> <span class="badge badge-primary">Dpto: {{$offer->department}}</span> <span class="badge badge-info">Ciudad: {{$offer->city}}</span></div>
                <div class="card mt-4 mb-4">
                    <div class="card-header" style="font-weight: 400;">
                      Cuerpo de la oferta
                    </div>
                    <div class="card-body">
                      <blockquote class="blockquote mb-0" style="font-size: .85em; font-weight: 400;">
                        {!! $offer->body !!}
                      </blockquote>
                    </div>
                </div>
                <p class="h6">Candidatos ({{$applicant_count}})</p>
                <table class="table mt-4 text-center">
					<thead class="thead-dark">
						<tr>
                            <th scope="col">Nombre</th>
                            <th scope="col">Ciudad</th>
                            <th scope="col">Email</th>
                            <th scope="col">Estudios</th>
                            <th scope="col">Exp. Retail</th>
                            <th scope="col">Disp. Horaria</th>
                            <th scope="col">Disp.Viaje</th>
                            <th scope="col">Última exp.</th>
						</tr>
					</thead>
					<tbody style="font-size: .85em">
                        @foreach($applicants as $applicant)
                        <tr>
                        <td><a href="#" class="applicant-link" data-applicant-id="{{$applicant->id}}">{{$applicant->name}} {{$applicant->surname}}</a></td>
                            <td>{{$applicant->city}}</td>
                            <td>{{$applicant->email}}</td>
                            <td>{{$applicant->studies}}</td>
                            <td>{{$applicant->retail_exp}}</td>
                            <td>{{$applicant->time_availability}}</td>
                            <td>{{$applicant->travel_availability}}</td>
                            <td>{{$applicant->last_exp}}</td>
                            {{--<td><i class="fa fa-eye" aria-hidden="true"></i></td>--}}
                        </tr>
                        @endforeach
					</tbody>
				</table>
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

    <div class="modal fade" id="remove-offer-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" style="border: unset;">
                <h5 class="modal-title">Eliminar oferta</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="font-weight: 400; text-align: center;">
                    Las ofertas eliminadas no se pueden recuperar. Todos los candidatos a la oferta serán desvinculados. ¿Estás seguro?
                </div>
                <div class="remove-modal-alert" style="font-weight: 400; padding: 0 2rem;">

                </div>
                <div class="modal-footer" style="border: unset;">
                    <button id="confirm-offer-remove" type="button" class="btn btn-danger">Si, eliminar oferta</button>
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
        title: "{{$offer->title}}",
        breadcrumbs: breadcrumb,
		buttons: {
            secondary: [feedsButton, rrhhGroupButton],
        },
    });
	</script>
	@include('includes.scripts')
	<script>
    $('.applicant-link').click(function() {
        var applicantId = $(this).data('applicant-id');
        redirect.dispatch(Redirect.Action.APP, '/rrhh/applicant/' + applicantId);
    });
	$('#edit-offer').click(function() {
        redirect.dispatch(Redirect.Action.APP, '/rrhh/update-offer/{{$offer->id}}');
    });
    $('#remove-offer').click(function() {
        $('#remove-offer-modal').modal();
    });
    $('#confirm-offer-remove').click(function() {
        destroy_data('{{$offer->id}}');
    });

    function destroy_data($offer_id)
    {
        $.ajax({
            method: 'POST',
            url:"https://padre.scalpers.es/rrhh/remove-offer/" + $offer_id,
            data: {
                "_token": "{{ csrf_token() }}"
            },
            success:function()
            {
                //redirect.dispatch(Redirect.Action.APP, '/rrhh/view');
                $('.remove-modal-alert').append('<div class="alert alert-primary">Oferta borrada con éxito</div>');
                $('.modal-footer > button').prop("disabled", true);
                setInterval(function(){
                    redirect.dispatch(Redirect.Action.APP, '/rrhh/view');
                }, 2000);
            }
        });
    }
	</script>
</body>

</html>
