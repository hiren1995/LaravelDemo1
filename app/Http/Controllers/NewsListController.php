<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;

//import models which we gonna use

use App\NewsList;

//import Resource which we gonna use

//use App\Http\Resources\NewsList as NewsListResource;

//import http request

//use App\Http\Requests;
//use Illuminate\Support\Facades\URL;
//use Illuminate\Support\Facades\DB;

class NewsListController extends Controller
{


    protected $authenticationModel;

    public function __construct(Request $request)
    {
        parent::__construct($request);     
        $this->authenticationModel= new \App\NewsList();
    }


    
    public function show(Request $request)
    {
     
        //$records = DB::table('Users')->get();
        //print_r($records);
        //return response()->json($records);

        
        //$users = NewsList::test();

        //eturn response()->json($users);


        echo $this->authenticationModel->test();

        //return view ('welcome')->with('records',$records);

    }

    
}

?>