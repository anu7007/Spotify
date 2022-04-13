<?php

use Phalcon\Mvc\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class IndexController extends Controller
{
    public function indexAction()
    {
        if (!$this->session->get('email')) {
            $this->response->redirect('/login');
        }
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
        $email = $this->session->get('email');
        $data = Users::find(

            [
                'conditions' => 'email=:email:',
                'bind' => [
                    'email' => $email,
                ]

            ]
        );
        if ($data) {
            $data[0]->token = $access;
            $data[0]->refresh_token = $response['refresh_token'];
            $data[0]->update();
            // header('location:http://localhost:8080/setting');
        }


        $clients = new Client();
        $response = $clients->get('https://api.spotify.com/v1/me?access_token=' . $access . '');
        $body = $response->getBody();
        $body = json_decode($body, true);
        $id = $body['id'];
        $this->session->set("id", $id);

        $this->response->redirect('index/search');
    }
    public function searchAction()
    {
        try {
            
            $access = $this->session->get('access');
            $q = $this->request->getPost('q');
            $q = str_replace(' ', '-', $q);
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
                        $response_Artist = $this->view->response_Artist = $this->response($access, $q, 'artist');
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
            
            if ($this->request->getPost('addtoplaylist')) {
                $uri = $this->request->getpost('uri');
                $access = ($this->session->get('access'));
                $playlistid = $this->request->getpost('addtoplaylist');
                $url = "https://api.spotify.com/";
                $client = new Client(
                    [
                        'base_uri' => $url,
                        'headers' => ['Authorization' => 'Bearer ' . $access]
                    ]
                );
                $response = $client->request('POST', "/v1/playlists/" . $playlistid . "/tracks?uris=" . $uri);
                echo "Track added successfully";
                // die;
            }

            

            //code to send all playlists existing in users account as soon as user search any song so that user can see AD TO PLAYLIST button with all existing playlists
            $uri = $this->request->get('uri');
            $id = ($this->session->get('id'));
            $clientt = new Client();
           
            $response1 = $clientt->get('https://api.spotify.com/v1/users/' . $id . '/playlists?access_token=' . $access . '');
            $playlist = $response1->getBody();
            
            $playlist = json_decode($playlist, true);
            $this->view->playlist = $playlist;
            $this->view->uri = $uri;
           
        
        } catch (ClientException $e) {

           
            $email = $this->session->get('email');
            $data = Users::find(

                [
                    'conditions' => 'email=:email:',
                    'bind' => [
                        'email' => $email,
                    ]

                ]
            );
          
            $token = $this->eventManager->fire('spotify:refreshToken', $this, $data);
            // echo $token;
            $data[0]->token = $token['access_token'];
            $this->session->set("access", $token['access_token']);
            $access = $this->session->get('access');
            $q = $this->request->getPost('q');
            $q = str_replace(' ', '-', $q);
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
                        $response_Artist = $this->view->response_Artist = $this->response($access, $q, 'artist');
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
            if ($this->request->getPost('addtoplaylist')) {
                $uri = $this->request->getpost('uri');
                $access = ($this->session->get('access'));
                $playlistid = $this->request->getpost('addtoplaylist');
                $url = "https://api.spotify.com/";
                $client = new Client(
                    [
                        'base_uri' => $url,
                        'headers' => ['Authorization' => 'Bearer ' . $access]
                    ]
                );
                $response = $client->request('POST', "/v1/playlists/" . $playlistid . "/tracks?uris=" . $uri);
                echo "Track added successfully";
                // die;
            }


            // //code to send all playlists existing in users account as soon as user search any song so that user can see AD TO PLAYLIST button with all existing playlists
            // $uri = $this->request->get('uri');
            // $id = ($this->session->get('id'));
            // $clientt = new Client();
            // $response1 = $clientt->get('https://api.spotify.com/v1/users/' . $id . '/playlists?access_token=' . $access . '');
            // $playlist = $response1->getBody();
            // $playlist = json_decode($playlist, true);
            // $this->view->playlist = $playlist;
            // $this->view->uri = $uri;
        

        }
        
    }
    function response($access, $q, $type)
    {
        try {
            $url = "https://api.spotify.com/v1/search?access_token=$access&q=$q&type=$type";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);
            $response = json_decode($result, true);
            return $response;
        } catch (ClientException $e) {

            die("hii");
            $email = $this->session->get('email');
            $data = Users::find(

                [
                    'conditions' => 'email=:email:',
                    'bind' => [
                        'email' => $email,
                    ]

                ]
            );
            $token = $this->eventManager->fire('spotify:refreshToken', $this, $data);
            echo $token;
            $data[0]->token = $token;
            $this->session->set("access", $data[0]->token);
        }
        // $url = "https://api.spotify.com/v1/search?access_token=$access&q=$q&type=$type";
        // $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // $result = curl_exec($ch);
        // $response = json_decode($result, true);
        // return $response;
    }
    public function createPlaylistAction()
    {
        $id = ($this->session->get('id'));
        $url = "https://api.spotify.com/";
        $val = $this->request->getpost();
        $access = $this->session->get('access');
        $client = new Client(

            [
                'base_uri' => $url,
                'headers' => ['Authorization' => 'Bearer ' . $access]

            ]
        );
        $args = [
            'name' => $val['playlist'],
            'description' => $val['description'],
            'public' => 'false'
        ];
        $response = $client->request('POST', '/v1/users/' . $id . '/playlists', ['body' => json_encode($args)]);
        $response =  $response->getBody();
        $response = json_decode($response, true);
        echo "<pre>";
        $playlistid = ($response['id']);
        $this->session->set("playid", $playlistid);
        $this->response->redirect('index/search');
    }
}
