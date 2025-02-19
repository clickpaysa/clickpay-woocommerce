<?php


abstract class ClickpayHelper
{

  public static function log($msg)
  {
      try {
          error_log("ClickPay: " . $msg);
      } 
      catch (\Throwable $th) {
      }
  }

  public static function send_api_request($request_url, $data, $profileid, $serverkey, $request_method = null)
  {
      $data['profile_id'] = $profileid;
      $curl = curl_init();
      curl_setopt_array($curl, array(
          CURLOPT_URL => $request_url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_SSL_VERIFYHOST => false,
          CURLOPT_CUSTOMREQUEST => isset($request_method) ? $request_method : 'POST',
          CURLOPT_POSTFIELDS => json_encode($data, true),
          CURLOPT_HTTPHEADER => array(
              'authorization:' . $serverkey,
              'Content-Type:application/json'
          ),
      ));

      $curl_response = curl_exec($curl);      
      $curl_error = curl_error($curl);
      curl_close($curl);

      $response = [];
      if ($curl_error != '')
      {
        error_log("ClickPay curl error: " . print_r($curl_error, true));
        $response = ['status' => 'error'];
      }
      else
      {        
        $data = json_decode($curl_response, true);
        $response = ['status' => 'success', 'data' => $data];
      }

      return $response;
  }

  public static function applepay_api_request($request_url, $data, $cert_file, $key_file)
  {
      $curl = curl_init();
      curl_setopt_array($curl, array(
          CURLOPT_URL => $request_url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_SSL_VERIFYPEER => true,
          CURLOPT_SSL_VERIFYHOST => 2,
          CURLOPT_SSLCERT => $cert_file,
          CURLOPT_SSLKEY => $key_file,
          CURLOPT_POST => true,
          CURLOPT_POSTFIELDS => json_encode($data, true),
          CURLOPT_HTTPHEADER => array(
              'Content-Type:application/json'
          ),
      ));

      $curl_response = curl_exec($curl);      
      $curl_error = curl_error($curl);
      curl_close($curl);

      $response = [];
      if ($curl_error != '')
      {
        error_log("ClickPay curl error: " . print_r($curl_error, true));
        $response = ['status' => 'error'];
      }
      else
      {        
        $data = json_decode($curl_response, true);
        $response = ['status' => 'success', 'data' => $data];
      }

      return $response;
  }

  public static function is_valid_redirect($post_values, $server_key)
    {

        // Request body include a signature post Form URL encoded field
        // 'signature' (hexadecimal encoding for hmac of sorted post form fields)
        $requestSignature = $post_values["signature"];
        unset($post_values["signature"]);
        $fields = array_filter($post_values);

        // Sort form fields
        ksort($fields);

        // Generate URL-encoded query string of Post fields except signature field.
        $query = http_build_query($fields);

        $signature = hash_hmac('sha256', $query, $server_key);
        if (hash_equals($signature, $requestSignature) === TRUE) {
            // VALID Redirect
            return true;
        } else {
            // INVALID Redirect
            return false;
        }
    }

}