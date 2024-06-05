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
	<div class="container my-5">
		<div class="content">
			<div id="create-offer">
            <p class="h4">Editar: {{$offer->title}} <a href="https://scalperscompany.com/pages/careers?offerid={{$offer->id}}" target="_blank" style="font-size: .5em; vertical-align: super;"><i class="fa fa-external-link" aria-hidden="true"></i></a><button type="submit" class="btn btn-dark" style="float: right;">Guardar cambios</button></p>
            <div><span class="badge badge-dark">Creación: {{$offer->created_at->format('d-m-Y')}}</span> <span class="badge badge-primary">Dpto: {{$offer->department}}</span> <span class="badge badge-info">Ciudad: {{$offer->city}}</span></div>
            <hr class="my-3">
            <div id="create-offer-alerts" class="mt-4"></div>
                <form id="create-offer-form" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <label for="Título">Título</label>
                    <input type="text" class="form-control" name="title" placeholder="Título de la oferta" value="{{$offer->title}}" required>
                    </div>
                    <div class="form-group">
                        <label for="summernote">Cuerpo de la oferta</label>
                        <textarea name="summernote">{{$offer->body}}</textarea>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="city">Ciudad / Población</label>
                            <input type="text" class="form-control" id="city" name="city" placeholder="Ciudad de la oferta" value="{{$offer->city}}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="department">Departamento / Área</label>
                            <select class="form-control" name="department">
                                @if ($offer->department)
                                <optgroup label="Actual">
                                    <option value="{{$offer->department}}">{{$offer->department}}</option>
                                    <option disabled>----------</option>
                                </optgroup>
                                @endif
                                <optgroup label="Tienda">
                                    <option value="Sales Assistant">Sales Assistant</option>
                                    <option value="Store Manager">Store Manager</option>
                                </optgroup>
                                <optgroup label="Operaciones">
                                    <option value="Almacén">Almacén</option>
                                    <option value="Atención al cliente">Atención al cliente</option>
                                    <option value="Logística">Logística</option>
                                </optgroup>
                                <optgroup label="Oficina">
                                    <option value="Arquitectura">Arquitectura</option>
                                    <option value="Big Data">Big Data</option>
                                    <option value="Compras">Compras</option>
                                    <option value="Comunicación">Comunicación</option>
                                    <option value="Diseño">Diseño</option>
                                    <option value="Expansión">Expansión</option>
                                    <option value="Finanzas-Administración">Finanzas-Administración</option>
                                    <option value="Marketing-Ecommerce">Marketing-Ecommerce</option>
                                    <option value="RRHH-Laboral">RRHH-Laboral</option>
                                    <option value="Sistemas">Sistemas</option>
                                    <option value="Retail">Retail</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="observations">Observaciones</label>
                        <textarea id="offer_obv" rows="4" class="form-control" name="observations">{{$offer->observations}}</textarea>
                    </div>
                    {{--
                    <div class="form-group col-md-6">
                        <label for="image">Seleccionar imagen</label><br>
                        <input type='file' onchange="readURL(this);" name="image" />
                    </div>
                    --}}
                    <div class="form-group text-right mt-4" style="display: none;">
                        <button type="submit" class="btn btn-dark" style="float: right;">Guardar cambios</button>
                    </div>
                </form>
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
        title: "Editar: {{$offer->title}}",
        breadcrumbs: breadcrumb,
        buttons: {
            secondary: [feedsButton, rrhhGroupButton],
        },
    });
	</script>

    @include('includes.scripts')
    @include('includes.tinymce')
    <script>
        $('#create-offer > p > button[type=submit]').click(function() {
            $('form#create-offer-form > div > button[type=submit]').click();
        });
		$('form#create-offer-form').submit(function(e) {
            tinyMCE.triggerSave();
            e.preventDefault();
			var endpoint = '/rrhh/update-offer/{{$offer->id}}';
            var data = $(this).serialize();
            $('#create-offer > #create-offer-alerts').empty();
			$('#create-offer > #create-offer-alerts').append('<div class="alert alert-dark" role="alert">Actualizando oferta de trabajo...</div>');
            $.post(endpoint, data)
            .done(function(data, status) {
                $('#create-offer > #create-offer-alerts').empty();
                $('#create-offer > #create-offer-alerts').append('<div class="alert alert-primary" role="alert"><p>¡Oferta actualizada con éxito!</p>');
            })
            .fail(function(data, status) {
                $('#create-offer > #create-offer-alerts').empty();
                $('#create-offer > #create-offer-alerts').append('<div class="alert alert-danger" role="alert"></div>');
                if (JSON.parse(data.responseText)["errors"]["summernote"]) {
                    $('#create-offer > #create-offer-alerts > .alert-danger').append('<p>El cuerpo de la oferta es requerido</p>');
                } else if (JSON.parse(data.responseText)["errors"]["title"]) {
                    $('#create-offer > #create-offer-alerts > .alert-danger').append('<p>El título es requerido</p>');
                } else {
                    $('#create-offer > #create-offer-alerts > .alert-danger').append('<p>Error de validación de campos</p>');
                }
            })
            .always(function(data, status) {
                //alert("Data: " + data + "\nStep: Finished");
            });
		});
	</script>
</body>

</html>
