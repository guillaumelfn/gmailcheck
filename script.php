<?php
require 'vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('./key.json'); // json file
$client->setScopes(['https://www.googleapis.com/auth/gmail.readonly']); // scope to read google
$client->setSubject('email@domain.com');  // Specify the user's email here

$service = new Google_Service_Gmail($client);

// parameters to only look at unread message

$optParams = [
    'labelIds' => 'INBOX',
    'q' => 'is:unread'
];

$messages = $service->users_messages->listUsersMessages('me', $optParams);

function getBody($message) {
    $body = '';

    // Check if the message parts exist
    if ($message->getPayload() && $message->getPayload()->getParts()) {
        foreach ($message->getPayload()->getParts() as $part) {
            if ($part['body'] && $part['mimeType'] == 'text/html') {
                $rawData = $part['body']->data;
                $sanitizedData = strtr($rawData,'-_', '+/');
                $body .= base64_decode($sanitizedData);
                break;  // Assumes first text/html part is the body
            }
        }
    } elseif ($message->getPayload()->getBody()) {
        $rawData = $message->getPayload()->getBody()->data;
        $sanitizedData = strtr($rawData,'-_', '+/');
        $body = base64_decode($sanitizedData);
    }

    return $body;
}

foreach ($messages as $message) {
    $msg = $service->users_messages->get('me', $message->getId(), ['format' => 'full']);

    // We used to get a snippet, not we get the full message.
  
    $snippet = getBody($msg);
    if (strpos($snippet, 'keyword') !== false) { // replace keyword by the word you want to look for in email
        // Send alert to Google Chat
        $webhookUrl = 'WEBHOOK_GENERATED'; // replace by the WEBHOOK
        $messageData = ['text' => 'Hi. Keyword "xxx" found in email. Message has been sent to this email.']; // detection sentence that will go on google chat 
        $options = [
            'http' => [
                'method'  => 'POST',
                'content' => json_encode($messageData),
                'header'=>  "Content-Type: application/json\r\n" .
                            "Accept: application/json\r\n"
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($webhookUrl, false, $context);
    }
}
