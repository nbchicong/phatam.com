<?php


/**
 * A command to easily switch between the environments.
 * You can look what the current environment is by issuing the command: `yiic environment`
 * You can set different environment by issuing the command: `yiic environment set --id=<ENV_ID>`
 *
 * @package YiiBoilerplate\Console
 */
class ProcessMp3Command extends CConsoleCommand {
//  public static function log($message) {
//    echo date('Y-m-d H:i:s') . ": " . $message . PHP_EOL;
//  }
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
   * @param $url
   * @param $imgUrl
   * @param $tagData IDTagInfo
   * @param $folderMp3
   * @param $cat
   * @return string
   */
  public function processVideo($url, $imgUrl, $tagData, $folderMp3, $cat) {
    $this->log("---- Start Convert Video ${url} to MP3");
    $crawInfo = isset(Yii::app()->params[ 'prefixmp3' ][ $cat ]) ? Yii::app()->params[ 'prefixmp3' ][ $cat ] : array('file' => '/var/www/phatammp3/prefixmp3/sachnoi.mp3', 'bitrate' => '128K');
    $processInfo = array('url' => $url, 'timeDownloadMp3' => 0, 'timeDownloadImage' => 0, 'timeMerge' => 0, 'timeWriteTag' => 0, 'imageAttach' => $imgUrl, 'fileMp3' => $tagData->FileName);
    $startTime = time();

    $prefixFile = $crawInfo[ 'file' ];
    $mp3Engine = new MP3Process();
//    $process = new CrawMp3Dirpy();
    $process = new DirpyDownloadMP3();

    /**
     * Download file mp3
     */

    $response = $process->downloadMp3ByDirpy($url, $crawInfo[ 'bitrate' ]);
    if ($response == false) {
      $this->log("------ Download mp3: " . $url . " fail!");
      return false;
    }
    $this->log("------ Download MP3 Response ".$response->fileName);
    $processInfo[ 'timeDownloadMp3' ] = time() - $startTime;
    $startTime = time();

    /**
     * Download file img
     */
    $responseImg = $process->downloadImage($imgUrl);
    if ($responseImg == false) {
      $this->log("------ Download Image: " . $imgUrl . " fail!");
      return false;
    }
    $this->log("------ Download image response ".$responseImg->filePath);
    $img = $responseImg->filePath;
    if ($responseImg->fileSize < 0) {
      $processInfo[ 'downloadimg' ] = 'fail';
    } else {
      $processInfo[ 'downloadimg' ] = 'success';
    }
    $processInfo[ 'timeDownloadImage' ] = time() - $startTime;
    $startTime = time();

    $this->log("------ MP3 Response file size: ".$response->fileSize);
    if ($response->fileSize > 0) {
      /**
       * Process merge file mp3
       */
      $desFile = $folderMp3 . '/' . $tagData->FileName;
      $fileIns = array($prefixFile, $response->filePath);
      $this->log("------ Merge MP3 with prefix");
      if ($mp3Engine->CombineMultipleMP3sTo($desFile, $fileIns)) {
        //Delete file download
        unlink($response->filePath);
        $processInfo[ 'timeMerge' ] = time() - $startTime;
        $startTime = time();

        /**
         * Process write IDTag to mp3
         */
        if ($responseImg->fileSize > 0) {
          if ($tagData->loadAttachedPicture($img)) {
            $processInfo[ 'attachImage' ] = 'success';
          } else {
            $processInfo[ 'attachImage' ] = 'false';
          }
        }
        if ($mp3Engine->WrigeIDTag($desFile, $tagData)) {
          //Delete file image
          if ($responseImg->fileSize > 0) {
            //                        unlink($responseImg->filePath);
          }
          $processInfo[ 'writeTag' ] = 'success';
          $processInfo[ 'timeWriteTag' ] = time() - $startTime;
          $this->log(json_encode($processInfo));
          return $tagData->FileName;
        } else {
          $processInfo[ 'writeTag' ] = 'fail';
          $processInfo[ 'timeWriteTag' ] = time() - $startTime;
        }
        $this->log("------ Merge MP3 with prefix: DONE");
      } else {
        $processInfo[ 'merge' ] = 'fail';
      }
    } else {
      $processInfo[ 'downloadmp3' ] = 'fail';
    }
    $this->log(json_encode($processInfo));
    return false;
  }

