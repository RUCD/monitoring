<?php

use Illuminate\Http\Request;
use App\Server;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('record/{server}', function(Request $request, Server $server) {
    if ($server->token !== $request->get("token", "")) {
        abort(403);
    }

    $data = $request->all();
    $data["server_id"] = $server->id;
    $data["time"] = time();

    $collection = Mongo::get()->monitoring->records;
    $collection->insertOne($data);

    return "ok";
});