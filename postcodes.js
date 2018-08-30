function init_postcodeBlock(blockId, address_table_id, zipcodes) {
    var city_field_td = cj(address_table_id + ' #address_'+blockId+'_city').parent();
    var postalcode_field_td = cj(address_table_id + ' #address_'+blockId+'_postal_code').parent();
    //postalcode_field_td.detach();
    city_field_td.parent().prepend(postalcode_field_td);
    var first_row = city_field_td.parent().parent().parent().parent().parent();
    first_row.before(zipcodes_getRowHtml(blockId, zipcodes));

    cj('#zipcode_lookup_'+blockId).select2({
        matcher: function(term, text) {
            // The text parameter looks like 2000 - Antewerpen.
            // So try to split it into a zipcode and a city part.
            // Split the zipcode text into a zipcode part
            // splitted[0] and a city part splitted[1].
            var splitted = text.split(" - ");
            if ((splitted[0].toUpperCase().indexOf(term.toUpperCase())==0) || (splitted[1].toUpperCase().indexOf(term.toUpperCase())==0)) {
                return true;
            }
            return false;
        }
    });
    zipcodes_addOnChange(blockId);

  cj('#address_' + blockId + '_country_id').change(function(e) {
    console.log(cj('#address_' + blockId + '_country_id').val());
    if ((cj('#address_' + blockId + '_country_id').val()) == 1020) {
      cj('#zipcodes_input_row_'+blockId).removeClass('hiddenElement');
    } else {
      cj('#zipcodes_input_row_'+blockId).addClass('hiddenElement');
    }
  });
}

function zipcodes_getRowHtml(blockId, zipcodes) {
    var html = '<tr class="zipcodes_input_row" id="zipcodes_input_row_'+blockId+'"><td>';
    html = html + 'Postcode lookup<br>';
    html = html + '<select type="text" class="crm-form-select" id="zipcode_lookup_'+blockId+'" style="width: 100%;" value="">';
    html = html + '<option value=""> - Lookup a postcode - </option>';
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