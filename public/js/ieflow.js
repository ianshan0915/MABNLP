var EntityDataTable;
var Page = 0;
var ExtractionResult = new Array();
jQuery(document).ready(function($) {

    $( window ).resize(function() {
        var containerwidth = $("#horizontal-container").width();
        $("#horizontal-container").scrollLeft(Page*containerwidth);
    });

    ShowOrHideLoader();
    document.getElementById("TextArea").value = readCookie("TextArea");

    EntityDataTable = $("#EntityDataTable").DataTable({
        scrollY:        '45vh',
        scrollCollapse: true,
        paging:         false,
        searching:      false,
        "createdRow": function( row, data, dataIndex ) {
            var table = $("#EntityDataTable").DataTable();

            var first_column_data = $("#EntityDataTable").DataTable().column(0).data();

            var enable_grouping = false;
            if(enable_grouping){
                if(howManySubstringsInArray(first_column_data, data[0]) > 1) {

                    table.rows($(row)).remove()
                    var indexes = table.rows().eq(0).filter(function (rowIdx) {
                        if (table.cell(rowIdx, 0).data().toLowerCase() == data[0].toLowerCase()) {
                            var currentvalue = table.cell(rowIdx, 2).data();
                            if (currentvalue.slice(-1) === ")") {
                                currentvalue = currentvalue.slice(0, -3);
                            }
                            var newvalue = data[2];
                            var number = currentvalue.split(",").length;

                            table.cell(rowIdx, 2).data(currentvalue + "," + newvalue + "(" + (number + 1) + ")").draw();
                            var rowtd = $('td', rowIdx);
                            rowtd.css('background-color', 'rgba(255,0,0, 0.7)');
                        }
                    });
                    table.draw();
                }
            }
            else{
                if ( data[2] == "Watson" ) {
                    $('td', row).css('background-color', 'rgba(175,238,238, 0.35)');
                }
                else if ( data[2] == "OpenCalais" ){
                    $('td', row).css('background-color', 'rgba(152,251,152, 0.35)');
                }
                else if ( data[2] == "Haven" ){
                    $('td', row).css('background-color', 'rgba(193, 169, 123, 0.35)');
                }
                else if ( data[2] == "Dandelion" ){
                    $('td', row).css('background-color', 'rgba(193, 123, 181, 0.35)');
                }
                else if ( data[2] == "MeaningCloud" ){
                    $('td', row).css('background-color', 'rgba(246, 45, 19, 0.35)');
                }
                else if ( data[2] == "TextRazor" ){
                    $('td', row).css('background-color', 'rgba(255, 10, 124, 0.35)');
                }
            }


        }
    });
    EntityDataTable.clear().draw();

    EntityNegationDataTable = $("#EntityNegationDataTable").DataTable({
        scrollY:        '45vh',
        scrollCollapse: true,
        paging:         false,
        searching:      false,
        "createdRow": function( row, data, dataIndex ) {
            var table = $("#EntityNegationDataTable").DataTable();
            if ( data[1] !== "" ){
                $('td', row).css('background-color', 'rgba(255, 0, 0, 0.35)');
            }
            else{
                $('td', row).css('background-color', 'rgba(0, 255, 0, 0.35)');
            }


        }
    });
    EntityNegationDataTable.clear().draw();


    DiseaseICD10DataTable = $("#DiseaseICD10DataTable").DataTable({
        scrollY:        '45vh',
        scrollCollapse: true,
        paging:         false,
        searching:      false,
        columns: [
            null,
            { "width": "20%" },
            null,
            null
        ],
        "createdRow": function( row, data, dataIndex ) {
            $('td', row).css('width', '25%');

            if ( data[3] == "NONE" ) {
                $('td', row).css('background-color', 'rgba(220,20,60, 0.35)');
            }
            else if ( data[3] == "ICD10" ){
                $('td', row).css('background-color', 'rgba(144,238,144, 0.35)');
            }
            else if ( data[3] == "SNOMEDCT_US" ){
                $('td', row).css('background-color', 'rgba(135,206,235, 0.35)');
            }
        }
    });
    DiseaseICD10DataTable.clear().draw();
    $("#NextButton").click(function () {
        console.log("Page: " + Page);
        var text = document.getElementById("TextArea").value;
        if(Page == 0){

            createCookie("TextArea",text,3);

            var WatsonIE = $("#WatsonIE").prop('checked');
            var OpenCalaisIE = $("#OpenCalaisIE").prop('checked');
            var Dandelion = $("#Dandelion").prop('checked');
            var Haven = $("#Haven").prop('checked');
            var MeaningCloud = $("#MeaningCloud").prop('checked');
            var TextRazor = $("#TextRazor").prop('checked');




            if(WatsonIE || OpenCalaisIE || Dandelion || Haven || MeaningCloud || TextRazor){
                if(MeaningCloud){
                    RunMeaningCloud(text);
                }
                if(WatsonIE){
                    RunWatson(text);
                }
                if(OpenCalaisIE){
                    RunOpenCalais(text);
                }
                if(Haven){
                    RunHaven(text);
                }
                if(Dandelion){
                    RunDandelion(text);
                }
                if(TextRazor){
                    RunTextRazor(text);
                }
            }
            else{
                alert("Select atleast one entity extraction algorithm");
                return;
            }


        }
        if(Page == 1){
            var EntityNamesArray = EntityDataTable.column(0).data();
            console.log("Names: ");
            console.log(EntityNamesArray);
            RunNegation(text, EntityNamesArray);

        }
        if(Page == 2){
            RunUMLSBeta(EntityDataTable.rows().data());
        }
        Page += 1;


        var containerwidth = $("#horizontal-container").width();
        var left = $("#horizontal-container").scrollLeft();
        $("#horizontal-container").animate({
            scrollLeft: left + containerwidth
        }, 2000);
    });
    function howManySubstringsInArray(arr,str)
    {
        var count=0;
        $.each(arr,function(i,item){
        if(item.toLowerCase().indexOf(str.toLowerCase())!=-1)
        {
            count ++;
        }
    });
    return count;
}
});
function RunOpenCalais(text){
    var settings =
    $.ajax({
        "url": "http://localhost/OpenCalais",
        "method": "POST",
        "data": {"text" : text},
        success: function(response){
            response = JSON.parse(response);

            for (var key in response) {
                // skip loop if the property is from prototype
                if (!response.hasOwnProperty(key)) continue;

                var obj = response[key];
                if(obj._typeGroup == "entities" && obj._type == "MedicalCondition"){
                    EntityDataTable.row.add([obj.name, "MedicalCondition", "OpenCalais"]);
                    ExtractionResult.push({"name":obj.name, "type": "MedicalCondition", "Source" : "OpenCalais", "PotentialICD10Code" : "0"})
                }
            }
            EntityDataTable.draw();


        },
        fail: function(error){

        },
        complete: function(){
            console.log("complete calais");
            ShowOrHideLoader();
        }
    });
    ShowOrHideLoader();
}
function RunWatson(text){
    var username = "92c0bd16-9f1e-4720-b3bc-bb0628a956a7";
    var password = "ZKLhlxfqyw1z";
    var baseurl = "http://localhost/WatsonNLU";

    var outputmode = "json";
    var apikey = "6f30339d95ec00a62403ac09df6d592b389bfd75";

    $.ajax({

        type: "POST",
        url: baseurl,
        data: {
            "text": String(text),
        },
        success: function (response) {
            response = JSON.parse(response);
            var entities = response["entities"];
            entities.forEach(function (item) {

                if (item.relevance > 0.1 && item.type == "HealthCondition") {
                    console.log("adding item "+item);
                    EntityDataTable.row.add([item.text, "MedicalCondition", "Watson"]);
                    ExtractionResult.push({"name":item.text, "type": "MedicalCondition", "Source" : "Watson", "PotentialICD10Code" : "0"})
                }
                if (item.relevance > 0.1 && item.type == "Drug") {
                    console.log("adding item "+item);
                    EntityDataTable.row.add([item.text, "Drug", "Watson"]);
                    ExtractionResult.push({"name":item.text, "type": "Drug", "Source" : "Watson", "PotentialICD10Code" : "0"})
                }
            });
            EntityDataTable.draw();

        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert("some error");
        },
        complete: function(){
            console.log("complete");
            ShowOrHideLoader();
        }
    });
    ShowOrHideLoader();
}
function RunDandelion(text){

    $.ajax({
        "url": "http://localhost/Dandelion",
        "method": "POST",
        "data": {"text" : text},
        success: function(response){
            response = JSON.parse(response);
            for (var key in response["annotations"]) {
                var obj = response["annotations"][key];
                var short_types = new Array();
                for (var typekey in obj.types){
                    if (response.hasOwnProperty(typekey)) continue;
                    var typeUrl = obj.types[typekey];
                    var splitUrl = typeUrl.split("/");
                    short_types.push(splitUrl[splitUrl.length-1]);

                }

                if(_.contains(short_types, "Disease") ){
                    EntityDataTable.row.add([obj.spot, "MedicalCondition", "Dandelion"]);
                    ExtractionResult.push({"name":obj.spot, "type": "MedicalCondition", "Source" : "Dandelion", "PotentialICD10Code" : "0"})
                }
                else if(_.contains(short_types, "Drug") ){
                    EntityDataTable.row.add([obj.spot, "Drug", "Dandelion"]);
                    ExtractionResult.push({"name":obj.spot, "type": "Drug", "Source" : "Dandelion", "PotentialICD10Code" : "0"})
                }

                if(obj._typeGroup == "entities" && obj._type == "MedicalCondition"){

                }
            }
            EntityDataTable.draw();


        },
        fail: function(error){
            alert(error);

        },
        complete: function(){
            console.log("complete dandelion");
            ShowOrHideLoader();
        }
    });
    ShowOrHideLoader();
}
function RunMeaningCloud(text){
    var key = 'dfe78864084853e9ee3ae548194c1038';
    var outputformat = 'json';
    var lang = 'en';
    var txt = document.getElementById("TextArea").value;

    var topictypes = 'a';

    $.ajax({
        type: 'POST',

        url: "http://api.meaningcloud.com/topics-2.0",
        data: {
            'key': key,
            'of': outputformat,
            'lang': lang,
            'txt': txt,
            'tt' : topictypes
        },
        success: function(response) {
            console.log(response);
            var entities = response["concept_list"];
            entities.forEach(function (item) {
                if(item.sementity.type.contains("Disease")){
                    EntityDataTable.row.add([item.form, "MedicalCondition", "MeaningCloud"]);
                    ExtractionResult.push({"name":item.form, "type": "MedicalCondition", "Source" : "MeaningCloud", "PotentialICD10Code" : "0", "Remote_name" : item.official_form})
                }

            });
            EntityDataTable.draw();
        }

    })
}
function RunHaven(text){

    $.ajax({
        type: 'POST',
        url: "http://localhost/Haven",
        data: {
            'text': text,
        },
        success: function(response) {
            response.forEach(function (item) {
                EntityDataTable.row.add([item.matches[0].original_text, "MedicalCondition", "Haven"]);
                if(item.hasOwnProperty("additional_information") &&
                    item.additional_information.hasOwnProperty("disease_icd10") &&
                    item.additional_information.disease_icd10.length == 1){

                    var url = item.additional_information.disease_icd10[0];
                    ExtractionResult.push({"name":item.matches[0].original_text, "type": "MedicalCondition", "Source" : "Haven", "PotentialICD10Code" : item.additional_information.disease_icd10[0]})
                }
                else{
                    ExtractionResult.push({"name":item.matches[0].original_text, "type": "MedicalCondition", "Source" : "Haven", "PotentialICD10Code" : "0"})
                }


            });
            EntityDataTable.draw();
        },
        fail: function(error){
            alert(error);

        },
        complete: function(){
            console.log("complete Haven");
            ShowOrHideLoader();
        }
    });
    ShowOrHideLoader();
}
function RunTextRazor(text){

    $.ajax({
        type: 'POST',
        url: "http://localhost/TextRazor",
        data: {
            'text': text,
        },
        success: function(response) {
            response.forEach(function (item) {
                var lowercasename = item.original_name.toLowerCase();
                var type = item.type;
                if(type == 'Disease'){
                    type = "MedicalCondition"
                }
                var o = {
                    "name":lowercasename,
                    "type": type,
                    "Source" : "TextRazor",
                    "PotentialICD10Code" : "0",
                    "documentid": "0"};

                EntityDataTable.row.add([
                    item.original_name,
                    type,
                    "TextRazor"]);
                ExtractionResult.push({
                    "name":item.original_name,
                    "type": type,
                    "Source" : "TextRazor",
                    "PotentialICD10Code" : "0"})

            });
            EntityDataTable.draw();
        },
        fail: function(error){
            alert(error);

        },
        complete: function(){
            console.log("complete Haven");
            ShowOrHideLoader();
        }
    });
    ShowOrHideLoader();
}
function RunNegation(text, concepts){;
    console.log(concepts);
    $.ajax({
        type: "POST",
        url: "http://localhost/Stanford",
        data: {
            "text": text,
            "terms" : concepts.join(","),
        },
        success: function (response) {
            console.log(response);
            response.forEach(function (item) {
                EntityNegationDataTable.row.add([item.term, item.negation]);
            });
            EntityNegationDataTable.draw();

        },
        error: function (xhr, textStatus, errorThrown) {
            //console.log(xhr.responseText);
        },
        complete: function(){
            console.log("complete");
            ShowOrHideLoader();
            $("#horizontal-container").css('overflow-x','scroll')
        }
    });

    ShowOrHideLoader();
}
function RunUMLS(concepts){
    console.log(concepts);

    var baseurl = "http://localhost/UMLS";

    var atomSoures = new Array();
    $("input[name=StandardOptions]").each( function () {
        if($(this).prop('checked')){
            atomSoures.push($(this).val());
        }
    });

    var HandledConcepts = new Array();

    concepts.each(function (row) {
        var concept_name = String(row[0]);
        if(!_.contains(HandledConcepts,concept_name.toLowerCase())){
            HandledConcepts.push(concept_name.toLowerCase());
            $.ajax({
                type: "POST",
                url: baseurl,
                data: {
                    "text": String(row[0]),
                    "atomsources" : atomSoures.join(","),
                    "remote" : "2",
                },
                success: function (response) {
                    //console.log(response);
                    //response = JSON.parse(response);

                    if(response.length == 0){
                        DiseaseICD10DataTable.row.add([row[0], "NONE", "NONE", "NONE"]);
                        DiseaseICD10DataTable.draw();
                        return;
                    }
                    console.log(response);
                    var ConceptName = response.NSTR;

                    var SourcesUsed = new Array();
                    response.atoms.forEach(function(item, index ){
                        if(!SourcesUsed.includes(item.SAB)){
                            SourcesUsed.push(item.SAB);
                            var AtomCode = item.CODE;
                            var AtomSource = item.SAB;
                            var AtomName = item.STR;

                            DiseaseICD10DataTable.row.add([row[0], ConceptName + " / " + AtomName , AtomCode, AtomSource]);
                            DiseaseICD10DataTable.draw();
                        }
                    });




                },
                error: function (xhr, textStatus, errorThrown) {
                    console.log(xhr.responseText);
                },
                complete: function(){
                    console.log("complete");
                    ShowOrHideLoader();
                    $("#horizontal-container").css('overflow-x','scroll')
                }
            });
        }
    });

    ShowOrHideLoader();
}
function RunUMLSBeta(concepts){
    var baseurl = "http://localhost/UMLS";
    var concept_array = _.uniq(concepts.column(0).data());

    var concept_string = concept_array.join();
    console.log(concept_string);
    var atomSoures = new Array();
    $("input[name=StandardOptions]").each( function () {
        if($(this).prop('checked')){
            atomSoures.push($(this).val());
        }
    });
    var HandledConcepts = new Array();
    $.ajax({
        type: "POST",
        url: baseurl,
        data: {
            "terms": String(concept_string),
            "atomsources" : atomSoures.join(","),
            "remote" : "3",
        },
        success: function (response) {

            console.log(response);
            for (var i = 0, concepts = response.length; i < concepts; i++) {
                var concept = response[i];
                console.log(concept);
                var ConceptName = concept.NSTR;
                var SourcesUsed = new Array();
                var OriginalTerm = concept.original_term;
                for (var j = 0, atoms = concept.atoms.length; j < atoms; j++) {
                    var item = concept.atoms[j];
                    console.log(item);

                    if(!SourcesUsed.includes(item.SAB)){
                        SourcesUsed.push(item.SAB);
                        var AtomCode = item.CODE;
                        var AtomSource = item.SAB;
                        var AtomName = item.STR;

                        DiseaseICD10DataTable.row.add([OriginalTerm, ConceptName + " / " + AtomName , AtomCode, AtomSource]);
                        DiseaseICD10DataTable.draw();
                    }
                }
            }
        },
        error: function (xhr, textStatus, errorThrown) {
            console.log(xhr.responseText);
        },
        complete: function(){
            console.log("complete");
            ShowOrHideLoader();
            $("#horizontal-container").css('overflow-x','scroll')
        }
    });
    ShowOrHideLoader();
}



var TimerSet = false;
function RepeatShowOrHideLoader(){
    TimerSet = false;
    ShowOrHideLoader();
}
function ShowOrHideLoader(){
    console.log("ACTIVE: " + $.active);
    if($.active == 0) {
        $("#IELoader").hide();
        $("#NextButton").prop('disabled', false);
        TimerSet = false;

    }
    else{
        if(!TimerSet){
            TimerSet = true;
            console.log( "setting timer");
            window.setTimeout(RepeatShowOrHideLoader, 2000);


        }
        $("#IELoader").show();
        $("#NextButton").prop('disabled', true);


    }
}
function createCookie(name, value, days) {
    var expires;

    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
}
function readCookie(name) {
    var nameEQ = encodeURIComponent(name) + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
    return null;
}
function eraseCookie(name) {
    createCookie(name, "", -1);
}


String.prototype.contains = function(StringToContain, caseSensitive){
    if(this == undefined){
        return false;
    }
    if(caseSensitive){
        if(this.indexOf(StringToContain) >= 0){
            return true;
        }
        return false;

    }
    else{
        if(this.toLowerCase().indexOf(StringToContain.toLowerCase()) >= 0){
            return true;
        }
        return false;
    }
}



