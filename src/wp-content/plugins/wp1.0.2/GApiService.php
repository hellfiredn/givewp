<?php

class GApiService
{
  public function __construct($domain = '', $xAPIClient = '', $secretKey = '')
  {
    $this->domain = $domain;
    $this->xAPIClient = $xAPIClient;
    $this->secretKey = $secretKey;
  }

  public function Request($path, $method = 'POST', $params = [])
  {
    $curl = curl_init();
    $header = $this->createRequestHeader($path, $method, $params);
    $postFields = json_encode($params);
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->domain . $path,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POSTFIELDS => $postFields,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_HEADER => true,
      CURLOPT_HTTPHEADER => $header,      
      CURLOPT_CUSTOMREQUEST => strtoupper($method)
    ));

    $response = curl_exec($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $body = substr($response, $header_size);

    return $body;
  }

  private function createRequestHeader($path, $method = 'POST', $params = [])
  {
    $header = array(
      "x-api-client:". $this->xAPIClient,
      "Content-Type: application/json; charset=UTF-8"
    );

    $payload = stripslashes(json_encode($params, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT));// json_encode($params, JSON_UNESCAPED_SLASHES );
    $rawStr = $path . $method . $payload . $this->secretKey;

    array_push($header, "x-api-validate: " . md5($rawStr));    
    return $header;
  }
}
