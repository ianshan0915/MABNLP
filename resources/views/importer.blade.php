@extends('layouts.app')




@section('content')


<div>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/DataTables/datatables.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('js/entityextraction.js')}}"></script>
    <script type="text/javascript" src="{{ asset('js/bootstrap/bootstrap.min.js')}}"></script>

    <script src="{{ asset('js/HighLightTextArea/jquery.highlighttextarea.js') }}"></script>
    <script src="{{ asset('js/underscore-min.js') }}"></script>

    <link rel="stylesheet" href="css/jquery-ui.min.css">
    <link rel="stylesheet" href="css/jquery.highlighttextarea.min.css">

    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>


    <link rel="stylesheet" href="css/bootstrap/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/bootstrap/bootstrap-horizon.css">

    <link rel="stylesheet" href="css/ieflow.css">

    <div class="content_container">
        Import Disease texts
        {{ Form::open(array('url' => 'import_file', 'method'=>'post', 'files'=>'true')) }}

        {{Form::file('xml')}}
        {{Form::submit('Click Me!')}}

        {{ Form::close() }}
    </div>
    <div class="content_container">
        Import Medications Texts
        {{ Form::open(array('url' => 'importi2b2', 'method'=>'post', 'files'=>'true')) }}

        {{Form::file('document')}}
        {{Form::submit('Click Me!')}}

        {{ Form::close() }}
    </div>
    <div class="content_container">
        Import Stripa Texts
        {{ Form::open(array('url' => 'importSTRIPAtexts', 'method'=>'post', 'files'=>'true')) }}

        {{Form::file('document')}}
        {{Form::submit('Click Me!')}}

        {{ Form::close() }}
    </div>

    <div class="content_container">
        Run Extraction algorithms
        <button id="launchbutton">
            Launch
        </button>
        <label>Document ID Like:</label><input id="ExtractorDocumentIDLike"/>
        <label>Document Limit:</label><input id="Limit"/>
        <label>Document Offset:</label><input id="Offset"/>
    </div>



    <div class="content_container">
        Import Disease Annotations
        {{ Form::open(array('url' => 'import_annotations_post', 'method'=>'post', 'files'=>'true')) }}

        {{ Form::file('annotations')}}
        {{ Form::submit('Click Me!')}}
        </br>
        {{ Form::label('Document_Id_Label', 'Document ID (Leave empty to do all)') }}
        {{ Form::text('document_id')}}
        </br>
        {{ Form::label('Similarity_Threshold_Label', 'Similarity Threshold (Default = 0.1)') }}
        {{ Form::text('Similarity_Threshold')}}
        </br>
        {{ Form::label('Extractor_Threshold_Label', 'Extractor Threshold (Default = 0.35)') }}
        {{ Form::text('Extractor_Threshold')}}
        </br>
        {{ Form::label('Negation_Label', 'Negation (Default = Disabled)') }}
        {{ Form::checkbox('Negation', 'Negation_Enabled', false) }}
        </br>
        {{ Form::close() }}

    </div>
    <div class="content_container">
        Import Medications Annotations
        {{ Form::open(array('url' => 'importi2b2annotations', 'method'=>'post', 'files'=>'true')) }}

        {{Form::file('annotations')}}
        {{Form::submit('Click Me!')}}

        </br>
        {{ Form::label('Document_Id_Label', 'Document ID (Leave empty to do all)') }}
        {{ Form::text('document_id')}}
        </br>
        {{ Form::label('Similarity_Threshold_Label', 'Similarity Threshold (Default = 0.1)') }}
        {{ Form::text('Similarity_Threshold')}}
        </br>
        {{ Form::label('Extractor_Threshold_Label', 'Extractor Threshold (Default = 0.35)') }}
        {{ Form::text('Extractor_Threshold')}}
        </br>
        {{ Form::label('Negation_Label', 'Negation (Default = Disabled)') }}
        {{ Form::checkbox('Negation', 'Negation_Enabled', false) }}
        </br>

        {{ Form::close() }}
    </div>
    <div class="content_container">
        Import STRIPA Annotations
        {{ Form::open(array('url' => 'importSTRIPAannotations', 'method'=>'post', 'files'=>'true')) }}

        {{Form::file('annotations')}}
        {{Form::submit('Click Me!')}}

        </br>
        {{ Form::label('Document_Id_Label', 'Document ID (Leave empty to do all)') }}
        {{ Form::text('document_id')}}
        </br>
        {{ Form::label('Similarity_Threshold_Label', 'Similarity Threshold (Default = 0.1)') }}
        {{ Form::text('Similarity_Threshold')}}
        </br>
        {{ Form::label('Extractor_Threshold_Label', 'Extractor Threshold (Default = 0.35)') }}
        {{ Form::text('Extractor_Threshold')}}
        </br>
        {{ Form::label('Negation_Label', 'Negation (Default = Disabled)') }}
        {{ Form::checkbox('Negation', 'Negation_Enabled', false) }}
        </br>
        {{ Form::label('Drug', 'Drug (Default = MedicalCondition)') }}
        {{ Form::checkbox('Medication', 'Medication_Enabled', false) }}
        </br>

        {{ Form::close() }}
    </div>


</div>
@endsection

