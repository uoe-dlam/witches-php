<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

class SparqlQuery {
    private string $sparqlQuery = 'SELECT distinct ?item ?itemLabel ?investigationDate
            ?residenceLabel ?residenceCoords ?sexLabel ?link ?occupationLabel ?socialClassificationLabel
            ?placeOfDeathLabel ?placeOfDeathCoords ?mannerOfDeathLabel ?detentionLocationLabel ?detentionLocationCoords
            WHERE
            {
              ?item wdt:P4478 ?witch .
              ?item wdt:P551 ?residence .
              ?residence wdt:P625 ?residenceCoords .
              optional { ?item wdt:P21 ?sex } .
              ?item wdt:P4478 ?link .
              optional { ?item wdt:P106 ?occupation .}
              optional { ?item wdt:P3716 ?socialClassification .}
              optional {
                ?item wdt:P20 ?placeOfDeath .
                ?placeOfDeath wdt:P625 ?placeOfDeathCoords .}
              optional { ?item wdt:P1196 ?mannerOfDeath .}
              optional { ?item p:P793 ?significantEventStatement .
              ?significantEventStatement ps:P793 wd:Q66458810 .
              OPTIONAL {?significantEventStatement pq:P585 ?investigationPoint }.
              OPTIONAL {?significantEventStatement pq:P580 ?investigationStart }
              }
              BIND(IF(BOUND(?investigationPoint), ?investigationPoint, ?investigationStart) as ?investigationDate)
              optional {  ?item wdt:P2632 ?detentionLocation .
                ?detentionLocation wdt:P625 ?detentionLocationCoords .}

              SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en". }
            }';

    public function __construct(private string $baseUrl, private Client $client)
    {
    }

    public function handle()
    {
        $fullUrl = $this->baseUrl . '?query=' . $this->encodeURIComponent( $this->sparqlQuery );
        $response = $this->client->request('GET', $fullUrl);

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header("Access-Control-Allow-Headers: X-Requested-With");
        header('Content-Type: application/json; charset=utf-8');
        echo $response->getBody();
        exit;
    }

    private function encodeURIComponent(string $string)
    {
        $revert = ['%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')'];
        return strtr(rawurlencode($string), $revert);
    }
}

$sparqlQuery = new SparqlQuery(
    'https://query.wikidata.org/sparql',
    new Client(['headers' => ['Accept' => 'application/sparql-results+json']])
);

$sparqlQuery->handle();