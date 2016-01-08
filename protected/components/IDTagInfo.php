<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 18/10/2014
 * Time: 00:30
 */

class IDTagInfo {

    const TAG = 'IDTagInfo';

    public $Title = '';
    public $FileName = '';
    public $Artist = '';
    public $Album = '';
    public $Year = '';
    public $Track = '';
    public $Comment = '';
    public $Genre = '';
    public $Picture = array(
        'data' => '',
        'description' => '',
        'mime' => '',
        'picturetypeid' => 0x03
    );

    public function loadAttachedPicture($imgFile){
        ob_start();
        if ($fd = fopen($imgFile, 'rb')) {
            ob_end_clean();
            $APICdata = fread($fd, filesize($imgFile));
            fclose ($fd);

            list($APIC_width, $APIC_height, $APIC_imageTypeID) = GetImageSize($imgFile);
            $imagetypes = array(1=>'gif', 2=>'jpeg', 3=>'png');
            if (isset($imagetypes[$APIC_imageTypeID])) {
                $this->Picture['data']          = $APICdata;
                $this->Picture['description']   = $imgFile;
                $this->Picture['mime']          = 'image/'.$imagetypes[$APIC_imageTypeID];
                return true;
            } else {
                CUtils::logError('Image file "'.$imgFile.'"invalid image format (only GIF, JPEG, PNG)',self::TAG);
            }
        } else {
            $errormessage = ob_get_contents();
            ob_end_clean();
            CUtils::logError('Cannot open '.$imgFile, self::TAG);
        }
        return false;
    }

    public function getTagData($attachPicture = true){
        $TagData['title'][] = $this->Title;
        $TagData['album'][] = $this->Album;
        $TagData['artist'][] = $this->Artist;
        $TagData['year'][] = $this->Year;
        $TagData['comment'][] = $this->Comment;
        $TagData['track'][] = $this->Track;
        if($attachPicture && $this->Picture['data']){
            $TagData['attached_picture'][0]['data'] = $this->Picture['data'];
            $TagData['attached_picture'][0]['description'] = $this->Picture['description'];
            $TagData['attached_picture'][0]['mime'] = $this->Picture['mime'];
            $TagData['attached_picture'][0]['picturetypeid'] = $this->Picture['picturetypeid'];
        }

        return $TagData;
    }

} 