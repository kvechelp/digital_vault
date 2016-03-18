<?php

$appResponse = array();

$url = "https://api-gw-o.antwerpen.be/acpaas/vault/v1/vaults";

$curl = curl_init($url);
curl_setopt($curl, CURLINFO_HEADER_OUT, true); // enable tracking
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
    array(	"Accept: application/json", "Authorization: Bearer e526c3930f7c4d7a8d380266563ce7ce","apikey: c71573f1-185f-4525-a918-c1715b80e4bf"));

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ( $status != 200 ) {
    die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
}

curl_close($curl);

$response = json_decode($json_response, true);

//$url = "https://api-gw-o.antwerpen.be/acpaas/vault/v1/upload/vaults/e29ab029-d577-428b-a530-49d04c80abb5/upload-link";
$url = "https://api-gw-o.antwerpen.be/acpaas/vault/v1/upload/vaults/" . $response['repos'][0]['id'] . "/upload-link";



$curl = curl_init($url);
curl_setopt($curl, CURLINFO_HEADER_OUT, true); // enable tracking
//curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
    array(	"Accept: application/json", "Authorization: Bearer e526c3930f7c4d7a8d380266563ce7ce","apikey: c71573f1-185f-4525-a918-c1715b80e4bf"));

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ( $status != 200 ) {
    die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
}

curl_close($curl);

$response = json_decode($json_response, true);

$uploadURL = $response['uploadLink'];

$url = $uploadURL;

move_uploaded_file($_FILES['file']['tmp_name'], "./".$_FILES['file']['name']);

if (version_compare(phpversion(), '5.5.0', '>=')) {
    $filePointer = new \CurlFile("./".$_FILES['file']['name']);
} else {
    $filePointer = "@" . $_FILES['file']['name'];
}

$appResponse['file_name'] = $_FILES['file']['name'];

$post = array ("file" => $filePointer, "parent_dir" => "/test/" );

$curl = curl_init($url);
curl_setopt($curl, CURLINFO_HEADER_OUT, true); // enable tracking
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
    array(
        "Authorization: Token ae7fa798a020f44b35abb91ce0ecad4037c10ded"
    ));

curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $post);

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ( $status != 200 ) {
    die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
}

curl_close($curl);

$response = json_decode($json_response, true);

$appResponse['vault_id'] = $response[0]['id'];

$url = "https://api-gw-o.antwerpen.be/acpaas/vault/v1/vaults/e29ab029-d577-428b-a530-49d04c80abb5/file?file-path=test/" . $_FILES['file']['name'] . "&click-mode=once";

$curl = curl_init($url);
curl_setopt($curl, CURLINFO_HEADER_OUT, true); // enable tracking
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
    array(	"Accept: application/json", "Authorization: Bearer e526c3930f7c4d7a8d380266563ce7ce","apikey: c71573f1-185f-4525-a918-c1715b80e4bf"));

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ( $status != 200 ) {
    die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
}

curl_close($curl);

$response = json_decode($json_response, true);
$appResponse['download_uri'] = $response['uri'];

// engine not available at the moment via DEV API manager
//$url = "https://api-gw-o.antwerpen.be/acpaas/notification/v1/notification";
$url = "http://ras396.rte.antwerpen.local:10281/api/notifications/notification";


$curl = curl_init($url);
curl_setopt($curl, CURLINFO_HEADER_OUT, true); // enable tracking
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
    array("Content-Type: application/json", "Accept: application/json"/*, "apikey: 58a5e14e-6dea-436f-970d-e701d4b5ae50"*/));


$post = "{
  \"submitterId\": \"3ca2ce3e-9683-4d24-9469-82de7ddf38c9\",
  \"applicationId\": \"2a6dd86b-e069-4a26-bf44-2244cd72b574\",
  \"moduleId\": \"a8e98b95-6e23-4ed5-92c1-3a5e8ab4e3bf\",
  \"roleId\": \"a6014d04-3eb0-4b8c-be16-b31cd25ae46a\",
  \"topicName\": \"My First Topic\",
  \"startDate\": \"null\",
  \"endDate\": \"null\",
  \"callbackUrl\": \"http://localhost:81\",
  \"clientReference\": \"digital vault test\",
  \"content\": [
    {
      \"channelName\": \"EMAIL\",
      \"apiConfigurationKey\": \"null\",
      \"languageCode\": \"nl-BE\",
      \"message\": {
        \"from\": \"kris.vanechelpoel@digipolis.be\",
        \"subject\": \"Download Link\",
        \"body\": \"" . $response['uri'] . "\"
      }
    }
  ],
  \"userIds\": [
    \"d2725487-3594-4240-90ea-0548f336d0cc\"
  ]
}";

curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $post);

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ( $status != 200 ) {
    die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
}

curl_close($curl);

$response = json_decode($json_response, true);

if ($json_response === '"Notification succesfully saved!"') {
    $appResponse['success'] = true;
} else {
    $appResponse['success'] = false;
}

echo json_encode($appResponse);