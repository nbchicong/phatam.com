<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 16/10/2014
 * Time: 22:19
 */


class CrawMp3Dirpy {

    public $ch = null;
    public $baseMp3URL = 'http://www.dirpy.com';

    public function log($message) {
        echo date('Y-m-d H:i:s').": ".$message.PHP_EOL;
    }

    function __construct() {
        $this->ch = new MyCurl();
    }

    public function downloadDirpyMp3($youtubeUrl, $bitrate){
      $this->log("-- Download MP3 by Dirpy.Com");
        $metadata = $this->getDirpyMp3Metadata($youtubeUrl, $bitrate);
        if($metadata == null){
            Yii::log("Not get meta data for link: ".$youtubeUrl, CLogger::LEVEL_ERROR, "ProcessMp3");
            return false;
        }
//        $url = $this->generateDirpyMp3Link($youtubeUrl, $metadata);
        $url = $this->renderMP3DownloadLinkForDirpy($youtubeUrl, $metadata);
        $downloadToken = $this->getDownloadToken();
        $url = $this->baseMp3URL."/download" . $url . "&downloadToken=" . $downloadToken;
//      $this->log("---- URL DOWNLOAD ${url}");
        $fileName = CVietnameseTools::makeCodeName($metadata['filename']).'.mp3';
        $response = $this->ch->download($url,$fileName);
        return $response;
    }

    public function downloadImage($imgUrl){
        $fileName = end(explode('/', $imgUrl));
        return $this->ch->download($imgUrl, $fileName);
    }

    public function getDirpyMp3Metadata($youtubeUrl, $bitrate){
      $this->log("Get MP3 Metadata by Dirpy");
        $data = array();
        $url = $this->baseMp3URL.'/studio';
        try {
            /**
             * @var MyCurlResponse $response
             */
            $response = $this->ch->get($url,array(
                'url' => $youtubeUrl
            ));
            phpQuery::newDocumentHTML($response);
            $data['filename'] = pq('#filename')->val();
            $data['id3tags'] = $this->serializeForm(pq('#form_id3')->serializeArray());
            $data['audio_format'] = $bitrate;
            $data['start_time'] = pq('#start_time')->val();
            $data['end_time'] = pq('#end_time')->val();
            if(empty($data['filename'])){
                return null;
            }
            return $data;
        } catch (Exception $e){
            return null;
        }
    }

    /**
     * Generate link download file mp3
     * @param $url
     * @param $metadata String $filename, $id3tags, $audio_format, $start_time, $end_time
     * @return string
     */
    public function renderMP3DownloadLinkForDirpy ($url, $metadata) {
        $this->log("-- Render MP3 link for Dirpy");
        $dirpyUrl = "?url=". urlencode($url).
            "&format_id=0".
            "&filename=". rawurlencode($metadata["filename"]).
            "&ext=mp3".
            "&audio_format=".$metadata["audio_format"].
            "&start_time=".$metadata["start_time"].
            "&end_time=".$metadata["end_time"].
            "&type=audio".
            "&".$metadata["id3tags"];
//        $this->log("-- Dirpy MP3 Link: ${dirpyUrl}");
        return $dirpyUrl;
    }

    public function getDownloadToken(){
        $downloadToken = intval(microtime(true)*1000);
        return $downloadToken;
    }

    /**
     * @param $formArray
     * @return String : name=value&name=>value
     */
    private function serializeForm($formArray){
        if(!is_array($formArray)){
            return '';
        }
        $str = '';
        foreach ($formArray as $item){
            $str .= urlencode($item['name']).'='.urlencode($item['value']).'&';
        }
        return $str;
    }

} 