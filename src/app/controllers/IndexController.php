<?php

use Phalcon\Mvc\Controller;

use GuzzleHttp\Client;

class IndexController extends Controller
{
    public function indexAction()
    {
    }
    public function spotifyAction()
    {
        $code = $_GET['code'];
        $this->session->code = $code;
        $client_id = "978342e86e454e4f8158c9cc2dc58458";
        $client_secret = "c8f23a6494f84d7b9b5f3b747bd3e988";
        $url = "https://accounts.spotify.com";
        $headers = [
            'Content-type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($client_id . ":" . $client_secret)
        ];
        $client = new Client(
            [
                'base_uri' => $url,
                'headers' => $headers
            ]
        );
        $query = ["grant_type" => 'authorization_code', 'code' => $code, 'redirect_uri' => 'http://localhost:8080/index/spotify'];
        $response = $client->request('POST', '/api/token', ['form_params' => $query]);
        $response = $response->getBody();
        $response = json_decode($response, true);
        $access = $response['access_token'];
        $this->session->access = $access;

        $this->response->redirect('index/search');
    }
    public function searchAction()
    {
        $access = $this->session->get('access');
        $q = $this->request->getPost('q');
        $q=str_replace(' ','-',$q);
        if ($this->request->getPost('search')) {
            $type = $this->request->getPost('type');
            $count = count($type);
            if ($count == 0) {
                
                $type = 'track';
                $this->view->response = $this->response($access, $q, $type);
            } else {
                if (in_array('albums', $type)) {
                    $this->view->response_Album = $this->response($access, $q, 'album');
                    
                }
                if (in_array('artists', $type)) {
                    $response_Artist=$this->view->response_Artist = $this->response($access, $q, 'artist');
                }
                if (in_array('tracks', $type)) {
                    $this->view->response_Track = $this->response($access, $q, 'track');
                }
                if (in_array('playlists', $type)) {
                    $this->view->response_Playlist = $this->response($access, $q, 'playlist');
                  
                }
                if (in_array('episodes', $type)) {
                    $response = $this->view->response_Episodes = $this->response($access, $q, 'episode');
                    echo '<pre>';
                    print_r($response);
                    // die;
                }
            }
        }
    }
    function response($access, $q, $type)
    {
        $url = "https://api.spotify.com/v1/search?access_token=$access&q=$q&type=$type";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $response = json_decode($result, true);
        return $response;
    }
}
