var innGrid = innGrid || {};
var soldOutDates = [];
var temp = 0;
var fetchDatesStatus = '';

$(function() {
    $('#policy_modal').on('shown.bs.modal', function() {})


    var storeCCInBookingEngine = $('#store_cc_in_booking_engine').val();
    var areGatewayCredentialsFilled = $('#are_gateway_credentials_filled').val();

    if (storeCCInBookingEngine == 1 && areGatewayCredentialsFilled == 1) {
        // mask fields
        if ($.payment) {
            $('[name="cc_expiry"]').payment('formatCardExpiry');
            $('[name="cc_cvc"]').payment('formatCardCVC');

            var $form = $("#guest-information-form");

            $form.validator({
                custom: {
                    cvc: function($el) {
                        return $.payment.validateCardCVC($el.val());
                    },
                    expiry: function($el) {
                        var date = $el.payment('cardExpiryVal');
                        return $.payment.validateCardExpiry(date.month, date.year);
                    }
                },
                errors: {
                    cvc: 'Invalid cvc code',
                    expiry: 'Invalid date'
                }
            });

            $form.validator().on('submit', function(e) {

                if (typeof submitLock !== "undefined" && submitLock) {
                    return false;
                }

                var submit = false;
                submitLock = true;
                $('input[type="submit"]').attr('disabled', true);

                var $expiry = $('input[name="cc_expiry"]');
                var $cvc = $('input[name="cc_cvc"]');
                var validation_passed = !e.isDefaultPrevented();

                var date = $expiry.payment('cardExpiryVal');

                if (validation_passed) {

                    // not using stripe tokenization anymore, using tokenex below
                    //tokenizeAndSubmit($expiry, $card, $cvc);

                    innGrid.deferredCreditCardValidation = $.Deferred();

                    $.when(innGrid.deferredCreditCardValidation)
                        .then(function() {
                            // user entered valid card number
                            innGrid.deferredWaitForTokenization = $.Deferred();

                            $('#credit_card_iframe')[0].contentWindow.postMessage('tokenize', '*');

                            $.when(innGrid.deferredWaitForTokenization)
                                .then(function(data) {
                                    var token = data.token;
                                    var year = (date.year.toString().length > 2 ? date.year.toString().substring(2) : date.year).toString();
                                    var masked_card_number = 'XXXX XXXX XXXX ' + data.lastFour;
                                    $form.append("<input type='hidden' name='token' value='" + token + "' />");
                                    $form.append("<input type='hidden' name='masked_card_number' value='" + masked_card_number + "' />");
                                    $form.append("<input type='hidden' name='cc_expiry_month' value='" + ('0' + date.month).slice(-2) + "' />");
                                    $form.append("<input type='hidden' name='cc_expiry_year' value='" + year + "' />");
                                    $form.append("<input type='hidden' name='cc_cvc_encrypted' value='" + data.cc_cvc_encrypted + "' />");

                                    $form[0].submit();
                                    //submitLock = false;
                                    //$('input[type="submit"]').attr('disabled', false);
                                })
                                .fail(function(message) {
                                    alert(message);
                                    submitLock = false;
                                    $('input[type="submit"]').attr('disabled', false);
                                });
                        })
                        .fail(function(validator) {
                            var errorMsg = "\n" + l('Invalid credit card number');
                            $('.credit_card_iframe.with-errors').text(errorMsg).parent().addClass('has-error');
                            submitLock = false;
                            $('input[type="submit"]').attr('disabled', false);
                            return;
                        });
                    $('.credit_card_iframe.with-errors').text('').parent().removeClass('has-error');
                    $('#credit_card_iframe')[0].contentWindow.postMessage('validate', '*');

                    return false;
                } else {
                    submitLock = false;
                    $('input[type="submit"]').attr('disabled', false);
                }
            });
        }

        var _iframe_listener = function(event) {
            if (event.origin === 'https://htp.tokenex.com' || event.origin === 'https://test-htp.tokenex.com') {
                var message = JSON.parse(event.data);
                switch (message.event) {
                    case 'focus':
                        $('#credit_card_iframe')[0].contentWindow.postMessage('enablePrettyFormat', '*');
                        break;
                    case 'cardTypeChange':
                        if (message.data.possibleCardType) {
                            $('#card-image').attr('src', getBaseURL() + 'images/cards/' + message.data.possibleCardType + '.jpg').show();
                        }
                        break;
                    case 'validation':
                        if (!message.data.isValid) {
                            //field failed validation
                            if (message.data.validator == "invalid" && innGrid.deferredCreditCardValidation &&
                                typeof innGrid.deferredCreditCardValidation.resolve === "function") {
                                innGrid.deferredCreditCardValidation.reject('invalid');
                            } else if (message.data.validator == "required" && innGrid.deferredCreditCardValidation &&
                                typeof innGrid.deferredCreditCardValidation.resolve === "function") {
                                innGrid.deferredCreditCardValidation.reject('required');
                            }
                        } else {
                            //validation valid!
                            if (innGrid.deferredCreditCardValidation && typeof innGrid.deferredCreditCardValidation.resolve === "function") {
                                innGrid.deferredCreditCardValidation.resolve();
                            }
                        }
                        break;
                    case 'post':
                        if (!message.data.success) {
                            // use message.data.error
                        } else {
                            //get token! message.data.token                        
                            var cvc = $('[name="cc_cvc"]').val();
                            if (cvc) {
                                $.ajax({
                                    type: "POST",
                                    url: getBaseURL() + "customer/get_cc_cvc_encrypted",
                                    data: {
                                        token: message.data.token,
                                        cvc: cvc
                                    },
                                    dataType: "json",
                                    success: function(data) {
                                        if (data.success) {
                                            message.data.cc_cvc_encrypted = data.cc_cvc_encrypted;
                                            innGrid.deferredWaitForTokenization.resolve(message.data);
                                        } else
                                            innGrid.deferredWaitForTokenization.resolve(message.data);
                                    },
                                    error: function(error) {
                                        innGrid.deferredWaitForTokenization.resolve(message.data);
                                    }
                                });
                            } else {
                                innGrid.deferredWaitForTokenization.resolve(message.data);
                            }
                        }
                        break;
                }
            }
        };

        if (window.addEventListener) {
            addEventListener("message", _iframe_listener, false);
        } else {
            attachEvent("onmessage", _iframe_listener);
        }
    }

});

