Extension was moved to: https://lab.civicrm.org/partners/civicoop/belgianpostcodes

# Postcode lookup voor Belgium.

This extension adds functionality to lookup city and province after entering a postcode.

# Installation

Copy the files to your extension directory afterwards you can install the extension through the CiviCRM extension manager.

# Techinical information

On the inline address edit form and on the contact edit form a piece of javascript is added. 
The javascript adds a new input field on the form. This field is an autocomplete field which autocomplete the postcode. 
After a postcode is selected or autocompleted the fields zipcode and city are filled. 

The autocomplete calls *civicrm/ajax/zipcodes/autocomplete* which returns a list of matching postcodes.

Upopn installation the Belgium postcode database is inserted in the table civicrm_zipcodes
