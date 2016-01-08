<?php

/**
 * db thì anh vào: http://phatam.com/phpmyadmin/
$db_name = 'phatam_phatam';  // MySQL database name
$db_user = 'phatam_phatam';  // MySQL username
$db_pass = 'gz5YoKtt';  // MySQL password
$db_host = 'localhost';

 */
// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Phat am process mp3',

	// preloading 'log' component
	'preload'=>array('log'),
    // autoloading model and component classes
    'import'=>array(
        'application.models.*',
        'application.components.*',
    ),
	// application components
	'components'=>array(
		// uncomment the following to use a MySQL database
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=phatam_phatam',
			'emulatePrepare' => true,
			'username' => 'phatam_phatam',
			'password' => 'gz5YoKtt',
			'charset' => 'utf8',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
					'maxFileSize' => 100*1024, // 100MB
				),
			),
		),
	),
    'params'=>array(
        'prefixmp3' => array(
            '1' => array(
                'file' => '/opt/phatammp3/prefixmp3/phapthoai.mp3',
                'bitrate' => '64K' //Bitrate download file tu dirpy (64K,128K,160K,192K,256K)
            ),
            '4' => array(
                'file' => '/opt/phatammp3/prefixmp3/baihat.mp3',
                'bitrate' => '128K' //Bitrate download file tu dirpy (64K,128K,160K,192K,256K)
            ),
            '5' => array(
                'file' => '/opt/phatammp3/prefixmp3/baihat.mp3',
                'bitrate' => '128K' //Bitrate download file tu dirpy (64K,128K,160K,192K,256K)
            ),
            '6' => array(
                'file' => '/opt/phatammp3/prefixmp3/baihat.mp3',
                'bitrate' => '128K' //Bitrate download file tu dirpy (64K,128K,160K,192K,256K)
            ),
            '7' => array(
                'file' => '/opt/phatammp3/prefixmp3/phapthoai.mp3',
                'bitrate' => '128K' //Bitrate download file tu dirpy (64K,128K,160K,192K,256K)
            ),
            '500' => array(
                'file' => '/opt/phatammp3/prefixmp3/baikinh.mp3',
                'bitrate' => '128K' //Bitrate download file tu dirpy (64K,128K,160K,192K,256K)
            ),
            '501' => array(
                'file' => '/opt/phatammp3/prefixmp3/phapthoai.mp3',
                'bitrate' => '64K' //Bitrate download file tu dirpy (64K,128K,160K,192K,256K)
            ),
            '502' => array(
                'file' => '/opt/phatammp3/prefixmp3/sachnoi.mp3',
                'bitrate' => '128K' //Bitrate download file tu dirpy (64K,128K,160K,192K,256K)
            ),
            '503' => array(
                'file' => '/opt/phatammp3/prefixmp3/phapthoai.mp3',
                'bitrate' => '128K' //Bitrate download file tu dirpy (64K,128K,160K,192K,256K)
            ),
        ),
        'foldermp3' => '/home/phatam/domains/phatam.com/public_html/upload2015/mp3/giang/',
        'baseUrl' => 'http://www.phatam.com/upload2015/mp3/giang/',
        'baseUrlImg' => 'http://www.phatam.com/rest/public/images/artist/',
        'lockfile' => '/tmp/processmp3.lock'
    ),
);

$url = 'http://www.dirpy.com/download?url=https%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3DmvyxhQRFF70&format_id=0&filename=L_p%20tr_nh%20web%20v_i%20java%20-%20B_i%201%3A%20Thi_t%20l_p%20m_i%20tr__ng&ext=mp3&audio_format=320K&start_time=00:00:00&end_time=00:15:01&type=audio&ID3%5Btitle%5D=L%E1%BA%ADp+tr%C3%ACnh+web+v%E1%BB%9Bi+java+-+B%C3%A0i+1%3A+Thi%E1%BA%BFt+l%E1%BA%ADp+m%C3%B4i+tr%C6%B0%E1%BB%9Dng&ID3%5Bartist%5D=&ID3%5Bcomment%5D=&ID3%5Bgenre%5D=&ID3%5Balbum%5D=&ID3%5Btrack%5D=0&ID3%5Byear%5D=&downloadToken=1451644291267';