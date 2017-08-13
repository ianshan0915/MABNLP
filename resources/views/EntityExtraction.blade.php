@extends('layouts.app')




@section('content')


<div>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/DataTables/datatables.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('js/entityextraction-ee.js')}}"></script>
    <script type="text/javascript" src="{{ asset('js/bootstrap/bootstrap.min.js')}}"></script>

    <script src="{{ asset('js/HighLightTextArea/jquery.highlighttextarea.js') }}"></script>
    <script src="{{ asset('js/underscore-min.js') }}"></script>

    <link rel="stylesheet" href="css/jquery-ui.min.css">
    <link rel="stylesheet" href="css/jquery.highlighttextarea.min.css">

    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>


    <link rel="stylesheet" href="css/bootstrap/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/bootstrap/bootstrap-horizon.css">
    <link rel="stylesheet" href="css/personal.css">



    <div class="content_container">
        <div id="IELoader" class="loader"></div>

        <div class="panel panel-default" style="margin-bottom: 0; height: 80%">
            <div class="panel-heading">Enter the text to process</div>
            <div class="panel-body">
                <textarea id="TextArea" rows="10" style="height:100%; width:100%; max-width: 100%; max-height:400px; text-align: justify; resize: none"></textarea>
            </div>
        </div>
        <div style="display: flex; flex-direction: row; ">
            <div class="panel panel-default" style="flex-grow: 1; align-items: stretch; height: 100%; ">
                <div class="panel-heading">What entity extraction algorithms should be used?</div>
                <div class="panel-body">
                    <input type="checkbox" name="IEOptions" id="WatsonIE"       value="Watson">
                    <label for="WatsonIE" id="WatsonIELabel">IBM Watson</label></br>

                    <input type="checkbox" name="IEOptions" id="OpenCalaisIE"   value="OpenCalais">
                    <label for="OpenCalaisIE" id="OpenCalaisIELabel">OpenCalaisIE</label></br>

                    <input type="checkbox" name="IEOptions" id="Haven"          value="Haven">
                    <label for="Haven" id="HavenLabel">Haven</label></br>

                    <input type="checkbox" name="IEOptions" id="Dandelion"      value="Dandelion">
                    <label for="Dandelion" id="DandelionLabel">Dandelion</label></br>

                    <input type="checkbox" name="IEOptions" id="MeaningCloud"   value="MeaningCloud">
                    <label for="MeaningCloud" id="MeaningCloudLabel">MeaningCloud</label></br>

                    <input type="checkbox" name="IEOptions" id="TextRazor"      value="TextRazor">
                    <label for="TextRazor" id="TextRazorLabel">TextRazor</label></br>
                </div>
            </div>

            <div class="panel panel-default" style="flex-grow: 1; align-items: stretch; height: 100%;">
                <div class="panel-heading">What standardizations would you like to find?</div>
                <div class="panel-body">
                    <input type="checkbox" name="StandardOptions" value="ICD10"> ICD-10<br>
                    <input type="checkbox" name="StandardOptions" value="ICD10DUT"> ICD-10 Dutch Translation <br>
                    <input type="checkbox" name="StandardOptions" value="SNOMEDCT_US"> SNOMEDCT <br>
                    <input type="checkbox" name="StandardOptions" value="RXNORM"> RXnorm <br>
                </div>
            </div>
        </div>
        <div style=" background-color: rgba(0,120,120,0.5); width: 100%; text-align: center";>
            <button id="ExtractButton"
                    style="
                    width: 80%;
                    margin-top: 20px;
                    margin-bottom: 20px;
                    margin-left: auto;
                    margin-right: auto">
                Extract!
            </button>
        </div>
        <table id="ResultDataTable" class="display, compact" style="background-color: white; height: 100%">
            <thead>
            <tr>
                <th> Disease Local   </th>
                <th> Disease Remote  </th>
                <th> Code            </th>
                <th> Source          </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            </tbody>
        </table>
    </div>



</div>
@endsection

