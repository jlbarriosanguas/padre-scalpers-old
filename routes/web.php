<?php

use App\Http\Middleware\VerifyShopifyHmac;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('home')->middleware(VerifyShopifyHmac::class);

Route::get('private/{filename}', ['uses' => 'ShopifyHmacFilesController@getFile', 'middleware' => VerifyShopifyHmac::class])->name('applicant_data');

Route::post('wheel', 'LoyaltyWheelController@wheeling');
Route::get('wheel', function() { exit(json_encode(array("errors" => "Unauthorized access"))); });

Route::get('graphqltest', 'ShopifyGraphQLTest@index');

Route::group(['prefix' => 'graphqltest'], function() {
	Route::get('index', 'ShopifyGraphQLTest@index');
	Route::post('create', 'ShopifyGraphQLTest@createUser');
	Route::post('update', 'ShopifyGraphQLTest@updateUser');
});

//Route::post('shopify-webhook/create-cart', 'ShopifyWebhookController@index');
//Route::get('shopify-webhook/create-cart', function() { exit(json_encode(array("errors" => "Unauthorized access"))); });
Route::get('graphqltest/stock/{id}/{address}', 'ShopifyGraphQLTest@getLocationStock');
Route::group(['prefix' => 'shopify-webhook'], function() {
	Route::get('create-cart', function() { exit(json_encode(array("errors" => "Unauthorized access"))); });
	Route::post('create-cart','ShopifyWebhookController@receiver');
	Route::post('order','ShopifyAppOrderController@receiver');
	Route::post('solidarity','ShopifyTempController@captureSolidarityShirt');
	Route::post('kly-notify-locstock','ShopifyTempController@notifyLocationItemsToKlaviyo');
	// Gift Wrap
	Route::post('fulfill/giftwrap','ShopifyFulfillmentGiftWrapController@giftwrapFulfillment');
	//Test
	Route::post('kly-notify-locstock-test','ShopifyTempController@notifyLocationItemsToKlaviyoTest');
});

// Install as Public App
Route::group(['prefix' => 'oauth'], function() {
	Route::get('install', 'ShopifyOAuthController@install')->name('install');
	Route::get('generate_token','ShopifyOAuthController@generateToken')->name('generate_token');
});

// Shopify User
Route::group(['prefix' => 'sfy-user'], function() {
	Route::get('create', function() { exit(json_encode(array("errors" => "Unauthorized access"))); });
	Route::post('create', 'ShopifyUserController@createUser');
	Route::get('update', function() { exit(json_encode(array("errors" => "Unauthorized access"))); });
	Route::post('update','ShopifyUserController@updateUser');
	// TEST
	Route::get('createtest', function() { exit(json_encode(array("errors" => "Unauthorized access"))); });
	Route::post('createtest', 'ShopifyUserControllerTest@createUser');
	Route::get('updatetest', function() { exit(json_encode(array("errors" => "Unauthorized access"))); });
	Route::post('updatetest','ShopifyUserControllerTest@updateUser');
	// NO FID INT
	Route::get('createint', function() { exit(json_encode(array("errors" => "Unauthorized access"))); });
	Route::post('createint', 'ShopifyExtendedRegisterController@registerNewUser');
	Route::get('updateint', function() { exit(json_encode(array("errors" => "Unauthorized access"))); });
	Route::post('updateint', 'ShopifyExtendedRegisterController@updateUser');
});

// Handles IB
Route::group(['prefix' => 'sfy-collection'], function() {
	Route::get('{storeCode}/ib/{vendors}/handles', 'ShopifyHandlesController@getHandles');
});

// Feeds
Route::group(['prefix' => 'feeds'], function() {
	Route::get('view', function () {
		return view('feeds');
	})->name('feeds')->middleware(VerifyShopifyHmac::class);
	Route::get('{merchant}/generate/{sfy_store}', 'ShopifyFeedController@generateFeed');
});

