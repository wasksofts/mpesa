# Introduction

This package seeks to help php developers implement the various Mpesa APIs without much hustle. It is based on the REST API whose documentation is available on https://developer.safaricom.co.ke.

#  Installation using composer
composer require wasksofts/mpesa --dev

#  example of configuration

require_once('vendor/autoload.php');

  use Wasksofts\Mpesa\Mpesa;

$mpesa  = new Mpesa();
$mpesa->config('consumer_key', '');
$mpesa->config('consumer_secret', '');
$mpesa->config('pass_key', '');
$mpesa->config('initiator_name', '');
$mpesa->config('initiator_pass', '');
$mpesa->config('shortcode', '174379');
$mpesa->config('shortcode1', '');
$mpesa->config('shortcode2', '');
$mpesa->config('callback_url', 'https://example.com/callback_url.php');
$mpesa->config('confirmation_url', 'https://example.com/confirmation_url.php');
$mpesa->config('validation_url', 'https://example.com/validation_url.php');
$mpesa->config('result_url', 'https://example.com/result_url.php');
$mpesa->config('timeout_url', 'https://example.com/timeout_url.php');
$mpesa->config('env', 'sandbox');
