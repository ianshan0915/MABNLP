<?php

namespace App\Http\Controllers;


use App\StanfordNLP\CorenlpAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PMA\libraries\Console;

use App\Http\Controllers\SDK\TextRazorSettings;
use App\Http\Controllers\SDK\TextRazor;



class TextRazorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $text = $_POST['text'];

        TextRazorSettings::setApiKey("6550792bcb47148f7dfd8b205e329bd15d66afbcb6a99927b15f36da");

        $textrazor = new TextRazor();
        //$texgma
        //trazor->addExtractor('words');
        //$textrazor->addExtractor('relations');
        $textrazor->addExtractor('entities');
        $result = $textrazor->analyze($text);

        $results = json_decode(json_encode($result));

       $results = $results->response->entities;

       $result_array = array();
       foreach ($results as $result){
           $resultItem = (object) array();
           if(isset($result->type) && (in_array("Disease",$result->type) || in_array("Drug",$result->type))) {
               $resultItem->{'original_name'} = $result->matchedText;
               $resultItem->{'remote_name'} = $result->entityId;
               $resultItem->{'type'} = in_array("Disease",$result->type) ? "Disease" : "Drug";
               array_push($result_array, $resultItem);
           }
       }
       return $result_array;


    }

}
