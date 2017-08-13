<?php

namespace App\Http\Controllers;


use Illuminate\Console\Scheduling\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Psy\Util\Json;

class XMLImporter extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }
    public function index()
    {
        return view('home');
    }
    public function ImportXML(){

        $xml = simplexml_load_file("icdClaML2016ens.xml") or die("Error: Cannot create object");


        try{
            set_time_limit(0);
            $my_std_class = json_decode(json_encode($xml));
        }
        catch (\Exception $exception){
            print_r($exception);
        }


        $classes = $my_std_class->{'Class'};


        DB::table('ICD10Classes')->delete();
        DB::table('ICD10SuperClasses')->delete();
        DB::table('ICD10Blocks')->delete();


        foreach ($classes as $class){
            if($class->{"@attributes"}->kind == "block"){
                $this->AddBlock($class);
            }
        }



        foreach ($classes as $class){
            if($class->{"@attributes"}->kind == "category"){
                if(isset($class->SubClass)){
                    //Found a superclass!
                    $this->AddSuperclass($class);
                }
            }
        }


        foreach ($classes as $class){
            if($class->{"@attributes"}->kind == "category"){
                if(!isset($class->SubClass) && isset($class->SuperClass)){
                    //Found a subclass!
                    $this->AddSubclass($class);
                }
            }
        }

        return JsonResponse::create("Finished");
    }
    public function AddBlock($class){
        $preferred = "";

        try{
            if(is_array($class->Rubric)){
                foreach ($class->Rubric as $rubric){
                    if($rubric->{'@attributes'}->kind == "preferred"){
                        if(is_string($rubric->Label)){
                            $preferred = $rubric->Label;
                        }
                        else{
                            $preferred = json_encode($rubric->Label);
                        }
                    }
                }
            }
            else{
                if($class->Rubric->{'@attributes'}->kind == "preferred"){
                    $preferred = $class->Rubric->Label;
                }
            }

            $code = $class->{"@attributes"}->code;
            DB::table('ICD10Blocks')->insert([
                'code'=>$code,
                'preferred' => $preferred
            ]);
        }
        catch (\Exception $exception){
            print_r($exception);

        }





    }
    public function AddSuperclass($class){
        $preferred = "";
        $note = "";
        try{
            if(is_array($class->Rubric)){
                foreach ($class->Rubric as $rubric){
                    if($rubric->{'@attributes'}->kind == "preferred"){
                        if(is_string($rubric->Label)){
                            $preferred = $rubric->Label;
                        }
                        else{
                            $preferred = json_encode($rubric->Label);
                        }
                    }

                }
                $code = $class->{"@attributes"}->code;
            }
            else{
                if($class->Rubric->{'@attributes'}->kind == "preferred"){
                    $preferred = $class->Rubric->Label;
                }
                $code = $class->{"@attributes"}->code;
            }
            $superclass = $class->SuperClass->{"@attributes"}->code;
            $result = DB::table('ICD10Blocks')->where('code',$superclass)->get();

            if(count($result) > 0){
                DB::table('ICD10SuperClasses')->insert([
                    'code'=>$code,
                    'preferred' => $preferred,
                    'superclass' => $superclass
                ]);
            }
            else{
                echo "block for superclass not found:" . $superclass .  "</br>";
            }
        }
        catch (\Exception $exception){
            print_r($exception);
        }
    }
    public function AddSubclass($class){

        $preferred = "";

        try{
            if(is_array($class->Rubric)){
                foreach ($class->Rubric as $rubric){
                    if($rubric->{'@attributes'}->kind == "preferred"){
                        if(is_string($rubric->Label)){
                            $preferred = $rubric->Label;
                        }
                        else{
                            $preferred = json_encode($rubric->Label);
                        }
                    }
                }
                $code = $class->{"@attributes"}->code;
            }
            else{
                if($class->Rubric->{'@attributes'}->kind == "preferred"){
                    $preferred = $class->Rubric->Label;
                }
                $code = $class->{"@attributes"}->code;
            }
            $superclass = $class->SuperClass->{"@attributes"}->code;

            $result = DB::table('ICD10SuperClasses')->where('code',$superclass)->get();
            $resultBlocks = DB::table('ICD10Blocks')->where('code',$superclass)->get();

            if(count($result) > 0) {
                DB::table('ICD10Classes')->insert([
                    'code'=>$code,
                    'preferred' => $preferred,
                    'superclass' => $superclass
                ]);
            }
            else if(count($resultBlocks) > 0){
                DB::table('ICD10Classes')->insert([
                    'code'=>$code,
                    'preferred' => $preferred,
                    'superblock' => $superclass
                ]);
            }
            else{
                echo "superclass for class not found:" . $superclass .  "</br>";
            }
        }
        catch (\Exception $exception){
            print_r($exception);

        }

    }
}


