# Swagger\Client\HlrApi

All URIs are relative to *https://apirest.isendpro.com/cgi-bin*

Method | HTTP request | Description
------------- | ------------- | -------------
[**getHlr**](HlrApi.md#getHlr) | **POST** /hlr | Vérifier la validité d&#39;un numéro


# **getHlr**
> \Swagger\Client\Model\HLRReponse getHlr($hlrrequest)

Vérifier la validité d'un numéro

Réalise un lookup HLR sur les numéros

### Example
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

$api_instance = new Swagger\Client\Api\HlrApi();
$hlrrequest = new \Swagger\Client\Model\HLRrequest(); // \Swagger\Client\Model\HLRrequest | 

try {
    $result = $api_instance->getHlr($hlrrequest);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling HlrApi->getHlr: ', $e->getMessage(), PHP_EOL;
}
?>
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **hlrrequest** | [**\Swagger\Client\Model\HLRrequest**](../Model/\Swagger\Client\Model\HLRrequest.md)|  |

### Return type

[**\Swagger\Client\Model\HLRReponse**](../Model/HLRReponse.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

