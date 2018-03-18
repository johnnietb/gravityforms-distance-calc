/* Initial functions to load. */
jQuery( document ).ready(function() {
  distCheckForListners();
  initGooglePlaces();
});

/* Event trigger sends addresses to our ajax API and pushes it to our field elements */
function calculateDrivingDistance( fieldId ){
    var start = jQuery( '#from_' + fieldId ).val();
    var destination = jQuery( '#to_' + fieldId ).val();
    var rate = jQuery( '#dc_km_rate_' + fieldId ).val();

    jQuery.getJSON(
      distance_calculator_strings.dc_ajax_path,
      {
        ajax_action:'calculateDistance',
        ajax_options:{
          from:start,
          to:destination,
          rate:rate
        }
      },
      function(data) {
        $addButton = jQuery( '#distance_calculator_' + fieldId + ' tr.dc_expense_row:last td');
        jQuery( '#from_' + fieldId ).val('');
        jQuery( '#to_' + fieldId ).val('');
        distCalcAddListItem( $addButton, data.description, data.expense);

      }
    );

    toggleDistanceCalculation( fieldId );
}

/* Delete the field row unless its that last visible row. */
function distCalcAddListItem( addButton, description, expense ) {
    var $addButton = jQuery( addButton );
    var $group     = $addButton.parents( '.distance-calculator tr' ),
        $clone     = $group.clone();

    // reset all inputs to empty state
    if (typeof description !== 'undefined' && typeof expense !== 'undefined') {
      $clone.find( '.dc_expense' ).val(description);
      $clone.find( '.dc_amount' ).val(expense);

    } else {
      $clone
          .find( 'input, select, textarea' )
          .not( ':checkbox, :radio' ).val( '' );
      $clone.find( ':checkbox, :radio' ).prop( 'checked', false );
    }

    $group.after( $clone );

    distCheckForListners();
    distCalculateTotal();
}

/* Delete the field row unless its that last visible row. */
function distCalcDeleteListItem( deleteButton ) {
    var $deleteButton = jQuery( deleteButton ),
        $group        = $deleteButton.parents( '.distance-calculator tr' ),
        $siblings     = $deleteButton.parents( '.distance-calculator tr' ).siblings().length;

    // only delete if its not the last element present.
    if ($siblings > 0) {
      $group.remove();
    }

    distCalculateTotal();
}

/* Check and add keyup/changelistners to all amount fields. */
function distCheckForListners() {
    var $amount = jQuery( '.distance-calculator .dc_amount');

    for (i = 0; i < $amount.length; i++) {
        jQuery( $amount[i] ).bind("keyup change", function(e) {
          if ( jQuery( this ).val().length > 9 ) {
            jQuery( this ).val(
              jQuery( this ).val().substring(0, 9)
            );
          }
            distCalculateTotal();
        })
    }
}

/* Calculate and update the total amount */
function distCalculateTotal() {
    var $amount = jQuery( '.distance-calculator .dc_amount'),
        $result = jQuery( '.distance-calculator .dc_total'),
        $total = 0;

    for (i = 0; i < $amount.length; i++) {
        $value = jQuery( $amount[i] ).val();
        if ( isNaN(parseFloat( $value ) )) {
          // skip if not a numeric value
          continue;
        }

        $total += parseFloat( jQuery( $amount[i] ).val() );
    }

    $result.val($total);
}

/* Toggle the address field container */
function toggleDistanceCalculation( fieldId ) {
  var $container = jQuery( '#distance_addresses_' + fieldId );

  if ($container.length > 0) {
    $container.slideToggle('100');
  }
}

/* Use Google Places Api to Autocomplete city fields. */
function initGooglePlaces() {
  var $placeFields = jQuery( '.distance_addresses .google_places_field');

  for (i = 0; i < $placeFields.length; i++) {
    autocomplete = new google.maps.places.Autocomplete(
        ($placeFields[i]),
        {types: ['geocode']});
  }
}