innGrid.checkAvailability = function(data) {
    console.log('checkAvailability done ' + data);
    var dates = data.toString();
    var d = dates.split(',');
    console.log(d);
    var date1 = new Date(d[0]);
    var date2 = new Date(d[1]);

    console.log(date1);
    console.log(date2);

    var dt1 = moment(date1).format('YYYY/DD/MM');
    var dt2 = moment(date2).format('YYYY/DD/MM');
    console.log(dt1);
    console.log(dt2);
    if (d[0] == d[1]) {
        $('#check-in-date').val(d[0]);
    }

    if (dt1 < dt2) {
        var checkInDate = date1.getFullYear() + '-' + ("0" + (date1.getMonth() + 1)).slice(-2) + '-' + ("0" + (date1.getDate())).slice(-2);
        var checkOutDate = date2.getFullYear() + '-' + ("0" + (date2.getMonth() + 1)).slice(-2) + '-' + ("0" + (date2.getDate())).slice(-2);
        console.log(checkInDate + "===" + checkOutDate);
        $('#check-in-date').val(d[0]);
        $('#check-out-date').val(d[1]);
    }

    var flag = false;
    for (var d = new Date(d[0]); d <= date2; d.setDate(d.getDate() + 1)) {
        var dateSelected = d.getFullYear() + '-' + ("0" + (d.getMonth() + 1)).slice(-2) + '-' + ("0" + (d.getDate())).slice(-2);
        console.log(dateSelected);
        fetchDatesStatus(dateSelected);
        if ($.inArray(dateSelected, innGrid.soldOutDates) != '-1') {
            console.log('clear data');
            $('#booking_calendar').DatePickerClear();
            flag = true;
        }
    }

}

innGrid.customRange = function(input) {
    var dateMin = null;

    if (input.id === "check-in-date") {
        dateMin = 0 //Hidden html element
    }

    if (input.id === "check-out-date") {
        dateMin = new Date($('#check-in-date').val());
        dateMin.setDate(dateMin.getDate() + 1);
        //getMonth method returns month between 0-11
        dateMin = dateMin.getUTCFullYear() + '-' + (dateMin.getUTCMonth() + 1) + '-' + dateMin.getUTCDate();
    }

    return {
        minDate: dateMin
    };
}