  /**
   * Xu ly Episode
   * @param $video PmVideos
   * @param $episodes Array PmEpisode
   * @param $folderMp3
   * @return array
   */
  public function processEpisode($video, $episodes, $folderMp3) {
    $this->log("-- Running process EPISODE");
    $this->log(`ls`);
    $basUrl = Yii::app()->params[ 'baseUrl' ];
    $audioInfo = array();
    $useYtThumb = false;
    //        $artistName = isset($video->pmArtist)?$video->pmArtist->name:$video->artist;
    $artistName = $video->artist;
    $artisFolder = CVietnameseTools::makeName($artistName);
    $this->log("---- Process Video has episodes" . $video->video_title . ' 1...');
    $tagData = new IDTagInfo();
    $tagData->Title = $video->video_title . '_1_wWw.PhatAm.com';;
    $tagData->FileName = CVietnameseTools::makeMp3Name($video->video_title) . '_' . CVietnameseTools::makeName($video->artist) . '_1' . '_wWw.PhatAm.com.mp3';
    $tagData->Album = $video->artist;
    $tagData->Artist = $video->artist;
    $tagData->Comment = $video->description;
    $coverUrl = $video->getCoverUrl();
    if ($video->pmArtist == null || empty($video->pmArtist->avatar)) {
      $useYtThumb = true;
    }
    $ytUrlArr = explode('?v=', $video->videoUrl->direct);
    $this->log("---- Video has episodes with ID: ".$ytUrlArr[1]);
    $mp3File = $this->downloadVideo($ytUrlArr[1], $coverUrl, $tagData, $folderMp3, $video->category);
    if (!$mp3File) {
      $this->log("---- Process " . $video->video_title . ' 1 fail!!');
    } else {
      $audioInfo[ 1 ] = array('title' => $video->video_title . '_1', 'url' => $basUrl . $artisFolder . '/' . $mp3File);
    }
    $i = 2;
    foreach ($episodes as $episode) {
      $this->log("*******************************************");
      $this->log("*           START PROCESS EPISODE         *");
      $this->log("*            " . date("H:i:s d/m/Y") . "         *");
      $this->log("*******************************************");
      $this->log("---- Process episode " . $video->video_title . ' ' . $episode->episode_id . '...');
      $tagEpisodeData = new IDTagInfo();
      $tagEpisodeData->Title = $video->video_title . '_' . intval($episode->episode_id) . '_wWw.PhatAm.com';
      $tagEpisodeData->FileName = CVietnameseTools::makeMp3Name($video->video_title) . '_' . CVietnameseTools::makeName($video->artist) . '_' . intval($episode->episode_id) . '_wWw.PhatAm.com.mp3';
      $tagEpisodeData->Album = $video->artist;
      $tagEpisodeData->Artist = $video->artist;
      $tagEpisodeData->Comment = $video->description;
      $coverEpisodeUrl = $coverUrl;
      if ($useYtThumb) {
        $coverEpisodeUrl = $episode->yt_thumb;
      }
      $mp3File = $this->downloadVideo($episode->yt_id, $coverEpisodeUrl, $tagEpisodeData, $folderMp3, $video->category);
      if (!$mp3File) {
        $this->log("---- Process episode" . $tagEpisodeData->Title . ' fail!!');
      } else {
        $audioInfo[ $i ] = array('title' => $video->video_title . '_' . intval($episode->episode_id), 'url' => $basUrl . $artisFolder . '/' . $mp3File);
      }
      $i++;
    }
    $this->log("-- END process EPISODE");
    $this->log("-- Audio Info of Video has EPISODE");
    $this->log($audioInfo);
    return $audioInfo;
  }

