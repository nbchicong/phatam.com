<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 17/10/2014
 * Time: 21:33
 */

require_once dirname(__FILE__) . '/getid3/getid3.php';
require_once dirname(__FILE__) . '/getid3/write.php';
require_once dirname(__FILE__) . '/IDTagInfo.php';

class MP3Process {

    const TAG = 'MP3Process';
    const TAG_FORMAT = "UTF-8";

    private $id3 = null;
    private $tagwriter = null;

    public function __construct() {
        $this->id3 = new getID3();
        $this->id3->setOption(array('encoding'=>self::TAG_FORMAT));
        $this->tagwriter = new getid3_writetags();
    }

    // sample usage:
    // $FilenameOut   = 'combined.mp3';
    // $FilenamesIn[] = 'file1.mp3';
    // $FilenamesIn[] = 'file2.mp3';
    // $FilenamesIn[] = 'file3.mp3';
    //
    // if (CombineMultipleMP3sTo($FilenameOut, $FilenamesIn)) {
    //     echo 'Successfully copied '.implode(' + ', $FilenamesIn).' to '.$FilenameOut;
    // } else {
    //     echo 'Failed to copy '.implode(' + ', $FilenamesIn).' to '.$FilenameOut;
    // }
    public function CombineMultipleMP3sTo($FilenameOut, $FilenamesIn){
        foreach ($FilenamesIn as $nextinputfilename) {
            if (!is_readable($nextinputfilename)) {
                CUtils::logError('Cannot read "'.$nextinputfilename, self::TAG);
                return false;
            }
        }
        ob_start();
        if ($fp_output = fopen($FilenameOut, 'wb')) {

            ob_end_clean();
            foreach ($FilenamesIn as $nextinputfilename) {

                $CurrentFileInfo = $this->id3->analyze($nextinputfilename);
                if (isset($CurrentFileInfo['fileformat']) && $CurrentFileInfo['fileformat'] == 'mp3') {

                    ob_start();
                    if ($fp_source = fopen($nextinputfilename, 'rb')) {

                        ob_end_clean();
                        $CurrentOutputPosition = ftell($fp_output);

                        // copy audio data from first file
                        fseek($fp_source, $CurrentFileInfo['avdataoffset'], SEEK_SET);
                        while (!feof($fp_source) && (ftell($fp_source) < $CurrentFileInfo['avdataend'])) {
                            fwrite($fp_output, fread($fp_source, 32768));
                        }
                        fclose($fp_source);

                        // trim post-audio data (if any) copied from first file that we don't need or want
                        $EndOfFileOffset = $CurrentOutputPosition + ($CurrentFileInfo['avdataend'] - $CurrentFileInfo['avdataoffset']);
                        fseek($fp_output, $EndOfFileOffset, SEEK_SET);
                        ftruncate($fp_output, $EndOfFileOffset);

                    } else {
                        $errormessage = ob_get_contents();
                        ob_end_clean();
                        CUtils::logError('failed to open '.$nextinputfilename.' for reading', self::TAG);
                        fclose($fp_output);
                        return false;

                    }

                } else {
                    CUtils::logError($nextinputfilename.' is not MP3 format', self::TAG);
                    fclose($fp_output);
                    return false;
                }

            }

        } else {
            $errormessage = ob_get_contents();
            ob_end_clean();
            CUtils::logError('failed to open '.$FilenameOut.' for writing', self::TAG);
            return false;

        }

        fclose($fp_output);
        return true;
    }

    /**
     * @param $filename
     * @param $tagData IDTagInfo
     */
    public function WrigeIDTag($filename, $tagData){
        $this->tagwriter->filename = $filename;
        $this->tagwriter->tagformats     = array('id3v2.3');
        $this->tagwriter->overwrite_tags = true;
        $this->tagwriter->tag_encoding   = self::TAG_FORMAT;
        $this->tagwriter->tag_data = $tagData->getTagData();
        if ($this->tagwriter->WriteTags()) {
            CUtils::logInfo('Successfully wrote tags to '.$filename, self::TAG);
            if (!empty($this->tagwriter->warnings)) {
                CUtils::logError('There were some warnings:">'.implode("\n", $this->tagwriter->warnings), self::TAG);
            }

            return true;
        } else {
            CUtils::logError('Failed to write tags!'.implode("\n", $this->tagwriter->errors), self::TAG);
            return false;
        }
    }


} 
