// initialize scope
if (scope.info == null || scope.info instanceof Array) {
    scope.info = {};
}
$('#scope').val(JSON.stringify(scope));

// repair form for autofill function of some browsers
$.each($(':checkbox'), function() {
    if (this.checked) {
        this.disabled = false;
        $(this).next().removeAttr('disabled');
        $('#'+this.id.replace('r_','')).prop('checked', true);
        $('#'+this.id.replace('r_','i_')).prop('disabled', false);
    }
});

// repair info inputs for autofill function of some browsers
$.each($('.scope_info'), function() {
    if (this.value != '') {
        this.disabled = false;
        $('#'+this.id.replace('i_','')).prop('checked', true);
        $('#'+this.id.replace('i_','r_')).prop('disabled', false);
    }
});

// updates the scope value for scope_name
function updateScope(scope_name) {
    var checkbox = $('#'+scope_name);
    var r_checkbox = $('#r_'+scope_name);
    var input = $('#i_'+scope_name);
    if (checkbox.is(':checked')) {
        r_checkbox.prop('disabled', false).prop('checked', true);
        r_checkbox.next().removeAttr('disabled');
        input.prop('disabled', false);
        addScopeAvailable(scope_name);
        addScopeRequired(scope_name);
    } else {
        r_checkbox.prop('checked', false).prop('disabled', true);
        r_checkbox.next().attr('disabled', true);
        input.prop('disabled', true).val('');
        delete scope.info[scope_name];
        removeScopeAvailable(scope_name);
        removeScopeRequired(scope_name);
    }
    $('#scope').val(JSON.stringify(scope));
}

// updates the required scope value for scope_name
function updateScopeRequired(scope_name) {
    var checkbox = $('#r_'+scope_name);
    if (checkbox.is(':checked')) {
        addScopeRequired(scope_name);
    } else {
        removeScopeRequired(scope_name);
    }
    $('#scope').val(JSON.stringify(scope));
}

// updates the scope info for scope_name
function updateScopeInfo(scope_name) {
    var input = $('#i_'+scope_name);
    if (input.val().trim() == '') {
        delete scope.info[scope_name];
    } else {
        scope.info[scope_name] = input.val();
    }
    $('#scope').val(JSON.stringify(scope));
}

// adds a scope to scope available
function addScopeAvailable(scope_name) {
    if (scope.available.indexOf(scope_name) < 0) {
        scope.available.push(scope_name);
    }
}

// adds a scope to scope required
function addScopeRequired(scope_name) {
    if (scope.required.indexOf(scope_name) < 0) {
        scope.required.push(scope_name);
    }
}

// removes a scope to scope available
function removeScopeAvailable(scope_name) {
    while (scope.available.indexOf(scope_name) != -1) {
        i = scope.required.indexOf(scope_name);
        scope.available.splice(i, 1);
    }
}

// removes a scope to scope required
function removeScopeRequired(scope_name) {
    while (scope.required.indexOf(scope_name) != -1) {
        i = scope.required.indexOf(scope_name);
        scope.required.splice(i, 1);
    }
}