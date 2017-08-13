<?php

namespace App\Http\Controllers;


use Illuminate\Console\Scheduling\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MultiThreadTest extends \Thread
{
    var $document_drug_array;
    var $type;
    var $prefix;
    var $extractor;

    public function __construct($document_drug_array, $type, $prefix, $extractor)
    {
        $this->document_drug_array = $document_drug_array;
        $this->type = $type;
        $this->prefix = $prefix;
        $this->extractor = $extractor;

    }
    public function run(){
        DocumentHandler::HandleAnnotationComparisonStandardized($this->document_drug_array, $this->type, $this->prefix, $this->extractor);

    }
}