<?php
Class DistanceCalculatorApi {

  public static function calculateDistance($from, $to, $rate) {
    $distance = 0;

    // lets sanitize our data
    $from = filter_var($from, FILTER_SANITIZE_STRING);
    $to = filter_var($to, FILTER_SANITIZE_STRING);
    $rate = filter_var($rate, FILTER_SANITIZE_STRING);

    if (!$from || !$to || !$rate) {
      self::responseJson('Wrong input');
    }

    $url = sprintf(
      'https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=%s&destinations=%s&key=AIzaSyAvyOtvhy0crTZYYX6Uc405_-mdUHAVTHM',
      urlencode($from),
      urlencode($to)
    );

    $map = curl_init();
    curl_setopt($map, CURLOPT_URL, $url);
    curl_setopt($map, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($map, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($map, CURLOPT_SSL_VERIFYHOST, false);

    // $output contains the output string
    if (!$result = curl_exec($map)) {
      curl_close($map);
      self::responseJson('Could not connect to google maps API');
    }

    curl_close($map);
    $result = json_decode($result);

    if (!isset($result->status) || $result->status != 'OK') {
      self::responseJson('Status not OK');
    };

    if (!isset($result->rows[0]->elements[0]->distance->value) || !isset($result->rows[0]->elements[0]->distance->text)) {
      self::responseJson('Returned data mismatch');
    }

    $distance = $result->rows[0]->elements[0]->distance->value / 1000;

    self::responseJson( array(
      'distance'    => $distance,
      'expense'     => self::calculateExpenses($distance, $rate),
      'from'        => $from,
      'to'          => $to,
      'description' => sprintf(
        'Kørsel fra %s til %s (%s)',
        $from,
        $to,
        $result->rows[0]->elements[0]->distance->text
      )
    ));
  }

  private static function calculateExpenses($distance, $rate) {
    return number_format($rate * $distance, 2, '.', '');
  }

  private static function responseJson($array) {
     echo json_encode($array);
     die();
  }

}

if ($_SERVER['REQUEST_METHOD'] != 'GET' || !isset($_GET['ajax_action']) || !$ajaxAction = filter_var($_GET['ajax_action'], FILTER_SANITIZE_STRING)) {
  die('An Error Occured');
}

if ( method_exists('DistanceCalculatorApi', $ajaxAction) ) {
  $options = $_GET['ajax_options'];
  call_user_func_array(array('DistanceCalculatorApi', $ajaxAction), $options);
}

?>
