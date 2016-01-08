<?php

/**
 * Copyright (c) 2015 CT1905
 * Created by Nguyen Ba Chi Cong <nbchicong@gmail.com>
 * Date: 05/12/2015
 * Time: 06:32
 * ---------------------------------------------------
 * @project: ytdownloader
 * @name: YTDownloader.php
 * @package: ${NAMESPACE}
 * @author: nbchicong
 */

include_once("YTDownloaderImpl.php");
include_once("YTDownloaderUtils.php");

class YTDownloader implements YTDownloaderImpl {
  public static function log($message) {
    if (is_array($message)) {
      echo date('Y-m-d H:i:s') . ": ";
      print_r($message);
      echo PHP_EOL;
    } else {
      echo date('Y-m-d H:i:s') . ": " . $message . PHP_EOL;
    }
  }
  /**
   * Class constructor method
   * @param null $str
   * @param bool|FALSE $instant
   * @param null $out
   * @throws Exception
   */
  public function __construct($str = NULL, $instant = FALSE, $out = NULL) {
    if (!function_exists('curl_init')) {
      throw new Exception('Script requires the PHP CURL extension.');
    }
    if (!function_exists('json_decode')) {
      throw new Exception('Script requires the PHP JSON extension.');
    }
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', '64M');
    $this->YTBaseUrl = "http://www.youtube.com/";
    $this->YTInfoUrl = $this->YTBaseUrl . "get_video_info?video_id=%s&el=embedded&ps=default&eurl=&hl=en_US";
    $this->YTInfoAlt = $this->YTBaseUrl . "oembed?url=%s&format=json";
    $this->YTThumbUrl = "http://img.youtube.com/vi/%s/%s.jpg";
    $this->YTThumbAlt = "http://i1.ytimg.com/vi/%s/%s.jpg";
    $this->CurlUA = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:11.0) Gecko Firefox/11.0";
    $this->audio = FALSE;
    $this->video = FALSE;
    $this->thumb = FALSE;
    $this->videoID = FALSE;
    $this->videoExt = FALSE;
    $this->videoTitle = FALSE;
    $this->videoThumb = FALSE;
    $this->videoQuality = FALSE;
    $this->audioQuality = FALSE;
    $this->audioFormat = FALSE;
    $this->videoThumbSize = FALSE;
    $this->defaultDownload = FALSE;
    $this->downloadThumbs = TRUE;
    $this->downloadsDir = FALSE;
    $this->audiosDir = FALSE;
    $this->downloadsArray = FALSE;
    $this->ffmpegLogsDir = FALSE;
    $this->ffmpegLogfile = FALSE;
    $this->ffmpegLogsActive = TRUE;
    $this->deleteVideo = TRUE;
    self::setDownloadsDir(YTDownloaderImpl::downloadFolder);
    self::setAudiosDir(YTDownloaderImpl::audioFolder);
    self::setFfmpegLogsDir(YTDownloaderImpl::ffmpegLogsDir);
    self::setFfmpegLogsActive(YTDownloaderImpl::ffmpegLogsActive);
    self::setDefaultDownload(YTDownloaderImpl::defaultDownload);
    self::setDownloadThumbnail(YTDownloaderImpl::downloadThumbnail);
    self::setThumbSize(YTDownloaderImpl::defaultThumbSize);
    self::setVideoQuality(YTDownloaderImpl::defaultVideoQuality);
    self::setAudioQuality(YTDownloaderImpl::defaultAudioQuality);
    self::setAudioFormat(YTDownloaderImpl::defaultAudioFormat);
    self::setDeleteVideo(YTDownloaderImpl::defaultDeleteVideo);
    if ($str != NULL) {
      self::setYoutube($str);
      if ($instant === TRUE) {
        self::doDownload($out);
      }
    }
  }

  public function doDownload($out) {
    $action = ($out == "audio" || $out == "video") ? $out : self::getDefaultDownload();
    return ($action == "audio") ? self::downloadAudio() : self::downloadVideo();
  }

  /**
   * Set the YouTube Video that shall be downloaded.
   * @param $str
   * @throws Exception
   */
  public function setYoutube($str) {
    $tmpId = self::parseYTUrl($str);
    $videoId = ($tmpId !== FALSE) ? $tmpId : $str;
    $url = sprintf($this->YTBaseUrl . "watch?v=%s", $videoId);
    $url = sprintf($this->YTInfoAlt, urlencode($url));
    if (self::curlHttpStatus($url) !== 200) {
      throw new Exception("Invalid Youtube video ID: $videoId");
      exit();
    } else {
      self::setVideoId($videoId);
    }
  }

  /**
   * Get the direct links to the YouTube Video.
   * @return array|mixed|string
   * @throws Exception
   */
  public function getDownloads() {
    $id = self::getVideoId();
    if ($id === FALSE) {
      throw new Exception("Missing video id. Use set_youtube() and try again.");
    } else {
      $videoInfo = self::getYTInfo();
      $videoData = self::getVideoData($videoInfo);
      if ($videoData === TRUE) {
        $videoMaps = self::getUrlMap($videoInfo);
        if (!is_array($videoMaps) || sizeof($videoMaps) == 0) {
          $errorMsg = "";
          if (strpos($videoInfo, "status=fail") !== FALSE) {
            preg_match_all('#reason=(.*?)$#si', $videoInfo, $errorMatches);
            if (isset($errorMatches[1][0])) {
              $errorMsg = urldecode($errorMatches[1][0]);
              $errorMsg = str_replace("Watch on YouTube", "", strip_tags($errorMsg));
              $errorMsg = "Youtube error message: " . $errorMsg;
            }
          }
          return $errorMsg;
        } else {
          $quality = self::getVideoQuality();
          if ($quality == 1) {
            usort($videoMaps, 'ascByQuality');
          } else if ($quality == 0) {
            usort($videoMaps, 'descByQuality');
          }
          self::setYTUrlMap($videoMaps);
          return $videoMaps;
        }
      } else {
        throw new Exception("Cannot get data if video ID $id.");
      }
    }
  }

  /**
   * Try to download the defined YouTube Video.
   * @param null $idx
   * @return int Returns 0 if download success,
   *                  or 1 if the video already exists on the download directory.
   * @throws Exception
   */
  public function downloadVideo($idx = NULL) {
    $idx = ($idx !== NULL) ? $idx : 0;
    $id = self::getVideoId();
    $this->log('---- Start Download Video '.$id);
    if ($id === FALSE) {
      throw new Exception("---- Missing video id. Use setYoutube() and try again.");
//      exit();
    } else {
      $ytUrlMap = self::getYTUrlMap();
      if ($ytUrlMap === FALSE) {
        $videos = self::getDownloads();
        self::setYTUrlMap($videos);
      } else {
        $videos = $ytUrlMap;
      }
      if (!is_array($videos)) {
        throw new Exception("---- Grabbing original file location(s) failed. $videos");
      } else {
        if (is_array($videos[$idx])) {
          $videos = $videos[$idx];
        }
        $title = self::getVideoTitle();
        $path = self::getDownloadsDir();
        if (isset($videos[0])&&!isset($videos["url"])) {
          $videos["url"] = $videos[2];
          $videos["type"] = $videos[0];
          $videos["ext"] = $videos[3];
        }
        $ytVideoUrl = $videos["url"];
        $res = $videos["type"];
        $ext = $videos["ext"];
        $videoTitle = $title . "_-_" . $res . "_-_youtubeid-$id";
        $videoFilename = $videoTitle.".".$ext;
        $thumbFilename = $videoTitle.".jpg";
        $video = $path . $videoFilename;
        self::setVideo($videoFilename);
        self::setThumb($thumbFilename);
        clearstatcache();
        if (!file_exists($video)) {
          $downloadThumbs = self::getDownloadThumbnail();
          if ($downloadThumbs === TRUE) {
            self::checkThumbs($id);
          }
          touch($video);
          chmod($video, 0775);
          $download = self::getFileByCurl($ytVideoUrl, $video);
          if ($download === FALSE) {
            throw new Exception("Saving $videoFilename to $path failed.");
//            exit();
          } else {
            if ($downloadThumbs === TRUE) {
              $thumb = self::getVideoThumb();
              if ($thumb !== FALSE) {
                $thumbnail = $path . $thumbFilename;
                self::getFileByCurl($thumb, $thumbnail);
                chmod($thumbnail, 0775);
              }
            }
            return 0;
          }
        } else {
          return 1;
        }
      }
    }
  }

  /**
   * Download and convert audio the defined Youtube video.
   * @return int Returns 0 if download success,
   *                  or 1 if the video already exists on the download directory.
   * @throws Exception
   */
  public function downloadAudio() {
    $ffmpeg = self::hasFfmpeg();
    if ($ffmpeg === FALSE) {
      throw new Exception("You must have Ffmpeg installed in order to use this function.");
//      exit();
    } else if ($ffmpeg === TRUE) {
      self::setVideoQuality(1);
      $dl = self::downloadVideo();
      if ($dl == 0 || $dl == 1) {
        $title = self::getVideoTitle();
        $path = self::getDownloadsDir();
//        $audiosDir = self::getAudiosDir();
        $ext = self::getAudioFormat();
        $ffmpegInputFile = $path . self::getVideo();
        $ffmpegOutputFile = $path . $title . "." . $ext;
//        if (!is_dir("/home/nbchicong/Music/".$audiosDir)) {
//          mkdir("/home/nbchicong/Music/".$audiosDir);
//        }
//        $result = array();
        if (!file_exists($ffmpegOutputFile)) {
          $logging = self::getFfmpegLogsActive();
          $ab = self::getAudioQuality() . "k";
          $cmd = "./ffmpeg -i \"$ffmpegInputFile\" -ar 44100 -ab $ab -ac 2 \"$ffmpegOutputFile\"";
          $ffmpegExe = exec($cmd);
          if ($logging !== FALSE) {
            $ffmpegLogsPath = self::getFfmpegLogsDir();
            $ffmpegLogFile = "ffmpeg." . date("Ymdhis") . ".log";
            $logfile = "./" . $ffmpegLogsPath . $ffmpegLogFile;
            self::setFfmpegLogFile($logfile);
            exec("touch $logfile");
            exec("chmod 777 $logfile");
            $logIt = "echo \"$ffmpegExe\" > \"$logfile\"";
            $lg = `$logIt`;
          }
          if ($dl == 0 && self::isDeleteVideo()) {
            unlink($ffmpegInputFile);
          }
          clearstatcache();
          if (file_exists($ffmpegOutputFile) !== FALSE) {
            self::setAudio($title . "." . $ext);
//            $result = array(
//                'title' => self::getAudio(),
//                'audioPath' => $ffmpegOutputFile,
//                'quality' => $ab
//            );
            return array(
                'title' => self::getAudio(),
                'audioPath' => $ffmpegOutputFile,
                'quality' => $ab
            );
          } else {
            unlink($ffmpegInputFile);
            throw new Exception("Something went wrong while converting the video into $ext format, sorry!");
//            exit();
          }
        } else {
          self::setAudio($title . "." . $ext);
//          $result = array(
//              'title' => self::getAudio(),
//              'audioPath' => $ffmpegOutputFile,
//              'quality' => self::getAudioQuality() . "k"
//          );
          return array(
              'title' => self::getAudio(),
              'audioPath' => $ffmpegOutputFile,
              'quality' => self::getAudioQuality() . "k"
          );
        }
      } else {
        $this->log("Cannot download video file from Youtube.");
        return false;
//        throw new Exception("Cannot download video file from Youtube.");
//        exit();
      }
    } else {
      $this->log("Cannot locate your Ffmpeg installation?! Thus, cannot convert the video.");
      return false;
//      throw new Exception("Cannot locate your Ffmpeg installation?! Thus, cannot convert the video.");
//      exit();
    }
  }

  /**
   * Get File Stats for the downloaded audio/video file.
   * @return mixed Returns an array containing formatted filestats,
   *                    or FALSE if file doesn't exist.
   */
  public function videoStats() {
    $file = self::getVideo();
    $path = self::getDownloadsDir();
    clearstatcache();
    $fileStats = stat($path . $file);
    if ($fileStats !== FALSE) {
      return array("size" => self::humanBytes($fileStats[ "size"]), "created" => date("d.m.Y H:i:s.", $fileStats["ctime"]), "modified" => date("d.m.Y H:i:s.", $fileStats["mtime"]));
    } else {
      return FALSE;
    }
  }

  /**
   * Check if input string is a valid YouTube URL
   * and try to extract the YouTube Video ID from it.
   * @param $url
   * @return mixed Returns YouTube Video ID, or FALSE.
   */
  private function parseYTUrl($url) {
    $pattern = '#^(?:https?://)?';
    $pattern .= '(?:www\.)?';
    $pattern .= '(?:';
    $pattern .= 'youtu\.be/';
    $pattern .= '|youtube\.com';
    $pattern .= '(?:';
    $pattern .= '/embed/';
    $pattern .= '|/v/';
    $pattern .= '|/watch\?v=';
    $pattern .= '|/watch\?.+&v=';
    $pattern .= ')';
    $pattern .= ')';
    $pattern .= '([\w-]{11})';
    $pattern .= '(?:.+)?$#x';
    preg_match($pattern, $url, $matches);
    return (isset($matches[1])) ? $matches[1] : FALSE;
  }

  /**
   * Get internal YouTube info for a Video.
   * @return string Returns video info as string.
   */
  private function getYTInfo() {
    $url = sprintf($this->YTInfoUrl, self::getVideoId());
    return self::curlGet($url);
  }

  /**
   * Get the public YouTube Info-Feed for a Video.
   * @return mixed Returns array, containing the YouTube Video-Title
   *                   and preview image URL,
   *                    or FALSE if parsing the feed failed.
   */
  private function getPublicInfo() {
    $url = sprintf($this->YTBaseUrl . "watch?v=%s", self::getVideoId());
    $url = sprintf($this->YTInfoAlt, urlencode($url));
    $info = json_decode(self::curlGet($url), TRUE);
    if (is_array($info) && sizeof($info) > 0) {
      return array("title" => $info["title"], "thumb" => $info["thumbnail_url"]);
    } else {
      return FALSE;
    }
  }

  /**
   * Get formatted video data from the public YouTube Info-Feed.
   * @param string $str Info-File contents for a YouTube Video.
   * @return bool Returns the status of getting video info
   * @throws Exception
   */
  private function getVideoData($str) {
    $ytInfo = $str;
    $pubInfo = self::getPublicInfo();
    if ($pubInfo !== FALSE) {
      $htmlTitle = utf8_decode($pubInfo["title"]);
      $videoTitle = self::canonicalize($htmlTitle);
    } else {
      $videoTitle = self::formattedVideoTitle($ytInfo);
    }
    if (is_string($videoTitle) && strlen($videoTitle) > 0) {
      self::setVideoTitle($videoTitle);
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Get the URL map for a YouTube Video.
   * @param string $data Info-File contents for a YouTube Video.
   * @return array|bool Returns an array, containg the Video URL map,
   *                         or FALSE if extracting failed.
   */
  private function getUrlMap($data) {
    preg_match('/stream_map=(.[^&]*?)&/i', $data, $match);
    if (!isset($match[1])) {
      return FALSE;
    } else {
      $formatUrl = urldecode($match[1]);
      if (preg_match('/^(.*?)\\\\u0026/', $formatUrl, $match2)) {
        $formatUrl = $match2[1];
      }
      $urls = explode(',', $formatUrl);
      $tmp = array();
      foreach ($urls as $url) {
        if (preg_match('/url=(.*?)&.*?itag=([0-9]+)/si', $url, $um)) {
          $tmp[$um[2]] = urldecode($um[1]);
        } elseif (preg_match('/itag=([0-9]+).*?&url=(.*)/si', $url, $um)) {
          $tmp[$um[1]] = urldecode($um[2]);
        }
      }
      $formats = array(
          '5' => array('flv', '240p', '7'),
          '6' => array('flv', '240p', '6'),
          '34' => array('flv', '320p', '5'),
          '35' => array('flv', '480p', '4'),
          '13' => array('3gp', '240p', '10'),
          '17' => array('3gp', '240p', '9'),
          '36' => array('3gp', '320p', '8'),
          '18' => array('mp4', '480p', '3'),
          '22' => array('mp4', '720p', '2'),
          '37' => array('mp4', '1080p', '1')
      );
      $videos = array();
      foreach ($formats as $format => $meta) {
        if (isset($tmp[$format])) {
          $videos = array(
              'pref' => $meta[2],
              'ext' => $meta[0],
              'type' => $meta[1],
              'url' => $tmp[$format]
          );
        }
      }
      return $videos;
    }
  }

  /**
   * Get the preview image for a YouTube Video.
   * @param string $id Valid YouTube Video-ID.
   */
  private function checkThumbs($id) {
    $thumbSize = self::getThumbSize();
    $thumbUri = sprintf($this->YTThumbUrl, $id, $thumbSize);
    if (self::curlHttpStatus($thumbUri) == 200) {
      $th = $thumbUri;
    } else {
      $thumbUri = sprintf($this->YTThumbAlt, $id, $thumbSize);
      if (self::curlHttpStatus($thumbUri) == 200) {
        $th = $thumbUri;
      } else {
        $th = FALSE;
      }
    }
    self::setVideoThumb($th);
  }

  /**
   * Get the YouTube Video Title and format it.
   * @param string $str Input string.
   * @return string Returns cleaned input string.
   */
  private function formattedVideoTitle($str) {
    preg_match_all('#title=(.*?)$#si', urldecode($str), $matches);
    $title = explode("&", $matches[ 1 ][ 0 ]);
    $title = $title[ 0 ];
    $title = htmlentities(utf8_decode($title));
    return self::canonicalize($title);
  }

  /**
   * Format the YouTube Video Title into a valid filename.
   * @param string $str Input string.
   * @return string Returns cleaned input string.
   */
  private function canonicalize($str) {
    $str = trim($str);
    $str = str_replace("&quot;", "", $str);
    $str = self::strynonym($str);
    $str = preg_replace("/[[:blank:]]+/", "_", $str);
    $str = preg_replace('/[^\x9\xA\xD\x20-\x7F]/', '', $str);
    $str = preg_replace('/[^\w\d_-]/si', '', $str);
    $str = str_replace('__', '_', $str);
    $str = str_replace('--', '-', $str);
    if (substr($str, -1) == "_" OR substr($str, -1) == "-") {
      $str = substr($str, 0, -1);
    }
    return trim($str);
  }

  // TODO: Rename to me
  /**
   * Replace common special entity codes for special character
   * vowels by their equivalent ASCII letter.
   * @param string $str Input string.
   * @return string Returns cleaned input string.
   */
  private function strynonym($str) {
    $specialVowels = array('&Agrave;' => 'A', '&agrave;' => 'a', '&Egrave;' => 'E', '&egrave;' => 'e', '&Igrave;' => 'I', '&igrave;' => 'i', '&Ograve;' => 'O', '&ograve;' => 'o', '&Ugrave;' => 'U', '&ugrave;' => 'u', '&Aacute;' => 'A', '&aacute;' => 'a', '&Eacute;' => 'E', '&eacute;' => 'e', '&Iacute;' => 'I', '&iacute;' => 'i', '&Oacute;' => 'O', '&oacute;' => 'o', '&Uacute;' => 'U', '&uacute;' => 'u', '&Yacute;' => 'Y', '&yacute;' => 'y', '&Acirc;' => 'A', '&acirc;' => 'a', '&Ecirc;' => 'E', '&ecirc;' => 'e', '&Icirc;' => 'I', '&icirc;' => 'i', '&Ocirc;' => 'O', '&ocirc;' => 'o', '&Ucirc;' => 'U', '&ucirc;' => 'u', '&Atilde;' => 'A', '&atilde;' => 'a', '&Ntilde;' => 'N', '&ntilde;' => 'n', '&Otilde;' => 'O', '&otilde;' => 'o', '&Auml;' => 'Ae', '&auml;' => 'ae', '&Euml;' => 'E', '&euml;' => 'e', '&Iuml;' => 'I', '&iuml;' => 'i', '&Ouml;' => 'Oe', '&ouml;' => 'oe', '&Uuml;' => 'Ue', '&uuml;' => 'ue', '&Yuml;' => 'Y', '&yuml;' => 'y', '&Aring;' => 'A', '&aring;' => 'a', '&AElig;' => 'Ae', '&aelig;' => 'ae', '&Ccedil;' => 'C', '&ccedil;' => 'c', '&OElig;' => 'OE', '&oelig;' => 'oe', '&szlig;' => 'ss', '&Oslash;' => 'O', '&oslash;' => 'o');
    return strtr($str, $specialVowels);
  }

  /**
   * Check if given directory exists. If not, try to create it.
   * @param string $dir Path to the directory.
   * @return boolean  Returns TRUE if directory exists,
   *                       or was created,
   *                       or FALSE if creating non-existing directory failed.
   */
  private function validDir($dir) {
    if (is_dir($dir) !== FALSE) {
      chmod($dir, 0777);
      return TRUE;
    } else {
      return (bool)!@mkdir($dir, 0777);
    }
  }

  /**
   * Check on the command line if we can find an Ffmpeg installation on the script host.
   * @return  boolean  Returns TRUE if Ffmpeg is installed on the server,
   *                        or FALSE if not.
   */
  private function hasFfmpeg() {
    $sh = `which ./ffmpeg`;
    return (bool)(strlen(trim($sh)) > 0);
  }

  /**
   * HTTP HEAD request with curl.
   * @param string $url String, containing the URL to curl.
   * @return int Returns a HTTP status code.
   */
  private function curlHttpStatus($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->CurlUA);
    curl_setopt($ch, CURLOPT_REFERER, $this->YTBaseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $str = curl_exec($ch);
    $int = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return intval($int);
  }

  /**
   * HTTP GET request with curl.
   * @param string $url String, containing the URL to curl.
   * @return string Returns string, containing the curl result.
   */
  private function curlGet($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->CurlUA);
    curl_setopt($ch, CURLOPT_REFERER, $this->YTBaseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $contents = curl_exec($ch);
    curl_close($ch);
    return $contents;
  }

  /**
   * HTTP GET request with curl that writes the curl result into a local file.
   * @param string $remoteFile String, containing the remote file URL to curl.
   * @param string $localFile String, containing the path to the file to save
   *                                  the curl result in to.
   * @return bool
   */
  private function getFileByCurl($remoteFile, $localFile) {
    $ch = curl_init($remoteFile);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->CurlUA);
    curl_setopt($ch, CURLOPT_REFERER, $this->YTBaseUrl);
    $fp = fopen($localFile, 'w');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    $result = curl_exec($ch);
    curl_close($ch);
    $isClose = fclose($fp);
    return $result && $isClose;
  }

  // Getter and Setter for the downloaded audio file.
  public function getAudio() {
    return $this->audio;
  }

  private function setAudio($audio) {
    $this->audio = $audio;
  }

  // Getter and Setter for the downloaded video file.
  public function getVideo() {
    return $this->video;
  }

  private function setVideo($video) {
    $this->video = $video;
  }

  // Getter and Setter for the downloaded video preview image.
  public function getThumb() {
    return $this->thumb;
  }

  private function setThumb($img) {
    if (is_string($img)) {
      $this->thumb = $img;
    } else {
      throw new Exception("Invalid thumbnail given: $img");
    }
  }

  // Getter and Setter whether to download the video, or convert to audio by default.
  public function getDefaultDownload() {
    return $this->defaultDownload;
  }

  public function setDefaultDownload($action) {
    if ($action == "audio" || $action == "video") {
      $this->defaultDownload = $action;
    } else {
      throw new Exception("Invalid download type. Must be either 'audio', or 'video'.");
    }
  }

  // Getter and Setter for the video quality.
  public function getVideoQuality() {
    return $this->videoQuality;
  }

  public function setVideoQuality($q) {
    if (in_array($q, array(0, 1))) {
      $this->videoQuality = $q;
    } else {
      throw new Exception("Invalid video quality.");
    }
  }

  // Getter and Setter for the audio quality.
  public function getAudioQuality() {
    return $this->audioQuality;
  }

  public function setAudioQuality($q) {
    if ($q >= 128 && $q <= 320) {
      $this->audioQuality = $q;
    } else {
      throw new Exception("Audio sample rate must be between 128 and 320.");
    }
  }

  // Getter and Setter for the audio output filetype.
  public function getAudioFormat() {
    return $this->audioFormat;
  }

  public function setAudioFormat($ext) {
    $validExts = array("mp3", "wav", "ogg", "mp4");
    if (in_array($ext, $validExts)) {
      $this->audioFormat = $ext;
    } else {
      throw new Exception("Invalid audio filetype '$ext' defined.
            Valid filetypes are: " . implode(", ", $validExts));
    }
  }

  // Getter and Setter for the download directory.
  public function getDownloadsDir() {
    return $this->downloadsDir;
  }

  public function setDownloadsDir($dir) {
    if (self::validDir($dir) !== FALSE) {
      $this->downloadsDir = $dir;
    } else {
      throw new Exception("Can neither find, nor create download folder: $dir");
    }
  }

  // Getter and Setter whether to log Ffmpeg processes.
  public function getFfmpegLogsActive() {
    return $this->ffmpegLogsActive;
  }

  public function setFfmpegLogsActive($b) {
    $this->ffmpegLogsActive = (bool)($b !== FALSE);
  }

  // Getter and Setter for the YouTube URL map.
  public function getYTUrlMap() {
    return $this->downloadsArray;
  }

  private function setYTUrlMap($videos) {
    $this->downloadsArray = $videos;
  }

  // Getter and Setter for the YouTube Video-ID.
  public function getVideoId() {
    return $this->videoID;
  }

  public function setVideoId($id) {
    if (strlen($id) == 11) {
      $this->videoID = $id;
    } else {
      throw new Exception("$id is not a valid Youtube Video ID.");
    }
  }

  // Getter and Setter for the formatted video title.
  public function getVideoTitle() {
    return $this->videoTitle;
  }

  public function setVideoTitle($str) {
    if (is_string($str)) {
      $this->videoTitle = $str;
    } else {
      throw new Exception("Invalid title given: $str");
    }
  }

  // Getter and Setter for thumbnail preferences.
  public function getDownloadThumbnail() {
    return $this->downloadThumbs;
  }

  public function setDownloadThumbnail($q) {
    if ($q == TRUE || $q == FALSE) {
      $this->downloadThumbs = (bool)$q;
    } else {
      throw new Exception("Invalid argument given to set_download_thumbnail.");
    }
  }

  // Getter and Setter for the video preview image size.
  public function getThumbSize() {
    return $this->videoThumbSize;
  }

  public function setThumbSize($s) {
    if ($s == "s") {
      $this->videoThumbSize = "default";
    } else if ($s == "l") {
      $this->videoThumbSize = "hqdefault";
    } else {
      throw new Exception("Invalid thumbnail size specified.");
    }
  }

  // Getter and Setter for the object's Ffmpeg log file.
  public function getFfmpegLogFile() {
    return $this->ffmpegLogfile;
  }

  private function setFfmpegLogFile($str) {
    $this->ffmpegLogfile = $str;
  }


  // Getter and Setter for the remote video preview image.
  public function getVideoThumb() {
    return $this->videoThumb;
  }

  private function setVideoThumb($img) {
    $this->videoThumb = $img;
  }

  // Getter and Setter for the Ffmpeg-Logs directory.
  public function getFfmpegLogsDir() {
    return $this->ffmpegLogsDir;
  }

  public function setFfmpegLogsDir($dir) {
    if (self::validDir($dir) !== FALSE) {
      $this->ffmpegLogsDir = $dir;
    } else {
      throw new Exception("Can neither find, nor create ffmpeg log directory '$dir', but logging is enabled.");
    }
  }

  /**
   * @return boolean
   */
  public function isDeleteVideo() {
    return $this->deleteVideo;
  }

  /**
   * @param boolean $deleteVideo
   */
  public function setDeleteVideo($deleteVideo) {
    $this->deleteVideo = $deleteVideo;
  }

  /**
   * @return string|boolean
   */
  public function getAudiosDir() {
    return $this->audiosDir;
  }

  /**
   * @param string|boolean $audiosDir
   */
  public function setAudiosDir($audiosDir) {
    $this->audiosDir = $audiosDir;
  }

  /**
   * Format file size in bytes into human-readable string.
   * @param int $bytes : Filesize in bytes.
   * @return string : Returns human-readable formatted filesize.
   */
  public function humanBytes($bytes) {
    $fileSize = $bytes;
    switch ($bytes):
      case $bytes < 1024:
        $fileSize = $bytes . ' B';
        break;
      case $bytes < 1048576:
        $fileSize = round($bytes / 1024, 2) . ' KiB';
        break;
      case $bytes < 1073741824:
        $fileSize = round($bytes / 1048576, 2) . ' MiB';
        break;
      case $bytes < 1099511627776:
        $fileSize = round($bytes / 1073741824, 2) . ' GiB';
        break;
    endswitch;
    return $fileSize;
  }
}