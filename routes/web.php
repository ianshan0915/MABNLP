<?php

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
});

Route::post('/Stanford', 'StanfordNLPController@index');
Route::post('/WatsonNLU', 'WatsonController@index');
Route::post('/Ambiverse', 'AmbiverseController@index');
Route::post('/OpenCalais', 'OpenCalaisController@index');
Route::post('/Dandelion',  'DandelionController@index');
Route::post('/Haven',  'HavenController@index');
Route::post('/TextRazor',  'TextRazorController@index');
Route::post('/GCloud',  'GCloudNaturalLanguageController@index');

Route::post('/FindICD10Codes', 'HomeController@FindICD10Codes');
Route::post('/FindSNOWMEDCodes', 'HomeController@FindSNOWMEDCodes');
Route::post('/FindATCCodes', 'HomeController@FindATCCodes');

Route::get('/ImportXML', 'XMLImporter@ImportXML');

Route::post('/UMLS', 'UMLSController@UMLS');
Route::get('/UMLS/ImportTerm', 'UMLSController@ImportTerm');



Route::get('/phpinfo1', function(){
    echo "hello";
});

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::get('/ieflow', 'IEflowController@index');
Route::get('/ee', function(){
    return view('EntityExtraction');

});
Route::get('/redirectAuth', function () {
    $query = http_build_query([
        'client_id' => '3',
        'redirect_uri' => 'http://localhost:8888/auth/callback',
        'response_type' => 'code',
        'scope' => '',
    ]);

    return redirect('http://localhost:8888/oauth/authorize?'.$query);
});
Route::get('/auth/callback', function (Request $request) {
    $http = new GuzzleHttp\Client;
    
    $code = $_REQUEST['code'];

    $response = $http->post('http://localhost:8888/oauth/token', [
        'form_params' => [
            'grant_type' => 'authorization_code',
            'client_id' => '3',
            'client_secret' => 'oJIRXoomxJ4Exav7QqrurxmVhz1jaJGGXz4Qwj5z',
            'redirect_uri' => 'http://localhost:8888/auth/callback',
            'code' => $code ,
        ],
    ]);

    return json_decode((string) $response->getBody(), true);
    
});

Route::get('/phpinfo', function(Request $request){
    phpinfo();
});
Route::get('/test', function(Request $request){
    //\App\Http\Controllers\DatabaseController::AreExtractionIDsIdentical(33, 186);
    return \App\Http\Controllers\DatabaseController::CreateExtractionGroupForDocumentId("obesity1");
});


Route::get('/importer', function(Request $request){
    return view('importer');
});
Route::get('/getalldocuments', 'DocumentHandler@GetAllDocuments');
Route::get('/test123', 'DocumentHandler@GetAllExtractionsForTerm');
Route::post('/import_file', 'DocumentHandler@Importfile');
Route::post('/insert_extraction', 'DocumentHandler@InsertExtraction');
Route::post('/import_annotations_post', 'DocumentHandler@ImportAnnotationsfile');


Route::post('/importi2b2',              'DocumentHandler@ImportDrugDocumentfile');
Route::post('/importi2b2annotations',   'DocumentHandler@ImportDrugDocumentAnnotationsfile');



Route::post('/importSTRIPAtexts', 'DocumentHandler@ImportSTRIPAtexts');
Route::post('/importSTRIPAannotations', 'DocumentHandler@ImportSTRIPAAnnotationsfile');

Route::get('/parser', 'StanfordNLPController@StanfordParser');
Route::post('/negation', 'NegationController@curl');






