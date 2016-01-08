<?php

/**
 * This is the model class for table "pm_videos".
 *
 * The followings are the available columns in table 'pm_videos':
 * @property integer $id
 * @property string $uniq_id
 * @property string $artist
 * @property string $video_title
 * @property string $description
 * @property string $yt_id
 * @property integer $yt_length
 * @property string $yt_thumb
 * @property integer $yt_views
 * @property string $category
 * @property string $submitted
 * @property string $lastwatched
 * @property string $added
 * @property integer $site_views
 * @property string $url_flv
 * @property integer $source_id
 * @property integer $language
 * @property string $age_verification
 * @property string $last_check
 * @property integer $status
 * @property string $featured
 * @property string $restricted
 * @property string $audio
 * @property integer $artist_id
 *
 * The followings are the available model relations:
 * @property PmVideosUrls $videoUrl
 * @property PmCategories $pmCategory
 * @property PmSources $pmSource
 * @property PmArtist $pmArtist
 * @property PmEpisode[] $pmEpisodes
 */
class PmVideos extends CActiveRecord
{

    const VIDEO_STATUS_UNCHECKD = 0;
    const VIDEO_STATUS_FOUND = 1;
    const VIDEO_STATUS_NOT_FOUND = 2;
    const VIDEO_STATUS_GEORETRICTED = 2;


	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pm_videos';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('description, restricted, audio, artist_id', 'required'),
			array('yt_length, yt_views, site_views, source_id, language, status, artist_id', 'numerical', 'integerOnly'=>true),
			array('uniq_id, lastwatched, added, last_check', 'length', 'max'=>10),
			array('artist, video_title, submitted', 'length', 'max'=>100),
			array('yt_id', 'length', 'max'=>50),
			array('yt_thumb, url_flv', 'length', 'max'=>255),
			array('category', 'length', 'max'=>30),
			array('age_verification, featured, restricted', 'length', 'max'=>1),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, uniq_id, artist, video_title, description, yt_id, yt_length, yt_thumb, yt_views, category, submitted, lastwatched, added, site_views, url_flv, source_id, language, age_verification, last_check, status, featured, restricted, audio, artist_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            'videoUrl' => array(self::BELONGS_TO, 'PmVideosUrls', 'uniq_id'),
            'pmCategory' => array(self::BELONGS_TO, 'PmCategories', 'category'),
            'pmSource' => array(self::BELONGS_TO, 'PmSources', 'source_id'),
            'pmArtist' => array(self::BELONGS_TO, 'PmArtist', 'artist_id'),
		);
	}

    /**
     *
     */
    public function getPmEpisodes(){
			Yii::log("Get Episodes of an video", CLogger::LEVEL_INFO, "ProcessMp3");
        return PmEpisode::model()->findAllByAttributes(array('uniq_id' => $this->uniq_id));
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'uniq_id' => 'Uniq',
			'artist' => 'Artist',
			'video_title' => 'Video Title',
			'description' => 'Description',
			'yt_id' => 'Yt',
			'yt_length' => 'Yt Length',
			'yt_thumb' => 'Yt Thumb',
			'yt_views' => 'Yt Views',
			'category' => 'Category',
			'submitted' => 'Submitted',
			'lastwatched' => 'Lastwatched',
			'added' => 'Added',
			'site_views' => 'Site Views',
			'url_flv' => 'Url Flv',
			'source_id' => 'Source',
			'language' => 'Language',
			'age_verification' => 'Age Verification',
			'last_check' => 'Last Check',
			'status' => 'Status',
			'featured' => 'Featured',
			'restricted' => 'Restricted',
			'audio' => 'Audio',
			'artist_id' => 'Artist',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('uniq_id',$this->uniq_id,true);
		$criteria->compare('artist',$this->artist,true);
		$criteria->compare('video_title',$this->video_title,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('yt_id',$this->yt_id,true);
		$criteria->compare('yt_length',$this->yt_length);
		$criteria->compare('yt_thumb',$this->yt_thumb,true);
		$criteria->compare('yt_views',$this->yt_views);
		$criteria->compare('category',$this->category,true);
		$criteria->compare('submitted',$this->submitted,true);
		$criteria->compare('lastwatched',$this->lastwatched,true);
		$criteria->compare('added',$this->added,true);
		$criteria->compare('site_views',$this->site_views);
		$criteria->compare('url_flv',$this->url_flv,true);
		$criteria->compare('source_id',$this->source_id);
		$criteria->compare('language',$this->language);
		$criteria->compare('age_verification',$this->age_verification,true);
		$criteria->compare('last_check',$this->last_check,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('featured',$this->featured,true);
		$criteria->compare('restricted',$this->restricted,true);
		$criteria->compare('audio',$this->audio,true);
		$criteria->compare('artist_id',$this->artist_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public function scopes()
    {
        return array(
            'notaudio'=>array(
                "condition" => "audio=''",
            ),
            'sourceyt' => array(
                "condition" => "source_id=3",
            ),
            'uncheckded' => array(
                "condition" => "status=0",
            ),
            'active' => array(
                "condition" => "status=1 or status=0",
            ),
        );
    }

    public function defaultScope()
    {
        return array(
            'order'=>'added DESC',
        );
    }

    public function recently($limit=5)
    {
        $this->getDbCriteria()->mergeWith(array(
            'order'=>'added DESC',
            'limit'=>$limit,
        ));
        return $this;
    }

    public function belongcategories($cats = array()){
        $criteria=new CDbCriteria;
        $criteria->addInCondition('category', $cats);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }

    public function getCoverUrl(){
        if($this->pmArtist == null || empty($this->pmArtist->avatar)){
            return $this->yt_thumb;
        }else{
            return $this->pmArtist->getAvatarUrl();
        }
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PmVideos the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
