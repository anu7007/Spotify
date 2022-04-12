<?php

namespace App\Listeners;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Stream;
use Phalcon\Di\Injectable;

use Phalcon\Events\Event;

class notificationListeners extends Injectable
{

public function refreshToken(Event $event, $values, $data)
    {
        echo "hii";
        die;

       
        $refresh=$data[0]->refresh;
        $clientId = "978342e86e454e4f8158c9cc2dc58458";
        $clientSecret = "c8f23a6494f84d7b9b5f3b747bd3e988";
        $url = "https://accounts.spotify.com";

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($clientId . ":" . $clientSecret)
        ];
        $client = new Client(
            [

                'base_uri' => $url,
                'headers' => $headers
            ]
        );

        $query = ["grant_type" => 'refresh_token', 'refresh_token' => $refresh];
        $response = $client->request('POST', '/api/token', ['form_params' => $query]);
        $response =  $response->getBody();
        $response = json_decode($response, true);
        // echo "<pre>";
        // print_r($response);
        // die;
        return $response['access_token'];


    }
}