<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApiController extends Controller
{
public function sms_receipt3(Request $request)
{
        Log::info(print_r($_POST,true));
        Log::info("Request: ".(String)$request);

        Log::info(print_r($_GET,true));

        if(!empty($_GET))
        {
            $dateTime = Carbon::now("Africa/Lagos");
            $date_now = $dateTime->format("d-m-Y, g:i a");

            $port = $_GET["port"];
            $receiver = "";

            if($port == "7.09"){
                $receiver = "09155210689"; // GLO
            }
            else if($port == "6.01"){
                $receiver = "09083360519"; // 9MOBILE
            }
            else if($port == "5.07"){
                $receiver = "09017698517"; // AIRTEL
            }
            else if($port == "2.05"){
                $receiver = "09137450540"; // MTN
            }

                $content_split_array = preg_split("/\r\n|\n|\r/", $_GET["content"]);
                if(!empty($content_split_array)){
                        $sender = trim(explode(":",$content_split_array[0])[1]);
                        $receiver_port = str_replace("\"","",trim(explode(":",$content_split_array[1])[1]));
                        $smsc = trim(explode(":",$content_split_array[2])[1]);
                        $scts = trim(explode(":", $content_split_array[3])[1]);
                        $sms_content = $content_split_array[5];
                        if(count($content_split_array) > 6){
                                $sms_content.= $content_split_array[6];
                        }

                        Log::info("Sender: ".$sender." - Receiver Port: ".$receiver_port." - SMSC: ".$smsc." - SCTS: ".$scts." - Content: ".$sms_content);

                        $data = array("date" =>$date_now, "ref"=>$scts,"keyword"=>"", "message"=>$sms_content, "phone"=>$sender, "from"=>$sender, "sender"=>$sender, "recipient"=>$receiver, "to"=>$receiver, "sim"=>$receiver, "server"=>$port);
           $data_string = json_encode($data);
           Log::info("Data pushed to Azure from gateway: ".print_r($data,true));

           $ch = curl_init('http://20.55.2.73:5400/api/SimTransactions/smswebhook');
           curl_setopt($ch, CURLOPT_POST, true);
           curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
           curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
           curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
           curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-type: application/x-www-form-urlencoded'));
                   $result = curl_exec($ch);
          Log::info("Result from called webhook: ".print_r($result,true));
                if ($result === false)
                {
                     $result = curl_error($ch);
                }
                curl_close($ch);
                }
        }

}
}
