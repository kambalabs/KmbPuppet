$(window).load(function () {

    var parser = document.createElement('a');
    parser.href = document.URL;
    var prefixUriMatch = parser.pathname.match(/(\/env\/[0-9]+)/);
    var prefixUri = prefixUriMatch ? prefixUriMatch[1] : '';

    $('#current-environment').change(function () {
        var newUrl = '';
        if (prefixUri) {
            var re = new RegExp(prefixUri)
            newUrl += parser.pathname.replace(re, '/env/' + $('#current-environment').val());
        } else {
            newUrl += '/env/' + $('#current-environment').val() + parser.pathname;
        }
        newUrl += parser.search + parser.hash;
        location.href = newUrl;
    });

    $('#groups > tbody').sortable({
        update: function (event, ui) {
            var data = $(this).sortable('serialize');
            $.post(prefixUri + '/puppet/groups/update', data).done(function (data) {
                if (data.error) {
                    location.reload(true);
                }
            });
        }
    }).disableSelection();

    $('.values-form').on('reset', function() {
        $(this).find('.new-element .form-control').prop('disabled', true);
    });

    $('.add-parameter').click(function(e) {
        var newElement = $(this).closest('.panel-heading').siblings('.panel-body').children('.tree').find('> ul > .new-element');
        newElement.show('fast');
        var formControl = newElement.find('.form-control');
        formControl.prop('disabled', false);
        formControl.focus();
        e.stopPropagation();
        return false;
    });

    $('#reports').dataTable($.extend({}, DATATABLES_NPROGRESS_DEFAULT_SETTINGS, {
        "serverSide": true,
        "ajax": {
            "url": window.location,
            "complete": function() {
                NProgress.done();
            },
            "error": function (cause) {
                console.log('Could not get reports list : ' + cause.statusText);
                $('#reports_processing').hide();
                NProgress.done();
            }
        }
    }));

    var confirmRemoveValue = $('#confirm-remove-value');
    confirmRemoveValue.on('show.bs.modal', function (e) {
        var element = $(e.relatedTarget);
        $('.confirm-text').html(element.attr('data-confirm-text'));
        $(this).find('.danger').click(function() {
            var treeLevel = element.closest('.tree-level');
            var form = treeLevel.closest('form.values-form');
            treeLevel.remove();
            form.submit();
        });
    });
    confirmRemoveValue.on('hide.bs.modal', function () {
        $(this).find('.danger').unbind('click');
    });

    $('#release').on('show.bs.modal', function (e) {
        $(this).find('form').attr('action', $(e.relatedTarget).data('href'));
        $(this).find('.release-warning').html($(e.relatedTarget).data('release-warning'));
    });

    $('#import').on('show.bs.modal', function (e) {
        $(this).find('form').attr('action', $(e.relatedTarget).data('href'));
        $(this).find('.release-warning').html($(e.relatedTarget).data('release-warning'));
    });

    $('#update-environment').on('show.bs.modal', function (e) {
        $(this).find('form').attr('action', $(e.relatedTarget).data('href'));
        $('#update-environment-name').val($(e.relatedTarget).attr('data-name'));
        $('#current-environment-name').html($(e.relatedTarget).attr('data-full-name'));
        $('#update-environment-default').prop('checked', $(e.relatedTarget).attr('data-default') == 1);
        var parentSelect = $('#update-parent-select');
        parentSelect.val($(e.relatedTarget).attr('data-parent-id'));
        parentSelect.trigger('chosen:updated');
    });

    function refreshUserSelect(id) {
        $.ajax({
            url: prefixUri + "/puppet/environment/" + id + "/available-users",
            dataType: "json"
        }).done(function (data) {
            $('#environment-user-select').html('');
            $(data.users).each(function () {
                $('#environment-user-select').append('<option value="' + this.id + '">' + this.name + ' - ' + this.login + '</option>');
            });
            $("#environment-user-select").trigger("chosen:updated");
        });
    }

    var environmentUsers = $('#environment-users').DataTable($.extend({}, DATATABLES_DEFAULT_SETTINGS, {
        "lengthChange": false,
        "displayLength": 5
    }));

    var manageEnvironmentUsers = $('#manage-environment-users');
    manageEnvironmentUsers.on('show.bs.modal', function (e) {
        $(this).find('form').attr('action', $(e.relatedTarget).data('href'));
        $('#current-environment-name').html($(e.relatedTarget).attr('data-full-name'));
        var id = $(e.relatedTarget).attr('data-id');

        $('#add-users').attr('data-environment-id', id);
        environmentUsers.ajax.url(prefixUri + '/puppet/environment/' + id + '/users').load();
        refreshUserSelect(id);
    });

    $('#add-users').click(function () {
        var id = $(this).attr('data-environment-id');
        var users = [];
        $("#environment-user-select option:selected").each(function () {
            users.push($(this).attr('value'));
        });
        $.ajax({
            type: "POST",
            url: prefixUri + "/puppet/environment/" + id + "/add-users",
            data: {'users': users}
        }).done(function () {
            environmentUsers.ajax.reload();
            refreshUserSelect(id);
        });
        return false;
    });

    manageEnvironmentUsers.on('click', '.remove-user', function () {
        var id = $(this).attr('data-environment-id');
        $.ajax({
            url: prefixUri + "/puppet/environment/" + id + "/user/" + $(this).attr('data-user-id') + "/remove"
        }).done(function () {
            environmentUsers.ajax.reload();
            refreshUserSelect(id);
        });
        return false;
    });

    $('#create-environment').on('show.bs.modal', function (e) {
        var parentSelect = $('#create-parent-select');
        parentSelect.val($(e.relatedTarget).attr('data-parent-id'));
        parentSelect.trigger('chosen:updated');
    });

    var groupServers = $('#group-servers');
    groupServers.on('show.bs.modal', function (e) {
        $('#servers-filter').val('');
        var servers = $(this).find('.list-group');
        servers.empty();
        var url = $(e.relatedTarget).data('href');
        if ($(e.relatedTarget).data('current')) {
            url = url + '?include=' + $('#group-include-pattern').val() + '&exclude=' + $('#group-exclude-pattern').val();
        }
        $.getJSON(url, function (data) {
            $('#modal-servers-count').html(data.servers.length);
            for (var index in data.servers) {
                servers.append('<a href="#" class="list-group-item">' + data.servers[index] + '</a>');
            }
        });
    });

    $('#servers-filter').keyup(function () {
        var rex = new RegExp($(this).val(), 'i');
        var item = $('.list-group-item');
        item.hide();
        item.filter(function () {
            return rex.test($(this).text());
        }).show();
    });

    $('.class-name').click(function () {
        $('tr.selected-class').removeClass('selected-class');
        $(this).closest('tr').addClass('selected-class');

        var activeParameters = $('.class-parameters.active');
        if (activeParameters.length) {
            activeParameters.hide();
            activeParameters.removeClass('active');
        } else {
            $('#group-description').hide();
        }
        var parameters = $('.class-parameters[data-class-id=' + $(this).attr('data-class-id') + ']');
        parameters.show();
        parameters.addClass('active');
        return false;
    });

    $('.class-parameters button.close').click(function () {
        $(this).closest('.class-parameters').removeClass('active').hide();
        $('#group-description').show();
    });
});
