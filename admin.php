<?php
//定数
const DELET_OFF = 0;
//$delete_off = 0;
const DELET_ON = 1;
date_default_timezone_set('Asia/Tokyo');

define('PASSWORD','N02katu02');
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

if(!empty($_GET['btn_logout'])){
  unset($_SESSION['admin_login']);
}
//ボタンを押した後
if( !empty($_POST['login_submit']) ) {
  if( !empty($_POST['admin_password']) && $_POST['admin_password'] === PASSWORD){
    $_SESSION['admin_login'] = true;
  } else{
    $error_message[] = 'ログインに失敗しました。';
  }

}


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
            SET update_at=?,
            delete_flag=".DELET_ON."
            WHERE id = ?");

    //sqlインジェクション対策
    $sql->bind_param("si",$up_date,$message_id);
    $sql->execute();
    $sql->bind_result($up_date,$message_id);
    $sql->store_result();
    $sql->fetch();

  
    $res = $mysqli->query($sql);

  }

  

	$sql->close();
	$mysqli->close();
  
  //リロード
		header("Location: ./admin.php");
	
}


$mysqli = new mysqli($host, $user, $password, $dbname);

// 表示するためのデータ
if( $mysqli->connect_errno ) {
	$error_message[] = 'データの読み込みに失敗しました。 エラー番号 '.$mysqli->connect_errno.' : '.$mysqli->connect_error;
} else {
	$sql = "SELECT id,post_name,message,create_at FROM board  WHERE delete_flag=0 ORDER BY create_at DESC ";
	$res = $mysqli->query($sql);
	
	if( $res ) {
		$message_array = $res->fetch_all(MYSQLI_ASSOC);
	}
	
	$mysqli->close();
}
?>



<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="index.css">
  <title>ひとこと掲示板 管理ページ</title>
</head>
<body>
<div class="header">
    <h1 id="hitokoto">ひとこと掲示板 管理ページ</h1>
    <a href="index.php" class="admin">戻る</a>

  </div>
    <?php if( !empty($error_message) ): ?>
      <ul class="error_message">
        <?php foreach( $error_message as $value ): ?>
          <li><?php echo $value; ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php if( !empty($_SESSION['admin_login']) && $_SESSION['admin_login'] === true): ?>
      <form method="get" action="">
            <input type="submit" name="btn_logout" value="ログアウト" class="btn_logout">
          </form>
      <?php if( !empty($message_array) ): ?>
        <?php foreach($message_array as $value ): ?>
          
        <article class="toukou">
            <div class="info">
                <h2><?php echo $value['post_name']; ?></h2>
                <time><?php echo date('Y年m月d日 H:i', strtotime($value['create_at'])); ?></time>
            </div>
            <p><?php echo $value['message']; ?></p>
            <form method="post">
              <input type="submit" name="delete" value="削除" class="square-btn_delete">
              <input type="hidden" name="message_id" value="<?php echo $value['id']; ?>" >  
            </form>
        </article>

        
        <?php endforeach; ?>
      <?php elseif( empty($message_array) ): ?>
        <p>現在投稿がありません。</p>
      <?php endif; ?>
    <?php else: ?>
      <form method="post">
        <div>
          <label for="password">管理者用パスワード</label>
          <input id="admin_password" type="password" name="admin_password">
        </div>
        <input type="submit" name="login_submit" value="ログイン" class="login">
      </form>
    <?php endif; ?>
</body>
</html>