<?php

namespace App\Http\Controllers;


use App\StanfordNLP\CorenlpAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PMA\libraries\Console;

class StanfordNLPController extends Controller
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
        $terms = $_POST['terms'];
        $terms = explode(',', $terms);


        $tokens = $this->GetTokens($text);

        $result = $this->MatchNegations($tokens, $terms);




        return $result;


    }
    public function GetTokens($text){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://localhost:9000/api/clinical");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "text=".$text);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));


        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);


        $result = json_decode($result);

        $tokens = array();
        $token_index = 0;
        foreach($result->sentences as $sentence){
            foreach($sentence->tokens as $token){
                $token->index = $token_index;
                $token_index += 1;
                $tokens[] = $token;
            }
        }



        return $tokens;
    }
    public function MatchNegations($tokens, $terms){
        $negations = ["no","not","none","no one","nobody","nothing","neither","nowhere","never"];
        $result = array();

        $tokenNames = array();
        foreach ($tokens as $value)
            $tokenNames[] = strtolower($value->originalText);

        foreach ($tokenNames as $index=>$token){
            foreach ($terms as $term){
                $exploded_term = explode(' ', $term);
                $first_word = $exploded_term[0];
                if($first_word == $token){
                    //Found a partial/complete term
                    if(count($exploded_term) > 1){
                        $i = 0;
                    }

                    if(count($exploded_term) > 1 && !$this->NextWordsMatch($tokenNames,$exploded_term,$index)){
                        //Term has more than one word but does not match
                        break;

                    }
                    else{
                        //
                        if($this->IsPreceededByNegation($tokenNames,$index,$negations)){
                            $result[] = (object) array("term"=>$term, "negation"=>$tokens[$index-1]->originalText);
                        }
                    }
                }
            }
        }


        /*
        foreach ($tokens as $index=>$token){

            if(
                in_array($token->originalText, $negations)
            ){
                if($index < count($tokens)-1){
                    $next_token = $tokens[$index+1]->originalText;
                    if(in_array($next_token, $terms)){
                        //Negation found!
                        $result[] = (object) array("term"=>$next_token, "negation"=>$token->originalText);
                    }
                }
                if($index > 0){
                    $previous_token = $tokens[$index-1]->originalText;
                    if(in_array($previous_token, $terms)){
                        $result[] = (object) array("term"=>$previous_token, "negation"=>$token->originalText);
                    }
                }
            }
        }
        */
        return $result;

    }
    public function NextWordsMatch($tokens, $exploded_term, $startindex){
        for ($i = 1; $i < count($exploded_term); $i++) {
            if(!$tokens[$startindex+$i] == $exploded_term[$i]){
                return false;
            }
        }
        return true;
    }
    public function IsPreceededByNegation($tokens, $term_index, $negations){
        if(isset($tokens[$term_index-1]) && in_array($tokens[$term_index-1],$negations)) {
            return true;
        }
        return false;

    }
    public function IsFollowedByNegation(){

    }


    public function StanfordParser($text){

        $path = $_SERVER['DOCUMENT_ROOT'];
        $parser = new \StanfordNLP\Parser(
            $path . '/stanfordparser/stanford-parser.jar',
            $path . '/stanfordparser/stanford-parser-3.7.0-models.jar'
        );
        $lines = explode(".", $text);
        foreach ($lines as $line){

        }
        $result = $parser->parseSentence("she had no family history of diabetes");
        return $result;
    }

}
