<?php
date_default_timezone_set('Asia/Tokyo');

//定数
const DELET_OFF = 0;

//$delete_off = 0;
const DELET_ON = 1;

//変数
$message_id=null;
$delete = null;
$now_date = null;
$up_date = null;
$data = null;
$file_handle = null;
$split_data = null;
$message = array();
$message_array = array();
$success_message=null;
$delete_message=null;
$error_message = array();
$error_message_name = array();
$error_message_text = array();

$clean = array();

//データベース接続
$password = 'root';
$dbname = 'intern';
$user = 'root';
$host = 'localhost';

session_start();
//ボタンを押した後
if( !empty($_POST['btn_submit']) ) {

  // 名前の入力
	if( empty($_POST['post_name']) ) {
		$error_message_name[] = '※表示名を入力してください。';
  }elseif(20 < mb_strlen($_POST['post_name']) ){
    $error_message_name[] = '※20文字以内で入力してください。';
  }else {
    $clean['post_name'] = htmlspecialchars( $_POST['post_name'], ENT_QUOTES);
    $_SESSION['post_name'] = $clean['post_name'];
  }
  
  // メッセージの入力
	if( empty($_POST['message']) ) {
		$error_message_text[] = '※ひと言メッセージを入力してください。';
	}elseif(140 < mb_strlen($_POST['message']) ){
    $error_message_text[] = '※140文字以内で入力してください。';
  } else {
    $clean['message'] = htmlspecialchars( $_POST['message'], ENT_QUOTES);
    $clean['message'] = preg_replace( '/\\r\\n|\\n|\\r/', '<br>', $clean['message']);
  }
  
  if( empty($error_message_name || $error_message_text) ) {
    //データベースに接続
    $mysqli = new mysqli($host, $user, $password, $dbname);

    // エラーの確認
    if( $mysqli->connect_errno ) {
      $error_message[] = '接続できてません。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
    } else {
    
			$mysqli->set_charset('utf8');
      $now_date = date("Y-m-d H:i:s");

			//SQL作成
      $sql=$mysqli->prepare("INSERT INTO board (post_name, message, create_at, update_at) VALUES (?,?,?,?)");
  
      //SQLインジェクション対策
      $sql->bind_param("ssss",$clean['post_name'],$clean['message'],$now_date,$now_date);
      $sql->execute();
      $sql->bind_result($clean['post_name'],$clean['message'],$now_date ,$now_date);
      $sql->store_result();
      $sql->fetch();

			$res = $mysqli->query($sql);
  
      //投稿できたか確認
			if( $sql ) {
				$_SESSION['success_message']  = '投稿しました！';
			} else {
				$error_message[] = '投稿に失敗しました！';
      }
      //sql終了
      $sql->close();
			$mysqli->close();
    }
    header('Location: ./');
  }

}

/*
//削除機能
 if( !empty($_POST['message_id']) ) {
	$message_id = (int)htmlspecialchars( $_POST['message_id'], ENT_QUOTES);
	$mysqli = new mysqli($host, $user, $password, $dbname);
	
	// 接続エラーの確認
	if( $mysqli->connect_errno ) {
		$error_message[] = 'データベースの接続に失敗しました。 エラー番号 ' . $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
	} else {
    $up_date = date("Y-m-d H:i:s");
    $sql =$mysqli->prepare( "UPDATE board
            SET update_at='$up_date',
            delete_flag=1
            WHERE id = ?");

    //sqlインジェクション対策
    $sql->bind_param("i",$message_id);
    $sql->execute();
    $sql->bind_result($message_id);
    $sql->store_result();
    $sql->fetch();

  
    $res = $mysqli->query($sql);

  }

  

	$sql->close();
	$mysqli->close();
  
  //リロード
		header("Location: ./index.php");
	
}
*/

$mysqli = new mysqli($host, $user, $password, $dbname);


// 表示するためのデータ
if( $mysqli->connect_errno ) {
	$error_message[] = 'データの読み込みに失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
} else {
  $sql =$mysqli->query("SELECT id,post_name,message,create_at FROM board  WHERE delete_flag= ".DELET_OFF." ORDER BY create_at DESC ");
  //sqlインジェクション対策
  //$sql->bind_param("i",$delete_off);
  //$sql->execute();
  //$sql->bind_result($delete_off);
  //$sql->store_result();
  //$sql->fetch();
	//$res = $mysqli->query($sql);
	if( $sql ) {
		$message_array = $sql->fetch_all(MYSQLI_ASSOC);
  }
  $sql->close();
	$mysqli->close();
}
?>



<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=yes">
  <link rel="stylesheet" href="index.css">
  <title>ひとこと掲示板</title>
</head>
<body>
  <div class="header">
    <h1 id="hitokoto">ひとこと掲示板</h1>
    <a href="admin.php" class="admin">管理用ページ</a>
  </div>
    <?php if( empty($_POST['btn_submit']) && !empty($_SESSION['success_message']) ): ?>
        <p class="succes_message"><?php echo $_SESSION['success_message']; ?></p>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if( !empty($error_message) ): ?>
      <ul class="error_message">
        <?php foreach( $error_message as $value ): ?>
          <li><?php echo $value; ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

  <form method="post" class="form">

    <div class="name">
      <label for="post_name">名前</label>
      <?php if( !empty($error_message_name) ): ?>
        <ul class="error_message">
          <?php foreach( $error_message_name as $value ): ?>
            <li><?php echo $value; ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <input id="post_name" type="text" name="post_name" value="">
    </div>
    <div class="messa">
      <label for="message">一言メッセージ</label>
      <?php if( !empty($error_message_text) ): ?>
        <ul class="error_message">
          <?php foreach( $error_message_text as $value ): ?>
            <li><?php echo $value; ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <textarea name="message" id="message"></textarea>
    </div>
      <input type="submit" name="btn_submit" value="書き込み" class="square-btn">
  </form>

  <hr>

  

    <?php if( !empty($message_array) ): ?>
      <?php foreach($message_array as $value ): ?>
      <article class="toukou">
          <div class="info">
              <h2><?php echo $value['post_name']; ?></h2>
              <time><?php echo date('Y年m月d日 H:i', strtotime($value['create_at'])); ?></time>
          </div>
          <p><?php echo $value['message']; ?></p>
      </article>
      <?php endforeach; ?>
    <?php elseif( empty($message_array) ): ?>
      <p>現在投稿がありません。</p>
    <?php endif; ?>
</body>
</html>