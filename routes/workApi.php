<?php


use App\Laravue\JsonResponse;
use App\service\WorkApi;
use Illuminate\Support\Facades\Log;


Route::group(['prefix' => 'api'], function () {
    Route::get('/work/getDpt/', function () {
        $id = request()->input('id', 1);
        $synch = new WorkApi();
        return response()->json(new JsonResponse($synch->get_department($id)))
            ->setCallback(request()->input('callback'));
    });
    Route::get('/work/saveDpt/', function () {
        $result = mb_convert_encoding($_SERVER['QUERY_STRING'], "UTF-8", "GB2312");
        parse_str($result, $output);
        $synch = new WorkApi();
        return response()->json(new JsonResponse($synch->put_work_dpt_force($output)))
            ->setCallback(request()->input('callback'));
    });
    Route::get('/work/saveUsr/', function () {
        $result = mb_convert_encoding($_SERVER['QUERY_STRING'], "UTF-8", "GB2312");
        parse_str($result, $output);
        $synch = new WorkApi();
        return response()->json(new JsonResponse($synch->put_work_user($output)))
            ->setCallback(request()->input('callback'));
    });
    Route::get('/work/savePL/', function () {
        $param = request()->input('dpts');
        Log::info($param);
        $error = [];
        $synch = new WorkApi();
        if (is_array($param) && count($param) > 0) {
            foreach ($param as $val) {
                $res = $synch->put_work_dpt_force($val);
                if ($res . errcode != 0) {
                    $error[] = $res . errmsg;
                }
            }
        }
        if (count($error) == 0) {
            return response()->json(new JsonResponse("操作成功"))
                ->setCallback(request()->input('callback'));
        } else {
            return response()->json(new JsonResponse("操作失败"))
                ->setCallback(request()->input('callback'));
        }
    });
    Route::get('/work/delDpt/', function () {
        $synch = new WorkApi();
        $res = $synch->del_dpt(request()->input("id"));
        return response()->json(new JsonResponse($res))
            ->setCallback(request()->input('callback'));
    });
    Route::get('/work/delUsr/', function () {
        $synch = new WorkApi();
        $res = $synch->del_user(request()->input("userid"));
        return response()->json(new JsonResponse($res))
            ->setCallback(request()->input('callback'));
    });
    Route::get('/work/getUsrDetail/', function () {
        $synch = new WorkApi();
        $res = $synch->get_user(request()->input("userid"));
        return response()->json(new JsonResponse($res))
            ->setCallback(request()->input('callback'));
    });
    Route::get('/work/getUsrList/', function () {
        $synch = new WorkApi();
        $res = $synch->get_dptmt_member(request()->input("id"));
        return response()->json(new JsonResponse($res))
            ->setCallback(request()->input('callback'));
    });

});

