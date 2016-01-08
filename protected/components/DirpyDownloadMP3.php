<?php

/**
 * Copyright (c) 2016 CT1905
 * Created by Nguyen Ba Chi Cong <nbchicong@gmail.com>
 * Date: 01/01/2016
 * Time: 06:54
 * ---------------------------------------------------
 * Project: phatammp3
 * @name: DirpyDownloadMP3.php
 * @package: ${NAMESPACE}
 * @author: nbchicong
 */
class DirpyDownloadMP3 {
  public $myCurl = null;
  public $baseDirpyURL = 'http://www.dirpy.com';

  public function log($message) {
    if (is_array($message)) {
      echo date('Y-m-d H:i:s') . ": ";
      print_r($message);
      echo PHP_EOL;
    } else {
      echo date('Y-m-d H:i:s') . ": " . $message . PHP_EOL;
    }
  }

  function __construct() {
    $this->myCurl = new MyCurl();
  }

  private function getSecondOfTime($time) {
    $mp3Duration = explode(':', $time);
    $second = 0;
    if (is_array($mp3Duration)) {
      $second = intval($mp3Duration[0]) * 3600;
      $second += intval($mp3Duration[1]) * 60;
      $second += intval($mp3Duration[2]) * 1;
      $this->log("------ Convert to second ". $second);
    }
    return $second;
  }

  /**
   * @param $youtubeUrl
   * @param $bitrate
   * @return bool|mixed|MyCurlFile
   */
  public function downloadMp3ByDirpy($youtubeUrl, $bitrate) {
    $this->log("------ Download MP3 by Dirpy.Com");
    $metadata = $this->getDirpyMp3Metadata($youtubeUrl, $bitrate);
    if ($metadata == null) {
      $this->log("ERROR: Not get meta data for link: " . $youtubeUrl);
      Yii::log("ERROR: Not get meta data for link: " . $youtubeUrl, CLogger::LEVEL_ERROR, "ProcessMp3");
      return false;
    }
    $url = $this->renderMP3DownloadLinkForDirpy($youtubeUrl, $metadata);
    $url = $this->baseDirpyURL . "/download" . $url . "&downloadToken=" . $this->getDownloadToken();
//    $this->log("---- URL DOWNLOAD ${url}");
    $fileName = CVietnameseTools::makeCodeName($metadata[ 'filename' ]) . '.mp3';
    $this->log("------ Downloading MP3");
    $response = $this->myCurl->download($url, $fileName);
    if ($response != false) {
      $folderSave = '/tmp/';
      $file = $folderSave.$fileName;
      $mp3File = new MP3File($file);
      $mp3Second = $mp3File->getDurationEstimate();
      $metadataTimeSecond = $this->getSecondOfTime($metadata['end_time']);
      $this->log("------ Checking duration mp3 file downloaded ". $mp3Second ." and compare with meta time ".$metadataTimeSecond);
      if ($mp3Second >= $metadataTimeSecond) {
        return $response;
      }
      return false;
    }
    return $response;
  }

  /**
   * @param $imgUrl
   * @return bool|mixed|MyCurlFile
   */
  public function downloadImage($imgUrl) {
    $fileName = end(explode('/', $imgUrl));
    return $this->myCurl->download($imgUrl, $fileName);
  }

  /**
   * @param $youtubeUrl
   * @param $bitrate
   * @return array|null
   */
  public function getDirpyMp3Metadata($youtubeUrl, $bitrate) {
    $this->log("------ Get MP3 Metadata by Dirpy");
    $data = array();
    $url = $this->baseDirpyURL . '/studio';
    try {
      $response = $this->myCurl->get($url, array('url' => $youtubeUrl));
      phpQuery::newDocumentHTML($response);
      $data[ 'filename' ] = pq('#filename')->val();
      $data[ 'id3tags' ] = $this->serializeForm(pq('#form_id3')->serializeArray());
      $data[ 'audio_format' ] = $bitrate;
      $data[ 'start_time' ] = pq('#start_time')->val();
      $data[ 'end_time' ] = pq('#end_time')->val();
      if (empty($data[ 'filename' ])) {
        return null;
      }
      return $data;
    } catch (Exception $e) {
      return null;
    }
  }

  /**
   * Generate link download file mp3
   * @param $url
   * @param $metadata String $filename, $id3tags, $audio_format, $start_time, $end_time
   * @return string
   */
  public function renderMP3DownloadLinkForDirpy($url, $metadata) {
    $this->log("------ Render MP3 link for Dirpy");
    $dirpyUrl = "?url=" . urlencode($url);
    $dirpyUrl.= "&format_id=0";
    $dirpyUrl.= "&filename=" . rawurlencode($metadata[ "filename" ]);
    $dirpyUrl.= "&ext=mp3";
    $dirpyUrl.= "&audio_format=" . $metadata[ "audio_format" ];
    $dirpyUrl.= "&start_time=" . $metadata[ "start_time" ];
    $dirpyUrl.= "&end_time=" . $metadata[ "end_time" ];
    $dirpyUrl.= "&type=audio";
    $dirpyUrl.= "&" . $metadata[ "id3tags" ];
//    $this->log("-- Dirpy MP3 Link: ${dirpyUrl}");
    return $dirpyUrl;
  }

  /**
   * @return int
   */
  public function getDownloadToken() {
    $downloadToken = intval(microtime(true) * 1000);
    return $downloadToken;
  }

  /**
   * @param $formArray
   * @return String : name=value&name=>value
   */
  private function serializeForm($formArray) {
    if (!is_array($formArray)) {
      return '';
    }
    $str = '';
    foreach ($formArray as $item) {
      $str .= urlencode($item[ 'name' ]) . '=' . urlencode($item[ 'value' ]) . '&';
    }
    return $str;
  }
}