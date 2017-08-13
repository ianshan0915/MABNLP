<?php

namespace App\Http\Controllers;


use App\StanfordNLP\CorenlpAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PMA\libraries\Console;

class WatsonController extends Controller
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

        $username = "b539e26f-b732-4969-b433-b3f41a44ee1b";
        $password = "3lt0nTdOQW6S";

        $url = "https://" . $username . ":" . $password . "@gateway.watsonplatform.net/natural-language-understanding/api/v1/analyze?version=2017-02-27";

        $text = $_POST['text'];
        $data = array(
            "text" => $text,
            "features" => array(
                "entities" => array(
                    "limit" => 100,
                ),
                "keywords" => array(
                    "limit" => 100,
                ),
                "concepts" => array(
                    "limit" => 100,
                ),
                "relations" => array(
                    "limit" => 100,
                )
            ),
        );
        $data_string = json_encode($data);




        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        ));


        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);

        $json = JsonResponse::create($output);

        return $json;

    }
    function debug_to_console( $data ) {

        if ( is_array( $data ) )
            $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
        else
            $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

        echo $output;
    }
}
