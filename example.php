#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

define('CONSUMER_KEY', 'YOUR API CONSUMER KEY');
define('CONSUMER_SECRET', 'YOUR API CONSUMER SECRET');

// instantiate the contextio object
$contextio = new ContextIO(CONSUMER_KEY, CONSUMER_SECRET);

echo("Enter the email address for an account you want to connect:\n");
$emailAddress = fgets(STDIN);

echo("Enter the first name you want on the account:\n");
$firstName = fgets(STDIN);

echo("Enter the last name you want on the account:\n");
$lastName = fgets(STDIN);

echo("Create a callback URL using http://requestb.in/ and enter it here:\n");
$callback = fgets(STDIN);
//create a connect token
$addTokenResponse = $contextIO->addConnectToken(
    array('callback_url' => $callback,
        'email' => $emailAddress,
        'first_name' => $firstName,
        'last_name' => $lastName));

//get the redirect url from the response, and direct the user to it
$redirectUrl = $addTokenResponse->getDataProperty('browser_redirect_url');
echo("Copy the following URL into your browser to connect your account:\n");
echo($redirectUrl . "\n");
echo("Hit Enter when you have authorized context.io on your account\n");
fgets(STDIN);

$getTokenResponse = $contextIO->getConnectToken($addTokenResponse->getDataProperty('token'));
//now get the userID for the new user you created
$user = $getTokenResponse->getDataProperty('user');
$userResponse = $contextIO->getUser($user['id']);

echo("Getting folders for newly created user $emailAddress\n");
$folderResponse = $contextIO->listEmailAccountFolders($user['id'], array('label' => 0));
print_r($folderResponse->getData());

$folder = $folderResponse->getDataProperty(0);
$folderName = $folder['name'];

echo("Getting message list for folder " . $folder . "\n");
$messageList = $contextIO->listMessages($user['id'], array('label' => 0, 'folder' => $folderName))->getData();
print_r($messageList);

echo("Getting a message\n");
$message = $contextIO->getMessage($user['id'], array('label' => 0, 'folder' => $folderName, 'message_id' => $messageList[0]['email_message_id']))->getData();
print_r($message);

echo("Getting the message flags\n");
$messageFlags = $contextIO->getMessageFlags($user['id'], array('label' => 0, 'folder' => $folderName, 'message_id' => $messageList[0]['email_message_id']))->getData();
print_r($messageFlags);

echo("Setting the read flag for a message\n");
$flagsResponse = $contextIO->markRead($user['id'], array('label' => 0, 'folder' => $folderName, 'message_id' => $messageList[0]['email_message_id']))->getData();
print_r($flagsResponse);

echo("Getting a message after setting the read flag\n");
$messageFlags = $contextIO->getMessageFlags($user['id'], array('label' => 0, 'folder' => $folderName, 'message_id' => $messageList[0]['email_message_id']))->getData();
print_r($messageFlags);
