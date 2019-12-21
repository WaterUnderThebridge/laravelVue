<?php

use Illuminate\Support\Facades\Log;

/**
 * Created by PhpStorm.
 * User: TONY
 * Date: 2017/12/13
 * Time: 22:24
 */
namespace App\Service;

class workApi{
    private $_access_token;
    private $_baseUrl;
    private $_corpid;
    private $_secret;
    private $_env;
    private $_res;

    /**
     * User constructor.
     * @param $env
     */
    public function __construct($env='prod')
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE');
        header('Content-Type:application/json; charset=utf-8');
        date_default_timezone_set("Asia/Shanghai");
//        $result = mb_convert_encoding($_SERVER['QUERY_STRING'], "UTF-8", "GB2312");
//        parse_str($result);

        $this->_baseUrl = "https://qyapi.weixin.qq.com/cgi-bin";
        $this->_env = $env;
        if($env ==='prod'){//正式环境
            $this->_corpid = "ww69112e44cbc422bf";
            $this->_secret = "rNhaYqQ-NrjvHczRLW52PeuMIYQhbm2goIarjpEsPoU";
        }else{//测试环境
            $this->_corpid = "ww0b034a3590df21fc";
            $this->_secret = "VrJ8K60RQbT1HZQu_5j6SL2xBC7PDLUPw4vcseFeuoc";
        }
        $this->get_token();
    }

    function get_token(){
        if(empty($this->_access_token)){
            if(isset($_SESSION['access_token'])){
                $this->_access_token = $_SESSION['access_token'];
            }else if(!empty($_REQUEST['_access_token'])){
                $this->_access_token = $_REQUEST['_access_token'];
            }else{
                $url = "{$this->_baseUrl}/gettoken?";
                $url .= "corpid={$this->_corpid}&corpsecret={$this->_secret}";
                //file_put_contents('e:\LOG\log_work.txt', "Time: ".date('Y-m-d H:i:s')."  ".$url."\r\n", FILE_APPEND);
                $result = $this->getCurl($url);
                //die(var_dump($result));
                if(!$result['errcode']){
                    $this->_access_token = $result['access_token'];
                }
            }
            if(!session_id()) session_start();
            $_SESSION['access_token'] = $this->_access_token;
        }
    }
    //部门
    function get_department($departmentid){
        $url = "{$this->_baseUrl}/department/list?";
        $url .= "access_token={$this->_access_token}&id=${departmentid}";
        return $this->getCurl($url);
    }
    function put_work_dpt($arrObj){
        $tableId = 'department';
        $recId = $arrObj['id'];
        $arrObj['parentid']=intval($arrObj['parentid']);;
        $res = $this->put_work($tableId,$recId,$arrObj);
        Log::info($arrObj);
        return $res;
    }

    function put_work_dpt_force($arrObj){
        $tableId = 'department';
        $recId = $arrObj['id'];
        $arrObj['parentid']=intval($arrObj['parentid']);
        file_put_contents('e:\LOG\log_work5.txt', "时间: ".date('Y-m-d H:i:s')."save:".json_encode($arrObj)."\r\n", FILE_APPEND);

        $res = $this->put_work($tableId,$recId,$arrObj,118);
        Log::info($res);
        return $res;
    }

    //用户
    function del_user($userid){
        $url = "{$this->_baseUrl}/user/delete?";
        $url .= "access_token={$this->_access_token}&userid=${userid}";
        return $this->getCurl($url);
    }

    function del_dpt($userid){
        $url = "{$this->_baseUrl}/department/delete?";
        $url .= "access_token={$this->_access_token}&id=${userid}";
        return $this->getCurl($url);
    }

    function get_dptmt_member($dep_id,$recursive=0){
        $url = "{$this->_baseUrl}/user/simplelist?";
        $url .= "access_token={$this->_access_token}&department_id=${dep_id}&fetch_child=${recursive}";
        //die($url);
        return $result = $this->getCurl($url);
    }
    function get_user($userid){
        $url = "{$this->_baseUrl}/user/get?";
        $url .= "access_token={$this->_access_token}&userid=${userid}";
        return $this->getCurl($url);
    }
    function put_work_user($arrObj,$action=""){
        if(!empty($arrObj['department'])){
            $deptIds = $arrObj['department'];
            $deptNames = $arrObj['gymname'];
            foreach($deptIds as $key => $dtp){
                //如果部门不存在，则新建
                if($dtp-500003>0 && $dtp-600004<0){
                    $post_data = array("name"=>$deptNames[$key],"id"=>$dtp,"parentid"=>1);
                    $out = print_r($post_data, true);
                    if(!strpos($deptNames[$key],'leave')){
                        $this->put_work_dpt($post_data);
                    }
                    file_put_contents('e:\LOG\log_work3.txt', "时间: ".date('Y-m-d H:i:s')."save:".$out."\r\n", FILE_APPEND);
                }
            }

        }
        //$post_data = array("name"=>"离职员工","id"=>"14444","parentid"=>1);
        //$this->put_work_dpt($post_data);
        $tableId = 'user';
        $recId = $arrObj['userid'];
        return $this->put_work($tableId,$recId,$arrObj,$action);
    }


    function exist($tableId,$recId){
        /*
          "errcode":60111 ->false;"errcode":0 ->true
        */
        file_put_contents('e:\LOG\log_work2.txt', "时间: ".date('Y-m-d H:i:s')."save_start:".$tableId.$recId."\r\n", FILE_APPEND);
        if($tableId=='user'){
            $this->res = $this->get_user($recId);
            if($this->res['errcode']==0){
                return true;
            }
        }
        if($tableId=='department'){
            $this->res = $this->get_department($recId);
            if($this->res['errcode']==0){
                return true;
            }
        }
        //die(var_dump($ret));
        return false;
    }
    function put_work($tableId,$recId,$arrObj,$action=""){
        //die(var_dump($this->exist_user($userid)));
        //file_put_contents('e:\LOG\log_work3.txt', "时间: ".date('Y-m-d H:i:s')."save:".json_encode($arrObj)."\r\n", FILE_APPEND);
        //file_put_contents('e:\LOG\log_work4.txt', "时间: ".date('Y-m-d H:i:s')."${tableId},${recId} \r\n", FILE_APPEND);
        if((!empty($recId)&&$this->exist($tableId,$recId))|| $action==2){
            $url = "{$this->_baseUrl}/{$tableId}/update?";
            file_put_contents('e:\LOG\log_work2.txt', "时间: ".date('Y-m-d H:i:s')."save_start:".$result."\r\n", FILE_APPEND);
            //clear parentid of dpt,not update parentid
            if($tableId=='department'&&$action!=118){
                unset($arrObj['parentid']);
            }
            if($tableId=='user'){
                if(!empty($arrObj['department'])){
                    //read depart of wechat
                    $depts = $this->res['department'];
                    file_put_contents('e:\LOG\log_work3.txt', "时间: ".date('Y-m-d H:i:s')."save:".json_encode($depts)."\r\n", FILE_APPEND);
                    foreach($depts as $dtp){
                        //从查询的部门中,保留原fiona手建部门
                        if(($dtp-500004<0 || $dtp-600004>0) && $dtp!=3 && $dtp!=11 && $dtp!=12 && $dtp!=13 && $dtp!=14 && $dtp!=10004 && $dtp!=14444){
                            array_push($arrObj['department'],$dtp);
                            file_put_contents('e:\LOG\log_work2.txt', "时间: ".date('Y-m-d H:i:s')."save:".$dtp."\r\n", FILE_APPEND);
                        }
                    }
                }else{
                    $arrObj['department']=array("14444");
                    file_put_contents('e:\LOG\log_work2.txt', "时间: ".date('Y-m-d H:i:s')."save:".var_export($arrObj['department'],true)."\r\n", FILE_APPEND);
                }
                //remove dpt 1 when more than 1 dpt
                if(count($arrObj['department'])>1){
                    $arrObj['department']=array_diff($arrObj['department'],[1]);
                    $arrObj['department']=array_values($arrObj['department']);
                }
            }
        }else{
            $url = "{$this->_baseUrl}/{$tableId}/create?";
            if($tableId=='user'){
                if($arrObj['enable']==0) return array("errcode"=>0,"errmsg"=>"not created");
            }
            if($tableId=='user' && $this->_env=="prod"){
                $arrObj["to_invite"]=true;
            }
            if($tableId=='department' && empty($arrObj['parentid'])){
                $arrObj['parentid']=1;
            }
        }

        $url .= "access_token=".$this->_access_token;
        file_put_contents('e:\LOG\log_work2.txt', "时间: ".date('Y-m-d H:i:s')."save_start:".$url."\r\n", FILE_APPEND);
        //die($url);
        if($tableId=='department'){
            $out = print_r($arrObj, true);
            file_put_contents('e:\LOG\log_work_dpt2.txt', "时间: ".date('Y-m-d H:i:s')."save:".$out."\r\n", FILE_APPEND);
        }
        $ret = $this->postCurl($url,$arrObj);
        return $ret;
    }
    function getCurl($url,$type='work',$debug=false){
        $con = curl_init($url);
        curl_setopt($con, CURLOPT_HEADER, false);
        curl_setopt($con, CURLOPT_POST, false);
        curl_setopt($con, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($con, CURLOPT_TIMEOUT,40);
        curl_setopt($con, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($con, CURLOPT_SSL_VERIFYHOST, FALSE);
        $ret = curl_exec($con);
        $encoding = mb_detect_encoding($ret, array("ASCII","UTF-8","GB2312","GBK"));
        if($encoding=="CP936"){
            $ret = mb_convert_encoding($ret, "UTF-8", "GB2312");
            //echo($ret);
        }
        //var_dump($ret);
        $http_status = curl_getinfo($con, CURLINFO_HTTP_CODE);
        //记录请求日志
        $arrLog = array(
            'type' => 'Get',
            'url' => $url,
            'http_status' => $http_status,
            'curl_error' => curl_error($con),
            'result' => $ret
        );
        curl_close($con);

        $ret = json_decode($ret,true);
        $this->token_overdue($ret['errcode']);
        return $ret;
    }
    function postCurl($url, $arrParams, $format='json', $type='work', $timeout = 15, $retry = 3){
        $con = curl_init((string)$url);
        if('json' === $format){// header
            $arrParams = json_encode($arrParams,JSON_UNESCAPED_UNICODE); //json字串化，但中文不转义
            curl_setopt($con, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        }else{
            curl_setopt($con, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        }
        curl_setopt($con, CURLOPT_POST, true);
        curl_setopt($con, CURLOPT_POSTFIELDS, $arrParams);
        curl_setopt($con, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($con, CURLOPT_TIMEOUT,4);
        curl_setopt($con, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($con, CURLOPT_SSL_VERIFYHOST, FALSE);

        $ret = curl_exec($con);
        file_put_contents("e:\LOG\log_${type}.txt", "Time: ".date('Y-m-d H:i:s')."  ".json_encode($ret)."\r\n", FILE_APPEND);
        $http_status = curl_getinfo($con, CURLINFO_HTTP_CODE);
        while($http_status != 200 && $retry--){
            $ret = curl_exec($con);
            $http_status = curl_getinfo($con, CURLINFO_HTTP_CODE);
        }
        //记录请求日志
        $arrLog = array(
            'type' => 'Post',
            'url' => $url,
            'arrParams' => $arrParams,
            'http_status' => $http_status,
            'curl_error' => curl_error($con),
            'retry' => $retry,
            'result' => $ret,
        );
        // 记录log
        //file_put_contents("e:\LOG\log_${type}.txt", "Time: ".date('Y-m-d H:i:s')."  ".json_encode($arrLog)."\r\n", FILE_APPEND);
        curl_close($con);

        $ret = json_decode($ret,true);
        $this->token_overdue($ret['errcode']);
        return $ret;
    }

    function token_overdue($code){
        if($code==60011||$code==40014){
            $_SESSION['access_token'] =null;
        }
    }
    //参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
    function curl_request($url,$post='',$cookie='', $returnCookie=0){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        }else{
            return $data;
        }
    }
}
