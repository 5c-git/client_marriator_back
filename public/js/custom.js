$(document).ready(function () {

    $('.loginAdminForm').submit(function (e) {
        e.preventDefault();
        var form = $(this).serialize();
        $.ajax({
            url: '/admin/loginAdminAjax/',
            type: "POST",
            data: form,
            success: function (data) {
                if (data = 'success') {
                    location.href = '/admin'
                } else {
                    $('.loginAdminForm').reset();
                }
            }
        });
    });

    $('.userEdit').submit(function (e) {
        e.preventDefault();
        if ($('#inputPassword').val() == $('#inputPasswordConfirm').val()) {
            var data = new FormData();

            var form_data = $(this).serializeArray();
            $.each(form_data, function (key, input) {
                data.append(input.name, input.value);
            });

            $(this).find('input[type="file"]').each(function (index, item) {
                if ($(item)[0].files[0]) {
                    data.append($(item).attr('name'), $(item)[0].files[0]);
                }
            })
            $.ajax({
                url: '/admin/users/editAjax/',
                type: "POST",
                data: data,
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status == 'success') {
                        $(document).Toasts('create', {
                            title: 'Changed',
                            body: 'Success user update',
                            autohide: true,
                            delay: 2000,
                            class: 'bg-success',
                        })
                    } else {
                        $(document).Toasts('create', {
                            title: 'Error',
                            body: 'Error change user',
                            autohide: true,
                            delay: 4000,
                            class: 'bg-danger',
                        })
                    }
                }
            });
        } else {
            $('#inputPassword').css('border', '1px solid red');
            $('#inputPasswordConfirm').css('border', '1px solid red');
        }
    })

    $('.userCreate').submit(function (e) {
        e.preventDefault();
        if ($('#inputPassword').val() == $('#inputPasswordConfirm').val()) {

            var data = new FormData();

            var form_data = $(this).serializeArray();
            $.each(form_data, function (key, input) {
                data.append(input.name, input.value);
            });

            $(this).find('input[type="file"]').each(function (index, item) {
                if ($(item)[0].files[0]) {
                    data.append($(item).attr('name'), $(item)[0].files[0]);
                }
            })

            $.ajax({
                url: '/admin/users/createAjax/',
                type: "POST",
                data: data,
                processData: false,
                contentType: false,
                success: function (data) {
                    if (data.status == 'success') {
                        location.href = data.url;
                    }
                }
            });
        } else {
            $('#inputPassword').css('border', '1px solid red');
            $('#inputPasswordConfirm').css('border', '1px solid red');
        }
    })

    $('.formCustomSubmit').submit(function (e) {
        e.preventDefault();
        var url = $(this).attr('action');

        var data = new FormData();

        var form_data = $(this).serializeArray();
        $.each(form_data, function (key, input) {
            data.append(input.name, input.value);
        });

        $(this).find('input[type="file"]').each(function (index, item) {
            if ($(item)[0].files[0]) {
                data.append($(item).attr('name'), $(item)[0].files[0]);
            }
        })

        $.ajax({
            url: url,
            type: "POST",
            data: data,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.status == 'success' && data.url) {
                    location.href = data.url;
                }
                if (data.status == 'success') {
                    $(document).Toasts('create', {
                        title: 'Success',
                        body: 'Success data set',
                        autohide: true,
                        delay: 2000,
                        class: 'bg-success',
                    })
                } else {
                    $(document).Toasts('create', {
                        title: 'Error',
                        body: 'Error data set',
                        autohide: true,
                        delay: 4000,
                        class: 'bg-danger',
                    })
                }
            },
            error: function (request, status, error) {
                $(document).Toasts('create', {
                    title: 'Error',
                    body: 'Error data set',
                    autohide: true,
                    delay: 4000,
                    class: 'bg-danger',
                })
            }
        });
    })

    $(document).on('click', '.addItemButton', function (e) {
        e.preventDefault();
        var block = $(this).parents('.addItemBlock').find('.addItem:last');
        block.after('<br>' + block[0].outerHTML);
    })


    $(document).on('click', '.addItemButtonSelect', function (e) {
        e.preventDefault();
        var count = 0
        $(this).parents('.form-group').find('.addItemSelect').each(function (index,item){
            if ($(item).find('select').hasClass("select2-hidden-accessible")) {
                $(item).find('select').select2("destroy");
            }
        })
        var block = $(this).parents('.form-group').find('.addItemSelect:last');
        var clone = block.clone();

        block.after(clone);
        $(clone).find('select').attr('id',$(clone).find('select').attr('id')+generateRandomString(5));
        $(clone).find('select').find('option').removeAttr('selected');
        $(this).parents('.form-group').find('.addItemSelect').each(function (index,item){
            $(item).find('select').attr('name','parentFields['+count+'][]');
            $(item).find('select').select2({
                theme: 'bootstrap4',
            });
            count++
        })
    })

    $(document).on('click', '.removeItemButtonSelect', function (e) {
        e.preventDefault();
        $(this).parents('.addItemSelect').remove();
    })

})
function generateRandomString(length) {
    var result = '';
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for (var i = 0; i<length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}
