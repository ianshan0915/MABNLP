<?php

namespace App\Http\Controllers;


use App\StanfordNLP\CorenlpAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PMA\libraries\Console;
use Google\Cloud\NaturalLanguage\NaturalLanguageClient;

class GCloudNaturalLanguageController extends Controller
{

    public function __construct()
    {

    }
    public function index()
    {
        if(isset($_POST['text'])){
            $text = $_POST['text'];
        }
        else{
            return "Text Parameter not set";
        }

        putenv("GOOGLE_APPLICATION_CREDENTIALS=Thesis-e433ee1a91f3.json");




        $projectId = 'thesis-163911';
        $language = new NaturalLanguageClient([
            'projectId' => $projectId
        ]);


        //$text = 'Hello, world!';

        $annotation = $language->analyzeEntities($text);


        return JsonResponse::create($annotation->entities());






    }
    function debug_to_console( $data ) {

        if ( is_array( $data ) )
            $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
        else
            $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

        echo $output;
    }
}
