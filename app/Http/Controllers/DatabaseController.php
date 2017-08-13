<?php

namespace App\Http\Controllers;


use Illuminate\Console\Scheduling\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DatabaseController extends Controller
{
    public static $cached_similar_document_cuis = array();
    public static function GetAllUMLSForExtractorInDocumentOfType($extractor, $document_id, $extraction_type){
        $document_extractions = DB::select(DB::raw("
 
SELECT * 
FROM `extractions_umls` AS a
INNER JOIN (SELECT *
	FROM `extractions`
	WHERE `documentid` = :docid1 and `type` = :extractiontype1 and " . "`extractor` = :extractor and " . "
	`term` IN (
    	SELECT DISTINCT `term` 
		FROM `extractions`
		WHERE `documentid` = :docid2 and `type` = :extractiontype2  ) 
		) AS b ON a.extraction_id = b.id


"),
            ["docid1" => $document_id, "extractiontype1"=>$extraction_type,"docid2" => $document_id, "extractiontype2"=>$extraction_type, "extractor"=>$extractor]);

        //$document_extractions = DatabaseController::HandleDuplicates($document_extractions, $document_id, $extractor);
        return $document_extractions;
    }
    public static function GetAllUMLSInDocumentOfType($document_id, $extraction_type){
        $document_extractions = DB::select(DB::raw("
 
SELECT * 
FROM `extractions_umls` AS a
INNER JOIN (SELECT *
	FROM `extractions`
	WHERE `documentid` = :docid1 and `type` = :extractiontype1 and 
	`term` IN (
    	SELECT DISTINCT `term` 
		FROM `extractions`
		WHERE `documentid` = :docid2 and `type` = :extractiontype2  ) 
		) AS b ON a.extraction_id = b.id
"),["docid1" => $document_id, "extractiontype1"=>$extraction_type,"docid2" => $document_id, "extractiontype2"=>$extraction_type]);

        //$document_extractions = DatabaseController::HandleDuplicates($document_extractions, $document_id, null);
        return $document_extractions;
    }
    public static function HandleDuplicates($document_extractions,$document_id, $extractor){
        foreach($document_extractions as $index_one=>$docid_one){
            foreach($document_extractions as $index_two=>$docid_two){
                if($docid_one->extraction_id != $docid_two->extraction_id){

                    if($extractor != null){
                        $identical = DatabaseController::AreExtractionIDsIdentical($docid_one->extraction_id, $docid_two->extraction_id, $document_id, $extractor);
                    }
                    else{
                        $identical = DatabaseController::AreExtractionIDsIdentical($docid_one->extraction_id, $docid_two->extraction_id, $document_id, null);
                    }
                    if($identical){
                        if($docid_one->priority < $docid_two->priority){
                            $docid_one->extraction_id = $docid_two->extraction_id;
                            $docid_one->term = $docid_two->term;
                            $document_extractions[$index_one] = $docid_one;
                        }
                        else{
                            $docid_two->extraction_id = $docid_one->extraction_id;
                            $docid_two->term = $docid_one->term;
                            $document_extractions[$index_one] = $docid_two;
                        }
                        //Extracted are the same, we should join em.
                    }

                }
            }
        }
        return $document_extractions;
    }

    public static function FindIdenticalTermsForDocument($document_id){
        $document_extractions = DB::select(DB::raw("         
            SELECT a.*, b.*
            FROM `extractions_umls` AS a
            INNER JOIN `extractions_umls` as b ON a.umls_id = b.umls_id
            WHERE a.extraction_id IN (SELECT `id` FROM `extractions` WHERE `documentid` = :docid1)
            AND b.extraction_id IN (SELECT `id` FROM `extractions` WHERE `documentid` = :docid2)
            AND a.extraction_id <> b.extraction_id
       
        "),
            ["docid1" => $document_id, "docid2"=>$document_id]);

        $similars = array();
        foreach ($document_extractions as $extraction_one){
            foreach ($document_extractions as $extraction_two){
                if($extraction_one->extraction_id != $extraction_two->extraction_id){
                    if($extraction_one->umls_id == $extraction_two->umls_id){
                        //These two are similar
                        if(isset($similars[$extraction_one->extraction_id][$extraction_two->extraction_id])){
                            if($extraction_one->extraction_id == 3 && $extraction_two->extraction_id == 178){
                                $i = 0;
                            }
                            $similars[$extraction_one->extraction_id][$extraction_two->extraction_id] += 1;
                        }
                        else{
                            $similars[$extraction_one->extraction_id][$extraction_two->extraction_id] = 1;
                        }
                    }
                }
            }
        }
        return $similars;
    }
    public static function FindIdenticalTermsForDocumentAndExtractor($document_id, $extractor){
        $document_extractions = DB::select(DB::raw("         
            SELECT *
            FROM `extractions_umls` AS a
            INNER JOIN `extractions_umls` as b ON a.umls_id = b.umls_id
            WHERE a.extraction_id IN (SELECT `id` FROM `extractions` WHERE `documentid` = :docid1 AND `extractor` = :extractor1)
            AND b.extraction_id IN (SELECT `id` FROM `extractions` WHERE `documentid` = :docid2 AND `extractor` = :extractor2)
            AND a.extraction_id <> b.extraction_id       
        "),
            ["docid1" => $document_id, "docid2"=>$document_id, "extractor1"=>$extractor, "extractor2"=>$extractor]);
        $similars = array();
        foreach ($document_extractions as $extraction_one){
            foreach ($document_extractions as $extraction_two){
                if($extraction_one->extraction_id != $extraction_two->extraction_id){
                    if($extraction_one->umls_id == $extraction_two->umls_id){
                        //These two are similar
                        $o = (object)array();
                        $o->similar_to = $extraction_two->extraction_id;
                        $o->priority = $extraction_two->priority;
                        $similars[$extraction_one->extraction_id][] = $o;
                    }
                }
            }
        }
        return $similars;
    }



    public static function GetExtractionsForUMLS($CUI){
        $result = DB::select(DB::raw("
SELECT `term`
 FROM `extractions` AS a
 WHERE `id` IN (
    SELECT `extraction_id` 
    FROM `extractions_umls` AS b
    WHERE `umls_id` = :cui` AND 
 )
"),["cui" => $CUI]);
        return $result;
    }
    public static function GetExtractionsForUMLSInDocument($CUI, $document_ID){
        $result = DB::select(DB::raw("
SELECT `term`, `extractor`
 FROM `extractions` AS a
 WHERE `documentid` = :docid 
 WHERE `id` IN (
    SELECT `extraction_id` 
    FROM `extractions_umls` AS b
    WHERE `umls_id` = :cui` AND 
 )
"),["cui" => $CUI, "docid" => $document_ID]);
        return $result;

    }
    public static function GetUMLSForExtractionID($Extraction_ID){
        $result = DB::select(DB::raw("
            SELECT *
             FROM `extractions_umls` AS a
             WHERE `extraction_id` = :Extraction
            "),["Extraction" => $Extraction_ID]);
        return $result;
    }

    public static function AreExtractionIDsIdentical($extraction_one, $extraction_two, $document_id, $extractor){
        if(isset( self::$cached_similar_document_cuis[$document_id . "-" .$extractor])){
            $similar= self::$cached_similar_document_cuis[$document_id . "-" .$extractor];
        }
        else if(isset( self::$cached_similar_document_cuis[$document_id])){
            $similar= self::$cached_similar_document_cuis[$document_id];
        }
        else{
            if($extractor != null){
                $similar = DatabaseController::FindIdenticalTermsForDocumentAndExtractor($document_id, $extractor);
                self::$cached_similar_document_cuis[$document_id . "-" .$extractor] = $similar;
            }
            else{
                $similar = DatabaseController::FindIdenticalTermsForDocument($document_id);
                self::$cached_similar_document_cuis[$document_id] = $similar;
            }


        }

        if(in_array($extraction_one, array_keys($similar))){
            $similarities = array();
            foreach ($similar[$extraction_one] as $index=>$item){
                if($item->similar_to == $extraction_two){
                    $similarities[] = $item;
                }
            }
            if(count($similarities) > 5){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }


    public static function CreateExtractionGroupForDocumentId($docid){
        $similar = DatabaseController::FindIdenticalTermsForDocument($docid);
        $groups_result = array();


        foreach ($similar as $extraction_id => $similar_to_array){
            $group = array($extraction_id);
            foreach ($similar_to_array as $similar_to_extraction_id=> $priority){
                $group[] = $similar_to_extraction_id;
            }
            $group = array_sort_recursive($group);
            $groups_result[] = $group;
        }
        $groups_result = array_values(array_map("unserialize", array_unique(array_map("serialize", $groups_result))));
        return JsonResponse::create($groups_result);
    }

    public static function GetAllExtractionsForDocument($document_id, $type){
        $builder = DB::table('extractions AS a')
            ->select('*')
            ->where('documentid','=',$document_id)
            ->where('type','=',$type);

        $result = $builder->get();
        $sql = $builder->toSQL();
        return $result;
    }
    public static function GetAllExtractionsForTerm($term){
        $raw = DB::raw("match(a.term) against('".$term."')");
        $builder = DB::table('extractions AS a')->select('*')->whereRaw($raw);
        $sql = $builder->toSQL();
        $result = $builder->get();
        return $result;
    }
    public static function GetAllExtractionForTermInDocument($document_id,$term){
        $raw = DB::raw("match(a.term) against('".$term."')");
        $builder = DB::table('extractions AS a')->select('*')->where('documentid','=',$document_id)->whereRaw($raw);
        $sql = $builder->toSQL();
        $result = $builder->get();
        return $result;

    }


}