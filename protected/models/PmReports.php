<?php

/**
 * This is the model class for table "pm_reports".
 *
 * The followings are the available columns in table 'pm_reports':
 * @property integer $id
 * @property string $r_type
 * @property string $entry_id
 * @property string $added
 * @property string $reason
 * @property string $submitted
 */
class PmReports extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pm_reports';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('r_type', 'length', 'max'=>1),
			array('entry_id', 'length', 'max'=>20),
			array('added', 'length', 'max'=>11),
			array('reason, submitted', 'length', 'max'=>100),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, r_type, entry_id, added, reason, submitted', 'safe', 'on'=>'search'),
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
			'r_type' => 'R Type',
			'entry_id' => 'Entry',
			'added' => 'Added',
			'reason' => 'Reason',
			'submitted' => 'Submitted',
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
		$criteria->compare('r_type',$this->r_type,true);
		$criteria->compare('entry_id',$this->entry_id,true);
		$criteria->compare('added',$this->added,true);
		$criteria->compare('reason',$this->reason,true);
		$criteria->compare('submitted',$this->submitted,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PmReports the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
