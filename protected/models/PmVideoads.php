<?php

/**
 * This is the model class for table "pm_videoads".
 *
 * The followings are the available columns in table 'pm_videoads':
 * @property integer $id
 * @property string $hash
 * @property string $name
 * @property string $flv_url
 * @property string $redirect_url
 * @property string $redirect_type
 * @property integer $clicks
 * @property string $impressions
 * @property string $status
 */
class PmVideoads extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pm_videoads';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('redirect_url', 'required'),
			array('clicks', 'numerical', 'integerOnly'=>true),
			array('hash', 'length', 'max'=>12),
			array('name', 'length', 'max'=>50),
			array('flv_url', 'length', 'max'=>255),
			array('redirect_type, status', 'length', 'max'=>1),
			array('impressions', 'length', 'max'=>9),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, hash, name, flv_url, redirect_url, redirect_type, clicks, impressions, status', 'safe', 'on'=>'search'),
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
			'hash' => 'Hash',
			'name' => 'Name',
			'flv_url' => 'Flv Url',
			'redirect_url' => 'Redirect Url',
			'redirect_type' => 'Redirect Type',
			'clicks' => 'Clicks',
			'impressions' => 'Impressions',
			'status' => 'Status',
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
		$criteria->compare('hash',$this->hash,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('flv_url',$this->flv_url,true);
		$criteria->compare('redirect_url',$this->redirect_url,true);
		$criteria->compare('redirect_type',$this->redirect_type,true);
		$criteria->compare('clicks',$this->clicks);
		$criteria->compare('impressions',$this->impressions,true);
		$criteria->compare('status',$this->status,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PmVideoads the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
