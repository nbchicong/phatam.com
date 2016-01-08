<?php

/**
 * This is the model class for table "pm_comments".
 *
 * The followings are the available columns in table 'pm_comments':
 * @property integer $id
 * @property string $uniq_id
 * @property string $username
 * @property string $comment
 * @property string $added
 * @property string $user_ip
 * @property integer $user_id
 * @property string $approved
 */
class PmComments extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pm_comments';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('comment', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('uniq_id', 'length', 'max'=>50),
			array('username', 'length', 'max'=>100),
			array('added', 'length', 'max'=>10),
			array('user_ip', 'length', 'max'=>20),
			array('approved', 'length', 'max'=>1),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, uniq_id, username, comment, added, user_ip, user_id, approved', 'safe', 'on'=>'search'),
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
			'uniq_id' => 'Uniq',
			'username' => 'Username',
			'comment' => 'Comment',
			'added' => 'Added',
			'user_ip' => 'User Ip',
			'user_id' => 'User',
			'approved' => 'Approved',
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
		$criteria->compare('username',$this->username,true);
		$criteria->compare('comment',$this->comment,true);
		$criteria->compare('added',$this->added,true);
		$criteria->compare('user_ip',$this->user_ip,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('approved',$this->approved,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PmComments the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
