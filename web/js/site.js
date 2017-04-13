/**
 * Created by MadMax on 13.12.2016.
 */
$(function(){
    $(".model-item").on('click', function (event) {
        $('#package-model').val($(this).text());
        console.log($('#package-model').val());
        $(".model-item.active").toggleClass("active");
        $(this).toggleClass("active");
        event.preventDefault();
    });

    $("#package_type").on('change', function(){
        var dataUrl = $("#package_deliverymans").attr('data-url');
        $.ajax({
            url: dataUrl+"&"+"packageType="+$(this).val(),
            success: function (data) {
                $("#package_deliverymans").empty();
                var deliverymans = $.parseJSON(data);
                if (deliverymans.length != 0) {
                    $("#package_deliverymans").append(
                        $("<option></option>").val("").html("Выберете курьера")
                    )
                    $.each(deliverymans, function (index, element) {
                        $("#package_deliverymans").append(
                            $("<option></option>").val(index).html(element)
                        )
                    });
                }
                else {
                    $("#package_deliverymans").append(
                        $("<option></option>").val("").html("Курьеры не найдены")
                    )
                }
            }
        });
    });

    $('body')
        .on('focus', '[contenteditable]',
            function(){
                document.execCommand('selectAll', false, null);
                $(this).data('before', $(this).html());
                return $(this);
            }
        )
        .on('blur', '[contenteditable]',
            function(){
                if ($(this).data('before') !== $(this).html()) {
                    $(this).data('before', $(this).html());
                    $(this).trigger('change');
                }
                return $(this);
            }
        )
        .on('keydown', '[contenteditable]',
            function(event){
                if (event.keyCode === 13)
                    event.preventDefault();
                return $(this);
            }
        )
        .on('keyup', '[contenteditable]',
            function(event) {
                if (event.keyCode === 13) {
                    if ($(this).data('before') !== $(this).html()) {
                        $(this).data('before', $(this).html());
                        $(this).trigger('change');
                    }
                }
                return $(this);
            }
        )
})
