<?php

namespace App\Http\Controllers;


use App\StanfordNLP\CorenlpAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PMA\libraries\Console;

class DandelionController extends Controller
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

        $token = "34829090e2bf4995965a67cbf97557ed";
        $include = "types, categories";

        $url_param = "text=".urlencode($text) . "&token=".$token . "&include=".$include;


        $ch = curl_init();

        $complete_url = "https://api.dandelion.eu/datatxt/nex/v1";

        curl_setopt($ch, CURLOPT_URL, $complete_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $url_param);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);



        return $result;


    }
    function debug_to_console( $data ) {

        if ( is_array( $data ) )
            $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
        else
            $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

        echo $output;
    }
}
