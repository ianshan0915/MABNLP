<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class NegationController extends Controller
{
    public function __construct()
    {

    }
    public function curl(){

        $terms = $_POST['terms'];
        $text = $_POST['text'];

        $url = "http://localhost:9000/api/negation";
        $data = array(
            "terms" => $terms,
            "text" => $text,
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

}