  public function actionLoadMP3() {
    /**
     * Process lock file
     */
    $lockFile = Yii::app()->params[ 'lockfile' ];
    $this->log("Testing LOCK file before convert MP3 action...");
    if (file_exists($lockFile)) {
      $this->log("LOCK file is exists, Other service running");
      return;
    } else {
      //Create lock file
      $fb = fopen($lockFile, "w") or die("Unable to open file!");
      $txt = "1";
      fwrite($fb, $txt);
      fclose($fb);
    }
    $this->log("*******************************************");
    $this->log("*          BEGIN PROCESS LOAD MP3         *");
    $this->log("*            ".date("H:i:s d/m/Y")."          *");
    $this->log("*******************************************");
    /**
     * @var PmVideos[] $videos
     */
    $folderMp3 = Yii::app()->params[ 'foldermp3' ];
    $videoSuccess = 0;
    $videoFail = 0;
    $basUrl = Yii::app()->params[ 'baseUrl' ];
    $cats = array_keys(Yii::app()->params[ 'prefixmp3' ]);
    $videos = PmVideos::model()->notaudio()->sourceyt()->active()->belongcategories($cats)->findAll();
    $this->log("Number video not have audio: " . count($videos));
    $count = 0;
    foreach ($videos as $video) {
      $this->log("*******************************************");
      $this->log("*            START PROCESS VIDEO          *");
      $this->log("*            ".date("H:i:s d/m/Y")."          *");
      $this->log("*******************************************");
      $this->log("-- Video name: " . $video->video_title . ' with episode uniq id: ' . $video->uniq_id);
      //            $artistName = isset($video->pmArtist)?$video->pmArtist->name:$video->artist;
      $this->log("-- Video index ". $count);
      if (!empty($video->audio)) {
        $this->log("-- Video has an audio, continue to other video");
        continue;
      }
      $this->log("-- Creating audio for this video");
      $artistName = $video->artist;
      $artistFolder = CVietnameseTools::makeName($artistName);
      $folder = $folderMp3 . $artistFolder;
      $this->log("-- Folder path: ${folder}");
      if (!file_exists($folder)) {
        $this->log("-- Artist folder not found, creating artist folder");
        mkdir($folder, 0755);
      }
      $episodes = $video->pmEpisodes;
      $this->log("-- Number of EPISODE: ".count($episodes));
      if (count($episodes) > 0) {
        $this->log("-- Process Video with ".count($episodes)." EPISODE");
        $audioInfo = $this->processEpisode($video, $episodes, $folder);
        if (count($audioInfo) <= 0) {
          $videoFail++;
        } else {
          $video->audio = serialize($audioInfo);
          if ($video->update()) {
            $this->log("UPDATE VIDEO ".$video->video_title." > HAS EPISODE SUCCESS!");
            $videoSuccess++;
          } else {
            $videoFail++;
          }
        }
      } else {
        $this->log("-- Process Single Video");
        $tagData = new IDTagInfo();
        $tagData->Title = $video->video_title . '_wWw.PhatAm.com';
        $tagData->FileName = CVietnameseTools::makeMp3Name($video->video_title) . '_' . CVietnameseTools::makeName($video->artist) . '_wWw.PhatAm.com.mp3';
        $tagData->Album = $video->artist;
        $tagData->Artist = $video->artist;
        $tagData->Comment = $video->description;
        $coverUrl = $video->getCoverUrl();
        $this->log("---- Tag info TITLE: ".$tagData->Title);
//        $this->log("---- Tag info FILE NAME: ".$tagData->FileName);
//        $this->log("---- Tag info ALBUM: ".$tagData->Album);
//        $this->log("---- Tag info ARTIST: ".$tagData->Artist);
//        $this->log("---- Tag info COMMENT: ".$tagData->Comment);
//        $this->log("---- Tag info COVER URL: ".$coverUrl);
        $mp3File = $this->downloadVideo($video->yt_id, $coverUrl, $tagData, $folder, $video->category);
        if (!$mp3File) {
          $this->log("---- Download MP3 ".$tagData->Title." Fail!");
          $videoFail++;
        } else {
          $this->log("---- Download MP3 ".$tagData->Title." Success!");
          $audioInfo[ 1 ] = array('title' => $video->video_title, 'url' => $basUrl . $artistFolder . '/' . $mp3File);
          $video->audio = serialize($audioInfo);
          if ($video->update()) {
            $this->log("UPDATE VIDEO ".$video->video_title." SUCCESS!");
            $videoSuccess++;
          } else {
            $videoFail++;
          }
        }

      }
      $this->log("************ END PROCESS VIDEO ************");
//      if ($count == 0) {
//        break;
//      }
      $count = $count + 1;
    }
    $this->log("END >>> Total process: " . count($videos));
    $this->log("END >>> Load mp3 for video success - " . $videoSuccess . '|fail - ' . $videoFail);
    unlink($lockFile);
  }

