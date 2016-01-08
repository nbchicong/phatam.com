<?php

/**
 * This is the model class for table "pm_users".
 *
 * The followings are the available columns in table 'pm_users':
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $name
 * @property string $gender
 * @property string $country
 * @property string $reg_ip
 * @property string $reg_date
 * @property string $last_signin
 * @property string $email
 * @property string $favorite
 * @property string $power
 * @property string $about
 * @property string $avatar
 * @property string $activation_key
 * @property string $new_password
 * @property string $website
 * @property string $facebook
 * @property string $twitter
 * @property string $lastfm
 */
class PmUsers extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pm_users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('power, about, website, facebook, twitter, lastfm', 'required'),
			array('username, password', 'length', 'max'=>100),
			array('name, email', 'length', 'max'=>150),
			array('gender', 'length', 'max'=>10),
			array('country', 'length', 'max'=>50),
			array('reg_ip, activation_key', 'length', 'max'=>20),
			array('reg_date, last_signin', 'length', 'max'=>12),
			array('favorite, power', 'length', 'max'=>1),
			array('avatar, website, facebook, twitter, lastfm', 'length', 'max'=>255),
			array('new_password', 'length', 'max'=>32),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, username, password, name, gender, country, reg_ip, reg_date, last_signin, email, favorite, power, about, avatar, activation_key, new_password, website, facebook, twitter, lastfm', 'safe', 'on'=>'search'),
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
			'username' => 'Username',
			'password' => 'Password',
			'name' => 'Name',
			'gender' => 'Gender',
			'country' => 'Country',
			'reg_ip' => 'Reg Ip',
			'reg_date' => 'Reg Date',
			'last_signin' => 'Last Signin',
			'email' => 'Email',
			'favorite' => 'Favorite',
			'power' => 'Power',
			'about' => 'About',
			'avatar' => 'Avatar',
			'activation_key' => 'Activation Key',
			'new_password' => 'New Password',
			'website' => 'Website',
			'facebook' => 'Facebook',
			'twitter' => 'Twitter',
			'lastfm' => 'Lastfm',
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
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('gender',$this->gender,true);
		$criteria->compare('country',$this->country,true);
		$criteria->compare('reg_ip',$this->reg_ip,true);
		$criteria->compare('reg_date',$this->reg_date,true);
		$criteria->compare('last_signin',$this->last_signin,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('favorite',$this->favorite,true);
		$criteria->compare('power',$this->power,true);
		$criteria->compare('about',$this->about,true);
		$criteria->compare('avatar',$this->avatar,true);
		$criteria->compare('activation_key',$this->activation_key,true);
		$criteria->compare('new_password',$this->new_password,true);
		$criteria->compare('website',$this->website,true);
		$criteria->compare('facebook',$this->facebook,true);
		$criteria->compare('twitter',$this->twitter,true);
		$criteria->compare('lastfm',$this->lastfm,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PmUsers the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
