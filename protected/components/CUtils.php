<?php
/**
 * Description of CUtils
 *
 * @author Nguyen Chi Thuc
 */
class CUtils {
    public static function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }
    public static function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

	public static function logInfo($msg, $category="Phatam") {
		Yii::log($msg, CLogger::LEVEL_INFO, $category);
	}

    public static function logError($msg, $category="Phatam") {
        Yii::log($msg, CLogger::LEVEL_ERROR, $category);
    }

    public static function logDebug($msg, $category="Phatam") {
        Yii::log($msg, CLogger::LEVEL_TRACE, $category);
    }
    /**
     * Check if $params in $arr invalid,
     * @param $arr
     * @return bool
     */
    public static function checkRequiredParams($arr) {
        foreach($arr as $param) {
            if (!isset($param) || empty($param)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param float $number
     * @return string (1.000)
     */
    public static function numberFormat($number){
        return number_format($number,0,'','.');
    }

    /**
     * @param double $price
     * @return String
     */
    public static function formatPrice($price) {
        if (!isset($price) || empty($price)) {
            return "0";
        }
        return "".number_format($price,0,',','.');
    }

	public static function randomString($length=32, $chars="abcdefghijklmnopqrstuvwxyz0123456789") {
		$max_ind = strlen($chars)-1;
		$res = "";
		for ($i =0; $i < $length; $i++) {
			$res .= $chars{rand(0, $max_ind)};
		}

		return $res;
	}

	public static function checkIPRange($ip) {
		$ipRanges = Yii::app()->params['ipRanges'];
		foreach ($ipRanges as $range) {
			if (CUtils::cidrMatch($ip, $range)) {
				return true;
			}
		}
		return false;
	}

	public static function checksum($str) {
		return md5($str);
	}

	public static function timeElapsedString($ptime) {
		$etime = time() - $ptime;

		if ($etime < 1) {
			return '0 giây';
		}

		$a = array( 12 * 30 * 24 * 60 * 60  => 'năm',
				30 * 24 * 60 * 60       => 'tháng',
				24 * 60 * 60            => 'ngày',
				60 * 60                 => 'giờ',
				60                      => 'phút',
				1                       => 'giây'
		);

		foreach ($a as $secs => $str) {
			$d = $etime / $secs;
			if ($d >= 1) {
				$r = round($d);
				return $r . ' ' . $str . ' trước';
			}
		}
	}

	public static function convertMysqlToTimestamp($dateString) {
		$format = '@^(?P<year>\d{4})-(?P<month>\d{2})-(?P<day>\d{2}) (?P<hour>\d{2}):(?P<minute>\d{2}):(?P<second>\d{2})$@';
		preg_match($format, $dateString, $dateInfo);
		$unixTimestamp = mktime(
				$dateInfo['hour'], $dateInfo['minute'], $dateInfo['second'],
				$dateInfo['month'], $dateInfo['day'], $dateInfo['year']
		);
		return $unixTimestamp;
	}

	public static function timeElapsedStringFromMysql($dateString) {
		$ptime = CUtils::convertMysqlToTimestamp($dateString);
		return CUtils::timeElapsedString($ptime);
	}

	public static function cidrMatch($ip, $range) {
		list ($subnet, $bits) = explode('/', $range);
		$ip = ip2long($ip);
		$subnet = ip2long($subnet);
		$mask = -1 << (32 - $bits);
		$subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
		return ($ip & $mask) == $subnet;
	}

	/**
	 *
	 * @param string $mobileNumber
	 * @param int type format: 0: format 84xxx, 1: format 0xxxx, 2: format xxxx
	 * @return String valid mobile
	 */
	public static function validateMobile($mobileNumber, $typeFormat = 0){
		$valid_number = '';

        // Remove string "+"
        $mobileNumber = str_replace('+84', '84', $mobileNumber);

        //TODO: for testing: dung so dung cua VMS goi qua charging test ko thanh cong
        if(preg_match('/^(84|0)(986636879)$/', $mobileNumber, $matches)){
            return "84986636879";
        }

        if(preg_match('/^(84|0)(90|93|120|121|122|126|128)\d{7}$/', $mobileNumber, $matches)){
			/**
			 * $typeFormat == 0: 8491xxxxxx
			 * $typeFormat == 1: 091xxxxxx
			 * $typeFormat == 2: 91xxxxxx
			 */
			if($typeFormat == 0){
				if ($matches[1] == '0' || $matches[1] == ''){
					$valid_number = preg_replace('/^(0|)/', '84', $mobileNumber);
				}else{
					$valid_number = $mobileNumber;
				}
			}else if($typeFormat == 1){
				if ($matches[1] == '84' || $matches[1] == ''){
					$valid_number = preg_replace('/^(84|)/', '0', $mobileNumber);
				}else{
					$valid_number = $mobileNumber;
				}
			}else if ($typeFormat == 2){
				if ($matches[1] == '84' || $matches[1] == '0'){
					$valid_number = preg_replace('/^(84|0)/', '', $mobileNumber);
				}else{
					$valid_number = $mobileNumber;
				}
			}

		}
		return $valid_number;
	}

    public static function clientIP() {
        if (!empty($_SERVER['HTTP_CLIENTIP'])) {
            return $_SERVER['HTTP_CLIENTIP'];
        }

        if (!empty($_SERVER['X_REAL_ADDR'])) {
            return $_SERVER['X_REAL_ADDR'];
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return gethostbyname(gethostname()); // tra ve ip local khi chay CLI
    }

    public static function dbNow() {
        return new CDbExpression('NOW()');
    }

    public static function dbNull() {
        return new CDbExpression('NULL');
    }

    /**
     * @param $profile VideoProfile
     * @param $subscriber Subscriber
     * @param $clientIP Ip client
     * @param $viewType View type: SubscriberViewLog::VIEW_TYPE_LIVE_NOT_FREE ...
     * @param null $package_asm id SubscriberPackageAsm
     * @return string
     * @throws Exception
     */
    public static function makeVideoStreamUrl($profile, $subscriber, $clientIP, $viewType, $package_asm = null) {
        $streamURL = "";
        $serverStreaming = Yii::app()->params['serverStreaming'];

        if($profile == null){
            throw new Exception('Profile null');
        }

        if($subscriber == null){
            throw new Exception('Subscriber is null');
        }

        // Build Streaming link URL
        switch ($profile->protocol){
            case VideoProfile::PROTOCOL_HLS:
                $streamURL .= 'http://'.$serverStreaming['vod_hls'].'/'.$profile->folder.'/'.$profile->stream_url.'.ssm/'.$profile->stream_url.'.m3u8';
                break;
            case VideoProfile::PROTOCOL_MMS:
                $streamURL .= 'http://'.$serverStreaming['vod_hls'].'/'.$profile->folder.'/'.$profile->stream_url.'.ssm/'.$profile->stream_url.'.m3u8';
                break;
            case VideoProfile::PROTOCOL_RTSP:
                $streamURL .= 'rtsp://'.$serverStreaming['vod_rtsp'].$profile->folder.'/'.$profile->stream_url.'.3gp';
                break;
            default:
                $streamURL .= 'http://'.$serverStreaming['vod_hls'].'/'.$profile->folder.'/'.$profile->stream_url.'.m3u8';
                break;
        }
        // Add token authentication access
        $security = self::getSecurityStreamUrl($subscriber->id, $clientIP, $profile->id);
        $streamURL .='?sessionID='.$security['session'].'&token='.$security['token'];
        /* @var $streamingLog SubscriberViewLog */
        $streamingLog = SubscriberViewLog::addStreamingLog($viewType, $subscriber, $profile->video_id, $streamURL, $security['session'], $clientIP, $package_asm);
        $profile->stream_url = $streamURL;
        return $streamURL;
    }

    /**
     * @param $token (Token + profile_id)
     * @param $sessionStream
     * @param $clientIP
     * @return bool
     */
    public static function verifyStream($token, $sessionStream, $clientIP){
        //TODO them phan check thgian cho token: sau delta second thi deny
        //Parse Token de lay dc token clean va profileID
        $arr = CUtils::parseTokenStream($token);
        if(!isset($arr['profileid'])){
            return false;
        }
        $profileid = $arr['profileid'];
        $tmpToken = self::makeTokenStream($sessionStream, $clientIP,$profileid);
        if($token == $tmpToken){
            CUtils::log('Valid Token: '.$tmpToken);
            return true;
        }else{
            CUtils::log('invalid Token: '.$tmpToken);
            return false;
        }
    }

    /**
     * @param $profile LiveProfile
     * @param $subscriber Subscriber
     * @param $clientIP
     * @param $viewType -
     * @param $package_asm - id cua package_asm
     * @return null|string
     */
    public static function makeLiveStreamUrl($profile, $subscriber, $clientIP, $viewType, $package_asm = null) {
        $streamURL = "";
        $serverStreaming = Yii::app()->params['serverStreaming'];

        if($profile == null){
            throw new Exception('Profile null');
        }

        if($subscriber == null){
            throw new Exception('Subscriber is null');
        }

        // Build Streaming link URL
        switch ($profile->protocol){
            case LiveProfile::PROTOCOL_HLS:
                $streamURL .= 'http://'.$serverStreaming['live_hls'].'/'.$profile->stream_url.'/'.$profile->folder.'.m3u8';
                break;
            case LiveProfile::PROTOCOL_MMS:
                $streamURL .= 'http://'.$serverStreaming['live_hls'].'/'.$profile->stream_url.'/'.$profile->folder;
                break;
            case LiveProfile::PROTOCOL_RTSP:
                $streamURL .= 'rtsp://'.$serverStreaming['live_rtsp'].'/'.$profile->stream_url.'/'.$profile->folder;
                break;
            case LiveProfile::PROTOCOL_RTMP:
                $streamURL .= 'rtmp://'.$serverStreaming['live_rtsp'].'/'.$profile->stream_url;
                break;
            default:
                $streamURL .= 'http://'.$serverStreaming['live_hls'].'/'.$profile->stream_url.'/'.$profile->folder.'.m3u8';
                break;
        }
        // Add token authentication access
        $security = self::getSecurityStreamUrl($subscriber->id, $clientIP, $profile->id);
        $streamURL .='?sessionID='.$security['session'].'&token='.$security['token'];
        /* @var $streamingLog SubscriberViewLog */
        $streamingLog = SubscriberViewLog::addStreamingLog($viewType, $subscriber, $profile->live_channel_id, $streamURL, $security['session'], $clientIP, $package_asm);
        $freeTime = ($package_asm == null)?0:$streamingLog->packageAsm->free_time_amount;
        if($viewType == SubscriberViewLog::VIEW_TYPE_LIVE_NOT_FREE){
            $streamURL .= '&maxtime='.$freeTime;
        }

        $profile->stream_url = $streamURL;
        return $streamURL;
    }

    public static function getSecurityStreamUrl($userid, $clientIP, $profileID){

        //Create session
        $session = self::makeSessionStream($userid);
        //Create token
        $token = self::makeTokenStream($session,$clientIP, $profileID);
        return array('session' => $session, 'token' => $token);
    }

    /**
     * Token = md5(secretKey+clientIP+session+profileID)
     * @param $session
     * @param $clientIP
     * @param $rofileID
     * @return string: token+profileid.
     */
    public static function makeTokenStream($session,$clientIP, $profileID){
        $secretKey = Yii::app()->params['secretKey'];
        $token = self::checksum($secretKey.$clientIP.$session.$profileID);
        CUtils::log('SecretKey:'.$secretKey.'|session:'.$session.'|ClientIP:'.$clientIP.'|Token:'.$token);
        return $token.$profileID;
    }

    /**
     * @param $image VideoImage|String
     */
    public static function makeImageUrl($image) {
        $url = "";
        if (is_string($image) || $image == null) {
            $url = $image;
        }
        else {
            $url = $image->url;
        }

        if (strpos($url, "http://") == 0 || strpos($url, "https://") == 0) {

        }
        else {

        }

        if (is_string($image) || $image == null) {
        }
        else {
            $image->url = $url;
        }
        $url =   CommonConst::HOST_IMAGE_ROOT.$url;
        return $url;

    }

    /**
     * @param $video Video|String
     */
    public  static function makeSubtitleUrl($video) {
        if (is_string($video) || $video == null) {
            return "*** TBD ***: ".$video;
        }
        else {
            $video->subtitle_url = "*** TBD ***: ".$video->subtitle_url;
            return $video->subtitle_url;
        }
    }

    public static function getLiveSession() {
        $result['sessionId'] = "*** TBD ***";
        $result['error'] = 0;
        $result['message'] = "success";
        return $result;
    }

    public static function strToHex($string){

        $hex = '';

        for ($i=0; $i<strlen($string); $i++){

            $ord = ord($string[$i]);

            $hexCode = dechex($ord);

            $hex .= substr('0'.$hexCode, -2);

        }

        return strToUpper($hex);

    }

    public static function hexToStr($hex){

        $string='';

        for ($i=0; $i < strlen($hex)-1; $i+=2){

            $string .= chr(hexdec($hex[$i].$hex[$i+1]));

        }

        return $string;

    }

    /**
     * @param $userid int
     * @return session String (session(8 string) + userid)
     */
    public static function  makeSessionStream($userid){
        $session = self::randomString(8) . $userid;
        return $session;
    }

    /**
     * @param $str Session = random(8)+userid
     * @return int //User ID
     */
    public static function parseSessionStream($str){
        $result = array();
        if(empty($str) || strlen($str) <= 8){
            return $result;
        }

        $result['session'] = substr($str,0,8);
        $result['userid'] = substr($str,8);
        return  intval($result['userid']);
    }

    /**
     * @param $str
     * @return array|int
     */
    public static function parseTokenStream($str){
        $result = array();
        if(empty($str) || strlen($str) <= 32){
            return $result;
        }

        $result['token'] = substr($str,0,32);
        $result['profileid'] = substr($str,32);
        return  $result;
    }

    /**
     * @param $profile VideoProfile
     */
    public  static function makeStreamUrl($profile) {
        $profile->stream_url = "*** TBD ***: ".$profile->stream_url;
    }

    public static function getStartDate($startDate){
        $date = new DateTime($startDate);
        $date->setTime(00, 00, 00);
        return $date->format('Y-m-d H:i:s');
    }

    public static function getEndDate($endDate){
        $date = new DateTime($endDate);
        $date->setTime(23, 59, 59);
        return $date->format('Y-m-d H:i:s');
    }

    public static function isToday($startDate, $endDate){
        $today = date("Y-m-d H:i:s", time());
        $startDate = CUtils::getStartDate($startDate);
        $endDate = CUtils::getEndDate($endDate);
        if($today >= $startDate && $today <= $endDate){
            return true;
        }
        else return false;
    }
    public static function getDateViaFormat($datetoformat,$format='d/m/Y'){
        $date = new DateTime($datetoformat);
        //$date->setTime(00, 00, 00);
        return $date->format($format);
    }
    public static function  format_time_to_hour($t,$f=':') // t = seconds, f = separator
    {
        return sprintf("%02d%s%02d%s%02d", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
    }

    /**
     * @param $filename
     * @return Array
     */
    public static function readExcelFile($filename,$removeFirst)
    {

        Yii::import('ext.phpexcel.XPHPExcel');
//        require_once(Yii::app()->basePath. DIRECTORY_SEPARATOR . 'extensions'. DIRECTORY_SEPARATOR.'phpexcel'. DIRECTORY_SEPARATOR.'vendor'. DIRECTORY_SEPARATOR .'PHPExcel'.DIRECTORY_SEPARATOR.'Shared'. DIRECTORY_SEPARATOR.'String.php');
//        require_once(Yii::app()->basePath. DIRECTORY_SEPARATOR . 'extensions'. DIRECTORY_SEPARATOR.'phpexcel'. DIRECTORY_SEPARATOR.'vendor'. DIRECTORY_SEPARATOR .'PHPExcel'.DIRECTORY_SEPARATOR.'Reader'.DIRECTORY_SEPARATOR.'DefaultReadFilter.php');
//        require_once(Yii::app()->basePath. DIRECTORY_SEPARATOR . 'extensions'. DIRECTORY_SEPARATOR.'phpexcel'. DIRECTORY_SEPARATOR.'vendor'. DIRECTORY_SEPARATOR .'PHPExcel'.DIRECTORY_SEPARATOR.'Reader'.DIRECTORY_SEPARATOR.'IReader.php');
//        require_once(Yii::app()->basePath. DIRECTORY_SEPARATOR . 'extensions'. DIRECTORY_SEPARATOR.'phpexcel'. DIRECTORY_SEPARATOR.'vendor'. DIRECTORY_SEPARATOR .'PHPExcel'.DIRECTORY_SEPARATOR.'Reader'.DIRECTORY_SEPARATOR.'Abstract.php');
//        require_once(Yii::app()->basePath. DIRECTORY_SEPARATOR . 'extensions'. DIRECTORY_SEPARATOR.'phpexcel'. DIRECTORY_SEPARATOR.'vendor'. DIRECTORY_SEPARATOR .'PHPExcel'.DIRECTORY_SEPARATOR.'Reader'.DIRECTORY_SEPARATOR.'Excel5.php');
        require_once(Yii::app()->basePath. DIRECTORY_SEPARATOR . 'extensions'. DIRECTORY_SEPARATOR.'phpexcel'. DIRECTORY_SEPARATOR.'vendor'. DIRECTORY_SEPARATOR .'PHPExcel'.DIRECTORY_SEPARATOR. 'IOFactory.php');

        $Reader = PHPExcel_IOFactory::createReaderForFile($filename);
        $Reader->setReadDataOnly(true); // set this, to not read all excel properties, just data
        $objPHPExcel = $Reader->load($filename);
        $sheet=$objPHPExcel->getSheet(0);
        $highestColumn = $sheet->getHighestColumn();
        $lastRow =$sheet->getHighestRow();
        $firtrow=$removeFirst? 2:0;
        $result=array();
        for($row=$firtrow;$row<=$lastRow;$row++){
            $rowData =  $objPHPExcel->getActiveSheet()->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                NULL, TRUE, FALSE);
            array_push($result,$rowData);

        }
        return $result;


    }
}


?>
