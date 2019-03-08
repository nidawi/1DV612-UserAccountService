<?php

// Transcribed JWT module
namespace lib;

class jwt {

  private static $header = array("alg" => "HS256", "typ" => "JWT");

  public static function sign(array $payload) : string {
    $basePayload = array_merge(
      array(
        "iss" => \Environment::JWT_ISS,
        "iat" => time(),
        "exp" => (time() + \Environment::JWT_EXPIRATION),
      ), $payload
    );

    $_header = self::encode(self::$header);
    $_payload = self::encode($basePayload);

    return "$_header.$_payload." . self::createSignature($_header, $_payload);
  }
  public static function verify(string $jwt) : array {
    $parts = explode(".", $jwt);
    if (count($parts) !== 3)
      throw new InvalidJWTException();

    $header = self::decode($parts[0]);
    if ($header !== self::$header) {
      throw new InvalidJWTHeaderException();
    }

    $constructedSignature = self::createSignature($parts[0], $parts[1]);
    $deliverySignature = $parts[2];

    if (hash_equals($constructedSignature, $deliverySignature)) {
      // Signature matches. Check iss, exp, aud.
      $jsonPayload = self::decode($parts[1]);

      $expValid = isset($jsonPayload["exp"]) && ($jsonPayload["exp"] >= time());
      if (!$expValid) throw new ExpiredJWTException();

      $issValid = isset($jsonPayload["iss"]) && in_array($jsonPayload["iss"], \Environment::JWT_VALID_ISS);
      if (!$issValid) throw new InvalidJWTIssuerException();

      $audValid = isset($jsonPayload["aud"]) && (is_array($jsonPayload["aud"])
        ? count(array_intersect($jsonPayload["aud"], \Environment::JWT_VALID_AUD)) > 0
        : in_array($jsonPayload["aud"], \Environment::JWT_VALID_AUD));
      if (!$audValid) throw new InvalidJWTAudienceException();

      // JWT is valid.
      return $jsonPayload;
    } else throw new InvalidJWTSignatureException();
  }


  private static function encode(array $value) : string {
    return self::base64url_encode(json_encode($value));
  }
  private static function decode(string $value) : array {
    return json_decode(self::base64url_decode($value), true);
  }

  private static function createSignature(string $header, string $payload) : string {
    $data = "$header.$payload";
    $hmac = hash_hmac("sha256", $data, \Environment::JWT_SECRET, true);
    return self::base64url_encode($hmac);
  }

  private static function base64url_encode($data) { 
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
  }
  private static function base64url_decode($data) { 
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $data )) % 4 ));
  } 
}

// Saving time and effort.
class InvalidJWTHeaderException extends \Exception {}
class InvalidJWTException extends \Exception {}
class InvalidJWTSignatureException extends \Exception {}
class ExpiredJWTException extends \Exception {}
class InvalidJWTAudienceException extends \Exception {}
class InvalidJWTIssuerException extends \Exception {}