//Function getBaseURL
//returns baseURL
var getBaseURL = function() {
    var url = $('#project_url').val();
    url = url ? url : 'app.minical.io';
    if (url.substring(url.length - 1) !== "/") {
        url = url + '/';
    }
    return url;
};

$('body').on('click', '.change_language', function() {
    var selected_lang = $(this).attr('lang_data');
    $.ajax({
        type: "POST",
        url: getBaseURL() + "online_reservation/language",
        data: { language: selected_lang, return_url: 'same_page' },
        success: function(responseData) {
            console.log(responseData);
            if (responseData == 'success') {
                location.reload();
            }
        },
    });
});

// for customize popup on shift+right-click
var translation_key = '';
var translation_key_id = '';
var language_length = '';
$(document).ready(function() {
    $('body').on('contextmenu', function(event) {
        if (typeof is_current_user_admin != "undefined" && is_current_user_admin) {
            if (event.shiftKey) {
                window.event.returnValue = false;
                $("#show_popup").addClass("show");
                $('#show_popup').css({ 'top': event.pageY, 'left': event.pageX, 'position': 'absolute' });
                translation_key = $.trim(event.target.childNodes[0].data);
            }
        }
    });
});

// this is from another SO post...  
$(document).bind("click", function(event) {
    if (event.target != $('.change_translation')[0]) {
        $("#show_popup").removeClass("show");
    }
});


$('body').on('click', '.change_translation', function() {

    $("#show_popup").removeClass("show");
    if (translation_key != '') {
        $(document).openTranslationModal({});
    } else {
        alert(l("Can't detect text. Please try again!"));
    }
});

/*  Plugin for Translation Modal
 *  
 */
