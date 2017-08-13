<?php $__env->startSection('content'); ?>


<div>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <script src="<?php echo e(asset('js/jquery-ui.min.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(asset('js/DataTables/datatables.min.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(asset('js/ieflow.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(asset('js/bootstrap/bootstrap.min.js')); ?>"></script>

    <script src="<?php echo e(asset('js/HighLightTextArea/jquery.highlighttextarea.js')); ?>"></script>
    <script src="<?php echo e(asset('js/underscore-min.js')); ?>"></script>

    <link rel="stylesheet" href="css/jquery-ui.min.css">
    <link rel="stylesheet" href="css/jquery.highlighttextarea.min.css">

    <link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>


    <link rel="stylesheet" href="css/bootstrap/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/bootstrap/bootstrap-horizon.css">

    <link rel="stylesheet" href="css/ieflow.css">

    <div class="content_container">
        <div id="IELoader" class="loader"></div>
        <div id="horizontal-container" class="horizontal-container">

            <div class="page">
                <div class="panel panel-default" style="margin-bottom: 0; height: 80%">
                    <div class="panel-heading">Information Extraction</div>
                    <div class="panel-body">
                        <textarea id="TextArea" rows="15" style="height:100%; width:100%; max-width: 100%; max-height:400px; text-align: justify; resize: none"></textarea>
                    </div>
                </div>
                <div>
                    <p>What entity extraction algorithms should be used?</p>
                    <input type="checkbox" name="IEOptions" id="WatsonIE" value="Watson"> IBM Watson <br>
                    <input type="checkbox" name="IEOptions" id="OpenCalaisIE" value="OpenCalais"> OpenCalais <br>
                    <input type="checkbox" name="IEOptions" id="Haven" value="Haven"> Haven <br>
                    <input type="checkbox" name="IEOptions" id="Dandelion" value="Dandelion"> Dandelion <br>
                    <input type="checkbox" name="IEOptions" id="MeaningCloud" value="MeaningCloud"> MeaningCloud <br>
                    <input type="checkbox" name="IEOptions" id="TextRazor" value="TextRazor"> TextRazor <br>

                </div>



            </div>

            <div class="page" style="background-color: #afbdd8;">
                <div style="position: relative; width: 100%; height: 100%">

                    <div style="background-color: white; position: relative; height: 70%">
                        <table id="EntityDataTable" class="display, compact" style="background-color: white; height: 80%">
                            <thead>
                            <tr>
                                <th>Disease</th>
                                <th>Type</th>
                                <th>Source</th>

                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td ></td>
                                <td></td>
                                <td></td>

                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <p style="color: #0000cc">What standardizations would you like to find?</p>
                        <input type="checkbox" name="StandardOptions" value="ICD10"> ICD-10<br>
                        <input type="checkbox" name="StandardOptions" value="ICD10DUT"> ICD-10 Dutch Translation <br>
                        <input type="checkbox" name="StandardOptions" value="SNOMEDCT_US"> SNOMEDCT <br>
                        <input type="checkbox" name="StandardOptions" value="RXNORM"> RXnorm <br>




                    </div>
                <div>



                </div>

                </div>
            </div>
            <div class="page" style="background-color: #afbdd8;">
                <div style="position: relative; width: 100%; height: 100%">

                    <div style="background-color: white; position: relative; height: 70%">
                        <table id="EntityNegationDataTable" class="display, compact" style="background-color: white; height: 80%">
                            <thead>
                            <tr>
                                <th>Disease</th>
                                <th>Negation</th>
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
            <div class="page" style="background-color: #ff9900;">
                <div style="position: relative; width: 100%; height: 100%">
                    <div style="background-color: white; position: relative; height: 100%">
                        <table id="DiseaseICD10DataTable" class="display, compact" style="background-color: white; height: 100%">
                            <thead>
                            <tr>
                                <th>Disease Local</th>
                                <th>Disease Remote</th>
                                <th>Code</th>
                                <th>Source</th>
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

            </div>
        </div>

        <button id="NextButton" style="width: 100%">Next</button>


    </div>



</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>