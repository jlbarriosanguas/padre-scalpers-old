<table id="offer-data" class="table mt-4 text-center">
    <thead class="thead-dark">
        <tr>
            <th scope="col">Fecha</th>
            <th scope="col">Título</th>
            <th scope="col">Departamento</th>
            <th scope="col">Ciudad</th>
            <th scope="col">Candidatos</th>
            <th scope="col">URL</th>
        </tr>
    </thead>
    <tbody style="font-size: .85em">
        @foreach($job_offers as $offer)
        <tr>
            <td>{{$offer->created_at->format('d-m-Y')}}</td>
            <td><a href="#" class="job_offer" data-offer="{{$offer->id}}">{{$offer->title}}</a></td>
            <td>{{$offer->department}}</td>
            <td>{{$offer->city}}</td>
            <td>{{DB::table('applicants')->where('job_id', $offer->id)->count()}}</td>
            <td><a href="https://scalperscompany.com/pages/careers?offerid={{$offer->id}}" target="_blank"><i class="fa fa-external-link" aria-hidden="true"></i></a></td>
        </tr>
        @endforeach
    </tbody>
</table>
{{ $job_offers->links("pagination::bootstrap-4") }}