(function($) {


    // initialize
    $("body").append(
        $("<div/>", {
            class: "modal fade",
            id: "translation-modal",
            "tabindex": "-1",
            "role": "dialog",
            "aria-hidden": true,
            style: "z-index: 9999;"
        }).modal({
            show: false,
            backdrop: 'static'
        }).append(
            $("<div/>", {
                class: "modal-dialog"
            }).append(
                $("<div/>", {
                    class: "modal-content"
                })
            )
        )
    );

    var allLanguageData = "";
    var TranslationModal = function(options) {
        var that = this;
        $.ajax({
            type: "POST",
            url: getBaseURL() + "language_translation/get_languages",
            data: {},
            dataType: "json",
            success: function(data) {
                allLanguageData = data;
                language_length = data.length;
                that._initializeTranslationModal(allLanguageData);
            },
        });
    };

    TranslationModal.prototype = {
        _init: function() {

        },
        _initializeTranslationModal: function(allLanguageData) {
            var that = this;
            // re-initialize by deleting the existing modal
            $("#translation-modal").modal('show');
            $("#translation-modal").find(".modal-content").html("");
            this._populateTransltionModel(allLanguageData);
        },

        _populateTransltionModel: function(allLanguageData) {
            var thisCall = this;
            var new_languages_options = "";
            var current_lang_id = "";
            new_languages_options = '<option value="" >Select Language</option>';
            var language_select = $('<select/>', {
                name: 'language_id[]',
                class: 'col-sm-5 trans_language form-control',
            });

            allLanguageData.forEach(function(data) {
                var languages_options = $('<option/>', {
                    value: data.id,
                    text: data.language_name
                });

                if (data.current_language == data.language_name) {
                    languages_options.prop('selected', true);
                    current_lang_id = data.id;
                }
                language_select.append(languages_options);

                // for new appended language seletor
                new_languages_options += '<option value="' + data.id + '" >' + data.language_name + '</option>';
            });


            this._defaultGetData(current_lang_id, translation_key); // get by deafult data if same phrase is available


            var new_language_selector_div = '<div class="col-sm-12 language_selected language_selector ">' +
                '<select name="language_id[]" class="col-sm-5 trans_language form-control">' +
                new_languages_options +
                '</select>' +
                '<span class="col-sm-2 trans_span_differ">' +
                ' : ' +
                '</span>' +
                '<input name="phrase_value[]" class="col-sm-5 trans_phrase form-control">' +
                '</div>';


            $('#translation-modal').find('.modal-content').html(
                $("<div/>", {
                    class: "modal-header",
                    html: "Translation : " + translation_key
                }).append(
                    $("<div/>", {
                        class: "pull-right"
                    }).append(
                        $("<button/>", {
                            type: "button",
                            class: "add_other_lang btn btn-sm btn-primary",
                            html: "Add translation for other language"
                        }).on('click', function() {

                            if ($('.language_selected').length < language_length) {
                                $('.new_language_selector_div').append(new_language_selector_div);
                            }
                        })
                    )
                )
            ).append(
                $("<div/>", {
                    class: "modal-body form-horizontal"
                }).append(
                    $("<div/>", {
                        class: "form-group new_language_selector_div "
                    }).append(
                        $("<div/>", {
                            class: "col-sm-12 language_selected checkbox_append_div"
                        }).append(
                            language_select // language select dropdown
                        ).append(
                            $('<span/>', {
                                class: 'col-sm-2 trans_span_differ',
                                text: " : "
                            })
                        ).append(
                            $('<input/>', {
                                name: 'phrase_value[]',
                                class: 'col-sm-5 trans_phrase form-control',
                                value: translation_key
                            })

                        )
                    )
                )).append(
                $("<div/>", {
                    class: "modal-footer"
                }).append(
                    $("<button/>", {
                        type: "button",
                        class: "btn btn-success save_translation",
                        id: "save_translation",
                        html: "Save"
                    }).on('click', function() {
                        var saveDataArray = [];
                        var phraseCheckedKeysArray = [];
                        var lang_ids = document.getElementsByName('language_id[]');
                        var phrase_vals = document.getElementsByName('phrase_value[]');
                        for (var i = 0; i < lang_ids.length; i++) {
                            var json = {};
                            var lang_id = lang_ids[i];
                            var phrase_val = phrase_vals[i];
                            json[lang_id.value] = phrase_val.value;
                            saveDataArray.push(json);
                        }

                        $.each($("input[name='phrase_key']:checked"), function() {
                            phraseCheckedKeysArray.push($(this).val());
                        });
                        var thisVal = $(this);
                        thisVal.prop("disabled", true);
                        $.ajax({
                            type: "POST",
                            url: getBaseURL() + "language_translation/save_lang_translation_data",
                            data: {
                                saveDataArray: saveDataArray,
                                phraseCheckedKeysArray: phraseCheckedKeysArray,
                                current_lang_id: current_lang_id,
                                translation_key: translation_key,
                                translation_key_id: translation_key_id
                            },
                            dataType: "json",
                            success: function(data) {
                                if (data.success) {
                                    thisVal.html("Translation saved!");
                                    setTimeout(function() {
                                        $('#translation-modal').modal('hide');
                                        location.reload();
                                    }, 1000);
                                } else {
                                    thisVal.prop("disabled", false);
                                }
                            },
                        });

                    })
                ).append(
                    $("<button/>", {
                        type: "button",
                        class: "btn btn-danger",
                        'data-dismiss': "modal",
                        html: "Close"
                    })
                )
            )

            $('body').on('change', '.trans_language', function() {
                var lang_id = $(this).val();
                var thisVal = $(this);

                $.ajax({
                    type: "POST",
                    url: getBaseURL() + "language_translation/get_translation_data",
                    data: { lang_id: lang_id, current_lang_id: current_lang_id, translation_key: translation_key },
                    dataType: "json",
                    success: function(responseData) {
                        if (responseData && responseData.length > 1) {
                            thisCall._ajaxOnLanguageDropdown(responseData); // get data by ajax , when we change language in dropdown
                            $('.phrase_checkbox_div').prepend('<span class="phrase_desc">Please select the phrase to be translated.</span>');
                        } else {
                            translation_key_id = responseData.phrase_id;
                            $(thisVal).parent().find('.trans_phrase').val(responseData.phrase);
                        }
                    },
                });
            });

        },

        _defaultGetData: function(current_lang_id, translation_key) {
            var that = this;
            $.ajax({
                type: "POST",
                url: getBaseURL() + "language_translation/get_translation_data",
                data: { current_lang_id: current_lang_id, translation_key: translation_key },
                dataType: "json",
                success: function(responseData) {
                    if (responseData && responseData.length > 1) {
                        that._ajaxOnLanguageDropdown(responseData); // get data by ajax , when we change language in dropdown
                        $('.phrase_checkbox_div').prepend('<span class="phrase_desc">Please select the phrase to be translated.</span>');
                        $('#save_translation').prop('disabled', true);
                    }
                },
            });
        },

        _ajaxOnLanguageDropdown: function(responseData) {
            $('.phrase_checkbox_div').remove('');
            $('.new_language_selector_div').prepend('<div class="phrase_checkbox_div"><div/>');
            $('.phrase_checkbox_div').html('');
            for (var k = 0; k < responseData.length; k++) {
                var checkbox_div = $("<div/>", {
                    class: "col-sm-12 phrase_checkbox"
                }).append($("<input/>", {
                    type: 'checkbox',
                    name: 'phrase_key',
                    class: 'col-sm-2',
                    value: responseData[k].id
                }).on('click', function() {
                    if ($("input[name='phrase_key']:checked").length > 0) {
                        $("#save_translation").prop('disabled', false);
                    } else {
                        $("#save_translation").prop('disabled', true);
                    }
                })).append(
                    $('<span/>', {
                        html: responseData[k].phrase_keyword
                    })
                );
                $('.phrase_checkbox_div').append(checkbox_div);
            }
        }

    };
    $.fn.openTranslationModal = function(options) {
        var body = $("body");
        // preventing against multiple instantiations
        alert(l('Please add translations from admin panel!'));
        // $.data(body, 'translationModal',
        //     new TranslationModal(options)
        // );
    }

})(jQuery, window, document);

