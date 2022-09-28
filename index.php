<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

class SparqlQuery {
    private string $sparqlQuery = 'SELECT distinct ?item ?itemLabel ?investigationDate
        ?residenceLabel ?residenceCoords ?sexLabel ?link ?occupationLabel ?socialClassificationLabel
        ?placeOfDeathLabel ?placeOfDeathCoords ?mannerOfDeathLabel ?detentionLocationLabel ?detentionLocationCoords
        ?investigationStart ?investigationEnd
        
        (GROUP_CONCAT(DISTINCT ?qualityWithQualifier; separator=\' | \') as ?qualities)
        (GROUP_CONCAT(DISTINCT ?chargeWithQualifier; separator=\' | \') as ?charges)
        (GROUP_CONCAT(DISTINCT ?ritualObjectLabel; separator=\' | \') as ?ritualObjects)
        #(GROUP_CONCAT(DISTINCT ?signifpersonWithQualifier; separator=\' | \') as ?signifpersons)
        (GROUP_CONCAT(DISTINCT ?meetingLocation; separator=\' | \') as ?meetingLocations)
        (GROUP_CONCAT(DISTINCT ?includingLabelQual; separator=\' | \') as ?including)
        
        WHERE
        {
          ?item wdt:P4478 ?link .
          ?item rdfs:label ?itemLabel .
          FILTER (lang(?itemLabel) = "en") .
          #OPTIONAL { ?item wdt:P793 ?trial .
          #  ?trial wdt:P31 wd:Q19902850 }.
        
          ?item wdt:P551 ?residence .
          ?residence wdt:P625 ?residenceCoords .
          ?residence rdfs:label ?residenceLabel .
          FILTER (lang(?residenceLabel) = "en") .
        
          OPTIONAL {
            ?item wdt:P21 ?sex .
            ?sex rdfs:label ?sexLabel .
            FILTER (lang(?sexLabel) = "en") .
          } .
          OPTIONAL {
            ?item wdt:P106 ?occupation .
            ?occupation rdfs:label ?occupationLabel .
            FILTER (lang(?occupationLabel) = "en") .
          }
          OPTIONAL {
            ?item wdt:P3716 ?socialClassification .
            ?socialClassification rdfs:label ?socialClassificationLabel .
            FILTER (lang(?socialClassificationLabel) = "en") .
        
          }
          OPTIONAL {
            ?item wdt:P20 ?placeOfDeath .
            ?placeOfDeath wdt:P625 ?placeOfDeathCoords .
            ?placeOfDeath rdfs:label ?placeOfDeathLabel .
            FILTER (lang(?placeOfDeathLabel) = "en") .
          }
          OPTIONAL {
            ?item wdt:P1196 ?mannerOfDeath .
            ?mannerOfDeath rdfs:label ?mannerOfDeathLabel .
            FILTER (lang(?mannerOfDeathLabel) = "en") .
          }
          OPTIONAL {
            ?item wdt:P2632 ?detentionLocation .
            ?detentionLocation wdt:P625 ?detentionLocationCoords .
            ?detentionLocation rdfs:label ?detentionLocationLabel .
            FILTER (lang(?detentionLocationLabel) = "en") .
          }
        
          #### TRIAL ####
        
          # SIGNIFICANT PERSON
          #  OPTIONAL {
          #    ?trial p:P3342 ?signifpersonNode .
          #    ?signifpersonNode ps:P3342 ?signifperson1 . # main value
          #    OPTIONAL {  # "object has role" qualifier
          #      ?signifpersonNode pq:P3831 ?objecthasrole .
          #      ?objecthasrole rdfs:label ?objecthasroleLabel .
          #      FILTER (lang(?objecthasroleLabel) = "en") .
          #      FILTER (?objecthasrole != wd:Q112813327) . # Remove "mention in witch trial" results
          #    } .
          #    ?signifperson1  rdfs:label ?signifperson1Label .
          #    FILTER (lang(?signifperson1Label) = "en") .
          #
          #    BIND(IF(
          #      BOUND(?objecthasroleLabel),
          #      CONCAT(?signifperson1Label," (", ?objecthasroleLabel, ")"),
          #      ?signifperson1Label) as ?signifpersonWithQualifier)
          #  } .
        
          #### INVESTIGATION ####
        
          OPTIONAL {
            ?investigation wdt:P921 ?item .
            ?investigation wdt:P31 wd:Q66458810 ;
                           wdt:P580|wdt:P585 ?investigationStart ;
            OPTIONAL { ?investigation wdt:P582 ?investigationEnd }
        
            # QUALITY (COPY THIS FOR MULTIPLE VALUE, WITH qualifier)
            OPTIONAL {
              ?investigation p:P1552 ?hasQualityNode .
              ?hasQualityNode ps:P1552 ?hasQuality . # main value
              OPTIONAL {  # "including" qualifier
                ?hasQualityNode pq:P1012 ?qualityIncluding .
                ?qualityIncluding rdfs:label ?qualityIncludingLabel .
                FILTER (lang(?qualityIncludingLabel) = "en") .
              } .
              ?hasQuality rdfs:label ?hasQualityLabel .
              FILTER (lang(?hasQualityLabel) = "en") .
        
              BIND(IF(
                BOUND(?qualityIncludingLabel),
                CONCAT(?hasQualityLabel," (", ?qualityIncludingLabel, ")"),
                ?hasQualityLabel) as ?qualityWithQualifier)
        
              OPTIONAL {
                ?hasQualityNode pq:P276 ?location .
                ?location rdfs:label ?locationLabel .
                FILTER (lang(?locationLabel) = "en") .
              }
        
              BIND(IF(
                ?hasQuality = wd:Q831942,
                ?locationLabel,
                ?unbound
              ) as ?meetingLocation)
            } .
        
            # CHARGE
            OPTIONAL {
              ?investigation p:P1595 ?chargeNode .
              ?chargeNode ps:P1595 ?charge .
              OPTIONAL {
                ?chargeNode pq:P4675 ?form .
                ?form rdfs:label ?formLabel .
                FILTER (lang(?formLabel) = "en") .
              } .
              ?charge rdfs:label ?chargeLabel .
              FILTER (lang(?chargeLabel) = "en") .
        
              BIND(IF(
                BOUND(?formLabel),
                CONCAT(?chargeLabel," (", ?formLabel, ")"),
                ?chargeLabel) as ?chargeWithQualifier)
            } .
        
             # CHARGE
            OPTIONAL {
              ?investigation p:P1012 ?includingNode .
              ?includingNode ps:P1012 ?including .
              OPTIONAL {
                ?includingNode pq:P1552 ?primary .
                ?primary rdfs:label ?primaryLabel .
                FILTER (lang(?primaryLabel) = "en") .
              } .
              ?including rdfs:label ?includingLabel .
              FILTER (lang(?includingLabel) = "en") .
        
              BIND(IF(
                BOUND(?primaryLabel),
                CONCAT(?includingLabel," (", ?primaryLabel, ")"),
                ?includingLabel) as ?includingLabelQual)
            } .
        
            # Characteristics
            #OPTIONAL {
            #  ?investigation wdt:P1012 ?including .
            #  ?including rdfs:label ?includingLabel .
            #  FILTER (lang(?includingLabel) = "en") .
            #} .
        
            # Ritual object (COPY THIS FOR MULTIPLE VALUE, but WITHOUT qualifier)
            OPTIONAL {
              ?investigation wdt:P8706 ?ritualObject .
              ?ritualObject rdfs:label ?ritualObjectLabel .
              FILTER (lang(?ritualObjectLabel) = "en") .
            } .
          }
        }
        
        GROUP BY ?item ?itemLabel ?investigationDate ?residenceLabel ?residenceCoords ?sexLabel ?link ?trialLabel
        ?occupationLabel ?socialClassificationLabel ?placeOfDeathLabel ?placeOfDeathCoords ?mannerOfDeathLabel
        ?detentionLocationLabel ?detentionLocationCoords ?investigation ?investigationStart ?investigationEnd';

    public function __construct(private string $baseUrl, private Client $client)
    {
    }

    public function handle()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header("Access-Control-Allow-Headers: X-Requested-With");
        header('Content-Type: application/json; charset=utf-8');

        if (file_exists('data.json') && filemtime('data.json') > strtotime("-1 day")) {
            echo file_get_contents('data.json');
            exit;
        }

        $response = $this->client->request(
            'POST',
            $this->baseUrl,
            ['body' => $this->sparqlQuery]
        );

        file_put_contents('data.json', $response->getBody());
        echo $response->getBody();
        exit;
    }
}

$sparqlQuery = new SparqlQuery(
    'https://query.wikidata.org/sparql',
    new Client(['headers' => ['Accept' => 'application/sparql-results+json', 'Content-Type' =>'application/sparql-query']])
);

$sparqlQuery->handle();