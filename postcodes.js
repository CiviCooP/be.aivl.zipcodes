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
    zipcodes_addOnChange(blockId, zipcodes);

  cj('#address_' + blockId + '_country_id').change(function(e) {
    var housenr_td = cj('#address_'+blockId+'_street_number').parent();
    var street_name_td = cj('#address_'+blockId+'_street_name').parent();
    if ((cj('#address_' + blockId + '_country_id').val()) == 1020) {
      if (typeof processAddressFields == 'function' && cj('#addressElements_'+blockId).length > 0) {
        processAddressFields('addressElements', blockId, 1);
        cj('#addressElements_' + blockId).show();
        cj('#streetAddress_' + blockId).hide();
      }
      cj(street_name_td).after(cj(housenr_td));
      cj('#zipcodes_input_row_'+blockId).removeClass('hiddenElement');
    } else {
      cj(housenr_td).after(cj(street_name_td));
      cj('#zipcodes_input_row_'+blockId).addClass('hiddenElement');
    }
  });

  cj('#address_' + blockId + '_country_id').trigger('change');
}

function zipcodes_getRowHtml(blockId, zipcodes) {
    var html = '<tr class="zipcodes_input_row" id="zipcodes_input_row_'+blockId+'"><td>';
    html = html + 'Postcode lookup<br>';
    html = html + '<select type="text" class="crm-form-select" id="zipcode_lookup_'+blockId+'" style="width: 100%;" value="">';
    html = html + '<option value=""> - Lookup a postcode - </option>';
    for(var zipcode in zipcodes) {
        html = html + '<option value="'+zipcodes[zipcode].zip+'">'+zipcodes[zipcode].zip +  ' - ' + zipcodes[zipcode].city+'</option>';
    }
    html = html + '</select>';
    html = html + '</td><td></td><td></td></tr>';
    return html;
}

function zipcodes_addOnChange(blockId, zipcodes) {
    cj('#zipcode_lookup_'+blockId).change(function (e) {
        zipcodes_fill(blockId, zipcodes);
    });
}

function zipcodes_fill(blockId, zipcodes) {
  var zipcode = cj('#zipcode_lookup_'+blockId).val();
  cj('#address_' + blockId + '_postal_code').val(zipcodes[zipcode].zip);
  cj('#address_' + blockId + '_city').val(zipcodes[zipcode].city);
  cj('#address_' + blockId + '_state_province_id').val(zipcodes[zipcode].state).trigger('change');
}

/**
 * 
 * remove all lookup widgets
 */
function zipcodes_reset() {
    cj('.zipcodes_input_row').remove();
}