innGrid.toggleSaveTranslationBtn = function() {
    if ($("input[name='phrase_key']:checked").length > 0) {
        $("#save_translation").prop('disabled', false);
    } else {
        $("#save_translation").prop('disabled', true);
    }
}


/*** Hotel calendar START ***/
var hoteldatepicker = null; // global

innGrid.initializeHotelCalendar = function(soldOutDisabledDates) {

    var onSelectRange = function() {
        console.log('Date range selected!');
        var date_range = $('#hotel-calendar-date-range').val().split(' - ');
        $('#check-in-date').val(date_range[0]);
        $('#check-out-date').val(date_range[1]);
    };


    var input = document.getElementById('hotel-calendar-date-range');
    var options = {
        animationSpeed: '0s',
        autoClose: false,
        enableCheckout: true,
        disabledDates: soldOutDisabledDates,
        onSelectRange: onSelectRange,
        moveBothMonths: true
    };
    hoteldatepicker = new HotelDatepicker(input, options);
    hoteldatepicker.open();



};

$(document).ready(function(evt) {
    if (typeof HotelDatepicker !== "undefined") {
        fetchRoomTypeAvailability(evt, function() { hoteldatepicker.reRenderCalendar(evt); }, 'previous');
        innGrid.initializeHotelCalendar();
    }

    $('body').on('click', '.check_availability', function() {
        $(this).prop('disabled', true);

        $('.availability_process').html('').css('color', '#333');
        var date_range = $('#hotel-calendar-date-range').val().split(' - ');
        var adultCount = $('.adult_count :selected').val();
        var childrenCount = $('.children_count :selected').val();
        var startDate = date_range[0];
        var endDate = date_range[1];


        if (!startDate || !endDate) {

            $('.availability_process').css('color', 'red').html(l('Please select check-in and checkout dates.', true));
            $(this).prop('disabled', false);
            return;
        }

        var url = $(location).attr('href'),
            parts = url.split("/"),
            companyID = parts[parts.length - 1];
        var image = getBaseURL() + "images/loading.gif";
        $('.availability_process').html("<img src='" + image + "' style='width: 15px;vertical-align: text-top;' />" + '  ' + "<span>" + l('Searching Availabilty', true) + " </span>");
        $.ajax({
            type: "POST",
            url: getBaseURL() + "online_reservation/check_room_type_availability",
            data: { company_id: companyID, adult_count: adultCount, children_count: childrenCount, start_date: startDate, end_date: endDate },
            dataType: "json",
            success: function(data) {
                $('.check_availability').prop('disabled', false);
                if (data.success) {
                    window.location.href = getBaseURL() + "online_reservation/show_reservations/" + data.company_id;
                } else {
                    $('.availability_process').css('color', 'red').html(data.msg);
                }
            }
        });
    });

    $(document).on('click', '.datepicker__month-button--next', function(evt) {
        hoteldatepicker.goToNextMonth(evt);
        return fetchRoomTypeAvailability(evt, function() { hoteldatepicker.reRenderCalendar(evt); }, 'next');
    });
    $(document).on('click', '.datepicker__month-button--prev', function(evt) {
        hoteldatepicker.goToPreviousMonth(evt);
        return fetchRoomTypeAvailability(evt, function() { hoteldatepicker.reRenderCalendar(evt); }, 'previous');
    });

});

