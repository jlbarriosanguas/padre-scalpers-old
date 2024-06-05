<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utilidades;
use App\JobOffer;
use App\Applicant;


class ShopifyJobOffersController extends Controller
{
    public function showList(Request $request)
    {
        $job_offers = JobOffer::orderBy('updated_at', 'desc')->paginate(20);
        if($request->ajax())
        {
            return View::make('rrhh.offers.offerpagination', compact('job_offers'))->render();
        }
        else
        {
            return View::make('rrhh.offers.list', compact('job_offers'));
        }
    }

    public function showOffer($offer_id)
    {
        $offer = JobOffer::findOrFail($offer_id);
        $applicants = $offer->getApplicants;
        $applicant_count = DB::table('applicants')->where('job_id', $offer_id)->count();
        return View::make('rrhh.offers.offer', compact('offer'), compact('applicants'))->with('applicant_count', $applicant_count);
    }

    public function createOffer()
    {
        return view('rrhh.offers.create');
    }

    public function createOfferPost(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required',
            'summernote' => 'required',
            'department' => 'required',
            'city' => 'required'
        ]);

        $offer = new JobOffer([
            'offer_status' => 'active',
            'title' => $request->get('title'),
            'body' => $request->get('summernote'),
            'department' => $request->get('department'),
            'city' => $request->get('city'),
            'observations' => $request->get('observations'),
            //'image' => $request->get('image')
        ]);
        $store_offer = $offer->save();

        if ($store_offer) {
            $response = 'Job Offer created';
        } else {
            $response = 'Error while storing values';
        }

        return $response;
    }

    public function editOffer($offer_id)
    {
        $offer = JobOffer::findOrFail($offer_id);
        return View::make('rrhh.offers.edit', compact('offer'));
    }

    public function editOfferPost(Request $request, $offer_id)
    {
        $update = [
            'title' => $request->get('title'),
            'body' => $request->get('summernote'),
            'department' => $request->get('department'),
            'city' => $request->get('city'),
            'observations' => $request->get('observations'),
             //'image' => $request->get('image')
        ];

        $up_query = JobOffer::where('id', $offer_id)->update($update);

        if ($up_query) {
            $response = 'Job Offer updated';
        } else {
            $response = 'Error while storing values';
        }

        return $response;
    }

    public function removeOfferPost($offer_id)
    {
        $offer = JobOffer::findOrFail($offer_id);
        $offer->delete();

        if ($offer) {
            $response = 'Job Offer removed';
        } else {
            $response = 'Error while deleting values';
        }

        return $response;
    }

    public function showApplicantList(Request $request)
    {
        $applicants = Applicant::orderBy('created_at', 'desc')->paginate(20);
        if($request->ajax())
        {
            return View::make('rrhh.applicants.applicantpagination', compact('applicants'))->render();
        }
        else
        {
            return View::make('rrhh.applicants.list', compact('applicants'));
        }
    }
    public function showApplicant($applicant_id)
    {
        $applicant = Applicant::findOrFail($applicant_id);
        return View::make('rrhh.applicants.applicant', compact('applicant'));
    }

    public function removeApplicantPost($applicant_id)
    {
        $applicant = Applicant::findOrFail($applicant_id);
        Storage::disk('applicants')->delete([$applicant->curriculum, $applicant->photo, $applicant->motivation_letter]);
        $applicant->delete();

        if ($applicant) {
            $response = 'Applicant removed';
        } else {
            $response = 'Error while deleting values';
        }

        return $response;
    }

    public function fetchOfferFromShopify($offer_id)
    {
        if ($offer_id == 'all')
        {
            $offers = JobOffer::all()->makeHidden(['applicants', 'observations', 'updated_at'])->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return response($offers)->header('Content-Type', 'application/json');
        }
        else
        {
            $offer = JobOffer::findOrFail($offer_id)->makeHidden(['applicants', 'observations', 'updated_at'])->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return response($offer)->header('Content-Type', 'application/json');
        }
    }

    public function createApplicantFromShopify(Request $request)
    {
        $rules = [
            'applicant' => 'required|array',
            'applicant.name' => 'required',
            'applicant.surname' => 'required',
            'applicant.email' => 'required',
            'applicant.phone' => 'required',
            'applicant.birthdate' => 'required',
            'applicant.studies' => 'required',
            'applicant.city' => 'required',
            'applicant.country' => 'required',
            'applicant.retail_exp' => 'required',
            'applicant.last_exp' => 'required',
            'applicant.english_level' => 'required',
            'applicant.time_availability' => 'required',
            'applicant.travel_availability' => 'required',
            'applicant.curriculum' => 'required|file|max:1024',
            'applicant.photo' => 'required|file|max:1024|image',
        ];

        $messages = [
            'applicant.curriculum.max' => 'Error al adjuntar el curriculum. El tamaño del archivo debe ser inferior a 1Mb.',
            'applicant.photo.max' => 'Error al adjuntar la foto. El tamaño del archivo debe ser inferior a 1Mb.',
            'applicant.curriculum.uploaded' => 'Error al adjuntar el curriculum. El tamaño del archivo debe ser inferior a 1Mb.',
            'applicant.photo.uploaded' => 'Error al adjuntar la foto. El tamaño del archivo debe ser inferior a 1Mb.',
            'applicant.photo.image' => 'Error al adjuntar la foto. El formato del archivo debe ser jpeg, png, bmp, gif, o svg',
        ];

        $validatedData = $request->validate($rules, $messages);

        $applicant = new Applicant([
            'job_id' => $request->input('applicant.job_id'),
            'name' => $request->input('applicant.name'),
            'surname' => $request->input('applicant.surname'),
            'birthday' => $request->input('applicant.birthdate'),
            'phone' => $request->input('applicant.phone'),
            'email' => $request->input('applicant.email'),
            'studies' => $request->input('applicant.studies'),
            'english_level' => $request->input('applicant.english_level'),
            'retail_exp' => $request->input('applicant.retail_exp'),
            'job' => $request->input('applicant.job'),
            'last_exp' => $request->input('applicant.last_exp'),
            'country' => $request->input('applicant.country'),
            'city' => $request->input('applicant.city'),
            'time_availability' => $request->input('applicant.time_availability'),
            'travel_availability' => $request->input('applicant.travel_availability'),
            // Default de 'saved', 'location' y 'observations' mal definido en migración. Gracias Aarón.
            // Fixed - 30-01-20
        ]);

        $store_applicant = $applicant->save();

        $cv_filename = $applicant->id . '_' . $applicant->job_id . '_' . $applicant->name . '_' . $applicant->surname . '_cv' . ($request->file('applicant.curriculum')->extension() ? '.' . $request->file('applicant.curriculum')->extension() : '');
        $sanitized_cv_filename = $this->sanitize($cv_filename);
        $store_cv = $request->file('applicant.curriculum')->storeAs('private/rrhh/applicants', $sanitized_cv_filename);

        $photo_filename =  $applicant->id . '_' . $applicant->job_id . '_' . $applicant->name . '_' . $applicant->surname . '_photo.' . $request->file('applicant.photo')->extension();
        $sanitized_photo_filename = $this->sanitize($photo_filename);
        $store_photo = $request->file('applicant.photo')->storeAs('private/rrhh/applicants', $sanitized_photo_filename);

        $applicantB = Applicant::find($applicant->id);
        $applicantB->curriculum = $sanitized_cv_filename;
        $applicantB->photo = $sanitized_photo_filename;
        if ($request->file('applicant.motivation_letter'))
        {
            $letter_filename =  $applicant->id . '_' . $applicant->job_id . '_' . $applicant->name . '_' . $applicant->surname . '_letter.' . $request->file('applicant.motivation_letter')->extension();
            $sanitized_letter_filename = $this->sanitize($letter_filename);
            $store_letter = $request->file('applicant.motivation_letter')->storeAs('private/rrhh/applicants', $sanitized_letter_filename);
            $applicantB->motivation_letter = $sanitized_letter_filename;
        }
        $update_applicant = $applicantB->save();

        if ($store_applicant && $update_applicant)
        {
            $response = 'Applicant submitted';
        }
        else
        {
            $response = 'Error while storing values';
        }

        return $response;
    }

    private function sanitize($string, $force_lowercase = true, $anal = false)
    {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "+", "[", "{", "]",
                       "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                       "â€”", "â€“", ",", "<", ">", "/", "?");
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
        return ($force_lowercase) ?
            (function_exists('mb_strtolower')) ?
                mb_strtolower($clean, 'UTF-8') :
                strtolower($clean) :
            $clean;
    }
}
