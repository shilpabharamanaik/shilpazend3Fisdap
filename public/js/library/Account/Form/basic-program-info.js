$(function(){
    $("#orgType").change(function(e){
        toggleProfession($(this).val());
        toggleEMSProviderTraining($(this).val());
    });

    toggleProfession($("#orgType").val());
    toggleEMSProviderTraining($("#orgType").val());

    $("#next-link").click(function(){
        $("form").submit()
    });
    
    $("#profession").change(function(e){
        toggleCertLevels($(this).val());
    });
});

function toggleProfession(orgType) {
    var professionElement = $("#profession").parents(".form-prompt");

    if (orgType == 1) {
        professionElement.show();
        toggleCertLevels($("#profession").val());
    } else {
        professionElement.hide();
        toggleCertLevels(0);
    }
}

function toggleCertLevels(professionId) {
    if (professionId > 0) {
        $("#cert-levels").show();
    } else {
        $("#cert-levels").hide();
    }


    $(".certs").hide();
    $("#certs_" + professionId).show();

}

function toggleEMSProviderTraining(orgType) {
    var emsTrainingElement = $("input[name=emsProviderTraining]").parents(".form-prompt");

    if (orgType == 2) {
        emsTrainingElement.show();
    } else {
        emsTrainingElement.hide();
    }
}