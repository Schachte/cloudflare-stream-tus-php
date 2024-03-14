<?php
require_once '../vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

function onRequest($context) {
    $request = $context['request'];
    $env = $context['env'];

    $CLOUDFLARE_ACCOUNT_ID = getenv('CLOUDFLARE_ACCOUNT_ID') ?: '';
    $CLOUDFLARE_API_TOKEN = getenv('CLOUDFLARE_API_TOKEN') ?: '';

    $endpoint = "https://api.cloudflare.com/client/v4/accounts/$CLOUDFLARE_ACCOUNT_ID/stream?direct_user=true";

    $uploadLength = $_SERVER['HTTP_UPLOAD_LENGTH'] ?? '';
    $uploadMetadata = $_SERVER['HTTP_UPLOAD_METADATA'] ?? '';

    $headers = [
        'Authorization' => "bearer $CLOUDFLARE_API_TOKEN",
        'Tus-Resumable' => '1.0.0',
        'Upload-Length' => $uploadLength,
        'Upload-Metadata' => $uploadMetadata,
        'Upload-Creator' => "schachte",
    ];

    $client = new Client();
    try {
        $response = $client->post($endpoint, [
            'headers' => $headers,
        ]);
    
        $destination = $response->getHeader('Location')[0];

        $responseHeaders = $response->getHeaders();
        foreach ($responseHeaders as $name => $values) {
            foreach ($values as $value) {
                header($name . ': ' . $value);
            }
        }
    
        header('Access-Control-Expose-Headers: Location');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Origin: *');
        header('Location: ' . $destination);
    
        http_response_code(200);
        exit();
    } catch (RequestException $e) {
        if ($e->hasResponse()) {
            $errorResponse = $e->getResponse();
            $statusCode = $errorResponse->getStatusCode();
            $errorMessage = $errorResponse->getBody()->getContents();
            $uploadLength = isset($request['Upload-Length']) ? $request['Upload-Length'] : '';
            $uploadMetadata = isset($request['Upload-Metadata']) ? $request['Upload-Metadata'] : '';

            header('Upload-Metadata' . $uploadMetadata);
            if (!empty($uploadMetadata)) {
                header('Upload-Metadata: ' . $uploadMetadata);
            }
            header('Upload-Length: ' . $uploadLength);
            if (!empty($uploadLength)) {
                header('Upload-Length: ' . $uploadLength);
            }
    
            header('Content-Type: application/json');
            http_response_code($statusCode);
            echo json_encode(['error' => $errorMessage]);
            exit();
        } else {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred']);
            exit();
        }
    }
}

$request = $_REQUEST;
$env = $_SERVER;

$response = onRequest(['request' => $request, 'env' => $env]);
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $header => $value) {
    header("$header: " . implode(', ', $value));
}

echo $response->getContent();