// RRHH
Route::group(['prefix' => 'rrhh'], function() {
	// Offers
	Route::get('view', 'ShopifyJobOffersController@showList')->name('offer_list')->middleware(VerifyShopifyHmac::class);
	Route::get('view/fetch', 'ShopifyJobOffersController@showList')->name('offer_list_fetch')->middleware(VerifyShopifyHmac::class);
	Route::get('offer/{offer_id}', 'ShopifyJobOffersController@showOffer')->name('show_offer')->middleware(VerifyShopifyHmac::class);
	Route::get('create-offer', 'ShopifyJobOffersController@createOffer')->name('new_offer')->middleware(VerifyShopifyHmac::class);
	Route::post('create-offer', 'ShopifyJobOffersController@createOfferPost')->name('post_offer');
	Route::get('update-offer/{offer_id}', 'ShopifyJobOffersController@editOffer')->name('edit_offer')->middleware(VerifyShopifyHmac::class);
	Route::post('update-offer/{offer_id}', 'ShopifyJobOffersController@editOfferPost')->name('post_edit_offer');
	Route::post('remove-offer/{offer_id}', 'ShopifyJobOffersController@removeOfferPost')->name('post_remove_offer'); // CSRF Protected
	// Applicants
	Route::get('applicants', 'ShopifyJobOffersController@showApplicantList')->name('applicant_list')->middleware(VerifyShopifyHmac::class);
	Route::get('applicants/fetch', 'ShopifyJobOffersController@showApplicantList')->name('applicant_list_fetch')->middleware(VerifyShopifyHmac::class);
	Route::get('applicant/{applicant_id}', 'ShopifyJobOffersController@showApplicant')->name('show_applicant')->middleware(VerifyShopifyHmac::class);
	Route::post('remove-applicant/{applicant_id}', 'ShopifyJobOffersController@removeApplicantPost')->name('post_remove_applicant'); // CSRF Protected
	// Offer Frontend
	Route::get('/fetch/{offer_id}', 'ShopifyJobOffersController@fetchOfferFromShopify')->name('fetch_offer_front'); // Public
	Route::post('register-applicant', 'ShopifyJobOffersController@createApplicantFromShopify')->name('post_applicant_front'); // Public
	// Get customer from Klaviyo
	Route::get('get-customer/{mail}/{store}', 'ShopifyUserController@getKlaviyoCustomer')->name('get_klaviyo_customer');
	
	Route::get('add-profile-to-list/{mail}/{first_name}/{last_name}/{date_submit}/{birthday_shopify}/{consent_disney}/{consent}', 'ShopifyUserController@addProfileToList')->name('add_profile_list');
	
	Route::post('get-stock', 'ShopifyUserController@getStock')->name('get_stock');
});

// CARROUSELES
Route::group(['prefix' => 'carrousel'], function() {
	// Completa el look
	Route::get('completa-look/{product_id}/{store_code}', 'ShopifyCarrouselController@completaLook');	
	// Related products
	Route::get('related-products/{product_id}/{store_code}', 'ShopifyCarrouselController@relatedProducts');
	
});

// Scalpify
Route::group(['prefix' => 'scalpify'], function() {
	Route::get('collections/view', function () {
		return view('scalpify.collections.view');
	})->name('scalpify_collections')->middleware(VerifyShopifyHmac::class);
	Route::post('collections/import', 'ShopifyGraphQLTest@index')->name('scalpify_smartcol_import'); // CSRF Protected
});

// Tracking
Route::get('/track/{id}', 'ShopifyTrackingController@index');
Route::get('/track-aftership/{id}', 'ShopifyTrackingController@test');
Route::get('/tracklist/{id}', 'ShopifyGraphQLTest@testTrack');

// CL Postal Codes
Route::get('/cl-cp/{comuna}/{address}', 'CorreosCLPostalCodeController@getCLPostalCode');

// Locations
Route::group(['prefix' => 'locations'], function() {
	Route::get('check/{locid}/{sku}', 'ShopifyTempController@checkLocationStock')->name('check_stock_location');
	Route::get('checkid/{locid}/{varid}', 'ShopifyTempController@checkLocationStockById')->name('checkid_stock_location');
	Route::post('store', 'ShopifyPickUpController@storeLocation')->name('store_location');
});

