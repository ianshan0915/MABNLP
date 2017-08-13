<?php

namespace App\Http\Controllers;


use App\StanfordNLP\CorenlpAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PMA\libraries\Console;

class AmbiverseController extends Controller
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

        $tokenResult = $this->requestOAuthToken();
        $tokenResult = json_decode($tokenResult);

        $token = $tokenResult->access_token;

        $post = array(
            "text" => $text,
            "language" => "en",
        );

        $post_data = json_encode($post);


        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.ambiverse.com/v1/entitylinking/analyze");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{'text: hello world");
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "AUTHORIZATION: " . $token;
        $headers[] = "Accept: application/json";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);



        return $result;


    }
    public function requestOAuthToken(){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.ambiverse.com/oauth/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "client_id=125f20ba&client_secret=9ceef7c0504ce67fdfa152d7928e7c1a&grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        $headers[] = "Accept: application/json";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

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
