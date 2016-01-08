<?php

/**
 * This is the model class for table "art_articles".
 *
 * The followings are the available columns in table 'art_articles':
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property string $category
 * @property integer $status
 * @property integer $date
 * @property integer $author
 * @property string $allow_comments
 * @property integer $comment_count
 * @property string $views
 */
class ArtArticles extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'art_articles';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('content', 'required'),
			array('status, date, author, comment_count', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>255),
			array('category', 'length', 'max'=>100),
			array('allow_comments', 'length', 'max'=>1),
			array('views', 'length', 'max'=>8),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, title, content, category, status, date, author, allow_comments, comment_count, views', 'safe', 'on'=>'search'),
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
			'title' => 'Title',
			'content' => 'Content',
			'category' => 'Category',
			'status' => 'Status',
			'date' => 'Date',
			'author' => 'Author',
			'allow_comments' => 'Allow Comments',
			'comment_count' => 'Comment Count',
			'views' => 'Views',
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
		$criteria->compare('title',$this->title,true);
		$criteria->compare('content',$this->content,true);
		$criteria->compare('category',$this->category,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('date',$this->date);
		$criteria->compare('author',$this->author);
		$criteria->compare('allow_comments',$this->allow_comments,true);
		$criteria->compare('comment_count',$this->comment_count);
		$criteria->compare('views',$this->views,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ArtArticles the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
