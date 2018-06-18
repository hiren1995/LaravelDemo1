<?php



use Illuminate\Http\Request;



use App\NewsList;


use App\Http\Resources\NewsList as NewsResource;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {


	$users = DB::select('select * from NewsList');

	print_r($users);


	//DB::insert('insert into Users (User_FirstName, User_LastName,User_MiddleName) values (?,?,?)',['Jigar','Rawat','Somabhai']);

	//echo " Inserted";

	//$affected = DB::update('update Users set User_FirstName = "Jigi" where User_FirstName = ?', ['Jigar']);

	//print_r("Rows affected",$affected);

	//$deleted = DB::delete('delete from Users where User_FirstName = "Hello"');

	//print_r($deleted);


	exit();

    //return view('welcome');
});


Route::get('/newsList','NewsListController@show');

/*

Route::get('/newsList',function(){

	

	//$users = DB::select('select * from NewsList');

	
	$users = DB::table('NewsList')->get();

	print_r(json_encode($users));


	exit();

	//return response()->json(["response"=>"Success","Data"=>$users]);
            

});
*/