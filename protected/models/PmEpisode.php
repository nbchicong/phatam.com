<?php

/**
 * This is the model class for table "pm_episode".
 *
 * The followings are the available columns in table 'pm_episode':
 * @property string $uniq_id
 * @property string $episode
 * @property string $embed
 * @property string $episode_id
 * @property string $title
 * @property integer $source_id
 * @property string $direct
 * @property string $yt_thumb
 * @property string $url_flv
 * @property string $yt_id
 * @property string $embed_code
 * @property string $mp4
 * @property integer $yt_length
 */
class PmEpisode extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pm_episode';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('uniq_id, episode, embed, episode_id, source_id, direct, yt_thumb, url_flv, yt_id, embed_code, mp4, yt_length', 'required'),
			array('source_id, yt_length', 'numerical', 'integerOnly'=>true),
			array('uniq_id', 'length', 'max'=>10),
			array('episode, embed', 'length', 'max'=>7),
			array('episode_id', 'length', 'max'=>4),
			array('title, direct, url_flv, mp4', 'length', 'max'=>225),
			array('yt_thumb', 'length', 'max'=>150),
			array('yt_id', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('uniq_id, episode, embed, episode_id, title, source_id, direct, yt_thumb, url_flv, yt_id, embed_code, mp4, yt_length', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'uniq_id' => 'Uniq',
			'episode' => 'Episode',
			'embed' => 'Embed',
			'episode_id' => 'Episode',
			'title' => 'Title',
			'source_id' => 'Source',
			'direct' => 'Direct',
			'yt_thumb' => 'Yt Thumb',
			'url_flv' => 'Url Flv',
			'yt_id' => 'Yt',
			'embed_code' => 'Embed Code',
			'mp4' => 'Mp4',
			'yt_length' => 'Yt Length',
		);
	}

    public function defaultScope()
    {
        return array(
            'order'=>'episode_id',
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

		$criteria->compare('uniq_id',$this->uniq_id,true);
		$criteria->compare('episode',$this->episode,true);
		$criteria->compare('embed',$this->embed,true);
		$criteria->compare('episode_id',$this->episode_id,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('source_id',$this->source_id);
		$criteria->compare('direct',$this->direct,true);
		$criteria->compare('yt_thumb',$this->yt_thumb,true);
		$criteria->compare('url_flv',$this->url_flv,true);
		$criteria->compare('yt_id',$this->yt_id,true);
		$criteria->compare('embed_code',$this->embed_code,true);
		$criteria->compare('mp4',$this->mp4,true);
		$criteria->compare('yt_length',$this->yt_length);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PmEpisode the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
