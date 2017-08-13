/**
 * Created by hugovankrimpen on 28/04/17.
 */

var extractions = new Object();
var extractionpromises = [];
var negationpromises = [];
var UMLSpromises = [];


jQuery(document).ready(function($) {
    ResultDataTable = $("#ResultDataTable").DataTable({
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
    ResultDataTable.clear().draw();

    $( window ).resize(function() {
        ResultDataTable.columns.adjust().draw();
    });

    if(document.getElementById("TextArea")){
        document.getElementById("TextArea").value = readCookie("TextArea");
    }

    ShowOrHideLoader();


    StartTimer();
    $("#launchbutton").click(function () {
        var ExtractorDocumentIDLike = document.getElementById('ExtractorDocumentIDLike').value
        var Limit = document.getElementById('Limit').value
        var Offset = document.getElementById('Offset').value
        if(ExtractorDocumentIDLike.length == 0){
            console.log("Enter a document id like");
            return;
        }
        $.ajax({
            "url": "http://localhost/getalldocuments",
            "method": "GET",
            "data": {
                "limit" : Limit,
                "offset" : Offset,
                "ExtractorDocumentIDLike" : ExtractorDocumentIDLike
                /*
                "documentids" : {
                    1 : 1006,
                }
                */
            },
            success: function(response) {
                StopTimer();
                console.log("All documents retrieved in: " + GetTimerTime() + "ms. Restarting timer now.");
                console.log('running extractions');
                StartTimer();
                RunExtractionsOnResponse(response,0);
            }
        });
    });
    $("#ExtractButton").click(function () {
        ResultDataTable.clear().draw();
        var text = document.getElementById("TextArea").value;
        createCookie("TextArea",text,3);

        RunExtraction(text,0,false);
        $.when.apply($,extractionpromises).then(function() {
            console.log(extractions);
            console.log('all extractions are finished after click');
            var concepts = new Array();
            for (var key in extractions) {
                if (!extractions.hasOwnProperty(key)) continue;
                var extractor_array = extractions[key];

                for (var i = 0; i < extractor_array.length; i++){
                    var entity = extractor_array[i];
                    var concept_name = entity.name;
                    concepts.push(concept_name);
                }
            }
            UMLSpromises.push(RunUMLSBeta(concepts));
            $.when.apply($,UMLSpromises).then(function() {
                console.log("UMLS done");

            });
        });
    });
    
    function RunExtractionsOnResponse(response, number) {
        var text = response[number].text;
        var docid = response[number].documentid;

        RunExtraction(text,docid,true);
        $.when.apply($,extractionpromises).then(function(){
            console.log(extractions);
            console.log('all extractions are finished for docid: ' + response[number].documentid);

            for (var extractor in extractions) {
                // skip loop if the property is from prototype
                if (!extractions.hasOwnProperty(extractor)) continue;
                var array = extractions[extractor];


                negationpromises.push(RunNegation(text,array,AddExtractionsToDatabase,extractor,docid));
            }
            $.when.apply($,negationpromises).then(function(){
                console.log('all negations are finished for docid: ' + response[number].documentid);
                negations = [];
                negationpromises = [];
                extractionpromises = [];
                if(number < response.length-1){
                    console.log("All extractions are finished for docid: " + response[number].documentid + " in time " + GetTimerTime() + "ms");
                    console.log("Now going to run on doc: " + response[(number+1)].documentid);
                    console.log("This was number: " + number)
                    RunExtractionsOnResponse(response, (number+1));
                }
            },function(){
                //error;
            })




        },function(){
            //error;
        })




    }
    function RunExtraction(text, docid, complete){
        if(complete){
            RunUMLS = false;
            extractionpromises.push(RunMeaningCloud(text,docid));
            extractionpromises.push(RunWatson(text,docid));
            extractionpromises.push(RunOpenCalais(text,docid));
            extractionpromises.push(RunHaven(text,docid));
            extractionpromises.push(RunDandelion(text,docid));
            extractionpromises.push(RunTextRazor(text,docid));
        }
        else{
            RunUMLS = true;
            var WatsonIE = $("#WatsonIE").prop('checked');
            var OpenCalaisIE = $("#OpenCalaisIE").prop('checked');
            var Dandelion = $("#Dandelion").prop('checked');
            var Haven = $("#Haven").prop('checked');
            var MeaningCloud = $("#MeaningCloud").prop('checked');
            var TextRazor = $("#TextRazor").prop('checked');

            if(WatsonIE || OpenCalaisIE || Dandelion || Haven || MeaningCloud || TextRazor){
                if(MeaningCloud){
                    extractionpromises.push(RunMeaningCloud(text,docid));
                }
                if(WatsonIE){
                    extractionpromises.push(RunWatson(text,docid));
                }
                if(OpenCalaisIE){
                    extractionpromises.push(RunOpenCalais(text,docid));
                }
                if(Haven){
                    extractionpromises.push(RunHaven(text,docid));
                }
                if(Dandelion){
                    extractionpromises.push(RunDandelion(text,docid));
                }
                if(TextRazor){
                    extractionpromises.push(RunTextRazor(text,docid));
                }
            }
            else{
                alert("Select atleast one entity extraction algorithm");
                return;
            }
        }
    }
    function AddExtractionsToDatabase(result, extractor){
        console.log("Adding extraction from: " + extractor + " amount: " + result.length);

        var nameArray = new Array();
        var documentidArray = new Array();
        var typeArray = new Array();
        var quantityArray = new Array();
        var negationArray = new Array();

        result.forEach(function (item) {
            nameArray.push(item.name);
            documentidArray.push(item.documentid);
            typeArray.push(item.type);
            quantityArray.push("null");
            negationArray.push(item.negation);
        });


        $.ajax({
            type: "POST",
            url: "http://localhost/insert_extraction",
            data: {
                "term": nameArray,
                "extractor": String(extractor),
                "documentid": documentidArray,
                "types": typeArray,
                "quantity": quantityArray,
                "negation": negationArray
            },
            success: function (response) {
                console.log("Succesfully inserted: " + extractor);

            },
            fail: function(error){
                console.log("Error inserted: " + extractor);
            },
        });



    }


});
document.addEventListener("LoadingCompleted", function(e){
    if(RunUMLS){
        var conceptarray = new Array();
        console.log("loading completed");

        //RunUMLSBeta(conceptarray);
    }
    else {

    }
}, false);

var RunUMLS = true;

function RunOpenCalais(text,docid){
    ShowOrHideLoader();
    return $.ajax({
        "url": "http://localhost/OpenCalais",
        "method": "POST",
        "data": {"text" : text},
        success: function(response){
            response = JSON.parse(response);
            var returnArray = new Array();
            for (var key in response) {
                // skip loop if the property is from prototype
                if (!response.hasOwnProperty(key)) continue;

                var obj = response[key];
                if(obj._typeGroup == "entities" && obj._type == "MedicalCondition"){
                    var object = {"name":obj.name, "type": "MedicalCondition", "Source" : "OpenCalais", "PotentialICD10Code" : "0", "documentid": docid};
                    returnArray.push(object);
                }
                if(obj._typeGroup == "entities" && obj._type == "PharmaceuticalDrug"){
                    var object = {"name":obj.name, "type": "Drug", "Source" : "OpenCalais", "PotentialICD10Code" : "0", "documentid": docid};
                    returnArray.push(object);
                }

            }
            extractions['OpenCalais'] = returnArray;
            //RunNegation(text,returnArray,callback,"OpenCalais",docid);
        },
        fail: function(error){

        },
        complete: function(){
            console.log("complete calais");
            ShowOrHideLoader();
        }
    });


}
function RunWatson(text,docid){
    var username = "92c0bd16-9f1e-4720-b3bc-bb0628a956a7";
    var password = "ZKLhlxfqyw1z";
    var baseurl = "http://localhost/WatsonNLU";

    var outputmode = "json";
    var apikey = "6f30339d95ec00a62403ac09df6d592b389bfd75";

    ShowOrHideLoader();

    return $.ajax({

        type: "POST",
        url: baseurl,
        data: {
            "text": String(text),
        },
        success: function (response) {
            response = JSON.parse(response);
            var entities = response["entities"];
            var returnArray = new Array();
            entities.forEach(function (item) {
                if (item.relevance > 0.1 && item.type == "HealthCondition") {
                    var o = {"name":item.text, "type": "MedicalCondition", "Source" : "Watson", "PotentialICD10Code" : "0", "documentid": docid};
                    returnArray.push(o)

                }
                if (item.relevance > 0.1 && item.type == "Drug") {
                    var o = {"name":item.text, "type": "Drug", "Source" : "Watson", "PotentialICD10Code" : "0", "documentid": docid};
                    returnArray.push(o)

                }
            });
            extractions['Watson'] = returnArray;
            //RunNegation(text,returnArray,callback,"Watson",docid);

        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert("some error");
        },
        complete: function(){
            console.log("completed watson");
            ShowOrHideLoader();
        }
    });

}
function RunDandelion(text,docid){
    ShowOrHideLoader();
    return $.ajax({
        "url": "http://localhost/Dandelion",
        "method": "POST",
        "data": {"text" : text},
        success: function(response){
            response = JSON.parse(response);
            var returnArray = new Array();
            var passedterms = new Array();
            for (var key in response["annotations"]) {
                var obj = response["annotations"][key];
                var short_types = new Array();
                for (var typekey in obj.types){
                    if (response.hasOwnProperty(typekey)) continue;
                    var typeUrl = obj.types[typekey];
                    var splitUrl = typeUrl.split("/");
                    short_types.push(splitUrl[splitUrl.length-1]);
                }
                var o;
                var name = obj.spot.toLowerCase();
                name = name.split('\n').join(' ');



                if(_.contains(short_types, "Disease") ){
                    o = {"name":name, "type": "MedicalCondition", "Source" : "Dandelion", "PotentialICD10Code" : "0", "documentid": docid};
                }
                else if(_.contains(short_types, "Drug") ){
                    o = {"name":obj.spot, "type": "Drug", "Source" : "Dandelion", "PotentialICD10Code" : "0", "documentid": docid};
                }
                if(!(o == undefined) && !(_.contains(returnArray,o)) && !(_.contains(passedterms,o.name))){
                    passedterms.push(name);
                    returnArray.push(o);
                }

            }
            returnArray = _.uniq(returnArray);
            extractions['Dandelion'] = returnArray;

            //RunNegation(text,returnArray,callback,"Dandelion",docid);
        },
        fail: function(error){
            alert(error);

        },
        complete: function(){
            console.log("Completed Dandelion. Amount of results: " + extractions['Dandelion'].length);
            ShowOrHideLoader();
        }
    });

}
function RunMeaningCloud(text,docid){
    var key = 'dfe78864084853e9ee3ae548194c1038';
    var outputformat = 'json';
    var lang = 'en';
    var txt = text;

    var topictypes = 'a';

    return $.ajax({
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

            var entities = response["concept_list"];
            var returnArray = new Array();
            entities.forEach(function (item) {
                if(item.sementity.type.contains("Disease")){
                    returnArray.push({"name":item.form, "type": "MedicalCondition", "Source" : "MeaningCloud", "PotentialICD10Code" : "0" , "documentid": docid, "Remote_name" : item.official_form})
                }
            });
            extractions['MeaningCloud'] = returnArray;
            //RunNegation(text,returnArray,callback,"MeaningCloud",docid);
        }
    })
}
function RunHaven(text,docid){

    ShowOrHideLoader();
    return $.ajax({
        type: 'POST',
        url: "http://localhost/Haven",
        data: {
            'text': text,
        },
        success: function(response) {
            var resultsnumber = 0;
            var returnArray = new Array();
            response.forEach(function (item) {
                var type;
                if(item.type == "drugs_eng"){
                    type = "Drug";
                }
                else if(item.type == "medical_conditions_eng"){
                    type = "MedicalCondition";
                }

                o = {"name":item.normalized_text.toLowerCase(), "type": type, "Source" : "Haven", "documentid": docid ,"PotentialICD10Code" : "0"};
                returnArray.push(o);
            });
            extractions['Haven'] = returnArray;
            //RunNegation(text,returnArray,callback,"Haven",docid);


        },
        fail: function(error){
            alert(error);

        },
        complete: function(){
            console.log("complete Haven");
            ShowOrHideLoader();
        }
    });

}
function RunTextRazor(text,docid){
    ShowOrHideLoader();
    return $.ajax({
        type: 'POST',
        url: "http://localhost/TextRazor",
        data: {
            'text': text,
        },
        success: function(response) {

            var returnArray = new Array();
            var passedterms = new Array();
            response.forEach(function (item) {
                var lowercasename = item.original_name.toLowerCase();
                var type = item.type;
                if(type == 'Disease'){
                    type = "MedicalCondition"
                }
                var o = {
                    "name":lowercasename,
                    "type": type,
                    "Source" : "TextRazorlasix" +
                    "",
                    "PotentialICD10Code" : "0",
                    "documentid": docid};
                if(!_.contains(returnArray,o) && !_.contains(passedterms,o.name) ){
                    passedterms.push(o.name);
                    returnArray.push(o);
                }


            });
            extractions['TextRazor'] = returnArray;
            //RunNegation(text,returnArray,callback,"TextRazor",docid);


        },
        fail: function(error){
            alert(error);

        },
        complete: function(){
            console.log("completed TextRazor");
            ShowOrHideLoader();
        }
    });

}
function RunNegation(text,concepts, callback, extractor, docid){
    console.log("Running Negations: " + extractor);

    var conceptnames = new Array();
    concepts.forEach(function(item, index ){
        conceptnames.push(item.name);
    });
    var ntext = text.split("\n").join(" ").match(/\(?[^\.\?\!]+[\.!\?]\)?/g).join("\n");

    ShowOrHideLoader();

    return $.ajax({
        type: "POST",
        url: "http://localhost:9000/api/negation",
        timeout: 300000,
        async: true,
        data: {
            "text": ntext,
            "terms" : conceptnames.join(","),
            "docid" : docid,
            "extractor" : extractor
        },
        success: function (response) {


            var negated_terms_array = response.split("\n");
            var handled_negations = new Array();


            concepts.forEach(function(concept, concept_index ){
                concept.negation = "0";
                negated_terms_array.forEach(function(negation,neg_index){
                    if(!_.contains(handled_negations,negation)){
                        if(concept.name == negation){
                            console.log("negated: " + negation + " @ index " + concept_index);
                            concept.negation = "1";
                            handled_negations.push(negation);
                        }
                    }
                });
            });
            console.log(concepts);

            if (typeof callback === "function") {
                callback(concepts, extractor);
            }
        },
        error: function (xhr, textStatus, errorThrown) {
            console.log("ERROR ZOMG");
            console.log('Request Status: ' + xhr.status + ' Status Text: ' + xhr.statusText + ' ' + xhr.responseText);
            console.log(textStatus);
            console.log(errorThrown);
        },
        complete: function(){
            console.log("completed negation for extractor: " + extractor);
        }
    });
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
                    //console.log(xhr.responseText);
                },
                complete: function(){
                    //console.log("complete");
                    ShowOrHideLoader();
                }
            });
        }
    });

    ShowOrHideLoader();
}
function RunUMLSBeta(concepts){
    var baseurl = "http://localhost/UMLS";

    var concept_array = _.uniq(concepts);

    var concept_string = concept_array.join();

    var atomSoures = new Array();
    $("input[name=StandardOptions]").each( function () {
        if($(this).prop('checked')){
            atomSoures.push($(this).val());
        }
    });
    var HandledConcepts = new Array();
    return $.ajax({
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

                        ResultDataTable.row.add([OriginalTerm, ConceptName + " / " + AtomName , AtomCode, AtomSource]);
                        ResultDataTable.draw();
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
        $("#ExtractButton").prop('disabled', false);
        TimerSet = false;

        var event = new CustomEvent("LoadingCompleted");
        document.dispatchEvent(event);
    }
    else{
        if(!TimerSet){
            TimerSet = true;
            console.log( "setting timer");
            window.setTimeout(RepeatShowOrHideLoader, 2000);
        }
        $("#IELoader").show();
        $("#ExtractButton").prop('disabled', true);


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

var timer;
var timercount = 0;
function StartTimer(){
    timercount = 0;
    timer = setInterval(TimerTick,100);
}
function TimerTick(){
    //Gets executed every tick
    timercount++;
}
function GetTimerTime(){
    var miliseconds = timercount * 100;
    return miliseconds;
}
function StopTimer(){
    clearInterval(timer);
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