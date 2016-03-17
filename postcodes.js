function init_postcodeBlock(blockId, address_table_id, zipcodes) {
    var city_field_td = cj(address_table_id + ' #address_'+blockId+'_city').parent();
    var postalcode_field_td = cj(address_table_id + ' #address_'+blockId+'_postal_code').parent();
    //postalcode_field_td.detach();
    city_field_td.parent().prepend(postalcode_field_td);
    var first_row = city_field_td.parent().parent().parent().parent().parent();
    first_row.before(zipcodes_getRowHtml(blockId, zipcodes));
    cj('#zipcode_lookup_'+blockId).crmSelect2();
    zipcodes_addOnChange(blockId);
}

function zipcodes_getRowHtml(blockId, zipcodes) {
    var html = '<tr class="zipcodes_input_row"><td>';
    html = html + 'Postcode lookup<br>';
    html = html + '<select type="text" class="crm-select2 crm-form-select" id="zipcode_lookup_'+blockId+'" value="">';
    html = html + '<option value=""> - Loopup a postcode - </option>';
    for(var i = 0; i < zipcodes.length; i++) {
        html = html + '<option value="'+zipcodes[i]+'">'+zipcodes[i]+'</option>';
    }
    html = html + '</select>';
    html = html + '</td><td></td><td></td></tr>';
    return html;
}

function zipcodes_addOnChange(blockId) {
    cj('#zipcode_lookup_'+blockId).change(function (e) {
        zipcodes_fill(blockId);
    });
}

function zipcodes_fill(blockId) {
    var value = cj('#zipcode_lookup_'+blockId).val();
    var values = value.split(' - ');
    if(values[0] && values[1]) {
        cj('#address_' + blockId + '_postal_code').val(values[0]);
        cj('#address_' + blockId + '_city').val(values[1]);
    }
}

/**
 * 
 * remove all lookup widgets
 */
function zipcodes_reset() {
    cj('.zipcodes_input_row').remove();
}

/*
 * The code sniplet below makes sure that whenever a new address block is added
 * the reset functions are run
 */
cj(function() {
    cj.each(['show', 'hide'], function (i, ev) {
        var el = cj.fn[ev];
        cj.fn[ev] = function () {
          this.trigger(ev);
          return el.apply(this, arguments);
        };
      });
});
