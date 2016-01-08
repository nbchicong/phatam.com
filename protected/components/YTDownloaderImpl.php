<?php

/**
 * Copyright (c) 2015 CT1905
 * Created by Nguyen Ba Chi Cong <nbchicong@gmail.com>
 * Date: 05/12/2015
 * Time: 06:33
 * ---------------------------------------------------
 * @project: ytdownloader
 * @name: YTDownloaderImpl.php
 * @package: ${NAMESPACE}
 * @author: nbchicong
 */

interface YTDownloaderImpl {
  const downloadFolder = "/tmp/";
  const audioFolder = "/tmp/";
  const defaultDeleteVideo = TRUE;
  const defaultDownload = "video";
  const defaultVideoQuality = 1;
  const defaultAudioQuality = 320;
  const defaultAudioFormat = "mp3";
  const downloadThumbnail = TRUE;
  const defaultThumbSize = "l";
  const ffmpegLogsActive = FALSE;
  const ffmpegLogsDir = "logs/";
}