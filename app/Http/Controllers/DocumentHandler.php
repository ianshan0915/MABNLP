<?php

namespace App\Http\Controllers;


use Illuminate\Console\Scheduling\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DocumentHandler extends Controller
{

    public static $Extractor_Threshold;
    public static $UMLS_Similarity_Threshold;
    public static $Negation_Enabled;




    //******************//
    //Document Importers//
    //******************//
    public function InsertExtraction(){

        try{
            $document_id = $_POST['documentid'];
            $terms = $_POST['term'];
            $extractor = $_POST['extractor'];
            $types = $_POST['types'];
            $quantity = $_POST['quantity'];
            $negated = $_POST['negation'];

            $data = array();
            foreach ($terms as $index=>$term){
                $single = array();
                $single['term'] = $term;
                $single['documentid'] = $document_id[$index];
                $single['extractor'] = $extractor;
                $single['type'] = $types[$index];
                $single['negated'] = $negated[$index];
                $quantity_value = $quantity[$index];
                if($quantity_value != "null"){
                    $single['quantity'] = $quantity_value;
                }
                $data[] = $single;
            }
            $result = DB::table('extractions')->insert($data);
        }
        catch(\Exception $e){
            return JsonResponse::create($e);
        }
    }
    public function GetAllDocuments(){
        if(isset($_GET['documentids'])){
            $documentids = $_GET['documentids'];
            $builder = DB::table('documents')->select('*');
            foreach ($documentids as $index=>$doc){
                $builder = $builder->orWhere('documentid', '=', $doc);
            }
            $sql = $builder->toSQL();
            $result = $builder->get();
            return JsonResponse::create($result);
        }
        else{
            $offset = $_GET['offset'];
            $builder = DB::table('documents')->select('*');
            if(isset($_GET['limit']) && $_GET['limit'] > 0){
                $limit = $_GET['limit'];
                $builder->limit($limit)->offset($offset);
            }
            $extractorlike = $_GET['ExtractorDocumentIDLike'];
            if(isset($extractorlike) && count($extractorlike) > 0){
                $builder = $builder->where('documentid', 'like', $extractorlike.'%');
            }
            $sql = $builder->toSQL();
            $result = $builder->get();

            return JsonResponse::create($result);
        }


    }
    public function Importfile(){
        $file = \Illuminate\Support\Facades\Input::file('xml');
        $xml = simplexml_load_file($file) or die("Error: Cannot create object");
        $json = json_decode(json_encode($xml));

        foreach ($json->docs->doc as $index=>$document){
            $document_id = $document->{'@attributes'}->id;
            $text = $document->text;
            DB::table('documents')->insert(['documentid'=>$document_id, 'text'=>$text]);
        }
    }
    public function ImportDrugDocumentfile(){
        $file = \Illuminate\Support\Facades\Input::file('document');
        $document_id = $file->getClientOriginalName();
        $json = json_decode(json_encode($file));

        $content = File::get($file);

        DB::table('documents')->insert(['documentid'=>"i2b2".$document_id, 'text'=>$content]);
    }

    //*******//
    //Obesity//
    //*******//
    public function ImportAnnotationsfile(){
        $file = \Illuminate\Support\Facades\Input::file('annotations');
        $xml = simplexml_load_file($file) or die("Error: Cannot create object");
        $json = json_decode(json_encode($xml));

        $document_disease_array = $this->CreateDocumentDiseaseArray($json);
        $throughUMLS = true;
        $maxpriority = 10;

        if(isset($_POST['through_umls'])){
            $throughUMLS = true;
        }
        if(isset($_POST['maxpriority'])){
            $maxpriority = $_POST['maxpriority'];
        }

        if($throughUMLS){
            $prefix = "obesity";
            $document_drug_array = $this->CalculateByIndividualFScore($document_disease_array, "MedicalCondition",$prefix);
            $this->CreateAndSaveCSV("obesity_result", "obesity", $document_drug_array);
        }
        else{
            $document_drug_array = $this->HandleAnnotationComparison($document_disease_array, "MedicalCondition", "obesity");
        }
        $json_response = JsonResponse::create($document_drug_array);
        return $json_response;
    }
    public function CreateDocumentDiseaseArray($json){
        $document_disease_array = array();
        foreach ($json->diseases[1]->disease as $index=>$disease){
            $disease_name = $disease->{'@attributes'}->name;
            foreach ($disease->doc as $document_judgement) {
                $doc_id = $document_judgement->{'@attributes'}->id;
                $judgement = $document_judgement->{'@attributes'}->judgment;
                if($judgement == "Y"){
                    $document_disease_array[$doc_id][] = (object)array("term" => $disease_name, "in_document" => "yes");
                }
                else if($judgement == "U"){
                    $document_disease_array[$doc_id][] = (object)array("term" => $disease_name, "in_document" => "undecided");
                }
                else{
                    $document_disease_array[$doc_id][] = (object)array("term" => $disease_name, "in_document" => "no");
                }
            }
        }
        return $document_disease_array;
    }
    public function CalculateYesAndNoPerDocument($document_disease_array){
        $ActualYesNoPerDocument = array();
        foreach ($document_disease_array as $document_index => $diseasearray){
            $ActualYesNoPerDocument[$document_index]{'yes'} = array();
            $ActualYesNoPerDocument[$document_index]{'no'} = array();
            foreach ($diseasearray as $disease){

                if($disease->in_document == 'yes'){
                    $ActualYesNoPerDocument[$document_index]{'yes'}[] = $disease->term;

                }
                if($disease->in_document == 'no'){
                    $ActualYesNoPerDocument[$document_index]{'no'}[] = $disease->term;
                }
            }
        }
        return $ActualYesNoPerDocument;
    }


    //***********//
    //Medications//
    //***********//
    public function ImportDrugDocumentAnnotationsfile(){
        $file = \Illuminate\Support\Facades\Input::file('annotations');
        $xml = simplexml_load_file($file) or die("Error: Cannot create object");
        $json = json_decode(json_encode($xml));

        $maxpriority = 10;
        $throughUMLS = true;

        if(isset($_POST['through_umls'])){
            $throughUMLS = true;
        }
        if(isset($_POST['maxpriority'])){
            $maxpriority = $_POST['maxpriority'];
        }

        $drug_array = $this->CreateDrugObjectArray($json);

        if($throughUMLS){
            $prefix = "i2b2";
            $document_drug_array = $this->CalculateByIndividualFScore($drug_array, "Drug",$prefix);
            $this->CreateAndSaveCSV("obesity_result", "obesity", $document_drug_array);
        }
        else{
            $document_drug_array = $this->HandleAnnotationComparison($drug_array, "Drug", "i2b2");
        }
        return JsonResponse::create($document_drug_array);
    }
    public function CreateDrugObjectArray($json){
        $document_drug_array = array();

        foreach ($json->document as $index=>$document){
            $text = $document->text;
            $id = $document->{"@attributes"}->id;
            $drug_array = array();

            $drug_lines = explode("\n", $text);
            $handled = array();
            foreach ($drug_lines as $drug_line){
                $drug_line = join("", explode("\t", $drug_line));
                $drug_properties = explode("||",$drug_line);
                if(count($drug_properties) == 1){
                    continue;
                }
                $drug = (object)array();

                foreach ($drug_properties as $drug_property){
                    $prop = explode("=", $drug_property);
                    $value = $prop[1];
                    $a = explode('"',$value);
                    $drug->{$prop[0]} = $a[1];
                }

                if($drug->m && !in_array($drug->m,$handled)){
                    $drug->{'term'} = str_replace(".", "", $drug->m);
                    $handled[] = $drug->m;
                    $drug_array[] = $drug;
                }

            }
            $document_drug_array[$id] = $drug_array;
        }
        return $document_drug_array;

    }

    //******//
    //STRIPA//
    //******//
    public function ImportSTRIPAtexts(){
        $file = \Illuminate\Support\Facades\Input::file('document');
        $document_id = $file->getClientOriginalName();
        $json = json_decode(json_encode($file));

        $content = File::get($file);

        DB::table('documents')->insert(['documentid'=>"stripa".$document_id, 'text'=>$content]);
    }
    public function ImportStripaAnnotationsfile(){
        $file = \Illuminate\Support\Facades\Input::file('annotations');

        $content = File::get($file);
        $csv_array = explode("\n", $content);

        $headers = array();
        $document_drug_array = array();
        foreach ($csv_array as $index => $entry){
            if($index == 0){
                //Handling Headers
                $headers = explode(",",$entry);
                continue;
            }
            $entry = explode(",",$entry);
            $object = (object)array();
            foreach ($entry as  $pindex => $property){
                $object->{$headers[$pindex]} = join("",explode('"',$property));
            }
            $object->{'term'} = $object->description;
            unset($object->description);
            $document_drug_array[$object->patientID][] = $object;
        }

        if(isset($_POST['Medication'])){
            $type = "Drug";
        }
        else{
            $type = "MedicalCondition";
        }

        $prefix = "stripa";

        $document_drug_array = $this->CalculateByIndividualFScore($document_drug_array, $type,$prefix);
        $this->CreateAndSaveCSV("obesity_result_".$type, "obesity", $document_drug_array);

        $json_response = JsonResponse::create($document_drug_array);
        return $json_response;
    }


    //****************//
    //HELPER FUNCTIONS//
    //****************//

    public function CalculateByIndividualFScore($document_drug_array, $type, $prefix){
        $this->SetGlobalVariables();
        $extractors = ["Watson", "OpenCalais", "Dandelion", "TextRazor", "Haven", "MeaningCloud"];
        $results = array();
        $fscores = array();

        $counter = 0;
        $result_array = array();
        foreach ($document_drug_array as $document_id => $clinical_term_array) {
            if(isset($_POST['document_id']) && $_POST['document_id'] != ""){
                if($_POST['document_id'] != $document_id){
                    continue;
                }
            }

            $extraction_count = count(DatabaseController::GetAllExtractionsForDocument($prefix.$document_id,$type));
            if($extraction_count == 0){
                continue;
            }
            $counter+=1;

            foreach ($extractors as $extractor) {
                $r = $this->HandleAnnotationComparisonStandardizedForDocument($document_id, $clinical_term_array, $type,$prefix,$extractor);
                //$r = $this->HandleAnnotationComparisonStandardized($document_drug_array, $type, $prefix, $extractor);
                $r = $this->CalculateStatistics($r);
                $results['Extractor_Results'][$extractor] = $r;
            }
            foreach ($results['Extractor_Results'] as $extractor => $extractor_result){
                $tp = $extractor_result['True_Positive'];
                $fp = $extractor_result['False_Positive'];
                $tn = $extractor_result['True_Negative'];
                $fn = $extractor_result['False_Negative'];
                $fscores[$extractor] = $this->CalculateFScore($tp,$fp,$fn);
            }
            $results['f-measures'] = $fscores;
            $total_fmeasure = 0;
            $f_measure_weight_array = array();
            foreach ($results['f-measures'] as $extractor => $measurements){
                $fmeasure = $measurements['fmeasure'];
                $total_fmeasure += $fmeasure;
            }
            foreach ($results['f-measures'] as $extractor => $measurements){
                $fmeasure = $measurements['fmeasure'];
                if($total_fmeasure == 0){
                    $f_measure_weight_array[$extractor] = 0;
                }
                else{
                    $f_measure_weight_array[$extractor] = $fmeasure / $total_fmeasure;
                }
            }
            $r = $this->HandleAnnotationComparisonStandardizedForDocument($document_id, $clinical_term_array, $type,$prefix,null);
            $r = $this->CalculateStatisticsWeighted($r,$f_measure_weight_array);
            $r['Original_Results'] = $results;
            $result_array['Documents'][$document_id] = $r;
        }
        $total_TP = 0;
        $total_FP = 0;
        $total_TN = 0;
        $total_FN = 0;
        foreach ($result_array['Documents'] as  $document_id => $results){
            $total_TP += $results['True_Positive_Weighted'];
            $total_FP += $results['False_Positive_Weighted'];
            $total_TN += $results['True_Negative_Weighted'];
            $total_FN += $results['False_Negative_Weighted'];
        }
        $result_array['Total_True_Positive_Weighted'] = $total_TP;
        $result_array['Total_False_Positive_Weighted'] = $total_FP;
        $result_array['Total_True_Negative_Weighted'] = $total_TN;
        $result_array['Total_False_Negative_Weighted'] = $total_FN;

        $result = $this->CalculateFScore($total_TP, $total_FP, $total_FN);
        $result_array['Recall'] = $result['recall'];
        $result_array['Precision'] = $result['precision'];
        $result_array['F-Score'] = $result['fmeasure'];
        $result_array['Negation_Enabled'] = self::$Negation_Enabled;
        $result_array['Similarity_Threshold'] = self::$UMLS_Similarity_Threshold;
        $result_array['Extractor_Threshold'] = self::$Extractor_Threshold;
        return $result_array;
    }
    public function CalculateFScore($tp, $fp, $fn){
        if($tp == 0){
            $recall = 0;
            $precision = 0;
        }
        else{
            $recall = $tp / ($tp + $fn);
            $precision = $tp / ($tp+$fp);
        }
        if($recall == 0 && $precision == 0){
            $fmeasure = 0;
        }
        else{
            $fmeasure = 2 * (($recall*$precision)/($recall+$precision));
        }
        return array("recall"=>$recall, "precision" => $precision, "fmeasure"=>$fmeasure);
    }
    public function HandleAnnotationComparisonStandardizedForDocument($document_index, $clinical_term_array, $term_type, $document_prefix, $extractor){
        $result_array = array();




        $annotation_positive = array();
        $annotation_negative = array();
        foreach ($clinical_term_array as $clinical_term){
            if(isset($clinical_term->in_document) && $clinical_term->in_document == "no"){
                $annotation_negative[] = $clinical_term->term;
            }
            else if (isset($clinical_term->in_document) && $clinical_term->in_document == "undecided"){
                $annotation_negative[] = $clinical_term->term;
            }
            else{
                $annotation_positive[] = $clinical_term->term;
            }
        }
        $annotation_all = array_merge($annotation_positive, $annotation_negative);
        $result = UMLSController::CompareTermsInDocument1($document_prefix.$document_index, $annotation_all, $term_type, $extractor, $document_prefix);
        $handled_clinical_term_positive = $result['Extracted_Positive'];
        $handled_clinical_term_negative = $result['Extracted_Negative'];
        $Extracted_Positive_Terms = array();
        foreach ($handled_clinical_term_positive as $extract_p=>$extractors){
            $Extracted_Positive_Terms[] = $extract_p;
        }
        $result_array = array_merge($result_array,array(
            'Document_Index'=> $document_index,
            'Extractor_Yes'=>$handled_clinical_term_positive,
            'Extractor_No'=>$handled_clinical_term_negative,
            'Actual_Yes' => $annotation_positive,
            'Actual_No' => $annotation_negative,
        ));
        return $result_array;
    }
    public function SetGlobalVariables(){
        if(isset($_POST['Extractor_Threshold']) && $_POST['Extractor_Threshold'] != ""){
            self::$Extractor_Threshold = $_POST['Extractor_Threshold'];
        }
        else{
            self::$Extractor_Threshold = 0.35;
        }

        if(isset($_POST['Similarity_Threshold']) && $_POST['Similarity_Threshold'] != ""){
            self::$UMLS_Similarity_Threshold = $_POST['Similarity_Threshold'];
        }
        else{
            self::$UMLS_Similarity_Threshold = 0.1;
        }
        if(isset($_POST['Negation'])){
            self::$Negation_Enabled = true;
        }
        else{
            self::$Negation_Enabled = false;
        }
    }

    public function CalculateStatistics($results){
        $TruePositiveTotal = 0;
        $FalsePositiveTotal = 0;
        $TrueNegativeTotal = 0;
        $FalseNegativeTotal = 0;

        $SingleExtractor = (object)array();
        $DoubleExtractor = (object)array();

        $SingleFalseExtractor = (object)array();
        $DoubleFalseExtractor = (object)array();

        $document = $results;

        $TruePositivesDocument = array();
        $FalsePositivesDocument = array();
        $TruePositiveDocument = 0;
        $FalsePositiveDocument = 0;
        $TrueNegativeDocument = 0;
        $FalseNegativeDocument = 0;


        foreach (array_keys($document['Extractor_Yes']) as $Extractor_Yes) {
            if (in_array(strtolower($Extractor_Yes), array_map('strtolower', $document['Actual_Yes']))) {
                //found true positive
                $TruePositiveDocument += 1;
                $TruePositivesDocument[] = $Extractor_Yes;

            } else if (!in_array(strtolower($Extractor_Yes), array_map('strtolower', $document['Actual_Yes']))) {
                //found false positive
                $FalsePositiveDocument += 1;
                $FalsePositivesDocument[] = $Extractor_Yes;
            }
        }
        foreach (array_keys($document['Extractor_No']) as $Extractor_No){
            if(in_array(strtolower($Extractor_No), array_map('strtolower',$document['Actual_No']))){
                //found true negative
                $TrueNegativeDocument += 1;

            }
            else if(
                !in_array(strtolower($Extractor_No), array_map('strtolower',$document['Actual_Yes'])) &&
                !in_array(strtolower($Extractor_No), array_map('strtolower',$document['Actual_No']))
            ){
                //Als ie zowel niet bij de actual yes als bij de actual no zit (voor als de actual no lijst leeg is)
                $TrueNegativeDocument += 1;
            }
            else if(!in_array(strtolower($Extractor_No), array_map('strtolower',$document['Actual_No']))){
                //found false negative
                $FalseNegativeDocument += 1;
            }
        }


        $document["True_Positive"] = $TruePositiveDocument;
        $document["False_Positive"] = $FalsePositiveDocument;
        $document["True_Negative"] = $TrueNegativeDocument;
        $document["False_Negative"] = $FalseNegativeDocument;

        foreach ($document['Extractor_Yes'] as $similar_yes => $similar_yes_extractors) {
            if(count($similar_yes_extractors) == 1 && in_array($similar_yes, $TruePositivesDocument)){
                $extractor = $similar_yes_extractors[0];
                isset($SingleExtractor->{$extractor}) ? $SingleExtractor->{$extractor} += 1 : $SingleExtractor->{$extractor} = 1 ;
            }
            else if(count($similar_yes_extractors) == 1 && in_array($similar_yes, $FalsePositivesDocument)){
                $extractor = $similar_yes_extractors[0];
                isset($SingleFalseExtractor->{$extractor}) ? $SingleFalseExtractor->{$extractor} += 1 : $SingleFalseExtractor->{$extractor} = 1 ;
            }
            if(count($similar_yes_extractors) == 2 && in_array($similar_yes, $TruePositivesDocument)){
                $extractor1 = $similar_yes_extractors[0];
                $extractor2 = $similar_yes_extractors[1];
                isset($DoubleExtractor->{$extractor1}) ? $DoubleExtractor->{$extractor1} += 1 : $DoubleExtractor->{$extractor1} = 1 ;
                isset($DoubleExtractor->{$extractor2}) ? $DoubleExtractor->{$extractor2} += 1 : $DoubleExtractor->{$extractor2} = 1 ;
            }
            else if(count($similar_yes_extractors) == 2 && in_array($similar_yes, $FalsePositivesDocument)){
                $extractor1 = $similar_yes_extractors[0];
                $extractor2 = $similar_yes_extractors[1];
                isset($DoubleFalseExtractor->{$extractor1}) ? $DoubleFalseExtractor->{$extractor1} += 1 : $DoubleFalseExtractor->{$extractor1} = 1 ;
                isset($DoubleFalseExtractor->{$extractor2}) ? $DoubleFalseExtractor->{$extractor2} += 1 : $DoubleFalseExtractor->{$extractor2} = 1 ;
            }
        }

        return $document;
    }
    public function CalculateStatisticsWeighted($results, $WeightArray){



        $TruePositiveDocumentWeighted = 0;
        $FalsePositiveDocumentWeighted = 0;
        $TrueNegativeDocumentWeighted = 0;
        $FalseNegativeDocumentWeighted = 0;

        $document = $results;

        foreach ($document['Extractor_Yes'] as $Term_Yes => $Extractor_Yes) {
            $term_probability_value = 0;
            foreach ($Extractor_Yes as $extractor){
                $term_probability_value += $WeightArray[$extractor];
            }
            if($term_probability_value > DocumentHandler::$Extractor_Threshold){
                $document['Weighted_Yes'][$Term_Yes] = $Extractor_Yes;
            }
            else{
                $document['Weighted_No'][$Term_Yes] = $Extractor_Yes;
            }
        }
        foreach ($document['Extractor_No'] as $Term_No => $Extractor_No) {
            $document['Weighted_No'][$Term_No] = $Extractor_No;
        }
        if(isset($document['Weighted_Yes'])){
            foreach ($document['Weighted_Yes'] as $Term_Yes => $Extractor_Yes) {
                if (in_array(strtolower($Term_Yes), array_map('strtolower', $document['Actual_Yes']))) {
                    //found true positive
                    $TruePositiveDocumentWeighted += 1;
                } else if (!in_array(strtolower($Term_Yes), array_map('strtolower', $document['Actual_Yes']))) {
                    //found false positive
                    $FalsePositiveDocumentWeighted += 1;
                }
            }
        }
        if(isset($document['Weighted_No'])){
            foreach ($document['Weighted_No'] as $Term_No=> $Extractor_No){
                if(in_array(strtolower($Term_No), array_map('strtolower',$document['Actual_No']))){
                    //found true negative
                    $TrueNegativeDocumentWeighted += 1;
                }
                else if(
                    !in_array(strtolower($Term_No), array_map('strtolower',$document['Actual_Yes'])) &&
                    !in_array(strtolower($Term_No), array_map('strtolower',$document['Actual_No']))
                ){
                    //Als ie zowel niet bij de actual yes als bij de actual no zit (voor als de actual no lijst leeg is)
                    $TrueNegativeDocumentWeighted += 1;
                }
                else if(!in_array(strtolower($Term_No), array_map('strtolower',$document['Actual_No']))){
                    //found false negative
                    $FalseNegativeDocumentWeighted += 1;
                }
            }
        }

        $document["True_Positive_Weighted"] = $TruePositiveDocumentWeighted;
        $document["False_Positive_Weighted"] = $FalsePositiveDocumentWeighted;
        $document["True_Negative_Weighted"] = $TrueNegativeDocumentWeighted;
        $document["False_Negative_Weighted"] = $FalseNegativeDocumentWeighted;
        return $document;
    }
    public function CalculateWeights($SingleExtractor,$DoubleExtractor,$SingleFalseExtractor,$DoubleFalseExtractor){
        $WeightArray = array();
        foreach ($SingleExtractor as $extractor=>$amount){
            isset($WeightArray[$extractor]) ? $WeightArray[$extractor] += $amount : $WeightArray[$extractor] = $amount;
        }
        foreach ($DoubleExtractor as $extractor=>$amount){
            isset($WeightArray[$extractor]) ? $WeightArray[$extractor] += $amount : $WeightArray[$extractor] = $amount;
        }
        foreach ($SingleFalseExtractor as $extractor=>$amount){
            isset($WeightArray[$extractor]) ? $WeightArray[$extractor] -= $amount : $WeightArray[$extractor] = $amount;
        }
        foreach ($DoubleFalseExtractor as $extractor=>$amount){
            isset($WeightArray[$extractor]) ? $WeightArray[$extractor] -= $amount : $WeightArray[$extractor] = $amount;
        }
        $min_weight_value = min(array_values($WeightArray));

        $stddev = $this->standard_deviation($WeightArray);
        $mean = array_sum($WeightArray) / count($WeightArray);
        $zscores = array();
        foreach ($WeightArray as $extractor=>$value){
            $z = ($value - $mean) / $stddev;
            $zscores[$extractor] = $z;
        }
        while(max($zscores) > 1 || min($zscores) < -1){
            foreach ($zscores as $index=>$z){
                $z = $z / 2;
                $zscores[$index]=$z;
            }
        }
        $average_weight = 1 / count($WeightArray);
        foreach ($WeightArray as $extractor=>$value){

            $value = $average_weight + ($average_weight * $zscores[$extractor]);
            $WeightArray[$extractor] = $value;
        }
        return $WeightArray;
    }


    public function CreateAndSaveCSV($name, $prefix, $result ){
        $list = array ();
        $list[] = array("sep=,");
        $list[] = array("DOC-ID","TP","FP", "TN", "FN", "RECALL", "PRECISION", "ACCURACY");
        foreach ($result['Documents'] as $doc_id => $document){
            $tp = $document['True_Positive_Weighted'];
            $fp = $document['False_Positive_Weighted'];
            $tn = $document['True_Negative_Weighted'];
            $fn = $document['False_Negative_Weighted'];
            $result = $this->CalculateFScore($tp,$fp,$fn);
            $recall = round($result['recall'],2);
            $precision = round($result['precision'], 2);
            $fscore = round($result['fmeasure'], 2);
            //Doc_id - TP - FP - TN - FN - Recall - Precision - Accuracy
            $list[] = array($prefix.$doc_id, $tp, $fp, $tn, $fn, $recall, $precision, $fscore);
        }
        $list[] = array();
        $list[] = array();
        $list[] = array("", "=SUM(B2:B101)","=SUM(C2:C101)","=SUM(D2:B101)","=SUM(D2:B101)");
        $fp = fopen($name.'.csv', 'w');
        foreach ($list as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
    }
    public function standard_deviation($aValues)
    {
        $fMean = array_sum($aValues) / count($aValues);
        //print_r($fMean);
        $fVariance = 0.0;
        foreach ($aValues as $i)
        {
            $fVariance += pow($i - $fMean, 2);

        }
        $size = count($aValues) - 1;
        return (float) sqrt($fVariance)/sqrt($size);
    }

    //***************//
    //OLD  functions //
    //***************//
    public function GetObjectInArrayWith($array,$property,$value){
        foreach ($array as $item){
            if(strtolower($item->{$property}) == strtolower($value)){
                return $item;
            }
        }
        return null;
    }
    public function GetObjectInArrayWithSimilarity($array,$property,$value, $similarity){
        foreach ($array as $item){
            similar_text($item->{$property}, $value, $percent);

            if($percent > $similarity){
                return $item;
            }
        }
        return null;
    }
    public function in_arrayi($needle, $haystack, $threshhold) {
        $returnArray = array();

        foreach ($haystack as $item){
            $DiseaseNameLowercase = strtolower($item->term);
            similar_text($needle, $DiseaseNameLowercase, $percent);
            if( strpos( $DiseaseNameLowercase, $needle ) !== false) {
                $containment_percentage = count($DiseaseNameLowercase)/count($needle);
                if($containment_percentage > $threshhold){
                    $returnArray[] = $item;
                    continue;
                }
            }

            if( strpos( $needle, $DiseaseNameLowercase ) !== false ) {
                $containment_percentage = count($needle)/count($DiseaseNameLowercase);
                if($containment_percentage > $threshhold){
                    $returnArray[] = $item;
                    continue;
                }
            }
            if($percent > $threshhold){
                $returnArray[] = $item;
                continue;
            }
        }
        return $returnArray;
    }
    public function ArrayOfObjectsContainsProperty($array,$property,$value){
        foreach ($array as $item){

            if(strtolower($item->{$property}) == strtolower($value)){
                return true;
            }
        }
        return false;
    }
    public function HandleAnnotationComparisonStandardized($document_term_array, $term_type, $document_prefix, $extractor){
        $result_array = array();
        $counter = 0;
        foreach ($document_term_array as $document_index => $clinical_term_array){
            if(isset($_POST['document_id']) && $_POST['document_id'] != ""){
                if($_POST['document_id'] != $document_index){
                    continue;
                }
            }
            $extraction_count = count(DatabaseController::GetAllExtractionsForDocument($document_prefix.$document_index,$term_type));
            if($extraction_count == 0){
                continue;
            }

            if($counter > 5){
                continue;
            }
            $counter += 1;

            $annotation_positive = array();
            $annotation_negative = array();
            foreach ($clinical_term_array as $clinical_term){
                if(isset($clinical_term->in_document) && $clinical_term->in_document == "no"){
                    $annotation_negative[] = $clinical_term->term;
                }
                else if (isset($clinical_term->in_document) && $clinical_term->in_document == "undecided"){
                    $annotation_negative[] = $clinical_term->term;
                }
                else{
                    $annotation_positive[] = $clinical_term->term;
                }
            }
            $result = UMLSController::CompareTermsInDocument1($document_prefix.$document_index, $annotation_positive, $term_type, $extractor);
            $handled_clinical_term_positive = $result['Extracted_Positive'];
            $handled_clinical_term_negative = $result['Extracted_Negative'];
            $Extracted_Positive_Terms = array();
            foreach ($handled_clinical_term_positive as $extract_p=>$extractors){
                $Extracted_Positive_Terms[] = $extract_p;
            }
            $result_array[$document_index] = array(
                'Document_Index'=> $document_index,
                'Extractor_Yes'=>$handled_clinical_term_positive,
                'Extractor_No'=>$handled_clinical_term_negative,
                'Actual_Yes' => $annotation_positive,
                'Actual_No' => $annotation_negative,1
            );

        }
        $result_array = array("Documents"=>$result_array);
        return $result_array;
    }
    public function HandleAnnotationComparison($document_term_array, $term_type, $document_prefix){
        $result_array = array();
        foreach ($document_term_array as $document_index => $clinical_term_array){
            $handle_negation = false;
            $sqlResults = DatabaseController::GetAllExtractionsForDocument($document_prefix . $document_index, $term_type);
            if(count($sqlResults) == 0){
                continue;
            }
            $handled_clinical_term_positive = array();
            $handled_clinical_term_negative = array();
            $annotation_positive = array();
            $annotation_negative = array();

            foreach ($clinical_term_array as $clinical_term) {
                //Can any of the diseases be matched against the extraction
                if(isset($clinical_term->in_document) && $clinical_term->in_document == "no"){
                    $annotation_negative[] = $clinical_term->term;
                }
                else{
                    $annotation_positive[] = $clinical_term->term;
                }

                $similar_terms = $this->in_arrayi($clinical_term->term, $sqlResults, 80);
                foreach ($similar_terms as $similar_term) {
                    if ($similar_term != null) {
                        //If so
                        if ($handle_negation && $similar_term->negated == 1) {
                            $handled_clinical_term_negative[$clinical_term->term][] = $similar_term->term;
                            $handled_clinical_term_negative[$clinical_term->term] = array_values(array_unique($handled_clinical_term_negative[$clinical_term->term]));
                        } else {
                            $handled_clinical_term_positive[$clinical_term->term][] = $similar_term->extractor;
                            $handled_clinical_term_positive[$clinical_term->term] = array_values(array_unique($handled_clinical_term_positive[$clinical_term->term]));
                        }
                    }
                }
                if (count($similar_terms) == null) {
                    //If not

                    $similar_terms_reduced_threshhold = $this->in_arrayi($clinical_term->term, $sqlResults, 50);
                    if(count($similar_terms_reduced_threshhold) > 0){
                        foreach ($similar_terms_reduced_threshhold as $similar_term) {
                            $handled_clinical_term_negative[$clinical_term->term][] = $similar_term->term;
                            $handled_clinical_term_negative[$clinical_term->term] = array_values(array_unique($handled_clinical_term_negative[$clinical_term->term]));
                        }
                    }
                    else{
                        $handled_clinical_term_negative[$clinical_term->term][] = "NOT FOUND";
                        $handled_clinical_term_negative[$clinical_term->term] = array_values(array_unique($handled_clinical_term_negative[$clinical_term->term]));
                    }
                }
            }
            $result_array[$document_index] = array(
                'Document_Index'=> $document_index,
                'Extractor_Yes'=>$handled_clinical_term_positive,
                'Extractor_No'=>$handled_clinical_term_negative,
                'Actual_Yes' => $annotation_positive,
                'Actual_No' => $annotation_negative,
            );


        }
        $result_array = array("Documents"=>$result_array);
        $result_array = $this->CalculateStatistics($result_array);
        return $result_array;
    }
}