// get soldout dates in next/previous months
function fetchRoomTypeAvailability(evt, callback, btnType) {

    var first_date, last_date;
    if ($(evt.target).attr('month') == 2) {
        if (btnType == 'previous') {
            var startMonth = $('.datepicker__month-name')[0].innerHTML;
            first_date = moment(startMonth).format("YYYY-MM-01");
            last_date = moment(startMonth);
            last_date = last_date.format("YYYY-MM-") + last_date.daysInMonth();
        } else {
            var endMonth = $('.datepicker__month-name')[1].innerHTML;
            first_date = moment(endMonth).format("YYYY-MM-01");
            last_date = moment(endMonth);
            last_date = last_date.format("YYYY-MM-") + last_date.daysInMonth();
        }
    } else if ($(evt.target).attr('month') == 1) {
        if (btnType == 'previous') {
            var startMonth = $('.datepicker__month-name')[0].innerHTML;
            first_date = moment(startMonth).format("YYYY-MM-01");
            last_date = moment(startMonth);
            last_date = last_date.format("YYYY-MM-") + last_date.daysInMonth();
        } else {
            var endMonth = $('.datepicker__month-name')[0].innerHTML;
            first_date = moment(endMonth).format("YYYY-MM-01");
            last_date = moment(endMonth);
            last_date = last_date.format("YYYY-MM-") + last_date.daysInMonth();
        }
    } else {
        var nowTemp = new Date();
        var curr_date = nowTemp.getFullYear() + '-' + ("0" + (nowTemp.getMonth() + 1)).slice(-2) + '-' + ("0" + (nowTemp.getDate())).slice(-2);
        var cur_month = moment(curr_date).format("MMMM, Y");

        var first_date = moment("1 " + cur_month).startOf('month').format("Y-MM-DD");
        var last_date = moment("1 " + cur_month).add(1, 'months').endOf('month').format("Y-MM-DD");
    }

    var image = getBaseURL() + "images/loading.gif";
    $('.calendar_process').addClass('hidden');
    $('.calendar_process').html("<img src='" + image + "' style='width: 15px;vertical-align: text-top;' />" + '  ' + "<span> " + 'Loading Calendar' + "...</span>");

    if (typeof get_rooms_available_AJAX !== "undefined" && get_rooms_available_AJAX) {
        get_rooms_available_AJAX.abort();
    }

    get_rooms_available_AJAX = $.ajax({
        type: "GET",
        url: getBaseURL() + 'room/get_rooms_available_AJAX/',
        data: {
            start_date: first_date,
            end_date: last_date,
            channel_key: 'obe',
            company_id: $('input[name="company_id"]').val()
        },
        dataType: "json",
        success: function(data) {
            $.each(data, function(key, value) {
                if ($.inArray(value, soldOutDates) == -1) {
                    soldOutDates.push(value);
                }
            });
            $('.calendar_process').removeClass('hidden');
            $('.calendar_process').html('').css('color', '#333');

            hoteldatepicker.resetDisabledDates(soldOutDates);

            callback(evt);
        },
        error: function(err) {
            console.log('in error');
        }
    });
};

innGrid.updateAvailabilities = function(start_date, end_date, room_type_id, channel_id, company_id) {
    $.ajax({
        type: "POST",
        url: getBaseURL() + "channel_manager/update_availabilities",
        data: {
            start_date: start_date,
            end_date: end_date,
            room_type_id: room_type_id ? room_type_id : '',
            channel_id: channel_id ? channel_id : '',
            company_id: company_id ? company_id : null
        },
        dataType: "json",
        success: function(data) {
            console.log(data);
        }
    });
}
$(document).ready(function() {
    if (typeof isReservationSuccessPage !== "undefined" && isReservationSuccessPage) {
        var company_id = $('input[name="company_id"]').val();
        innGrid.updateAvailabilities(reservationCheckInDate, reservationCheckOutDate, null, null, company_id);
    }
});

