
jQuery(document).ready(function($) {

    var AllExtractedDiseases = new Array();
    var AllExtractedATC10Diseases = new Object();
    var AllExtractedDrugs = new Array();

    var ATCCodeResults = new Object();
    var ICD10CodeResults = new Object();





    //<editor-fold desc="Datatables declarations">

    //<editor-fold desc="Watson declarations">
    var ConceptResultDataTableWatson = $("#ConceptDataTableWatson").DataTable();
    ConceptResultDataTableWatson.clear().draw();

    var EntityResultDataTableWatson = $("#EntityDataTableWatson").DataTable({
        "scrollY": "200px",
        "paging": false
    });
    EntityResultDataTableWatson.clear().draw();

    var DiseaseDataTableWatson = $("#DiseaseDataTableWatson").DataTable({
        "scrollY": "200px",
        "paging": false
    });
    DiseaseDataTableWatson.clear().draw();

    var DrugQuantityDataTableWatson = $("#DrugQuantityDataTableWatson").DataTable({
        "scrollY": "200px",
        "paging": false
    });
    DrugQuantityDataTableWatson.clear().draw();
    //</editor-fold>>

    var ConceptResultDataTableMeaningCloud = $("#ConceptDataTableMeaningCloud").DataTable();
    ConceptResultDataTableMeaningCloud.clear().draw();

    var EntityResultDataTableMeaningCloud = $("#EntityDataTableMeaningCloud").DataTable();
    EntityResultDataTableMeaningCloud.clear().draw();

    var ConceptResultDataTableIntellexer = $("#ConceptDataTableIntellexer").DataTable();
    ConceptResultDataTableIntellexer.clear().draw();

    var EntityResultDataTableIntellexer = $("#EntityDataTableIntellexer").DataTable();
    EntityResultDataTableIntellexer.clear().draw();

    var EntityResultDataTableStanfordNLP = $("#EntityDataTableStanfordNLP").DataTable();
    EntityResultDataTableStanfordNLP.clear().draw();

    var EntityResultDataTableOpenCalais = $("#EntityResultDataTableOpenCalais").DataTable();
    EntityResultDataTableOpenCalais.clear().draw();

    var ICD10DataTable = $("#ICD10DataTable").DataTable();
    ICD10DataTable.clear().draw();
    var ATCDataTable = $("#ATCDataTable").DataTable();
    ATCDataTable.clear().draw();

    //</editor-fold>

    //<editor-fold desc="Toggle Dialog">



    var Dialog = $("#dialog").dialog({autoOpen: false, width: 300});

    $('#DiseaseDataTableWatson').on('click', 'tr', function () {
        var Data = DiseaseDataTableWatson.row(this).data();
        var ICD10Code = Data[0];
        var ICD10Data = ICD10CodeResults[ICD10Code];


        if(ICD10Data !== undefined){
            Dialog.dialog({title: "ICD10 Findings", width: 500});

            Dialog.dialog().html("<b>Code</b>: " + ICD10Data.code + "</br><b>Constant + Information Type:</b> "+ ICD10Data.constantType + " " + ICD10Data.informationType + "</br> <b>Description</b>: "+ ICD10Data.description);
            Dialog.dialog('open');
        }
        return false;
    });
    $('#DrugQuantityDataTableWatson').on('click', 'tr', function () {
        var Data = DrugQuantityDataTableWatson.row(this).data();
        var DrugQuantityCombo = Data[0];
        var ATCResult = ATCCodeResults[DrugQuantityCombo];
        if(ATCResult !== undefined){
            Dialog.dialog({title: "ATC Findings"});
            Dialog.dialog().html("<b>Code:</b> " + ATCResult.standardCode + "</br><b>Description:</b> "+ ATCResult.description + "</br><b>Form:</b> " + ATCResult.form + "</br><b>Route:</b> "+ ATCResult.route + "</br><b>Dosis:</b> " + ATCResult.strength);
            Dialog.dialog('open');
        }
        return false;
    });
    $('#ICD10DataTable').on('click', 'tr', function () {

        var Data = ICD10DataTable.row(this).data();
        var ICD10Code = Data[0];
        var ICD10Data = AllExtractedATC10Diseases[ICD10Code];
        if(ICD10Data !== undefined){
            Dialog.dialog({title: "ICD10 Findings", width: 500});

            Dialog.dialog().html("<b>Code</b>: " + ICD10Data.code + "</br><b>Constant + Information Type:</b> "+ ICD10Data.constantType + " " + ICD10Data.informationType + "</br> <b>Description</b>: "+ ICD10Data.description);
            Dialog.dialog('open');
        }
        return false;
    });

    //</editor-fold">

    //<editor-fold desc="Button Clicks">
    $("#IEWatsonButton").click(function () {

        //AlchemyAPI
        //
        // var baseurl = "https://gateway-a.watsonplatform.net/calls/text/TextGetCombinedData";
        // var outputmode = "json";
        // var apikey = "b3427d208d067efd6316fddab929ed68be0aa376";

        document.getElementById("watsonloader").style.visibility = "visible";
        document.getElementById("WatsonIEContainer").style.visibility = "hidden";


        //Watson NLU
        var username = "92c0bd16-9f1e-4720-b3bc-bb0628a956a7";
        var password = "ZKLhlxfqyw1z";
        var baseurl = "http://localhost/WatsonNLU";

        var outputmode = "json";
        var apikey = "6f30339d95ec00a62403ac09df6d592b389bfd75";

        var text = document.getElementById("TextArea").value;

        $.ajax({

            type: "POST",
            url: baseurl,
            data: {
                "text": String(text),
            },
            success: function (response) {
                response = JSON.parse(response);

                var array = new Array();

                var entities = response["entities"];
                EntityResultDataTableWatson.clear().draw();
                var DiseaseArray = new Array();
                entities.forEach(function (item) {
                    if (item.relevance > 0.1) {
                        EntityResultDataTableWatson.row.add([item.text, item.type, item.relevance])
                        array.push(item.text);
                        if(item.type == "HealthCondition"){
                            DiseaseArray.push(item.text);
                        }
                    }
                });
                EntityResultDataTableWatson.column('2:visible').order('desc');
                EntityResultDataTableWatson.draw();

                var DrugArray = new Array();
                var QuantityArray = new Array();


                entities.forEach(function (item) {
                    if (item.type == "Drug") {
                        DrugArray.push(item.text);
                    }
                    if (item.type == "Quantity") {
                        QuantityArray.push(item.text);
                    }
                });
                var DrugQuantityPerLineCombo = new Array();
                var DrugQuantityPerSentenceCombo = new Array();
                var DrugQuantityPerCommaSentenceCombo = new Array();
                var DrugQuantityCertain = new Array();
                var WordToMark = new Array();

                var Lines = text.split(/\r|\n/);
                var Sentences = text.split(".");
                var CommaSeperated = text.split(/[.,]/);
                var WhiteSpaceSeperated = text.replace(new RegExp('\r?\n','g'), " ").split(" ");

                WhiteSpaceSeperated.forEach(function (word) {
                    QuantityArray.forEach(function (quantity) {

                        if(
                            word.contains(quantity,false) || //If the quantity is not split in two
                            (quantity.split(" ").length > 2) && (word.contains(quantity.split(" ")[0],false) && WhiteSpaceSeperated[WhiteSpaceSeperated.indexOf(word)+1].contains(quantity.split(" ")[1],false))
                        ) {
                            //console.log("Found quantity: " + quantity);
                            var previousIndex = WhiteSpaceSeperated.indexOf(word) - 1;
                            var previousWord = WhiteSpaceSeperated[previousIndex];
                            var nextIndex = WhiteSpaceSeperated.indexOf(word) + 2;
                            var nextWord = WhiteSpaceSeperated[nextIndex];
                            //console.log("Previous word: " + previousWord);
                            //console.log("Next word: " + nextWord);
                            DrugArray.forEach(function (drug) {
                                if (previousWord.contains(drug,false)||
                                    nextWord.contains(drug,false) ) {
                                    //console.log("Found: " + quantity + " with the drug: " + drug + " next to it.");
                                    DrugQuantityCertain.push(quantity + "-" + drug);
                                }
                            });
                        }
                    });
                });
                Lines.forEach(function (line) {
                    var ComboPerLine = new Array();
                    DrugArray.forEach(function (drug) {
                        QuantityArray.forEach(function (quantity) {


                            if (line.toLowerCase().indexOf(drug.toLowerCase()) >= 0 && line.toLowerCase().indexOf(quantity.toLowerCase()) >= 0) {
                                //A drug/quantity combination was found on one single line
                                ComboPerLine.push(quantity + "-" + drug);

                                //DrugQuantityPerLineCombo.push(quantity + " " + drug);
                            }
                        });
                    });
                    if (ComboPerLine.length == 1) {
                        console.log("Certain: " + ComboPerLine + "in line: " + line);
                        WordToMark.push(ComboPerLine[0].split("-")[0]);
                        WordToMark.push(ComboPerLine[0].split("-")[1]);

                        DrugQuantityCertain = DrugQuantityCertain.concat(ComboPerLine);

                    }
                    else if(ComboPerLine.length > 1) {
                        //console.log("NOT certain: " + ComboPerLine + "\n \n In line: " + line);
                        DrugQuantityPerLineCombo = DrugQuantityPerLineCombo.concat(ComboPerLine);
                    }
                    else{
                        //console.log("Nothing found in line: " + line);
                    }

                });
                Sentences.forEach(function (line) {
                    DrugArray.forEach(function (drug) {
                        QuantityArray.forEach(function (quantity) {
                            if (line.toLowerCase().indexOf(drug.toLowerCase()) >= 0 && line.toLowerCase().indexOf(quantity.toLowerCase()) >= 0) {
                                //A drug/quantity combination was found in one single sentence
                                DrugQuantityPerSentenceCombo.push(quantity + "-" + drug);
                            }
                        });

                    });
                });
                CommaSeperated.forEach(function (line) {
                    DrugArray.forEach(function (drug) {
                        QuantityArray.forEach(function (quantity) {
                            if (line.toLowerCase().indexOf(drug.toLowerCase()) >= 0 && line.toLowerCase().indexOf(quantity.toLowerCase()) >= 0) {
                                //A drug/quantity combination was found in one single sentence
                                DrugQuantityPerCommaSentenceCombo.push(quantity + "-" + drug);
                            }
                        });

                    });
                });

                var ResultArray = _.intersection(DrugQuantityPerLineCombo, DrugQuantityPerSentenceCombo, DrugQuantityPerCommaSentenceCombo);
                DrugQuantityCertain = _.uniq(DrugQuantityCertain);
                DrugQuantityDataTableWatson.clear().draw();


                DrugQuantityCertain.forEach (function(drugQuantityCombo){
                    $.ajax({

                        type: "POST",
                        url: "http://localhost/FindATCCodes",
                        dataType: "json",
                        data: {
                            "name" : drugQuantityCombo.split('-')[1],
                            "quantity" : drugQuantityCombo.split('-')[0]
                        },
                        success: function (response) {
                            if(response.length > 0){

                                AllExtractedDrugs.push(drugQuantityCombo.name);
                                var atccode = response[0].standardCode;
                                console.log("atc code: "+ atccode)
                                DrugQuantityDataTableWatson.row.add([drugQuantityCombo, "Certain", atccode]);
                                ATCCodeResults[drugQuantityCombo] = response[0];
                                DrugQuantityDataTableWatson.draw();
                            }
                            else{
                                DrugQuantityDataTableWatson.row.add([drugQuantityCombo, "Certain", "unknown"]);
                                DrugQuantityDataTableWatson.draw();
                            }

                        },
                        error: function (err) {
                            console.log(JSON.stringify(err, null, 2));
                        }
                    });


                });


                ResultArray.forEach (function(drugQuantityCombo){
                    console.log("adding " + drugQuantityCombo);
                    DrugQuantityDataTableWatson.row.add([drugQuantityCombo, "Uncertain", "unknown"]);
                });
                DrugQuantityDataTableWatson.draw();


                var data_to_send = JSON.stringify(DiseaseArray);
                $.ajax({

                    type: "POST",
                    url: "http://localhost/FindICD10Codes",
                    dataType: "json",
                    data: {
                        "HealthConditions" : data_to_send
                    },
                    success: function (response) {


                        DiseaseDataTableWatson.clear().draw();

                        response.forEach(function (item) {
                            DiseaseDataTableWatson.row.add([item.code, item.OriginalHealthCondition]);
                            ICD10CodeResults[item.code] = item;

                            AllExtractedDiseases.push(item.OriginalHealthCondition);
                        });


                        DiseaseDataTableWatson.draw();
                        console.log('success');

                    },
                    error: function (err) {
                        console.log(JSON.stringify(err, null, 2));
                    }
                }).always(function(data) {
                    console.log('always');
                    document.getElementById("watsonloader").style.visibility = "hidden";
                    document.getElementById("WatsonIEContainer").style.visibility = "visible";
                });




            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert("some error");
            }
        });

    });
    $("#IEStanfordNLPButton").click(function () {
        var txt = document.getElementById("TextArea").value;
        $.ajax({
            url: "http://localhost:9000/api",
            type: "POST",
            //headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            data:{
                'text' : txt
            },

            success: function (response) {
                console.log("RESULT: " + response);
                for (var key in response) {
                    console.log(key);

                }
                /*
                for (var key in response) {
                    // skip loop if the property is from prototype
                    if (!response.hasOwnProperty(key)) continue;

                    var obj = response[key];
                    for (var prop in obj) {
                        // skip loop if the property is from prototype
                        if(!obj.hasOwnProperty(prop)) continue;

                        // your code
                        EntityResultDataTableStanfordNLP.row.add([obj.word, obj.ner])
                    }
                }

                response.forEach(function (arrayItem) {
                    if(arrayItem.w > 0.1) {
                        ConceptResultDataTableIntellexer.row.add([arrayItem.text, arrayItem.w])
                    }
                });
                */
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert("some error");
            }
        });

    });
    $("#IEAmbiverseButton").click(function () {

        $.ajax({
            type: 'POST',

            url: "https://api.ambiverse.com/oauth/token",
            headers: {
                "Access-Control-Allow-Origin":"*",
                "Access-Control-Allow-Methods":"GET, POST, PATCH, PUT, DELETE, OPTIONS",
                "Access-Control-Allow-Headers":"Origin, Content-Type, X-Auth-Token"
            },
            data: {
                'grant_type': 'client_credentials',
                'client_id': '125f20ba',
                'client_secret': '9ceef7c0504ce67fdfa152d7928e7c1a'
            },
            success: function(msg) {
                console.log(msg);
            }

        })
    });
    $("#IEMeaningCloudButton").click(function () {

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
         var concepts = response["concept_list"];
         ConceptResultDataTableMeaningCloud.clear().draw();
         concepts.forEach(function (arrayItem) {
         ConceptResultDataTableMeaningCloud.row.add([arrayItem.form, arrayItem.relevance])
         });
         ConceptResultDataTableMeaningCloud.column('1:visible').order('desc');
         ConceptResultDataTableMeaningCloud.draw();

         var entities = response["entity_list"];
         EntityResultDataTableMeaningCloud.clear().draw();
         entities.forEach(function (item) {
         var ctype = item.sementity.type;
         var arr = ctype.split(">");
         var type = "Unknown";
         if(arr.length >= 2 && (arr[arr.length-1] + ">" + arr[arr.length-2]).length < 30 ){
         type = arr[arr.length-1] + ">" + arr[arr.length-2];
         }
         else{
         type = arr[arr.length-1];
         }
         if(item.relevance > 10){
         EntityResultDataTableMeaningCloud.row.add([item.form,type, item.relevance])
         }


         });
         EntityResultDataTableMeaningCloud.column('2:visible').order('desc');
         EntityResultDataTableMeaningCloud.draw();
         }

         })

    });
    $("#IEIntellexerButton").click(function () {
        var txt = document.getElementById("TextArea").value;
        var ners = new Array("Unknown",
            "Person",
            "Organization",
            "Location",
            "Title",
            "Position",
            "Age",
            "Date",
            "Duration",
            "Nationality",
            "Event",
            "Url",
            "MiscellaneousLocation"
        );


        var settingsConcepts = {
            "url": "http://api.intellexer.com/summarizeText?apikey=459ab014-443a-4379-9612-6066250d9408&summaryRestriction=20&returnedTopicsCount=20&loadConceptsTree=true&loadNamedEntityTree=true&usePercentRestriction=true&conceptsRestriction=20&structure=general&fullTextTrees=true&wrapConcepts=true",
            "method": "POST",
            "data": txt
        }
        var settingsConcepts1 = {
            "url": "http://api.intellexer.com/clusterizeText?apikey=459ab014-443a-4379-9612-6066250d9408",
            "method": "POST",
            "data": txt,
            "options": {
                "topics": ["Health.pharmaceuticals"]
            }

        }
        $.ajax(settingsConcepts).done(function (response) {
            console.log(response);
            var concepts = response["items"];
            ConceptResultDataTableIntellexer.clear().draw();
            concepts.forEach(function (arrayItem) {
                if(arrayItem.weight > 0.1) {
                    ConceptResultDataTableIntellexer.row.add([arrayItem.text, arrayItem.weight])
                }
            });
            ConceptResultDataTableIntellexer.column('1:visible').order('desc');
            ConceptResultDataTableIntellexer.draw();

        });




        var settings = {
            "url": "http://api.intellexer.com/recognizeNeText?apikey=459ab014-443a-4379-9612-6066250d9408&loadNamedEntities=true&loadRelationsTree=true&loadSentences=true",
            "method": "POST",
            "data": txt
        }

        $.ajax(settings).done(function (response) {
            var entities = response["entities"];
            EntityResultDataTableIntellexer.clear().draw();
            entities.forEach(function (item) {

                EntityResultDataTableIntellexer.row.add([item.text,ners[item.type], "Unknown"]);
            });
            EntityResultDataTableIntellexer.column('2:visible').order('desc');
            EntityResultDataTableIntellexer.draw();
        });
    });
    $("#OpenCalaisButton").click(function () {
        var txt = document.getElementById("TextArea").value;
        var settings = {
            "url": "http://localhost/OpenCalais",
            "method": "POST",
            "data": {"text" : txt}
        }
        $.ajax(settings).done(function (response) {

            response = JSON.parse(response);


            EntityResultDataTableOpenCalais.clear().draw();
            for (var key in response) {
                // skip loop if the property is from prototype
                if (!response.hasOwnProperty(key)) continue;

                var obj = response[key];
                if(obj._typeGroup == "entities"){
                    EntityResultDataTableOpenCalais.row.add([obj.name,obj._type,obj.relevance]);
                    if(obj._type == "MedicalCondition"){
                        AllExtractedDiseases.push(obj.name);
                    }


                }
            }
            EntityResultDataTableOpenCalais.column('2:visible').order('desc');
            EntityResultDataTableOpenCalais.draw();

        });
    });
    $("#UpdateResultsButton").click(function () {

        var data_to_send = JSON.stringify(_.uniq(AllExtractedDiseases));
        console.log(data_to_send);
        $.ajax({

            type: "POST",
            url: "http://localhost/FindICD10Codes",
            dataType: "json",
            data: {
                "HealthConditions" : data_to_send
            },
            success: function (response) {
                console.log(response);

                ICD10DataTable.clear().draw();
                response.forEach(function (item) {
                    ICD10DataTable.row.add([item.code, item.OriginalHealthCondition]);
                    AllExtractedATC10Diseases[item.code] = item;
                });
                ICD10DataTable.draw();

            },
            error: function (err) {
                console.log(JSON.stringify(err, null, 2));
            }
        });
    });
    //</editor-fold>
});


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

