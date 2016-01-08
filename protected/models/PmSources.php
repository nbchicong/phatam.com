<?php

/**
 * This is the model class for table "pm_sources".
 *
 * The followings are the available columns in table 'pm_sources':
 * @property integer $source_id
 * @property string $source_name
 * @property string $source_rule
 * @property string $url_example
 * @property string $last_check
 * @property string $flv_player_support
 * @property string $embed_player_support
 * @property string $embed_code
 * @property string $user_choice
 */
class PmSources extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pm_sources';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('embed_code, user_choice', 'required'),
			array('source_name', 'length', 'max'=>20),
			array('source_rule', 'length', 'max'=>40),
			array('url_example', 'length', 'max'=>100),
			array('last_check', 'length', 'max'=>10),
			array('flv_player_support, embed_player_support', 'length', 'max'=>1),
			array('user_choice', 'length', 'max'=>15),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('source_id, source_name, source_rule, url_example, last_check, flv_player_support, embed_player_support, embed_code, user_choice', 'safe', 'on'=>'search'),
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
			'source_id' => 'Source',
			'source_name' => 'Source Name',
			'source_rule' => 'Source Rule',
			'url_example' => 'Url Example',
			'last_check' => 'Last Check',
			'flv_player_support' => 'Flv Player Support',
			'embed_player_support' => 'Embed Player Support',
			'embed_code' => 'Embed Code',
			'user_choice' => 'User Choice',
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

		$criteria->compare('source_id',$this->source_id);
		$criteria->compare('source_name',$this->source_name,true);
		$criteria->compare('source_rule',$this->source_rule,true);
		$criteria->compare('url_example',$this->url_example,true);
		$criteria->compare('last_check',$this->last_check,true);
		$criteria->compare('flv_player_support',$this->flv_player_support,true);
		$criteria->compare('embed_player_support',$this->embed_player_support,true);
		$criteria->compare('embed_code',$this->embed_code,true);
		$criteria->compare('user_choice',$this->user_choice,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PmSources the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
