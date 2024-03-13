<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title>Easing</title>
    <!-- Autres balises meta, liens CSS, etc. peuvent être ajoutés ici -->
</head>
<body>
<?php /* Template Name: Liste logements */

$login = "admin";
$passwd = "admin";
$API_URL = "http://localhost:8000";
$auth_url = "/auth/token";

$complete_url = $API_URL.$auth_url;

$body = "username=".$login."&password=".$passwd;

$headers = array(
    "Content-Type: application/x-www-form-urlencoded",
    "Accept: application/json"
);

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => $complete_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_HTTPHEADER => $headers
));

$response = curl_exec($curl);

echo $response;

echo "<br>";

echo "test<br>";

$token_access = json_decode($response, true)['access_token'];

echo "$token_access<br>";

$body = array(
    'ressource'=>"ns0__record",
    "ressource_id"=>"ns0__identifier",
    "sub_ressource"=>"ns0__taxon",
    "choice_of_resource_data_fields"=>array("ns0__location", "ns0__identifier"),
    "essential_filters"=>array("ns0__setSpec"),
    "nb_criteria_for_each_data"=>30,
    "threshold_percentage_max_filter"=>0.1,
    "filters_criteria"=>array(
        array("PHY", "MATHS")
    ),
    "WITH_RETURN"=>array(
        "ressource.ns0__identifier"=>"ID",
        "ressource.ns0__title_string"=>"title",
        "ressource.ns0__description_string"=>"description",
        "collect(taxon.ns0__entry_string)"=>"phrases",
    ),
    "special_return"=>array(
        "ns0__resource_identifier"=>"ns0__location"
    ),
    "user_request"=>"Exercice de calcul de trajectoire",
    "method"=>"TF-IDF+Word2Vec",
    "n_result"=>6,
    "similarity_method"=>"cosine"
);

//[2393  708  133  858 1598 1916 2433 2298 1483 572]      Lucas
//[2393  346  133  893 2704 1087   97 1868 1825 612 2280] API

// Convert data array to JSON
$jsonData = json_encode($body);

// Set the HTTP headers
$options = array(
    'http' => array(
        'header'  => "Content-Type: application/json\r\n" . "Accept: application/json\r\n" . "Authorization: bearer $token_access\r\n",
        'method'  => 'POST',
        'content' => $jsonData
    )
);

// Create a stream context
$context  = stream_context_create($options);

$url = 'http://localhost:8000/IA/search';

// Make the POST request
$response = file_get_contents($url, false, $context);


// Check for errors
if ($response === false) {
    // Handle cURL error
    // Handle error
    echo "Error: ";
} else {
    // Response received successfully, handle it
    echo "Success <pre>".print_r(json_decode($response, true), true)."</pre>";
}

?>
</body>
</html>
