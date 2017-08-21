<?php            
            $url='https://gcm-http.googleapis.com/gcm/send';
            $api_key = 'AIzaSyD4g9foVHVqrKZ74d8GowNhqH5ZV9nQgFs'; // change this by your API key
            $message = "android push test";
            $device_token = 'APA91bEg9rDVU6Bfi8SY4oExceBEdvh1DBZ3aZ2W6Qajp1x8ocLCINSoZcbah52ClmogX88XKsnD8UGfIA68PofmnA7hH2KEnpsffoHWEPLXKg0n_RsFvpQbezzRXZjqGLxWhhNVymSZ1';
            /// --- Common PUSH Set ---
            $data=array(
                "notification" => array("title" => "HandCrowd", "text" => $message),
                "delay_while_idle"=> true,
                "to" => $device_token
            );
            /*
            $data=array(
                'data' => $message,
                'dry_run'=>false,
                "delay_while_idle"=> true,
                'registration_ids' => array($device_token)
            );*/

            $curl = curl_init($url);
            $headers = array("Content-Type:" . "application/json", "Authorization:" . "key=" . $api_key);
            curl_setopt_array($curl, array (
                CURLOPT_HTTPHEADER =>$headers,      
                CURLOPT_ENCODING => "gzip" ,
                CURLOPT_FOLLOWLOCATION => true ,
                CURLOPT_RETURNTRANSFER => true ,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => json_encode($data)
            ));
            $result = curl_exec($curl); 
            try {
                $r = json_decode($result);
                if ($r->success == 1)
                    print "Push notification delivered to Android. device_token:" . $device_token;
            }
            catch (Exception $e) {

            }

            curl_close ($curl); 
?>
