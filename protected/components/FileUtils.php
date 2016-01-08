<?php

/**
 * Copyright (c) 2016 CT1905
 * Created by Nguyen Ba Chi Cong <nbchicong@gmail.com>
 * Date: 08/01/2016
 * Time: 13:23
 * ---------------------------------------------------
 * @project: ytdownloader
 * @name: YTDownloader.php
 * @package: ${NAMESPACE}
 * @author: nbchicong
 */

class FileUtils {
    public static function getSize($filePath) {
        if (is_readable($filePath)) {
            return filesize($filePath);
        }
        return 0;
    }
}