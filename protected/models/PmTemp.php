<?php

/**
 * This is the model class for table "pm_temp".
 *
 * The followings are the available columns in table 'pm_temp':
 * @property integer $id
 * @property string $url
 * @property string $artist
 * @property string $video_title
 * @property string $description
 * @property string $tags
 * @property integer $category
 * @property string $username
 * @property integer $user_id
 * @property integer $added
 * @property integer $source_id
 * @property integer $language
 * @property string $thumbnail
 */
class PmTemp extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pm_temp';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('description, thumbnail', 'required'),
			array('category, user_id, added, source_id, language', 'numerical', 'integerOnly'=>true),
			array('url, artist, video_title, tags', 'length', 'max'=>255),
			array('username', 'length', 'max'=>100),
			array('thumbnail', 'length', 'max'=>25),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, url, artist, video_title, description, tags, category, username, user_id, added, source_id, language, thumbnail', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
			'url' => 'Url',
			'artist' => 'Artist',
			'video_title' => 'Video Title',
			'description' => 'Description',
			'tags' => 'Tags',
			'category' => 'Category',
			'username' => 'Username',
			'user_id' => 'User',
			'added' => 'Added',
			'source_id' => 'Source',
			'language' => 'Language',
			'thumbnail' => 'Thumbnail',
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
		$criteria->compare('url',$this->url,true);
		$criteria->compare('artist',$this->artist,true);
		$criteria->compare('video_title',$this->video_title,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('tags',$this->tags,true);
		$criteria->compare('category',$this->category);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('added',$this->added);
		$criteria->compare('source_id',$this->source_id);
		$criteria->compare('language',$this->language);
		$criteria->compare('thumbnail',$this->thumbnail,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PmTemp the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
