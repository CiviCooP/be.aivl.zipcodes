{*
 * File for the address block on the contact edit page
 *}

{literal}
<script type="text/javascript">

function init_postcodenl_contact_form() {
    var zipcodes = {/literal}{$zipcodes}{literal};
    var addressBlocks = cj('.crm-edit-address-block');
    addressBlocks.each(function(index, item) {
        var block = cj(item).attr('id').replace('Address_Block_', '');
        console.log(block);
        init_postcodeBlock(block, 'table#address_table_'+block, zipcodes);
    });
}

cj(function() {
    zipcodes_reset();
    init_postcodenl_contact_form();
});

</script>
{/literal}