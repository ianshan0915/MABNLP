<?php

namespace App\Http\Controllers;


use Illuminate\Console\Scheduling\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\Config\Definition\Exception\Exception;

class UMLSController extends Controller
{
    public static $service_url;
    public static $cached_annotation_umls = array();
    private $CURLMH;

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
        return view('home');
    }


    public function UMLS(){
        if(isset($_POST['remote']) && $_POST['remote'] == 1){
            return $this->UMLSRemoteDatabase();
        }
        else if(isset($_POST['remote']) && $_POST['remote'] == 2){
            return $this->UMLSCombination();
        }
        else if(isset($_POST['remote']) && $_POST['remote'] == 3){
            return $this->UMLSCombinationMulti();
        }
        else{
            return $this->UMLSLocalDatabase();
        }
    }
    private static function UMLSGetServiceTicket(){
        $api_key = "f70d6f42-d385-48df-81a3-23d79e71d2a4";

        $url = "https://utslogin.nlm.nih.gov/cas/v1/api-key";

        if(!isset($ch)){
            $ch = curl_init();
        }


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "apikey=".$api_key);
        curl_setopt($ch, CURLOPT_POST, 1);

        $result = curl_exec($ch);
        //echo "CURL for getting service ticket:" . curl_getinfo($ch, CURLINFO_TOTAL_TIME) . "</br>";
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }



        // Create a DOM object
        $dom = new \DOMDocument();
