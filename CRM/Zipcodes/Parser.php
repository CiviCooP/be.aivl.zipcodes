<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Zipcodes_Parser {

  protected $street_units = array();

  /**
   * @var CRM_Zipcodes_Parser
   */
  protected static $_singleton;

  /**
   * @return \CRM_Zipcodes_Parser
   */
  public static function singleton() {
    if (!isset(self::$_singleton)) {
      self::$_singleton = new CRM_Zipcodes_Parser();
    }
    return self::$_singleton;
  }

  /**
   * When the country of an address is belgium set the right street_address in the right formatting.
   *
   * @param \CRM_Core_Form $form
   */
  public static function setStreetAddressOnForm(CRM_Core_Form $form) {
    if (!$form instanceof CRM_Contact_Form_Inline_Address && !$form instanceof CRM_Contact_Form_Contact) {
      return;
    }
    // Set the all address Field Values
    $values = $form->getVar('_values');
    $allAddressFields = $form->get_template_vars('allAddressFieldValues');
    $allAddressFields = json_decode($allAddressFields, TRUE);
    foreach($values['address'] as $locBlockNo => $address) {
      if ($address['country_id'] == 1020) {
        if ($allAddressFields && isset($allAddressFields['street_address_' . $locBlockNo]) && isset($address['street_address'])) {
          $allAddressFields['street_address_' . $locBlockNo] = $address['street_address'];
        }
        $defaults = array();
        $defaults['address'][$locBlockNo]['street_address'] = $address['street_address'];
        $form->setDefaults($defaults);
      }
    }
    if ($allAddressFields) {
      $form->assign('allAddressFieldValues', json_encode($allAddressFields));
    }
  }

  /**
   * The build form hook retrieves the submitted Street unit which we could use later
   * to glue the address together. CiviCRM somehow adds the street unit to the street address and makes the street unit field empty.
   *
   * @param \CRM_Core_Form $form
   *
   */
  public static function buildAddressForm(CRM_Core_Form $form) {
    if (!$form instanceof CRM_Contact_Form_Inline_Address && !$form instanceof CRM_Contact_Form_Contact) {
      return;
    }

    $parser = self::singleton();
    $parser->street_units = array();

    $submittedValues = $form->exportValues();
    foreach($submittedValues['address'] as $locBlockNo => $address) {
      if (isset($address['country_id']) && $address['country_id'] == 1020) {
        $street_unit = $address['street_unit'];
        $parser->street_units[] = $street_unit;
      }
    }

  }

  public static function pre($op, $objectName, $id, &$params) {
    if (($op == 'edit' || $op == 'create') && $objectName == 'Address') {
      $parser = self::singleton();
      $parser->parseAddress($params);
    }
  }

  /**
   * Parse the address for Belgium addresses
   * Glues together the different parts of an address or explode
   * the the street_adress into the different parts
   *
   * Returns an array with the changed parts of the address
   *
   * @param array $params
   * @return array
   */
  protected function parseAddress(&$params) {
    if (isset($params['country_id']) && $params['country_id'] == 1020) {
      // Fix street unit
      if (empty($params['street_unit']) && is_array($this->street_units)) {
        $street_unit = array_shift($this->street_units);
        if (!empty($street_unit)) {
          $params['street_unit'] = $street_unit;
          // Check if street unit is part of the street_name and if so remove it
          $matches = [];
          if (preg_match('/^(.*) (' . $street_unit . ')$/', $params['street_name'], $matches)) {
            $params['street_name'] = substr($params['street_name'], 0, (-1 * strlen($street_unit) - 1));
          }
        }
      }
      /*
       * glue if street_name <> empty and street_number <> empty, split otherwise if street_address not empty
       */
      if (!empty($params['street_address']) && (empty($params['street_name']) || empty($params['street_number']))) {
        $streetParts = $this->splitStreetAddressBE($params['street_address']);
        $params['street_name'] = $streetParts['street_name'];
        if (isset($streetParts['street_number']) && !empty($streetParts['street_number'])) {
          $params['street_number'] = $streetParts['street_number'];
        }
        if (isset($streetParts['street_unit']) && !empty($streetParts['street_unit'])) {
          $params['street_unit'] = $streetParts['street_unit'];
        }
        $params['street_address'] = $this->glueStreetAddressBE($streetParts);
      } elseif (!empty($params['street_name']) && !empty($params['street_number'])) {
        $params['street_address'] = $this->glueStreetAddressBE($params);
      }
    }
  }

  /**
   * function to glue street address from components in params
   * @param array, expected street_name, street_number and possibly street_unit
   * @return $parsedStreetAddressNl
   */
  protected function glueStreetAddressBE($params) {
    $parsedStreetAddressBE = "";
    /*
     * do nothing if no street_name in params
     */
    if (isset($params['street_name'])) {
      $parsedStreetAddressBE = trim($params['street_name']);
      if (isset($params['street_number']) && !empty($params['street_number'])) {
        $parsedStreetAddressBE .= " " . trim($params['street_number']);
      }
      if (isset($params['street_unit']) && !empty($params['street_unit'])) {
        $parsedStreetAddressBE .= " " . trim($params['street_unit']);
      }
    }
    return $parsedStreetAddressBE;
  }

  /**
   * function to split street_address into components according to Belgium formats.
   * @param streetAddress, containing parsed address in possible sequence
   *        street_number, street_name, street_unit
   *        street_name, street_number, street_unit
   * @return $result, array holding street_number, street_name and street_unit
   */
  protected function splitStreetAddressBE($streetAddress) {
    $result = array();
    /*
     * do nothing if streetAddress is empty
     */
    if (!empty($streetAddress)) {
      /*
       * split into parts separated by spaces
       */
      $addressParts = explode(" ", $streetAddress);
      $foundStreetNumber = false;
      $streetName = null;
      $streetNumber = null;
      $streetUnit = null;
      foreach ($addressParts as $partKey => $addressPart) {
        /*
         * if the part is numeric, there are several possibilities:
         * - if the partKey is 0 so it is the first element, it is
         *   assumed it is part of the street_name to cater for
         *   situation like 2e Wormenseweg
         * - if not the first part and there is no street_number yet (foundStreetNumber
         *   is false), it is assumed this numeric part contains the street_number
         * - if not the first part but we already have a street_number (foundStreetNumber
         *   is true) it is assumed this is part of the street_unit
         */
        if (is_numeric($addressPart)) {
          if ($foundStreetNumber == false) {
            $streetNumber = $addressPart;
            $foundStreetNumber = true;
          } elseif ($foundStreetNumber) {
            $streetUnit .= " " . $addressPart;
          }
        } else {
          /*
           * if part is not numeric, there are several possibilities:
           * - if the street number is found, set the whole part to streetUnit
           * - if there is no streetNumber yet and it is the first part, set the
           *   whole part to streetName
           * - if there is no streetNumber yet and it is not the first part,
           *   check all digits:
           *   - if the first digit is numeric, put the numeric part in streetNumber
           *     and all non-numerics to street_unit
           *   - if the first digit is not numeric, put the lot into streetName
           */
          if ($foundStreetNumber == true) {
            if (!empty($streetName)) {
              $streetUnit .= " " . $addressPart;
            } else {
              $streetName .= " " . $addressPart;
            }
          } else {
            if ($partKey == 0) {
              $streetName .= $addressPart;
            } else {
              $partLength = strlen($addressPart);
              if (is_numeric(substr($addressPart, 0, 1))) {
                for ($i = 0; $i < $partLength; $i++) {
                  if (is_numeric(substr($addressPart, $i, 1))) {
                    $streetNumber .= substr($addressPart, $i, 1);
                    $foundStreetNumber = true;
                  } else {
                    $streetUnit .= " " . substr($addressPart, $i, 1);
                  }
                }
              } else {
                $streetName .= " " . $addressPart;
              }
            }
          }
        }
      }
      $result['street_name'] = trim($streetName);
      $result['street_number'] = $streetNumber;
      $result['street_unit'] = trim($streetUnit);
    }
    return $result;
  }

}