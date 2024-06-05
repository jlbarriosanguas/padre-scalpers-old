<table id="applicant-data" class="table mt-4 text-center">
    <thead class="thead-dark">
        <tr>
            <th scope="col">Fecha</th>
            <th scope="col">Nombre</th>
            <th scope="col">Email</th>
            <th scope="col">Oferta</th>
            <th scope="col">Estudios</th>
            <th scope="col">Inglés</th>
            <th scope="col">Disp. Horaria</th>
            <th scope="col">Disp. Viaje</th>
            <th scope="col">Exp. Retail</th>
            {{--<th scope="col">Última Exp.</th>--}}
        </tr>
    </thead>
    <tbody style="font-size: .85em">
        @foreach($applicants as $applicant)
        <tr>
            <td>{{$applicant->created_at->format('d-m-Y')}}</td>
            <td><a href="#" class="applicant" data-applicant="{{$applicant->id}}">{{$applicant->name}} {{$applicant->surname}}</a></td>
            <td>{{$applicant->email}}</td>
            <td><a href="#" class="job_offer" data-offer="{{$applicant->job_id}}">{{$applicant->job}}</a></td>
            <td>{{$applicant->studies}}</td>
            <td>{{$applicant->english_level}}</td>
            <td>{{$applicant->time_availability}}</td>
            <td>{{$applicant->travel_availability}}</td>
            <td>{{$applicant->retail_exp}}</td>
            {{--<td>{{$applicant->last_exp}}</td>--}}
        </tr>
        @endforeach
    </tbody>
</table>
{{ $applicants->links("pagination::bootstrap-4") }}