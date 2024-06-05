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

		<div class="content">
			<div id="fb-feed">
				<p class="h4">Candidatos <span class="badge badge-primary" style="font-size: 50%; vertical-align: middle;">v0.1</span> <span class="badge badge-warning" style="font-size: 50%; vertical-align: middle;">Experimental</span></p>
				<hr class="my-3">
				<p class="mb-4" style="font-weight: 400;">Listado completo de candidatos</p>
				<div id="ajax-offer-container">
					@include('rrhh.applicants.applicantpagination')
				</div>
			</div>
		</div>
    </div>

    @include('includes.appbridge')
    <script>
    var myTitleBar = TitleBar.create(app, {
        title: "RRHH",
		buttons: {
			primary: newOfferButton,
            secondary: [feedsButton, rrhhGroupButton],
        },
    });
	</script>
	@include('includes.scripts')
    <script>
        $('.applicant').click(function(e){
            e.preventDefault();
            var applicantId = $(this).attr('data-applicant');
            redirect.dispatch(Redirect.Action.APP, '/rrhh/applicant/' + applicantId);
        });
        $('.job_offer').click(function(e){
            e.preventDefault();
            var offerId = $(this).attr('data-offer');
            redirect.dispatch(Redirect.Action.APP, '/rrhh/offer/' + offerId);
        });
		$(document).on('click', '.pagination a', function(event){
			event.preventDefault();
			var page = $(this).attr('href').split('page=')[1];
			var loc = window.location.toString();
    		var params = loc.split('?')[1];
			fetch_data(page, params);
			document.body.scrollTop = 0; // For Safari
  			document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
		});

		function fetch_data(page, params)
		{
			$.ajax({
			url:"https://padre.scalpers.es/rrhh/applicants/fetch?page=" + page + "&" + params,
			success:function(data)
			{
				$('#ajax-offer-container').html(data);
				$('.applicant').click(function(e){
					e.preventDefault();
					var applicantId = $(this).attr('data-applicant');
					redirect.dispatch(Redirect.Action.APP, '/rrhh/applicant/' + applicantId);
				});
                $('.job_offer').click(function(e){
                    e.preventDefault();
                    var offerId = $(this).attr('data-offer');
                    redirect.dispatch(Redirect.Action.APP, '/rrhh/offer/' + offerId);
                });
			}
		});
		}
    </script>
</body>

</html>
