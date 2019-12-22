<?php

use App\Laravue\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use App\Service\OSS;


Route::group(['prefix' => 'api'], function () {
    /*
    Route::get('/attachments', function () {
        //Storage::put('/attachment/download/baidu.jpg', file_get_contents("https://www.baidu.com/img/bd_logo1.png?where=super"));
        $files = Storage::allFiles('/attachment/download/');
        return response()->json(new JsonResponse($files));

    });
    Route::get('/preparation_files/{gymcode?}', function ($gymcode = '500004') {
        //?表示可选，但必须有默认值
        $dir = "/preparationFiles/{$gymcode}";
        $files = Storage::allFiles($dir);
        return response()->json(new JsonResponse($files));
    });*/
    Route::get('/oss/{id?}', function ($id = "") {
        $resourceUrl = rtrim(Request::input("resourceUrl"),"/");
        $files = OSS::oss_list("$resourceUrl/$id");
        if(empty($res)) $files="nodata";
        return response()->json(new JsonResponse($files))
            ->setCallback(request()->input('callback'));
    });
    Route::delete('/oss/{id}', function ($id) {
        $resourceUrl = trim(Request::input("resourceUrl"),"/");
        $res = OSS::deleteObject("${resourceUrl}/${id}");
        return response()->json(new JsonResponse($res))
        ->setCallback(request()->input('callback'));
    });
    Route::post('/oss', function () {
        if (Request::hasFile('file')) {
                $resourceUrl = trim(Request::input("resourceUrl"),"/");
                $file = Request::file('file');
                // 重命名文件
                $fileName = Request::input("filename").".".$file-> extension();
                if(empty($fileName)){
                    $fileName = $file->getClientOriginalName();
                }
                $res = OSS::upload("${resourceUrl}/${fileName}",$file->getRealPath());
                // Log::info($res);
                return response()->json(new JsonResponse($res))
                    ->setCallback(request()->input('callback'));
        } else {
                return response()->json(new JsonResponse("","no file"))
                    ->setCallback(request()->input('callback'));
        }
    });


});
?>