// Load HTML from a string
        $dom->loadHTML($result);


        foreach($dom->getElementsByTagName('form') as $link) {
            self::$service_url = $link->getAttribute('action');
        }
    }
    private function UMLSGetRequestTicket(){
        if(!isset($this->service_url)){
            $this->UMLSGetServiceTicket();
        }
        if(!isset($ch)){
            $ch = curl_init();
        }


        curl_setopt($ch, CURLOPT_URL, $this->service_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "service=http://umlsks.nlm.nih.gov");
        curl_setopt($ch, CURLOPT_POST, 1);

        $result = curl_exec($ch);
        //echo "CURL for getting request ticket:" . curl_getinfo($ch, CURLINFO_TOTAL_TIME) . "</br>";
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        return $result;
    }
    private static function UMLSGetRequestTicketBatch($amount){
        if(!isset(self::$service_url)){
            UMLSController::UMLSGetServiceTicket();
        }
        $multihandle = curl_multi_init();

        $handles = array();
        for ($i = 0; $i <= $amount; $i++){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$service_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "service=http://umlsks.nlm.nih.gov");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_multi_add_handle($multihandle,$ch);
            $handles[] = $ch;
        }

        $running = null;
        do {
            curl_multi_exec($multihandle, $running);
        } while ($running);


        $tickets = array();
        foreach ($handles as $handle) {
            $result = curl_multi_getcontent($handle);
            $tickets[] = $result;
        }
        return $tickets;
    }
    private function UMLSByURL($uri){
        $ticket = $this->UMLSGetRequestTicket();
        $atomsources = $_POST['atomsources'];
        $values = array(
            'ticket' => $ticket,
            'sabs' => $atomsources
        );
        $params = http_build_query($values);
        $url = $uri;
        if (strpos($uri, 'relations') === false) {
            $url = $uri."?".$params;
        }
        else{
            $url = $uri . "?ticket=".$ticket;
        }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        //echo "CURL for URL: " . $url . ": " . curl_getinfo($ch, CURLINFO_TOTAL_TIME). "</br>";
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);

        $json = json_decode($result);
        return $json;
    }
    private function UMLSRemoteDatabase(){
        $ticket = $this->UMLSGetRequestTicket();

        $searchstring = $_POST['terms'];
        $atomsources = $_POST['atomsources'];

        $values = array(
            'ticket' => $ticket,
            'string' => $searchstring,
            //'sabs' => $atomsources

        );

        $params = http_build_query($values);
        $url = "https://uts-ws.nlm.nih.gov/rest/search/current?".$params;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);



        $result = curl_exec($ch);

        //echo "CURL for URL: " . $url . ": " . curl_getinfo($ch, CURLINFO_TOTAL_TIME). "</br>";
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }



        $json = json_decode($result);
        $NumberOfResults = 0;
        $MaxResults = 2;

        foreach ($json->result->results as $index=>$result){
            if($index < $MaxResults){
                if(isset($result->uri)){
                    $NumberOfResults++;
                    $url = $result->uri . "/atoms";
                    $atoms =$this->UMLSByURL($url);
                    /*
                    $bool = usort($atoms->result, function($a, $b){
                        if($a->termType == "PT" &&  $b->termType != "PT"){
                            return -1;
                        }
                        else if ($a->termType == "PT" &&  $b->termType == "PT"){
                            return 0;
                        }
                        else{
                            return 1;
                        }
                    });
                    */
                    //$result->{"atomInformation"} = $atoms;
                    //$result->{"cuiInformation"} = $this->UMLSByURL($result->uri)->result;
                    //$relationsURL = $result->{"cuiInformation"}->relations;
                    //$result->{"relationInformation"} = $this->UMLSByURL($relationsURL);
                }
            }

        }



        return JsonResponse::create($json->result->results);
    }
    private function UMLSLocalDatabase(){
        $text = $_POST['terms'];
        $words = explode(' ', $text);
        $completeResult = array();
        $builder = DB::connection('umls_db');






        //$result = $builder->select(DB::raw("select * from mrxns_eng AS a INNER JOIN mrconso as B on a.cui = b.cui and a.lui = b.lui where match(a.nstr) against('".$text."') AND b.sab = 'SNOMEDCT_US'"));
        $result = $builder->select(DB::raw("select * from mrxns_eng AS a where match(a.nstr) against('".$text."')"));

        foreach ($result as $s){
            $atom_string_count = count(explode(' ',$s->NSTR));
            $occurrences = substr_count(strtolower($s->NSTR),strtolower($text));
            $text_word_count = count($words);

            $s->{'accuracy'} = ($occurrences*$text_word_count) / $atom_string_count;
            if($s->{'accuracy'}  > 0.3){
                $completeResult[] = $s;
            }

        }
        $bool = usort($completeResult, function($a, $b){
            if($a->accuracy > $b->accuracy){
                return -1;
            }
            else if ($a->accuracy == $b->accuracy){
                return 0;
            }
            else{
                return 1;
            }
        });


        $completeResult = array_map('json_encode', $completeResult);
        $completeResult = array_unique($completeResult);
        $completeResult = array_map('json_decode', $completeResult);


        foreach ($completeResult as $index=>$result){
            $newresult = $builder
                ->table('mrconso  AS a')
                ->select("*")
                ->where("a.cui", "=", $result->CUI)
                ->where("a.lui", "=", $result->LUI)
                ->where( function ( $query )
                {
                    $atomsources = explode(',',$_POST['atomsources']);
                    foreach ($atomsources as $asource)
                    $query->orWhere("a.sab", "=", $asource);
                })

                ->get();
            //$newresult = $builder->select(DB::raw("select * from mrconso AS a where a.cui = :cui and a.lui = :lui and a.sab = 'icd10'"), array('cui'=>$result->CUI, 'lui'=>$result->LUI));
            $result->{'atoms'} = $newresult;
        }
        foreach ($completeResult as $index=>$result) {
            if(count($result->atoms) > 0) {
                return JsonResponse::create($result);
            }
        }
        return JsonResponse::create(array());
    }
    private function UMLSCombinationMulti(){

        $searchstring = $_POST['terms'];
        $searchArray = explode(',',$searchstring);
        $atomsources = $_POST['atomsources'];
        $this->CURLMH = curl_multi_init();

        $tickets = $this->UMLSGetRequestTicketBatch(count($searchArray));

        $handles = array();
        foreach ($searchArray as $index=>$concept){

            $values = array(
                'ticket' => $tickets[$index],
                'string' => $concept,
                'sabs' => $atomsources
            );

            $params = http_build_query($values);
            $url = "https://uts-ws.nlm.nih.gov/rest/search/current?".$params;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_multi_add_handle($this->CURLMH,$ch);
            $handles[] = $ch;
        }

        $running = null;
        do {
            curl_multi_exec($this->CURLMH, $running);
        } while ($running);

        $completeResult = array();
        foreach ($handles as $index=>$handle){
            $result = curl_multi_getcontent($handle);
            $result = json_decode($result);

            $topresult = $result->result->results[0];
            $topresult->{'NSTR'} = $topresult->name;
            $topresult->{'responsetime'} = curl_getinfo($handle, CURLINFO_TOTAL_TIME);



            $CUI = $topresult->ui;
            $builder = DB::connection('umls_db');
            $newresult = $builder
                ->table('mrconso  AS a')
                ->select("*")
                ->where("a.cui", "=", $CUI)
                ->where( function ( $query )
                {
                    $atomsources = explode(',',$_POST['atomsources']);
                    foreach ($atomsources as $asource)
                        $query->orWhere("a.sab", "=", $asource);
                })
                ->get();
            if(count($newresult) > 0){
                $correctatom = array();
                foreach ($newresult as $atom){
                    if($atom->TTY == "PT"){
                        array_push($correctatom, $atom);
                    }
                }
                $topresult->{'atoms'} = $correctatom;
                $topresult->{'original_term'} = $searchArray[$index];

                $completeResult[] = $topresult;
            }
        }
        return JsonResponse::create($completeResult);
    }
    private function UMLSCombination(){

        $ticket = $this->UMLSGetRequestTicket();

        $searchstring = $_POST['text'];
        $atomsources = $_POST['atomsources'];

        $values = array(
            'ticket' => $ticket,
            'string' => $searchstring,
            'sabs' => $atomsources
        );

        $params = http_build_query($values);
        $url = "https://uts-ws.nlm.nih.gov/rest/search/current?".$params;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        $result = json_decode($result);

        $topresult = $result->result->results[0];
        $topresult->{'NSTR'} = $topresult->name;
        $topresult->{'responsetime'} = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }





        $CUI = $topresult->ui;

        $builder = DB::connection('umls_db');
        $newresult = $builder
            ->table('mrconso  AS a')
            ->select("*")
            ->where("a.cui", "=", $CUI)
            ->where( function ( $query )
            {
                $atomsources = explode(',',$_POST['atomsources']);
                foreach ($atomsources as $asource)
                    $query->orWhere("a.sab", "=", $asource);
            })

            ->get();
        if(count($newresult) > 0){
            $correctatom = array();
            foreach ($newresult as $atom){
                if($atom->TTY == "PT"){
                    array_push($correctatom, $atom);
                }
            }
            $topresult->{'atoms'} = $correctatom;

            return JsonResponse::create([$topresult]);
        }
        else{
            return JsonResponse::create(array());
        }



    }
    public static function GetUMLSIdentifierForTerms($list_of_terms){

        $returnarray = array();
        $tickets = UMLSController::UMLSGetRequestTicketBatch(count($list_of_terms));
        $CURLMH = curl_multi_init();
        foreach ($list_of_terms as $index=>$term) {



            $values = array(
                'ticket' => $tickets[$index],
                'string' => $term,
            );

            $params = http_build_query($values);
            $url = "https://uts-ws.nlm.nih.gov/rest/search/current?" . $params;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_multi_add_handle($CURLMH, $ch);
            $handles[] = $ch;
        }

        $running = null;
        do {
            curl_multi_exec($CURLMH, $running);
        } while ($running);


        foreach ($handles as $index=>$handle){
            $result = curl_multi_getcontent($handle);
            $result = json_decode($result);
            if($result == null){
                $returnarray[$list_of_terms[$index]] = array();
                continue;
            }
            else{
                for ($c = 0; $c < min(10, count($result->result->results)); $c++){
                    $item = $result->result->results[$c];
                    $returnarray[$list_of_terms[$index]][] = $item;
                }
            }
        }

        return $returnarray;
    }
    public static function CompareTermsInDocument1($document_id, $annotation_terms, $extraction_type, $extractor, $prefix){


        $non_cached = array();
        foreach ($annotation_terms as $index=>$umls){
            if(!in_array($umls, array_keys(self::$cached_annotation_umls))){
                $non_cached[] = $umls;
            }
        }
        if($extractor != null){
            $document_extractions = DatabaseController::GetAllUMLSForExtractorInDocumentOfType($extractor,$document_id,$extraction_type);
        }
        else{
            $document_extractions = DatabaseController::GetAllUMLSInDocumentOfType($document_id,$extraction_type);
        }
        if(count($document_extractions) == 0){
            return ["Extracted_Positive" => array(), "Extracted_Negative" => array()];
        }
        $document_extraction_umls_identifiers = array();
        $document_extraction_term_umls_identifiers = array();
        $document_cuid_extraction = array();
        $Extracted_Positive = array();
        $Extracted_Negative = array();


        if(count($non_cached)>0){
            $annotation_umls_identifiers = UMLSController::GetUMLSIdentifierForTerms($annotation_terms);
        }
        else{
            $annotation_umls_identifiers = array();
            foreach ($annotation_terms as $index=>$aterm){
                $annotation_umls_identifiers[$aterm] = self::$cached_annotation_umls[$aterm];
            }
        }
        foreach ($document_extractions as $extraction){
            $document_extraction_term_umls_identifiers[$extraction->term][$extraction->priority] = $extraction->umls_id;
            $document_extraction_id_umls[$extraction->id][] = $extraction->umls_id;
            $document_extraction_id_object[$extraction->id] = (object)array(
                "extractor"=>$extraction->extractor,
                "term" => $extraction->term,
                "negated" => $extraction->negated);

            $document_term_extraction[$extraction->term][] = $extraction;
            $document_cuid_extraction[$extraction->umls_id] = $extraction->term;
        }
        $annotation_umls_cuids = array();

        foreach ($annotation_umls_identifiers as $annotation_term => $annotation_array){
            foreach ($annotation_array as $index=>$annotation_item){
                UMLSController::AddAnnotationItemToCache($annotation_term,$annotation_item);
                $cui = $annotation_item->ui;
                $annotation_umls_cuids[$annotation_term][] = $cui;
            }
        }
        $ExtractedButNotAnnotated = array();
        foreach ($document_extraction_id_umls as $extraction_id => $extraction_umls_set) {
            $extraction = $document_extraction_id_object[$extraction_id];
            $extraction_term = UMLSController::NormalizeWord($extraction->term);
            $extractor = $extraction->extractor;
            $extraction_cuid_set = $document_extraction_term_umls_identifiers[$extraction->term];
            ksort($extraction_cuid_set);
            $extraction_annotation_links = array();
            foreach ($annotation_umls_cuids as $annotation_term => $annotation_cuid_set){
                if($extraction_term == "metoprolol" && $document_id == "stripa4"){
                    $i = 0;
                    if($annotation_term == "Diabetes"){
                        $i = 0;
                    }
                }
                if(UMLSController::DoUMLSSetsMatch($extraction_cuid_set, $annotation_cuid_set)){
                    $extraction_annotation_links[$extraction_term] = (object)array(
                        "extraction_cuids"=>$extraction_cuid_set,
                        "annotation_cuids"=>$annotation_cuid_set,
                        "annotation_term"=>$annotation_term,
                        );
                    $i = 0;
                }
            }
            if(count($extraction_annotation_links) > 0){
                //Link found between the current extraction and one or more of the annotations. Using the annotation term for further references.
                foreach ($extraction_annotation_links as $extraction_term => $link_object){
                    $annotation_term = UMLSController::NormalizeWord($link_object->annotation_term);

                    //If
                    if (DocumentHandler::$Negation_Enabled && $extraction->negated == 1) {
                        $extractor = $extraction->extractor;// . "_" . $extraction_umls;
                        $Extracted_Negative[$annotation_term][] = $extractor;
                        $Extracted_Negative[$annotation_term] = array_values(array_unique($Extracted_Negative[$annotation_term]));
                    } else {
                        $extractor = $extraction->extractor;// . "_" . $extraction_umls;
                        $Extracted_Positive[$annotation_term][] = $extractor;
                        $Extracted_Positive[$annotation_term] = array_values(array_unique($Extracted_Positive[$annotation_term]));
                    }
                }
            }
            else{
                //Did not find a link, but did extract it. Using a combination of CUIDS for further references. This should not be done when using the diabetes test set
                if($prefix !== "obesity"){
                    $found_match = false;
                    foreach ($ExtractedButNotAnnotated as $base64_cuid_key => $extractor_array){
                        $current_set = explode("-",base64_decode($base64_cuid_key));
                        if(UMLSController::DoUMLSSetsMatch($current_set, $extraction_cuid_set)){
                            //If the current loop extraction matches

                            //Calculate Intersection
                            $intersection = array_intersect($current_set,$extraction_cuid_set);

                            //Calculate the new key, based on the intersection
                            $cui_key = base64_encode(join("-",$intersection));

                            //Add the extractor to the current list of extractors and put it in for the new key. Make it unique.
                            $ExtractedButNotAnnotated[$cui_key] = array_merge($ExtractedButNotAnnotated[$base64_cuid_key],[$extractor]);
                            $ExtractedButNotAnnotated[$cui_key] = array_values(array_unique($ExtractedButNotAnnotated[$cui_key]));

                            //Remove the old key
                            if($cui_key !== $base64_cuid_key){
                                unset($ExtractedButNotAnnotated[$base64_cuid_key]);
                            }


                            //Set the found match flag to true;
                            $found_match = true;
                        }
                    }
                    if(!$found_match){
                        //If no match was found in the list of "extracted but not annotated"
                        //Set up new key and add it.
                        $cui_key = base64_encode(join("-",$extraction_cuid_set));
                        $ExtractedButNotAnnotated[$cui_key][] = $extractor;
                        $ExtractedButNotAnnotated[$cui_key] = array_values(array_unique($ExtractedButNotAnnotated[$cui_key]));
                    }
                }
            }

        }
        foreach ($ExtractedButNotAnnotated as $base64_cuid_key => $extractor_array){
            $current_set = explode("-",base64_decode($base64_cuid_key));
            $cuid_term = $document_cuid_extraction[$current_set[0]];
            if(in_array($cuid_term,array_keys($Extracted_Positive))){
                //Dit zou niet mogen gebeuren, maargoed
                $Extracted_Positive[$cuid_term] == array_merge($Extracted_Positive[$cuid_term],$extractor_array);
                $Extracted_Positive[$cuid_term] = array_values(array_unique($Extracted_Positive[$cuid_term]));
            }else{
                $Extracted_Positive[$cuid_term] = $extractor_array;
            }

        }

        $Extracted_Positive_Lowercase = array_map('strtolower',array_keys($Extracted_Positive));
        $Extracted_Negative_Lowercase = array_map('strtolower',array_keys($Extracted_Negative));
        $All_Extracted = array_merge($Extracted_Positive_Lowercase,$Extracted_Negative_Lowercase);
        $Annotation_Terms_Lowercase = array_map('strtolower',array_values($annotation_terms));
        $Annotations_Not_Found = array_values(array_diff($Annotation_Terms_Lowercase, $All_Extracted));
        foreach ($Annotations_Not_Found as $index=> $a_name){
            if(!in_array($a_name,$Extracted_Negative)){
                $Extracted_Negative[$a_name] = array();
            }
        }


        return ["Extracted_Positive" => $Extracted_Positive, "Extracted_Negative" => $Extracted_Negative];

    }
    public static function DoUMLSSetsMatch($SetOne, $SetTwo){
        $min_count = min(count($SetOne), count($SetTwo));
        $complete_intersect = array_intersect($SetOne, $SetTwo);
        $complete_weight = count($complete_intersect) / $min_count;

        $Sliced_One = array_slice($SetOne, 0, 3);
        $Sliced_Two = array_slice($SetTwo, 0, 3);
        $top_intersect = array_intersect($Sliced_One, $Sliced_Two);
        $min_count_top = min(count($Sliced_One), count($Sliced_Two));
        $top_weight = count($top_intersect) / $min_count_top;

        $complete_weight = $complete_weight * 0.35;
        $top_weight = $top_weight * 0.65;

        $total_weight = $complete_weight + $top_weight;




        if($total_weight > DocumentHandler::$UMLS_Similarity_Threshold){
            return true;
        }
        return false;
    }
    public static function NormalizeWord($word){
        $word = strtolower($word);
        $word = join(" ",explode("\n", $word));
        return $word;

    }
    public static function AddAnnotationItemToCache($annotation_term,$annotation_item){
        if(
            isset(self::$cached_annotation_umls[$annotation_term]) &&
            !in_array($annotation_item,self::$cached_annotation_umls[$annotation_term])
        ){
            self::$cached_annotation_umls[$annotation_term][] = $annotation_item;
        }
        else if(!isset(self::$cached_annotation_umls[$annotation_term])){
            self::$cached_annotation_umls[$annotation_term][] = $annotation_item;
        }
    }


    public function ImportTerm(){


        $amount_of_extractions = DB::table('extractions')->select("*")->count();
        $batch_size = 100;
        $iterations = ceil($amount_of_extractions / $batch_size);


        for ($i = 0; $i < $iterations; $i++){
            $returnarray = array();
            $extractions = DB::table('extractions')->select("*")->where('documentid', 'LIKE', 'obesity%')->where('extractor','=','Dandelion')->limit($batch_size)->offset($i*$batch_size)->get();
            $handles = array();
            $this->CURLMH = curl_multi_init();
            $tickets = $this->UMLSGetRequestTicketBatch(count($extractions));
            foreach ($extractions as $index=>$extraction){


                $values = array(
                    'ticket' => $tickets[$index],
                    'string' => $extraction->term
                );

                $params = http_build_query($values);
                $url = "https://uts-ws.nlm.nih.gov/rest/search/current?".$params;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_multi_add_handle($this->CURLMH,$ch);
                $handles[] = $ch;
            }
            $running = null;
            do {
                curl_multi_exec($this->CURLMH, $running);
            } while ($running);


            foreach ($handles as $index=>$handle){
                if(gettype($extractions[$index]) == 'object'){
                    $id = $extractions[$index]->id;
                }
                else{
                    continue;
                }


                $result = curl_multi_getcontent($handle);
                $result = json_decode($result);
                if($result == null){
                    $returnarray[$id] = array();
                    continue;
                }
                else{
                    for ($c = 0; $c < min(10, count($result->result->results)); $c++){
                        $item = $result->result->results[$c];
                        $returnarray[$id][] = $item;
                    }
                }
            }
            $data = array();
            foreach ($returnarray as $extraction_id=>$umlsresults){
                foreach ($umlsresults as $index=>$umlsresult){
                    $single = array();
                    $single['extraction_id'] = $extraction_id;
                    $single['umls_id'] = $umlsresult->ui;
                    $single['priority'] = $index;
                    $data[] = $single;
                }
            }
            DB::table('extractions_umls')->insert($data);
        }


        return JsonResponse::create($returnarray);
    }
    public function GetTermForCUI(){


        $amount_of_extractions = DB::table('extractions')->select("*")->count();
        $batch_size = 100;
        $iterations = ceil($amount_of_extractions / $batch_size);


        for ($i = 0; $i < $iterations; $i++){
            $returnarray = array();
            $extractions = DB::table('extractions')->select("*")->where('documentid', 'LIKE', 'obesity%')->where('extractor','=','Dandelion')->limit($batch_size)->offset($i*$batch_size)->get();
            $handles = array();
            $this->CURLMH = curl_multi_init();
            $tickets = $this->UMLSGetRequestTicketBatch(count($extractions));
            foreach ($extractions as $index=>$extraction){


                $values = array(
                    'ticket' => $tickets[$index],
                    'string' => $extraction->term
                );

                $params = http_build_query($values);
                $url = "https://uts-ws.nlm.nih.gov/rest/search/current?".$params;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_multi_add_handle($this->CURLMH,$ch);
                $handles[] = $ch;
            }
            $running = null;
            do {
                curl_multi_exec($this->CURLMH, $running);
            } while ($running);


            foreach ($handles as $index=>$handle){
                if(gettype($extractions[$index]) == 'object'){
                    $id = $extractions[$index]->id;
                }
                else{
                    continue;
                }


                $result = curl_multi_getcontent($handle);
                $result = json_decode($result);
                if($result == null){
                    $returnarray[$id] = array();
                    continue;
                }
                else{
                    for ($c = 0; $c < min(10, count($result->result->results)); $c++){
                        $item = $result->result->results[$c];
                        $returnarray[$id][] = $item;
                    }
                }
            }
            $data = array();
            foreach ($returnarray as $extraction_id=>$umlsresults){
                foreach ($umlsresults as $index=>$umlsresult){
                    $single = array();
                    $single['extraction_id'] = $extraction_id;
                    $single['umls_id'] = $umlsresult->ui;
                    $single['priority'] = $index;
                    $data[] = $single;
                }
            }
            DB::table('extractions_umls')->insert($data);
        }


        return JsonResponse::create($returnarray);
    }
    function contains($needle, $haystack, $casesensitive)
    {
        if($casesensitive){
            $bool = strpos($haystack, $needle) !== false;
            return  $bool;
        }
        else{
            $bool = strpos(strtolower($haystack), strtolower($needle)) !== false;
            return  $bool;
        }


    }

}


