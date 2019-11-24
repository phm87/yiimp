<?php
// https://github.com/KomodoPlatform/developer-docs/blob/master/docs/basic-docs/atomicdex/atomicdex-tutorials/atomicdex-walkthrough.md
function atomicdex_api_query($method, $params=array(), $returnType='object')
{
//    curl --url "http://127.0.0.1:7783" --data "{\"userpass\":\"$userpass\",\"method\":\"my_balance\",\"coin\":\"MORTY\"}"
	require_once('/etc/yiimp/keys.php');
	$uri = "http://127.0.0.1:7783";

	$json_body = "{\"userpass\"=\"".EXCH_ATOMICDEX_RPCPASSWORD."\",\"method\":\"".$method."\"";
	if ($method == "electrum")	{
	  foreach ($params as $p => $v)   {
    		$json_body .= ",";
		if ($p == "servers")	{
			$first = true;
			$json_body .= "\"servers\":[";
			foreach ($v as $pp => $vv)   {
				if ($first == false)	$json_body .= ",";
				$json_body .= "{\"url\":\"".$vv['url']."\"}";
				$first = false;
				}
			$json_body .= "]";
			}
		  else
	    		$json_body .= "\"$p\":\"$v\"";
    		}
	}
	else	{
		foreach ($params as $p => $v)   {
    			$json_body .= ",";
    			$json_body .= "\"$p\":\"$v\"";
    			}
	}
  $json_body .= "}";
  $ch = curl_init($uri);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json_body);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $data = curl_exec($ch);
  $res = json_decode($data);
//        unset($headers);
  if(empty($res)) {
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    debuglog("atomicdex-api: $method failed ($status) ".strip_data($data).' '.curl_error($ch));
    }
  curl_close($ch);
  return $res;
}

function atomicdex_api_user($method, $url_params=array(), $json_body='')
{
	return atomicdex_api_query($method, $url_params);
}
