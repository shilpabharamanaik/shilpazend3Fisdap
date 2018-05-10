$(function () {
    disableTransfer();

    $(document).on("submit", "form", function(e) {
        var data = $('.merge-account-form').serializeArray().reduce(function(obj, item) {
            obj[item.name] = item.value;
            return obj;
        }, {});

        var errorMsg = "";

        data['studentIdA'] = data['studentIdA'].replace(/[^0-9]/g, "");
        data['studentIdB'] = data['studentIdB'].replace(/[^0-9]/g, "");

        if (data["usernameGroup"] == null) {
            errorMsg += "Missing Username<br/>";
        }
        if (data["programGroup"] == null) {
            errorMsg += "Missing Program<br/>";
        }
        if (data["userGroup"] == null) {
            errorMsg += "Missing Name<br/>";
        }
        if (data["emailGroup"] == null) {
            errorMsg += "Missing Email<br/>";
        }
        if (data["certGroup"] == null) {
            errorMsg += "Missing Certification<br/>";
        }
        if (data["gradGroup"] == null) {
            errorMsg += "Missing Grad Date<br/>";
        }

        if (errorMsg.length > 0) {
            $('#merge-account-error').html(errorMsg);
            $('#merge-account-error').show();

            e.preventDefault();
            return false;
        } else {
            $('#merge-account-error').hide();
        }
    });

    $("#studentIdA").change(function () {
        if ($(this).val() != "") {
            $("#throbberA").show();

            $.post(
                '/account/edit/find-users-to-merge',
                {
                    id: $(this).val().replace(/[^0-9]/g, ""),
                    studentIdOther: $("#studentIdB").val().replace(/[^0-9]/g, "")
                },

                function (response) {
                    if (response == null) {
                        $('#merge-account-error').html("No Student found for ID: "+$('#studentIdA').val().replace(/[^0-9]/g, ""));
                        $('#merge-account-error').show();
                        resetForm('A');
                    } else {
                        $('#merge-account-error').hide();
                        resetRadioButtons();

                        $("#usernameRadioA").val($("#studentIdA").val());
                        $("#usernameLabelA").html(response['user_username']);

                        if ($("#usernameRadioB").val() == response['user_username']) {
                            $("#usernameRadioA").attr('checked', true);
                            $('#usernameResult').html($("#usernameLabelA").html());

                            $("#testsRadioA").attr('checked', true);
                            $('#testsResult').html($("#shiftsLabelA").html());
                        } else {
                            $('#usernameResult').html('');
                        }
                        
                        $("#programRadioA").val(response['program_id']);
                        $("#programLabelA").html(response['program_name']);

                        if ($("#programRadioB").val() == response['program_id']) {
                            $("#programRadioA").attr('checked', true);
                            $('#programResult').html($("#programLabelA").html());

                            $("#shiftsRadioA").attr('checked', true);
                            $('#shiftsResult').html($("#shiftsLabelA").html());
                        } else {
                            $('#programResult').html('');
                        }
                        
                        $("#userRadioA").val(response['user_name']);
                        $("#userLabelA").html(response['user_name']);

                        if ($("#userRadioB").val() == response['user_name']) {
                            $("#userRadioA").attr('checked', true);
                            $('#userResult').html(response['user_name']);
                        } else {
                            $('#userResult').html('');
                        }
                        
                        $("#emailRadioA").val(response['user_email']);
                        $("#emailLabelA").html(response['user_email']);

                        if ($("#emailRadioB").val() == response['user_email']) {
                            $("#emailRadioA").attr('checked', true);
                            $('#emailResult').html(response['user_email']);
                        } else {
                            $('#emailResult').html('');
                        }

                        $("#certRadioA").val(response['cert']);
                        $("#certLabelA").html(response['cert']);

                        if ($("#certRadioB").val() == response['cert']) {
                            $("#certRadioA").attr('checked', true);
                            $('#certResult').html(response['cert']);
                        } else {
                            $('#certResult').html('');
                        }

                        $("#gradRadioA").val(response['grad_date']);
                        $("#gradLabelA").html(response['grad_date']);

                        if ($("#gradRadioB").val() == response['grad_date']) {
                            $("#gradRadioA").attr('checked', true);
                            $('#gradResult').html(response['grad_date']);
                        } else {
                            $('#gradResult').html('');
                        }

                        // The products from ProductShields have links around them.
                        // Rather than write another method in the Entity, I'm just
                        // going to parse out the <a> tags.
                        var prods = response['products'];
                        if (prods != null) {
                            prods = prods.replace(/<a.*?>/g, '');
                            prods = prods.replace(/<\/a>/g, '');
                        }
                        $("#productsLabelA").html(prods);

                        var prodsResult = response['products_result'];
                        if (prodsResult != null) {
                            prodsResult = prodsResult.replace(/<a.*?>/g, '');
                            prodsResult = prodsResult.replace(/<\/a>/g, '');
                        }
                        $("#productsResult").html(prodsResult);
                        $("#snA").val(response['sn']);

                        $("#shiftsRadioA").val(response['shift_count']);
                        $("#shiftsLabelA").html(response['shift_count']);

                        if (response['test_data'] != null) {
                            var testDataString = "Finished: " + response['test_data']['finished_count'] + "<br/>" +
                                "In Progress: " + response['test_data']['inprogress_count'] + "<br/>" +
                                "Abandoned: " + response['test_data']['abandoned_count'];
                        } else {
                            var testDataString = "N/A";
                        }

                        $("#testsLabelA").html(testDataString);

                        if ($("#testsLabelB").html() == testDataString) {
                            $("#testsLabelA").attr('checked', true);
                            $('#testsResult').html(testDataString);
                        } else {
                            $('#testsResult').html('');
                        }

                        $("#throbberA").fadeOut();
                    }

                    if ($("#studentIdA").val().length > 0 && $("#studentIdB").val().length > 0) {
                        enableTransfer();
                    } else {
                        disableTransfer();
                    }
                }
            );
        }
    });

    $("#studentIdB").change(function () {
        if ($(this).val() != "") {
            $("#throbberB").show();

            $.post(
                '/account/edit/find-users-to-merge',
                {
                    id: $(this).val().replace(/[^0-9]/g, ""),
                    studentIdOther: $("#studentIdA").val().replace(/[^0-9]/g, "")
                },

                function (response) {
                    if (response == null) {
                        $('#merge-account-error').html("No Student found for ID: "+$('#studentIdB').val().replace(/[^0-9]/g, ""));
                        $('#merge-account-error').show();
                        resetForm('B');
                    } else {
                        $('#merge-account-error').hide();
                        resetRadioButtons();

                        $("#usernameRadioB").val($("#studentIdB").val());
                        $("#usernameLabelB").html(response['user_username']);

                        if ($("#usernameRadioA").val() == response['user_username']) {
                            $("#usernameRadioA").attr('checked', true);
                            $('#usernameResult').html($("#usernameLabelB").html());

                            $("#testsRadioA").attr('checked', true);
                            $('#testsResult').html($("#shiftsLabelA").html());
                        } else {
                            $('#usernameResult').html('');
                        }

                        $("#programRadioB").val(response['program_id']);
                        $("#programLabelB").html(response['program_name']);
                        
                        if ($("#programRadioA").val() == response['program_id']) {
                            $("#programRadioA").attr('checked', true);
                            $('#programResult').html(response['program_name']);

                            $("#shiftsRadioA").attr('checked', true);
                            $('#shiftsResult').html($("#shiftsLabelA").html());
                        } else {
                            $('#programResult').html('');
                        }

                        $("#userRadioB").val(response['user_name']);
                        $("#userLabelB").html(response['user_name']);

                        if ($("#userRadioA").val() == response['user_name']) {
                            $("#userRadioA").attr('checked', true);
                            $('#userResult').html(response['user_name']);
                        } else {
                            $('#userResult').html('');
                        }

                        $("#emailRadioB").val(response['user_email']);
                        $("#emailLabelB").html(response['user_email']);

                        if ($("#emailRadioA").val() == response['user_email']) {
                            $("#emailRadioA").attr('checked', true);
                            $('#emailResult').html(response['user_email']);
                        } else {
                            $('#emailResult').html('');
                        }

                        $("#certRadioB").val(response['cert']);
                        $("#certLabelB").html(response['cert']);

                        if ($("#certRadioA").val() == response['cert']) {
                            $("#certRadioA").attr('checked', true);
                            $('#certResult').html(response['cert']);
                        } else {
                            $('#certResult').html('');
                        }

                        $("#gradRadioB").val(response['grad_date']);
                        $("#gradLabelB").html(response['grad_date']);

                        if ($("#gradRadioA").val() == response['grad_date']) {
                            $("#gradRadioA").attr('checked', true);
                            $('#gradResult').html(response['grad_date']);
                        } else {
                            $('#gradResult').html('');
                        }

                        var prods = response['products'];
                        if (prods != null) {
                            prods = prods.replace(/<a.*?>/g, '');
                            prods = prods.replace(/<\/a>/g, '');
                        }
                        $("#productsLabelB").html(prods);

                        var prodsResult = response['products_result'];
                        if (prodsResult != null) {
                            prodsResult = prodsResult.replace(/<a.*?>/g, '');
                            prodsResult = prodsResult.replace(/<\/a>/g, '');
                        }
                        $("#productsResult").html(prodsResult);
                        $("#snB").val(response['sn']);

                        $("#shiftsRadioB").val(response['shift_count']);
                        $("#shiftsLabelB").html(response['shift_count']);

                        if (response['test_data'] != null) {
                            var testDataString = "Finished: " + response['test_data']['finished_count'] + "<br/>" +
                                "In Progress: " + response['test_data']['inprogress_count'] + "<br/>" +
                                "Abandoned: " + response['test_data']['abandoned_count'];
                        } else {
                            var testDataString = "N/A";
                        }

                        $("#testsLabelB").html(testDataString);

                        if ($("#testsLabelA").html() == testDataString) {
                            $("#testsLabelA").attr('checked', true);
                            $('#testsResult').html(testDataString);
                        } else {
                            $('#testsResult').html('');
                        }

                        $("#throbberB").fadeOut();
                    }

                    if ($("#studentIdA").val().length > 0 && $("#studentIdB").val().length > 0) {
                        enableTransfer();
                    } else {
                        disableTransfer();
                    }
                }
            );
        }
    });

    $('input[type=radio]').change(function(event) {
        var col = this.id.substr(this.id.length - 1, this.id.length);

        if (col == 'A') {
            var labelId = this.id.substr(0, this.id.length - 6) + 'LabelA';
        } else {
            var labelId = this.id.substr(0, this.id.length - 6) + 'LabelB';
        }

        $('#' + this.id.substr(0, this.id.length - 6) + 'Result').html($('#' + labelId).html());

        switch (this.id) {
            case 'usernameRadioA':
                $('#testsRadioA').attr('checked', true);
                $('#testsResult').html($('#testsLabelA').html());
                break;
            case 'usernameRadioB':
                $('#testsRadioB').attr('checked', true);
                $('#testsResult').html($('#testsLabelB').html());
                break;
            case 'programRadioA':
                $('#shiftsRadioA').attr('checked', true);
                $('#shiftsResult').html($('#shiftsLabelA').html());
                break;
            case 'programRadioB':
                $('#shiftsRadioB').attr('checked', true);
                $('#shiftsResult').html($('#shiftsLabelB').html());
                break;
            default:
                break;
        }
    });

    //Bind this keypress function to all of the input tags
    $("input").keypress(function (evt) {
        //Deterime where our character code is coming from within the event
        var charCode = evt.charCode || evt.keyCode;
        if (charCode  == 13) { //Enter key's keycode
            return false;
        }
    });

    function disableTransfer() {
        $("#save").attr("disabled", "disabled");
        $("#save").css("opacity", "0.55");
    }

    function enableTransfer() {
        $("#save").removeAttr("disabled");
        $("#save").css("opacity", "1.00");
    }

    function resetRadioButtons() {
        $('input[type=radio]').each(function() {
            $(this).attr('checked', false);
        });
    }
    
    function resetForm(col) {
        if (col == 'A') {
            $("#usernameRadioA").val('');
            $("#usernameLabelA").html('');
            
            $("#programRadioA").val('');
            $("#programLabelA").html('');

            $("#userRadioA").val('');
            $("#userLabelA").html('');

            $("#emailRadioA").val('');
            $("#emailLabelA").html('');

            $("#certRadioA").val('');
            $("#certLabelA").html('');

            $("#gradRadioA").val('');
            $("#gradLabelA").html('');

            $("#snA").val('');
            $("#productsRadioA").val('');
            $("#productsLabelA").html('');

            $("#shiftsRadioA").val('');
            $("#shiftsLabelA").html('');

            $("#testsRadioA").val('');
            $("#testsLabelA").html('');

            $("#throbberA").fadeOut();
        } else {
            $("#usernameRadioB").val('');
            $("#usernameLabelB").html('');
            
            $("#programRadioB").val('');
            $("#programLabelB").html('');

            $("#userRadioB").val('');
            $("#userLabelB").html('');

            $("#emailRadioB").val('');
            $("#emailLabelB").html('');

            $("#certRadioB").val('');
            $("#certLabelB").html('');

            $("#gradRadioB").val('');
            $("#gradLabelB").html('');

            $("#snB").val('');
            $("#productsRadioB").val('');
            $("#productsLabelB").html('');

            $("#shiftsRadioB").val('');
            $("#shiftsLabelB").html('');

            $("#testsRadioB").val('');
            $("#testsLabelB").html('');

            $("#throbberB").fadeOut();
        }

        $('#usernameResult').html('');
        $('#programResult').html('');
        $('#userResult').html('');
        $('#emailResult').html('');
        $('#certResult').html('');
        $('#gradResult').html('');
        $('#productsResult').html('');
        $('#shiftsResult').html('');
        $('#testsResult').html('');


        resetRadioButtons();
    }
});