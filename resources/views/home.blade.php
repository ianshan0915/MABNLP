@extends('layouts.app')




@section('content')


<div>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/DataTables/datatables.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('js/ProcessIEForm.js')}}"></script>
    <script type="text/javascript" src="{{ asset('js/bootstrap/bootstrap.min.js')}}"></script>

    <script src="{{ asset('js/HighLightTextArea/jquery.highlighttextarea.js') }}"></script>
    <script src="{{ asset('js/underscore-min.js') }}"></script>

    <link rel="stylesheet" href="css/jquery-ui.min.css">
    <link rel="stylesheet" href="css/jquery.highlighttextarea.min.css">

    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>


    <link rel="stylesheet" href="css/bootstrap/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/bootstrap/bootstrap-horizon.css">

    <link rel="stylesheet" href="css/personal.css">

    <div>
        <div>

            <div id="IEContainer"></div>

            <div class="panel panel-default">
                <div class="panel-heading">Information Extraction</div>
                <div class="panel-body">
                    <textarea id="TextArea" rows="10" style="height:auto; width:100%; max-width: 100%; text-align: justify;"></textarea>
                </div>
            </div>
            <div class="panel panel-default ">
                <div class="panel-heading">
                    <div>
                        <button data-toggle="collapse" href="#collapse1">Watson</button>
                        <button data-toggle="collapse" href="#collapse2">Meaningcloud</button>
                        <button data-toggle="collapse" href="#collapse3">Intellexer</button>
                        <button data-toggle="collapse" href="#collapse4">Stanford</button>
                        <button data-toggle="collapse" href="#collapse5">OpenCalais</button>
                        <button data-toggle="collapse" href="#collapse6">Results</button>

                    </div>
                </div>

            </div>
            <div class="row row-horizon" id="HorizontalScroller">
                <div id="collapse1" class="collapse">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <button style="margin: 10px" id="IEWatsonButton" >Extract Info (Watson)!</button>
                        </div>
                    </div>
                    <div id="WatsonIEContainer" class="col-md-6" style="background-color: #0000cc">
                        <div id="watsonloader" class="loader"></div>


                        <div class="panel panel-default">
                            <div class="panel-heading">Entities</div>
                            <div class="panel-body">
                                <table class="display" id="EntityDataTableWatson">
                                    <thead>
                                    <tr>
                                        <th>Text</th>
                                        <th>Types</th>
                                        <th>Relevance</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>

                                    </tbody>

                                </table>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">Drug/Quantity Combinations</div>
                            <div class="panel-body">
                                <table class="display" id="DrugQuantityDataTableWatson">
                                    <thead>
                                    <tr>
                                        <th>Text</th>
                                        <th>Certainty</th>
                                        <th>ATC Code</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>

                                    </tbody>

                                </table>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">Diseases</div>
                            <div class="panel-body">
                                <table class="display" id="DiseaseDataTableWatson">
                                    <thead>
                                    <tr>
                                        <th>ICD10 Code </th>
                                        <th>Disease</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td ></td>
                                        <td></td>

                                    </tr>

                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="collapse2" class="collapse">
                    <div id="MeaningCloudContainer" class="col-md-6" style="background-color: #ff9900">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <button style="margin: 10px" id="IEMeaningCloudButton" >Extract Info (MeaningCloud)!</button>
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">Concepts</div>
                            <div class="panel-body">
                                <table class="display" id="ConceptDataTableMeaningCloud">
                                    <thead>
                                    <tr>
                                        <th>Text</th>
                                        <th>Relevance</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                    </tr>

                                    </tbody>

                                </table>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">Entities</div>
                            <div class="panel-body">
                                <table class="display" id="EntityDataTableMeaningCloud">
                                    <thead>
                                    <tr>
                                        <th>Text</th>
                                        <th>Types</th>
                                        <th>Relevance</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>

                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>


                <div id="collapse3" class="collapse">
                    <div id="IntellexerIEContainer" class="col-md-6" style="background-color: #5e5e5e">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <button id="IEIntellexerButton" >Extract Info (Intellexer)!</button>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">Concepts</div>
                            <div class="panel-body">
                                <table class="display" id="ConceptDataTableIntellexer">
                                    <thead>
                                    <tr>
                                        <th>Text</th>
                                        <th>Relevance</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                    </tr>

                                    </tbody>

                                </table>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">Entities</div>
                            <div class="panel-body">
                                <table class="display" id="EntityDataTableIntellexer">
                                    <thead>
                                    <tr>
                                        <th>Text</th>
                                        <th>Types</th>
                                        <th>Relevance</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>

                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="collapse4" class="collapse">
                    <div id="StanfordNLPContainer" class="col-md-6" style="background-color: #761c19">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <button id="IEStanfordNLPButton" >Extract Info (Stanford)!</button>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">Concepts</div>
                            <div class="panel-body">
                                <table class="display" id="EntityResultDataTableStanfordNLP">
                                    <thead>
                                    <tr>
                                        <th>Text</th>
                                        <th>Relevance</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                    </tr>

                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="collapse5" class="collapse">
                    <div id="OpenCalaisContainer" class="col-md-6" style="background-color: #00CC66">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <button id="OpenCalaisButton" >Extract Info (OpenCalais)!</button>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">Concepts</div>
                            <div class="panel-body">
                                <table class="display" id="EntityResultDataTableOpenCalais">
                                    <thead>
                                    <tr>
                                        <th>Text</th>
                                        <th>Entity Type</th>
                                        <th>Relevance</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>

                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="collapse6" class="collapse">
                    <div id="ResultsContainer" class="col-md-6" style="background-color: #aa1111">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <button id="UpdateResultsButton" >Update Results!</button>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">Concepts</div>
                            <div class="panel-body">
                                <div class="panel panel-default">
                                    <div class="panel-heading">ATC10-Codes</div>
                                    <div class="panel-body">
                                        <table class="display" id="ATCDataTable">
                                            <thead>
                                            <tr>
                                                <th>Text</th>
                                                <th>ATC Code</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                            </tr>

                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                                <div class="panel panel-default">
                                    <div class="panel-heading">Diseases</div>
                                    <div class="panel-body">
                                        <table class="display" id="ICD10DataTable">
                                            <thead>
                                            <tr>
                                                <th>ICD10 Code</th>
                                                <th>Disease</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td></td>
                                                <td></td>

                                            </tr>

                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
            <div id="dialog" title="Dialog Title">I'm in a dialog</div>




        </div>
    </div>
</div>
@endsection

