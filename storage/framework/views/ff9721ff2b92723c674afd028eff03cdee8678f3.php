<?php $__env->startSection('content'); ?>


<div>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <script src="<?php echo e(asset('js/jquery-ui.min.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(asset('js/DataTables/datatables.min.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(asset('js/entityextraction.js')); ?>"></script>
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
        Import Disease texts
        <?php echo e(Form::open(array('url' => 'import_file', 'method'=>'post', 'files'=>'true'))); ?>


        <?php echo e(Form::file('xml')); ?>

        <?php echo e(Form::submit('Click Me!')); ?>


        <?php echo e(Form::close()); ?>

    </div>
    <div class="content_container">
        Import Medications Texts
        <?php echo e(Form::open(array('url' => 'importi2b2', 'method'=>'post', 'files'=>'true'))); ?>


        <?php echo e(Form::file('document')); ?>

        <?php echo e(Form::submit('Click Me!')); ?>


        <?php echo e(Form::close()); ?>

    </div>
    <div class="content_container">
        Import Stripa Texts
        <?php echo e(Form::open(array('url' => 'importSTRIPAtexts', 'method'=>'post', 'files'=>'true'))); ?>


        <?php echo e(Form::file('document')); ?>

        <?php echo e(Form::submit('Click Me!')); ?>


        <?php echo e(Form::close()); ?>

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
        <?php echo e(Form::open(array('url' => 'import_annotations_post', 'method'=>'post', 'files'=>'true'))); ?>


        <?php echo e(Form::file('annotations')); ?>

        <?php echo e(Form::submit('Click Me!')); ?>

        </br>
        <?php echo e(Form::label('Document_Id_Label', 'Document ID (Leave empty to do all)')); ?>

        <?php echo e(Form::text('document_id')); ?>

        </br>
        <?php echo e(Form::label('Similarity_Threshold_Label', 'Similarity Threshold (Default = 0.1)')); ?>

        <?php echo e(Form::text('Similarity_Threshold')); ?>

        </br>
        <?php echo e(Form::label('Extractor_Threshold_Label', 'Extractor Threshold (Default = 0.35)')); ?>

        <?php echo e(Form::text('Extractor_Threshold')); ?>

        </br>
        <?php echo e(Form::label('Negation_Label', 'Negation (Default = Disabled)')); ?>

        <?php echo e(Form::checkbox('Negation', 'Negation_Enabled', false)); ?>

        </br>
        <?php echo e(Form::close()); ?>


    </div>
    <div class="content_container">
        Import Medications Annotations
        <?php echo e(Form::open(array('url' => 'importi2b2annotations', 'method'=>'post', 'files'=>'true'))); ?>


        <?php echo e(Form::file('annotations')); ?>

        <?php echo e(Form::submit('Click Me!')); ?>


        </br>
        <?php echo e(Form::label('Document_Id_Label', 'Document ID (Leave empty to do all)')); ?>

        <?php echo e(Form::text('document_id')); ?>

        </br>
        <?php echo e(Form::label('Similarity_Threshold_Label', 'Similarity Threshold (Default = 0.1)')); ?>

        <?php echo e(Form::text('Similarity_Threshold')); ?>

        </br>
        <?php echo e(Form::label('Extractor_Threshold_Label', 'Extractor Threshold (Default = 0.35)')); ?>

        <?php echo e(Form::text('Extractor_Threshold')); ?>

        </br>
        <?php echo e(Form::label('Negation_Label', 'Negation (Default = Disabled)')); ?>

        <?php echo e(Form::checkbox('Negation', 'Negation_Enabled', false)); ?>

        </br>

        <?php echo e(Form::close()); ?>

    </div>
    <div class="content_container">
        Import STRIPA Annotations
        <?php echo e(Form::open(array('url' => 'importSTRIPAannotations', 'method'=>'post', 'files'=>'true'))); ?>


        <?php echo e(Form::file('annotations')); ?>

        <?php echo e(Form::submit('Click Me!')); ?>


        </br>
        <?php echo e(Form::label('Document_Id_Label', 'Document ID (Leave empty to do all)')); ?>

        <?php echo e(Form::text('document_id')); ?>

        </br>
        <?php echo e(Form::label('Similarity_Threshold_Label', 'Similarity Threshold (Default = 0.1)')); ?>

        <?php echo e(Form::text('Similarity_Threshold')); ?>

        </br>
        <?php echo e(Form::label('Extractor_Threshold_Label', 'Extractor Threshold (Default = 0.35)')); ?>

        <?php echo e(Form::text('Extractor_Threshold')); ?>

        </br>
        <?php echo e(Form::label('Negation_Label', 'Negation (Default = Disabled)')); ?>

        <?php echo e(Form::checkbox('Negation', 'Negation_Enabled', false)); ?>

        </br>
        <?php echo e(Form::label('Drug', 'Drug (Default = MedicalCondition)')); ?>

        <?php echo e(Form::checkbox('Medication', 'Medication_Enabled', false)); ?>

        </br>

        <?php echo e(Form::close()); ?>

    </div>


</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>