<?php

namespace App\Http\Controllers;


use App\StanfordNLP\CorenlpAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PMA\libraries\Console;

class HavenController extends Controller
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

        $key = "3f53ea63-ff94-4ca4-87ef-d5ff4da80909";
        $entity_type = array("drugs_eng", "medical_conditions_eng");

        $result_array = array();
        foreach ($entity_type as $type){

            $ch = curl_init();

            $parameters = "&apikey=".$key."&entity_type=".$type."&text=". urlencode($text);
            $url = "https://api.havenondemand.com/1/api/sync/extractentities/v2";


            curl_setopt($ch, CURLOPT_URL, $url  );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close ($ch);
            $result = json_decode($result);
            $result_array = array_merge($result_array, $result->entities);

        }
        return $result_array;


    }
}