// Draft Order
Route::group(['prefix' => 'draftorder'], function() {
	Route::post('create', 'ShopifyDraftOrderController@createDraft')->name('create_draft');
	Route::get('create', function() { exit(json_encode(array("errors" => "Unauthorized access"))); });
});

// Loyaltylion Custom Events
Route::group(['prefix' => 'lty-ce'], function() {
	Route::post('app-download', 'LoyaltyCustomEventsController@appDownload')->name('ltyce_app_download');
	Route::post('medium-survey', 'LoyaltyCustomEventsController@mediumSurvey')->name('ltyce_medium_survey');
	Route::post('ib-survey', 'LoyaltyCustomEventsController@invitedBrandsSurvey')->name('ltyce_ib_survey');
	Route::post('sorteo-nieve', 'VerifyLoyaltyWebhook@verify_webhook')->name('ltyce_sorteo_nieve');
	Route::post('reward/vogue', 'LoyaltyCustomEventsController@rewardsPadreVogue')->name('ltyce_rewards_padre_vogue');
	Route::post('reward/ad', 'LoyaltyCustomEventsController@rewardsPadreAd')->name('ltyce_rewards_padre_ad');
	Route::post('reward/vf', 'LoyaltyCustomEventsController@rewardsPadreVf')->name('ltyce_rewards_padre_vf');
	Route::post('reward/gq', 'LoyaltyCustomEventsController@rewardsPadreGq')->name('ltyce_rewards_padre_gq');
	Route::post('reward/brooklyn', 'LoyaltyCustomEventsController@rewardsPadreBrooklyn')->name('ltyce_rewards_padre_brooklyn');
	Route::post('reward/revolutgold', 'LoyaltyCustomEventsController@rewardsRevolutGold')->name('ltyce_rewards_padre_revolut_gold');
	Route::post('reward/revolutbronze', 'LoyaltyCustomEventsController@rewardsRevolutBronze')->name('ltyce_rewards_padre_revolut_bronze');
	Route::post('reward/synergym', 'LoyaltyCustomEventsController@rewardsPadreSynergym')->name('ltyce_rewards_padre_synergym');
	Route::post('reward/bobbibrown', 'LoyaltyCustomEventsController@rewardsPadreBobbibrown')->name('ltyce_rewards_padre_bobbibrown');
	Route::post('reward/ps', 'LoyaltyCustomEventsController@rewardsPadrePolardSound')->name('ltyce_ps');
	Route::post('reward/nextory', 'LoyaltyCustomEventsController@rewardsPadreNextStory')->name('ltyce_ps');
	Route::post('reward/barajacartas', 'LoyaltyCustomEventsController@rewardsPadreBarajaCartas')->name('ltyce_ps');
	Route::get('get-pic-subscription/{mail}', 'LoyaltyCustomEventsController@getPicSubscription')->name('get_pic_subscription');
	Route::post('reward/traveler', 'LoyaltyCustomEventsController@rewardsPadreTraveler')->name('ltyce_rewards_padre');
	Route::get('app-download', function() { exit(json_encode(array("errors" => "Unauthorized access"))); });
});

// Google Geocoding
Route::group(['prefix' => 'geocode'], function() {
	Route::get('{address}/{country_code}', 'GoogleGeocodingController@getGeocoding')->name('google_get_geocoding');
});

// Update customer interests on SHopify
Route::get('update-preferences', 'UpdateMailPreferences@updateShopifyInterests')->name('update-sfy-interests');

// Persistent cart
Route::group(['prefix' => 'cart'], function () {
    Route::get('check','CartControllers@checkCartToken');
    Route::get('delete','CartControllers@removeCartToken');
    Route::get('update','CartControllers@modifyCartToken');
});


Route::get('testab/{mail}', 'TestABController@registerTaggedUser');
// Route::get('/get-customer/{mail}', [ShopifyUserController::class, 'getKlaviyoCustomer']);

// RRHH
// Route::group(['prefix' => 'stock'], function() {
// 	Route::post('get-stock', 'ShopifyUserController@getStock')->name('get_stock'); 
// });