  /**
   * @param string $fileOut Path file out
   * @param array $fileIn Array path file in
   * @return string Path file out
   */
  public function mergeFile($fileOut, $fileIn) {
    $fileInputStr = '';
    $fileComplexStr = '';
    $countInput = count($fileIn);
    $count = 0;
    foreach ($fileIn as $file) {
      if (!is_readable($file)) {
        $this->log('Cannot read '.$file);
        return false;
      }
      $fileInputStr .= ' -i '.$file;
      $fileComplexStr .= '['.$count++.':0]';
    }
    if ($fileInputStr != '' && $countInput>=2) {
      $sh = "./ffmpeg $fileInputStr -filter_complex '".$fileComplexStr."concat=n=$countInput:v=0:a=1[out]' -map '[out]' $fileOut";
//      $sh = './ffmpeg'. $fileInputStr .' -filter_complex amix=inputs='. $countInput .':duration=first:dropout_transition=3 '.$fileOut;
      $this->log($sh);
      exec($sh);
    }
    return true;
  }

  public function downloadVideo($ytId, $coverUrl, $tagsData, $folder, $category) {
    $this->log("---- Start Convert Video ${ytId} to MP3");
    if (empty($ytId)) {
      $this->log("---- Youtube Id is not null");
      return false;
    }
    $videoLink = sprintf('https://www.youtube.com/watch?v=%s', $ytId);
    $crawInfo = isset(Yii::app()->params['prefixmp3'][$category]) ? Yii::app()->params['prefixmp3'][$category] : array('file' => '/var/www/phatammp3/prefixmp3/sachnoi.mp3', 'bitrate' => '320K');
    $processInfo = array('url' => $videoLink, 'timeDownloadMp3' => 0, 'timeDownloadImage' => 0, 'timeMerge' => 0, 'timeWriteTag' => 0, 'imageAttach' => $coverUrl, 'fileMp3' => $tagsData->FileName);
    $startTime = time();

    $prefixFile = $crawInfo['file'];
    $mp3Engine = new MP3Process();
    /**
     * Download video file and convert to mp3
     */
    try {
      $this->log("-- Start Init Tool");
      $converter = new YTDownloader($videoLink);
      $converter->setAudioFormat('mp3');
      $converter->setFfmpegLogsActive(false);
      $converter->setDeleteVideo(false);
      $response = $converter->downloadAudio();
//      $this->log($response);
//      $videoFile = new DownloadFile($response['videoPath']);
//      $mp3File = new DownloadFile($response['audioPath']);
//      $this->log("------ Checking duration mp3 file converted ". $mp3File->getDurationEstimate() ." and compare with video duration ".$videoFile->getDurationEstimate());
//      if ($mp3File->getDurationEstimate() < $videoFile->getDurationEstimate()) {
//        $this->log("------ Download mp3 form video: " . $videoLink . " fail!");
//        return false;
//      }
      if ($response == false) {
        $this->log("------ Download mp3 form video: " . $videoLink . " fail!");
        return false;
      }
      $mp3TmpPath = $response['audioPath'];
      $videoTmpPath = $response['videoPath'];
      $this->log("------ Download MP3 Response ".$mp3TmpPath);
      $processInfo['timeDownloadMp3'] = time() - $startTime;

      /**
       * Download file img
       */
      $responseImg = $converter->getVideoThumb();
      if ($responseImg == false) {
        $this->log("------ Download Image: " . $coverUrl . " fail!");
        return false;
      }
      $startTime = time();

      $this->log("------ MP3 Response file size: ". FileUtils::getSize($mp3TmpPath));
      if (FileUtils::getSize($mp3TmpPath) > 0) {
        /**
         * Process merge file mp3
         */
        $desFile = $folder . '/' . $tagsData->FileName;
        $fileIns = array($prefixFile, $mp3TmpPath);
        $this->log("------ Merge MP3 with prefix");
        if ($this->mergeFile($desFile, $fileIns)) {
          //Delete file download
          unlink($mp3TmpPath);
          $processInfo[ 'timeMerge' ] = time() - $startTime;
          $startTime = time();

          /**
           * Process write IDTag to mp3
           */
          $this->log("Image response ". $responseImg);
          $imageThumb = $converter->getDownloadsDir().$converter->getThumb();
          $this->log("Image Thumbs file size ". FileUtils::getSize($imageThumb));
          if (FileUtils::getSize($imageThumb) > 0) {
            $this->log("load attach image ". $tagsData->loadAttachedPicture($imageThumb));
            if ($tagsData->loadAttachedPicture($imageThumb)) {
              $processInfo[ 'attachImage' ] = 'success';
            } else {
              $processInfo[ 'attachImage' ] = 'false';
            }
          }
          if ($mp3Engine->WrigeIDTag($desFile, $tagsData)) {
            //Delete file image
            if (FileUtils::getSize($imageThumb) > 0) {
              unlink($imageThumb);
            }
            $processInfo[ 'writeTag' ] = 'success';
            $processInfo[ 'timeWriteTag' ] = time() - $startTime;
            $this->log(json_encode($processInfo));
            unlink($videoTmpPath);
            return $tagsData->FileName;
          } else {
            $processInfo[ 'writeTag' ] = 'fail';
            $processInfo[ 'timeWriteTag' ] = time() - $startTime;
          }
          $this->log("------ Merge MP3 with prefix: DONE");
        } else {
          $processInfo[ 'merge' ] = 'fail';
        }
      } else {
        $processInfo[ 'downloadmp3' ] = 'fail';
      }
      $this->log(json_encode($processInfo));
      return false;
    } catch (Exception $e) {
      $this->log("------ Convert to MP3 Failed: ".$e->getMessage());
      return false;
    }
  }

