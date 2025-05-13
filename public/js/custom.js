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
            var nameField = 'parentFields';
            if($(item).find('select').attr('data-name')){
                nameField = $(item).find('select').attr('data-name');
            }
            $(item).find('select').attr('name',nameField+'['+count+'][]');
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








    $('.importFormDirectory').submit(function (e) {
        e.preventDefault();

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
        $('.linkJs').val('');
        $('.typeJs').val('');
        $('.jsDataImportBody').html('');
        $('.jsDataImport').hide();
        $('.loaderForJs').show();
        $('.importSaveFormDirectory').hide();

        $.ajax({
            url: '/admin/importDirectory/import',
            type: "POST",
            data: data,
            async: true,
            processData: false,
            contentType: false,
            success: function (data) {

                if(data.status == 'success') {
                    $('.jsDataImport').show();
                    $('.importSaveFormDirectory').show();
                    var iter = 1;
                    for (var key in data.table) {
                        var valNew = '';
                        if (data.table[key] && data.table[key].new && data.table[key].new.id && (data.table[key].new.name || data.table[key].new.code)) {
                            valNew = '                    Uuid - ' + data.table[key].new.id + '\n' +
                                '                    <br>\n';
                            if (data.table[key].new.name) {
                                valNew = valNew + '                    Name - ' + data.table[key].new.name + '\n'
                            }
                            if (!data.table[key].new.name && data.table[key].new.code) {
                                valNew = valNew + '                    Name - ' + data.table[key].new.code + '\n'
                            }
                        }
                        ;
                        var valOld = '';
                        if (data.table[key] && data.table[key].old && data.table[key].old.id && data.table[key].old.name) {
                            valOld = '                    Uuid - ' + data.table[key].old.id + '\n' +
                                '                    <br>\n' +
                                '                    Name - ' + data.table[key].old.name + '\n'
                        }
                        ;


                        $('.jsDataImportBody').append('<tr>\n' +
                            '                <th scope="row">' + iter + '</th>\n' +
                            '                <td>\n' +
                            valNew +
                            '                </td>\n' +
                            '                <td>\n' +
                            valOld +
                            '                </td>\n' +
                            '\n' +
                            '            </tr>')

                        iter++;
                    }
                    $('.linkJs').val(data.link);
                    $('.typeJs').val(data.type);
                    $('.loaderForJs').hide();
                }else{
                    location.reload();
                }
            }
        });
    })


    $('.importSaveFormDirectory').submit(function (e) {
        e.preventDefault();
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
            url: '/admin/importDirectory/importSave',
            type: "POST",
            data: data,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.status == 'success') {
                    $(document).Toasts('create', {
                        title: 'Success',
                        body: 'Success data save',
                        autohide: true,
                        delay: 2000,
                        class: 'bg-success',
                    })
                } else {
                    $(document).Toasts('create', {
                        title: 'Error',
                        body: 'Error data save',
                        autohide: true,
                        delay: 4000,
                        class: 'bg-danger',
                    })
                }
            },
            error: function (request, status, error) {
                $(document).Toasts('create', {
                    title: 'Error',
                    body: 'Error data save',
                    autohide: true,
                    delay: 4000,
                    class: 'bg-danger',
                })
            }
        });
    })


    $('.settingEdit').submit(function (e) {
        e.preventDefault();
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
            url: '/admin/settings/saveAjax',
            type: "POST",
            data: data,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.status == 'success') {
                    $(document).Toasts('create', {
                        title: 'Success',
                        body: 'Success settings save',
                        autohide: true,
                        delay: 2000,
                        class: 'bg-success',
                    })
                    setTimeout(function(){location.reload();},2000);
                } else {
                    $(document).Toasts('create', {
                        title: 'Error',
                        body: 'Error settings save',
                        autohide: true,
                        delay: 4000,
                        class: 'bg-danger',
                    })
                }
            },
            error: function (request, status, error) {
                $(document).Toasts('create', {
                    title: 'Error',
                    body: 'Error data save',
                    autohide: true,
                    delay: 4000,
                    class: 'bg-danger',
                })
                setTimeout(function(){location.reload();},2000);
            }
        });
    })

    $('.certificatesEdit').submit(function (e) {
        e.preventDefault();
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
            url: '/admin/certificates/saveAjax',
            type: "POST",
            data: data,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.status == 'success') {
                    $(document).Toasts('create', {
                        title: 'Success',
                        body: 'Success settings save',
                        autohide: true,
                        delay: 2000,
                        class: 'bg-success',
                    })
                    setTimeout(function(){location.reload();},2000);
                } else {
                    $(document).Toasts('create', {
                        title: 'Error',
                        body: 'Error settings save',
                        autohide: true,
                        delay: 4000,
                        class: 'bg-danger',
                    })
                }
            },
            error: function (request, status, error) {
                $(document).Toasts('create', {
                    title: 'Error',
                    body: 'Error data save',
                    autohide: true,
                    delay: 4000,
                    class: 'bg-danger',
                })
                setTimeout(function(){location.reload();},2000);
            }
        });
    })

    $('.qrCodeCreate').submit(function (e) {
        e.preventDefault();
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
            url: '/admin/qr_code/createUserLink',
            type: "POST",
            data: data,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.status == 'success') {
                    $('.linkBlockJs').show();
                    $('.linkJs').html('/register/?hash='+data.data.hash);

                    //setTimeout(function(){location.reload();},2000);
                } else {
                    $(document).Toasts('create', {
                        title: 'Error',
                        body: 'Пользователи уже существует или данные устарели ',
                        autohide: true,
                        delay: 4000,
                        class: 'bg-danger',
                    })
                }
            },
            error: function (request, status, error) {
                $(document).Toasts('create', {
                    title: 'Error',
                    body: 'Error data save',
                    autohide: true,
                    delay: 4000,
                    class: 'bg-danger',
                })
                //setTimeout(function(){location.reload();},2000);
            }
        });
    })


    $('.rolesCustomJS').change(function (e) {
        e.preventDefault();
        var data = new FormData();

        var obj = $(this);
        var form_data = $(this).parents('form').serializeArray();
        $.each(form_data, function (key, input) {
            data.append(input.name, input.value);
        });

        $.ajax({
            url: '/admin/qr_code/getBindings',
            type: "POST",
            data: data,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.status == 'success') {
                    var option = '';
                    $('.addSelectJs').html('');
                    data.data.forEach(function (items,indexs){

                        option += '<div class="form-group row" style="display: block">  ' +
                            '<label for="select" class="col-sm-6 col-form-label">Справочник - '+data.name[indexs]+'</label> ' +
                            '<select class="rolesCustomJSBindings custom-select" name="'+data.userFields[indexs]+'[]" required >';
                        items.forEach(function (item,index){
                            option += '<option value="'+item.id+'">'+item.name+'</option>';
                        })
                        option += '</select>' + '</div>'
                    })

                    $('.addSelectJs').append(option);

                } else {
                    $(document).Toasts('create', {
                        title: 'Error',
                        body: 'Error settings save',
                        autohide: true,
                        delay: 4000,
                        class: 'bg-danger',
                    })
                }
            },
            error: function (request, status, error) {
                $(document).Toasts('create', {
                    title: 'Error',
                    body: 'Error data save',
                    autohide: true,
                    delay: 4000,
                    class: 'bg-danger',
                })
                //setTimeout(function(){location.reload();},2000);
            }
        });
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