var extraCharges = new Array();
var prevExtraIDArray = new Array();

$(document).on('click', '.extra-check', function() {
    extraID = $(this).parents('.extra-field-tr').attr('id');
    ratePlanID = $(this).parents('.extra-field-tr').data('rate_plan_id');
    extraChargeTypeID = $(this).parents('.extra-field-tr').data('charge_type_id');
    extraRate = $(this).parents('.extra-field-tr').find('.rate-span-' + extraID).text();
    extraName = $(this).parents('.extra-field-tr').find('.name-span-' + extraID).text();
    extraChargeScheme = $(this).parents('.extra-field-tr').find('.charging-scheme-span-' + extraID).text();
    extraType = $(this).parents('.extra-field-tr').find('.extra-type-span-' + extraID).text();
    isChecked = $(this).is(':checked');

    var extraRow = {};

    if (isChecked) {

        extraRow['amount'] = extraRate;
        extraRow['charge_type_id'] = extraChargeTypeID;
        extraRow['extra_name'] = extraName;
        extraRow['extra_id'] = extraID;
        extraRow['rate_plan_id'] = ratePlanID;
        extraRow['charging_scheme'] = extraChargeScheme;
        extraRow['extra_type'] = extraType;

        $('.qty-div-' + extraID + '-' + ratePlanID).removeClass('hidden');

        extraCharges.push(extraRow);
    } else {

        $(this).find('.extra_qty_' + extraID).val(1);
        extraCharges = $.grep(extraCharges, function(e) {
            return e.extra_id != extraID;
        });
        $('.qty-div-' + extraID + '-' + ratePlanID).addClass('hidden');
    }

    $('.rate_plan_extra').val(JSON.stringify(extraCharges));
    console.log(extraCharges);
});

$(document).on('click', '.qty_plus', function() {

    var extraRow = {};
    var extraID = $(this).attr('id');
    var qty = $(this).parents('.input-group').find('.extra_qty_' + extraID).val();
    $(this).parents('.input-group').find('.extra_qty_' + extraID).val(parseInt(qty) + 1);

    extraChargeTypeID = $(this).parents('.extra-field-tr').data('charge_type_id');
    ratePlanID = $(this).parents('.extra-field-tr').data('rate_plan_id');
    extraRate = $(this).parents('.extra-field-tr').find('.rate-span-' + extraID).text();
    extraName = $(this).parents('.extra-field-tr').find('.name-span-' + extraID).text();
    extraChargeScheme = $(this).parents('.extra-field-tr').find('.charging-scheme-span-' + extraID).text();
    extraType = $(this).parents('.extra-field-tr').find('.extra-type-span-' + extraID).text();

    extraRow['amount'] = extraRate;
    extraRow['charge_type_id'] = extraChargeTypeID;
    extraRow['extra_name'] = extraName;
    extraRow['extra_id'] = extraID;
    extraRow['rate_plan_id'] = ratePlanID;
    extraRow['charging_scheme'] = extraChargeScheme;
    extraRow['extra_type'] = extraType;

    extraCharges.push(extraRow);
    $('.rate_plan_extra').val(JSON.stringify(extraCharges));
    console.log(extraCharges);
});

$(document).on('click', '.qty_minus', function() {

    var extraID = $(this).attr('id');
    var qty = $(this).parents('.input-group').find('.extra_qty_' + extraID).val();
    if (qty > 1) {
        $(this).parents('.input-group').find('.extra_qty_' + extraID).val(parseInt(qty) - 1);

        $.each(extraCharges, function(key, value) {
            if (value.extra_id == extraID) {
                extraCharges.splice(key, 1);
                return false;
            }
        });
        $('.rate_plan_extra').val(JSON.stringify(extraCharges));
        console.log(extraCharges);
    }
});

$("#booking_engine_form").click(function() {
    var check = false;
    var fields = $("#guest-information-form")
        .find("input")
        .filter('[required]:visible')
        .serializeArray();
    $.each(fields, function(i, field) {
        if (!field.value) {
            alert(field.name + " is required");
        } else {
            check = true;
        }
    });
    if (check) {
        var event = new CustomEvent('post.submit_user');
        document.dispatchEvent(event);
    }

});