  public function actionTestConverter() {
    $folderMp3 = Yii::app()->params['foldermp3'];
    $criteria = new CDbCriteria();
    $criteria->addInCondition('yt_id', array('JA9eP5G1PTo'));
    $videos = PmVideos::model()->findAll($criteria);
    $this->log("video found: " . count($videos));
    foreach ($videos as $video) {
      if (empty($video->audio)) {
        $this->log("-- Video not has audio");
        $this->log("-- Creating audio for this video");
        $artistName = $video->artist;
        $artistFolder = CVietnameseTools::makeName($artistName);
        $folder = $folderMp3 . $artistFolder;
        $this->log("-- Folder path: ${folder}");
        if (!file_exists($folder)) {
          $this->log("-- Artist folder not found, creating artist folder");
          mkdir($folder, 0755);
        }
        $this->log("-- Process Single Video");
        $tagData = new IDTagInfo();
        $tagData->Title = $video->video_title . '_wWw.PhatAm.com';
        $tagData->FileName = CVietnameseTools::makeMp3Name($video->video_title) . '_' . CVietnameseTools::makeName($video->artist) . '_wWw.PhatAm.com.mp3';
        $tagData->Album = $video->artist;
        $tagData->Artist = $video->artist;
        $tagData->Comment = $video->description;
        $coverUrl = $video->getCoverUrl();
        $this->log("---- Tag info TITLE: ".$tagData->Title);
        $this->downloadVideo($video->yt_id, $coverUrl, $tagData, $folder, $video->category);
      }
    }
  }

