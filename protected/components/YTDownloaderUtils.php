<?php

/**
 * Copyright (c) 2015 CT1905
 * Created by Nguyen Ba Chi Cong <nbchicong@gmail.com>
 * Date: 05/12/2015
 * Time: 06:33
 * ---------------------------------------------------
 * @project: ytdownloader
 * @name: YTDownloaderUtils.php
 * @package: ${NAMESPACE}
 * @author: nbchicong
 */

/**
 * @param $valueA
 * @param $valueB
 * @return int
 */
function ascByQuality($valueA, $valueB) {
  $a = $valueA['pref'];
  $b = $valueB['pref'];
  if ($a == $b) return 0;
  return ($a < $b) ? -1 : +1;
}

/**
 * @param $valueA
 * @param $valueB
 * @return int
 */
function descByQuality($valueA, $valueB) {
  $a = $valueA['pref'];
  $b = $valueB['pref'];
  if ($a == $b) return 0;
  return ($a > $b) ? -1 : +1;
}