<?php 
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \stdClass;

class AlbumsController{
    
    public function getAll($request, $response, $arg){
        $q = $request->getQueryParams()["q"];
        $client = new \GuzzleHttp\Client();

        //AccountData
        $accountData = $client->request('POST','https://accounts.spotify.com/api/token',[
            'headers' => [
                'Authorization' => 'Basic Mjg5MTAxYjYwNmY0NDU2NDk3NDg3MTliMjBiNGIwZDQ6YTI0ZWY4NDY5ODNhNGY1OTlmZjMwNmVmNmI2MmFkZWM=',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'body' => 'grant_type=client_credentials'
        ]);

        $datosUser = json_decode($accountData->getBody()->getContents());
        $accessToken = $datosUser->access_token;
        
        //Artist
        //Replace space to %20
        $changeSpace = str_replace(" ","%20",$q);
        $artistData = $client->request('GET','https://api.spotify.com/v1/search?q=artist%3A'.$changeSpace.'&type=artist&limit=1',[
            'headers' => [
                'Authorization' => 'Bearer '. $accessToken,
                'Content-Type' => 'application/json'
            ]
        ]);
        
        $artistDataJson = json_decode($artistData->getBody()->getContents());
        $artistArray = $artistDataJson->artists;
        
        if(count($artistArray->items) > 0){
            $idArtist = $artistArray->items[0]->id;
            //Albums
            $artistAlbumsData = $client->request('GET','https://api.spotify.com/v1/artists/'.$idArtist.'/albums',[
                'headers' => [
                    'Authorization' => 'Bearer '. $accessToken,
                    'Content-Type' => 'application/json'
                ]
            ]);

            $artistAlbumsDataJson = json_decode($artistAlbumsData->getBody()->getContents());
            $artistAlbumsArray = $artistAlbumsDataJson->items;
            //print_r(count($artistAlbumsArray));
            
            $dataReturn = array();
            
            for($i = 0; $i < count($artistAlbumsArray); $i++){

                $obj = new stdClass();
                $obj->name = $artistAlbumsArray[$i]->name;
                $obj->released = $artistAlbumsArray[$i]->release_date;
                $obj->tracks = $artistAlbumsArray[$i]->total_tracks;
                $obj->cover = new stdClass();
                    $obj->cover->height = $artistAlbumsArray[$i]->images[0]->height;
                    $obj->cover->width = $artistAlbumsArray[$i]->images[0]->width;
                    $obj->cover->url = $artistAlbumsArray[$i]->images[0]->url;

                array_push($dataReturn, $obj);
            }

            $response->getBody()->write(json_encode($dataReturn));
            return $response
            ->withHeader('Content-Type', 'application/json');
        }else{
            $response->getBody()->write("Artist Not Found");
            return $response;
        }
    }
}