  public function processTestEpisode($video, $episodes) {
    $basUrl = Yii::app()->params[ 'baseUrl' ];
    $audioInfo = array();
    $useYtThumb = false;
    //        $artistName = isset($video->pmArtist)?$video->pmArtist->name:$video->artist;
    $artistName = $video->artist;
    $artisFolder = CVietnameseTools::makeName($artistName);
    $this->log("Process Video has episodes " . $video->video_title . ' 1...');
    $tagData = new IDTagInfo();
    $tagData->Title = $video->video_title . '_1_wWw.PhatAm.com';;
    $tagData->FileName = CVietnameseTools::makeMp3Name($video->video_title) . '_' . CVietnameseTools::makeName($video->artist) . '_1' . '_wWw.PhatAm.com.mp3';
    $tagData->Album = $video->artist;
    $tagData->Artist = $video->artist;
    $tagData->Comment = $video->description;
    $coverUrl = $video->getCoverUrl();
    if ($video->pmArtist == null || empty($video->pmArtist->avatar)) {
      $useYtThumb = true;
    }
    //    $mp3File = $this->processVideo($video->videoUrl->direct, $coverUrl, $tagData, $folderMp3, $video->category);
    $this->log("Process testing base video ");
    $this->log("Base video title: ".$video->video_title . '_1');
    $audioInfo[ 1 ] = array('title' => $video->video_title . '_1', 'url' => $basUrl . $artisFolder . '/' . $video->video_title);
    $i = 2;
    foreach ($episodes as $episode) {
      $this->log("Process episode " . $video->video_title . ' ' . $episode->episode_id . '...');
      $tagEpisodeData = new IDTagInfo();
      $tagEpisodeData->Title = $video->video_title . '_' . intval($episode->episode_id) . '_wWw.PhatAm.com';
      $tagEpisodeData->FileName = CVietnameseTools::makeMp3Name($video->video_title) . '_' . CVietnameseTools::makeName($video->artist) . '_' . intval($episode->episode_id) . '_wWw.PhatAm.com.mp3';
      $tagEpisodeData->Album = $video->artist;
      $tagEpisodeData->Artist = $video->artist;
      $tagEpisodeData->Comment = $video->description;
      $coverEpisodeUrl = $coverUrl;
      if ($useYtThumb) {
        $coverEpisodeUrl = $episode->yt_thumb;
      }
      $this->log("Process testing episode of video ");
      $this->log("Episode title: ".$video->video_title . '_1');
      $audioInfo[ $i ] = array('title' => $video->video_title . '_' . intval($episode->episode_id), 'url' => $basUrl . $artisFolder . '/' . $video->video_title . '_' . intval($episode->episode_id));
      $i++;
    }
    return $audioInfo;
  }

  public function actionTestEpisode() {
    /**
     * @var PmVideos[] $videos
     */
    $cats = array_keys(Yii::app()->params[ 'prefixmp3' ]);
//    $videos = PmVideos::model()->notaudio()->sourceyt()->active()->belongcategories($cats)->findAll();
    $criteria = new CDbCriteria();
    $criteria->addInCondition('uniq_id', array('836452c77'));
    $videos = PmVideos::model()->findAll($criteria);
    $this->log("Number video not have audio: " . count($videos));
    foreach ($videos as $video) {
      $this->log('File name: ' . CVietnameseTools::makeCodeName($video->video_title) . '_' . CVietnameseTools::makeName($video->artist) . '_wWw.PhatAm.com' . ' - ' . $video->uniq_id);
      $episodes = $video->pmEpisodes;
      $this->log("Searching Episode");
      $this->log($episodes);
      $this->log("-- Number of EPISODE: ".count($episodes));
      if (count($episodes) > 0) {
        //        continue;
        $this->log("-- Process Video with ".count($episodes)." EPISODE");
        $audioInfo = $this->processTestEpisode($video, $episodes);
        $this->log("-- Audio Info ");
        $this->log($audioInfo);
      }
    }
  }
}

