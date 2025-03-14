<?php header("Content-Type:text/html;charset=utf-8"); ?>
<?php 

if (version_compare(PHP_VERSION, '5.1.0', '>=')) { // PHP5.1.0以上の場合のみタイムゾーンを定義
	date_default_timezone_set('Asia/Tokyo'); // タイムゾーンの設定 (日本以外の場合には適宜設定)
}

//
// 必須設定
//

// トップページURL ※送信完了後に「トップページへ戻る」ボタンが表示される
$site_top = "https://tsukasamaru.net/";

//管理者のメールアドレス ※複数指定する場合は「,」で区切る 例 $to = "aa@aa.aa,bb@bb.bb";
$to = "welcome@tsukasamaru.net";

//送信元メールアドレス ※設置先サイトと同じドメイン推奨
$from = "welcome@tsukasamaru.net";

//フォームのメールアドレス入力箇所のname属性の値
$Email = "Email";


//
// セキュリティ、スパム防止のための設定
//

//スパム防止のためのリファラチェック (する=1, しない=0)
$Referer_check = 1;
$Referer_check_domain = "tsukasamaru.net";

// セッションによるワンタイムトークン（CSRF対策、及びスパム防止）(する=1, しない=0)
$useToken = 1;


//
// 任意設定
//

// Bccで送るメールアドレス ※複数指定する場合は「,」で区切る 例 $to = "aa@aa.aa,bb@bb.bb";
$BccMail = "";

// 管理者宛に送信されるメールのタイトル（件名）
$subject = "webサイトからのお問い合わせ";

// 送信確認画面の表示(する=1, しない=0)
$confirmDsp = 1;

// 送信完了後に自動的に指定のページ(サンクスページなど)に移動する (する=1, しない=0)
// CV率を解析したい場合などはサンクスページを別途用意し、URLをこの下の項目で指定する
// 0にすると、デフォルトの送信完了画面が表示される
$jumpPage = 0;
$thanksPage = "https://tsukasamaru.net/thanks.html";

// 必須入力項目を設定 (する=1, しない=0)
$requireCheck = 1;

// 必須入力項目 (入力フォームで指定したname属性の値をカンマで区切って指定)
$require = array('ご氏名','Email');


//
// 自動返信メール設定
//

// 差出人に送信内容確認メール（自動返信メール）を送る (送る=1, 送らない=0)
// 送る場合は、フォーム側のメール入力欄のname属性の値が上記「$Email」で指定した値と同じである必要がある
$remail = 1;

// 自動返信メールの送信者欄に表示される名前　※会社名など (空でも良い)
$refrom_name = "";

// 差出人に送信確認メールを送る場合のメールのタイトル
$re_subject = "お問い合わせありがとうございました";

// フォーム側の「氏名」箇所のname属性の値　※自動返信メールの「○○様」の表示で使用 (空でも良い)
$dsp_name = '氏名';

// 自動返信メール冒頭
$remail_text = <<< TEXT

お問い合わせありがとうございました。
早急にご返信致しますので今しばらくお待ちください。

送信内容は以下になります。

TEXT;


// 自動返信メールに署名を表示 (する=1, しない=0) ※管理者宛にも表示される。
$mailFooterDsp = 1;

// 署名
$mailSignature = <<< TEXT

────────────────────────
海辺の農園宿つかさ丸
〒910-3402 福井市鮎川町20-2-26
Tel: 0776-88-2962
E-mail: welcome@tsukasamaru.net

松井 司
────────────────────────

TEXT;


// メールアドレスの形式チェックを行うかどうか (する=1, しない=0)
$mail_check = 1;

// 全角英数字→半角変換を行うかどうか (する=1, しない=0)
$hankaku = 0;

// 全角英数字→半角変換を行う項目のname属性の値 (入力フォームで指定したname属性の値をカンマで区切って指定)
$hankaku_array = array('電話番号','金額');

// -fオプションによるエンベロープFrom (Return-Path) の設定 (する=1, しない=0) ※サーバーによっては稀にこの設定が必須の場合もある
$use_envelope = 1; // hetemlは必要

// 機種依存文字の変換
// 変換前の文字
$replaceStr['before'] = array('①','②','③','④','⑤','⑥','⑦','⑧','⑨','⑩','№','㈲','㈱');
// 変換後の文字
$replaceStr['after'] = array('(1)','(2)','(3)','(4)','(5)','(6)','(7)','(8)','(9)','(10)','No.','（有）','（株）');


//
// 関数実行、変数初期化
//

// トークンチェック用のセッションスタート
if($useToken == 1 && $confirmDsp == 1){
	session_name('PHPMAILFORMSYSTEM');
	session_start();
}
$encode = "UTF-8"; // このファイルの文字コード定義（変更不可）
if(isset($_GET)) $_GET = sanitize($_GET); // NULLバイト除去 //
if(isset($_POST)) $_POST = sanitize($_POST); // NULLバイト除去 //
if(isset($_COOKIE)) $_COOKIE = sanitize($_COOKIE); // NULLバイト除去 //
if($encode == 'SJIS') $_POST = sjisReplace($_POST,$encode); // Shift-JISの場合に誤変換文字の置換実行
$funcRefererCheck = refererCheck($Referer_check,$Referer_check_domain); // リファラチェック実行

// 変数初期化
$sendmail = 0;
$empty_flag = 0;
$post_mail = '';
$errm ='';
$header ='';

if($requireCheck == 1) {
	$requireResArray = requireCheck($require); // 必須チェック実行し返り値を受け取る
	$errm = $requireResArray['errm'];
	$empty_flag = $requireResArray['empty_flag'];
}
// メールアドレスチェック
if(empty($errm)){
	foreach($_POST as $key=>$val) {
		if($val == "confirm_submit") $sendmail = 1;
		if($key == $Email) $post_mail = h($val);
		if($key == $Email && $mail_check == 1 && !empty($val)){
			if(!checkMail($val)){
				$errm .= "<p class=\"error_messe\">【".$key."】はメールアドレスの形式が正しくありません。</p>\n";
				$empty_flag = 1;
			}
		}
	}
}
  
if(($confirmDsp == 0 || $sendmail == 1) && $empty_flag != 1){
	
	// トークンチェック（CSRF対策）※確認画面がONの場合のみ実施
	if($useToken == 1 && $confirmDsp == 1){
		if(empty($_SESSION['mailform_token']) || ($_SESSION['mailform_token'] !== $_POST['mailform_token'])){
			exit('ページ遷移が不正です');
		}
		if(isset($_SESSION['mailform_token'])) unset($_SESSION['mailform_token']); // トークン破棄
		if(isset($_POST['mailform_token'])) unset($_POST['mailform_token']); // トークン破棄
	}
	
	// 差出人に届くメールをセット
	if($remail == 1) {
		$userBody = mailToUser($_POST,$dsp_name,$remail_text,$mailFooterDsp,$mailSignature,$encode);
		$reheader = userHeader($refrom_name,$from,$encode);
		$re_subject = "=?iso-2022-jp?B?".base64_encode(mb_convert_encoding($re_subject,"JIS",$encode))."?=";
	}
	// 管理者宛に届くメールをセット
	$adminBody = mailToAdmin($_POST,$subject,$mailFooterDsp,$mailSignature,$encode,$confirmDsp);
	$header = adminHeader($post_mail,$BccMail);
	$subject = "=?iso-2022-jp?B?".base64_encode(mb_convert_encoding($subject,"JIS",$encode))."?=";
	
	// -fオプションによるエンベロープFrom (Return-Path) の設定 (safe_modeがOFFの場合かつ上記設定がONの場合のみ実施)
	if($use_envelope == 0){
		mail($to,$subject,$adminBody,$header);
		if($remail == 1 && !empty($post_mail)) mail($post_mail,$re_subject,$userBody,$reheader);
	}else{
		mail($to,$subject,$adminBody,$header,'-f'.$from);
		if($remail == 1 && !empty($post_mail)) mail($post_mail,$re_subject,$userBody,$reheader,'-f'.$from);
	}
}
else if($confirmDsp == 1){ 

//
// 送信確認画面
//

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="format-detection" content="telephone=no">
		<title>送信内容の確認</title>
    <link rel="stylesheet" href="../style-1.0.css">
    <link rel="icon" href="../favicon.ico">
	</head>
	<body>
		<div id="contact" class="section">
      <div class="section__container">
				<?php if($empty_flag == 1){ ?>
					<h4>入力にエラーがあります。
						<br>下記をご確認の上「戻る」ボタンにて修正をお願い致します。</h4>
					<div style="color:red"><?php echo $errm; ?></div>
					<div class="form__buttons">
						<input type="button" value="前画面に戻る" onClick="history.back()">
					</div>
				<?php }else{ ?>
					<h2 align="center">送信内容の確認</h2>
					<p>以下の内容で間違いがなければ、「送信する」ボタンを押してください。</p>
					<form class="form" action="<?php echo h($_SERVER['SCRIPT_NAME']); ?>" method="POST">
						<table class="form__table">
							<?php echo confirmOutput($_POST); // 入力内容を表示 ?>
						</table>
						<div class="form__buttons">
							<input type="hidden" name="mail_set" value="confirm_submit">
							<input type="hidden" name="httpReferer" value="<?php echo h($_SERVER['HTTP_REFERER']);?>">
							<input type="submit" value="送信する">
							<input type="button" value="前画面に戻る" onClick="history.back()">
						</div>
					</form>
				<?php } ?>
			</div>
		</div>
	</body>
</html>
<?php

}

if(($jumpPage == 0 && $sendmail == 1) || ($jumpPage == 0 && ($confirmDsp == 0 && $sendmail == 0))) { 

//
// 送信完了画面
//

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="../style-1.0.css">
    <link rel="icon" href="../favicon.ico">
		<title>送信完了</title>
	</head>
	<body>
		<?php if($empty_flag == 1){ ?>
			<div id="contact" class="section">
      	<div class="section__container">
					<h4>入力にエラーがあります。
						<br>下記をご確認の上「前画面に戻る」ボタンにて修正をお願い致します。</h4>
					<div style="color:red"><?php echo $errm; ?></div>
					<div class="form__buttons">
						<input type="button" value="前画面に戻る" onClick="history.back()">
					</div>
				</div>
			</div>
		<?php }else{ ?>
			<div id="contact" class="section" align="center">
					送信ありがとうございました。
					<br>送信は正常に完了しました。
					<br><a href="<?php echo $site_top ;?>" style="display:block;margin:1.5rem auto;text-align:center;">トップページへ戻る&raquo;</a>
				</div>
			</div>
			<!--  CV率を計測する場合ここにAnalyticsコードを貼り付け -->
		<?php } ?>
	</body>
</html>
<?php 

}

// 確認画面無しの場合の表示、指定のページに移動する設定の場合、エラーチェックで問題が無ければ指定ページヘリダイレクト
else if(($jumpPage == 1 && $sendmail == 1) || $confirmDsp == 0) { 
	if($empty_flag == 1){ ?>
		<div>
			<h4>入力にエラーがあります。
				<br>下記をご確認の上「戻る」ボタンにて修正をお願い致します。</h4>
			<div style="color:red"><?php echo $errm; ?></div>
			<div class="form__buttons">
				<input type="button" value="前画面に戻る" onClick="history.back()">
			</div>
		</div>
<?php 
	}else{ header("Location: ".$thanksPage); }
}


//
// 関数定義
//

function checkMail($str){
	$mailaddress_array = explode('@',$str);
	if(preg_match("/^[\.!#%&\-_0-9a-zA-Z\?\/\+]+\@[!#%&\-_0-9a-zA-Z]+(\.[!#%&\-_0-9a-zA-Z]+)+$/", "$str") && count($mailaddress_array) ==2){
		return true;
	}else{
		return false;
	}
}
function h($string) {
	global $encode;
	return htmlspecialchars($string, ENT_QUOTES,$encode);
}
function sanitize($arr){
	if(is_array($arr)){
		return array_map('sanitize',$arr);
	}
	return str_replace("\0","",$arr);
}
// Shift-JISの場合に誤変換文字の置換関数
function sjisReplace($arr,$encode){
	foreach($arr as $key => $val){
		$key = str_replace('＼','ー',$key);
		$resArray[$key] = $val;
	}
	return $resArray;
}
// 送信メールにPOSTデータをセットする関数
function postToMail($arr){
	global $hankaku,$hankaku_array;
	$resArray = '';
	foreach($arr as $key => $val) {
		$out = '';
		if(is_array($val)){
			foreach($val as $key02 => $item){ 
				// 連結項目の処理
				if(is_array($item)){
					$out .= connect2val($item);
				}else{
					$out .= $item . ', ';
				}
			}
			$out = rtrim($out,', ');
			
		}else{ $out = $val; } // チェックボックス(配列)追記ここまで
		
		if (version_compare(PHP_VERSION, '5.1.0', '<=')) { // PHP5.1.0以下の場合のみ実行 (7.4でget_magic_quotes_gpcが非推奨になったため)
			if(get_magic_quotes_gpc()) { $out = stripslashes($out); }
		}
		
		// 全角→半角変換
		if($hankaku == 1){
			$out = zenkaku2hankaku($key,$out,$hankaku_array);
		}
		if($out != "confirm_submit" && $key != "httpReferer") {
			$resArray .= "【 ".h($key)." 】 ".h($out)."\n";
		}
	}
	return $resArray;
}
// 確認画面の入力内容出力用関数
function confirmOutput($arr){
	global $hankaku,$hankaku_array,$useToken,$confirmDsp,$replaceStr;
	$html = '';
	foreach($arr as $key => $val) {
		$out = '';
		if(is_array($val)){
			foreach($val as $key02 => $item){ 
				// 連結項目の処理
				if(is_array($item)){
					$out .= connect2val($item);
				}else{
					$out .= $item . ', ';
				}
			}
			$out = rtrim($out,', ');
			
		}else{ $out = $val; } // チェックボックス(配列)追記ここまで
		
		if (version_compare(PHP_VERSION, '5.1.0', '<=')) { // PHP5.1.0以下の場合のみ実行 (7.4でget_magic_quotes_gpcが非推奨になったため)
			if(get_magic_quotes_gpc()) { $out = stripslashes($out); }
		}
		
		// 全角→半角変換
		if($hankaku == 1){
			$out = zenkaku2hankaku($key,$out,$hankaku_array);
		}
		
		$out = nl2br(h($out)); // ※追記 改行コードを<br>タグに変換
		$key = h($key);
		$out = str_replace($replaceStr['before'], $replaceStr['after'], $out); // 機種依存文字の置換処理
		
		$html .= "<tr><th>".$key."</th><td>".$out;
		$html .= '<input type="hidden" name="'.$key.'" value="'.str_replace(array("<br />","<br>"),"",$out).'" />';
		$html .= "</td></tr>\n";
	}
	// トークンをセット
	if($useToken == 1 && $confirmDsp == 1){
		$token = sha1(uniqid(mt_rand(), true));
		$_SESSION['mailform_token'] = $token;
		$html .= '<input type="hidden" name="mailform_token" value="'.$token.'" />';
	}
	
	return $html;
}

// 全角→半角変換
function zenkaku2hankaku($key,$out,$hankaku_array){
	global $encode;
	if(is_array($hankaku_array) && function_exists('mb_convert_kana')){
		foreach($hankaku_array as $hankaku_array_val){
			if($key == $hankaku_array_val){
				$out = mb_convert_kana($out,'a',$encode);
			}
		}
	}
	return $out;
}
// 配列連結の処理
function connect2val($arr){
	$out = '';
	foreach($arr as $key => $val){
		if($key === 0 || $val == ''){ // 配列が未記入（0）、または内容が空のの場合には連結文字を付加しない（型まで調べる必要あり）
			$key = '';
		}elseif(strpos($key,"円") !== false && $val != '' && preg_match("/^[0-9]+$/",$val)){
			$val = number_format($val); // 金額の場合には3桁ごとにカンマを追加
		}
		$out .= $val . $key;
	}
	return $out;
}

// 管理者宛送信メールヘッダ
function adminHeader($post_mail,$BccMail){
	global $from;
	$header="From: $from\n";
	if($BccMail != '') {
	  $header.="Bcc: $BccMail\n";
	}
	if(!empty($post_mail)) {
		$header.="Reply-To: ".$post_mail."\n";
	}
	$header.="Content-Type:text/plain;charset=iso-2022-jp\nX-Mailer: PHP/".phpversion();
	return $header;
}
// 管理者宛送信メールボディ
function mailToAdmin($arr,$subject,$mailFooterDsp,$mailSignature,$encode,$confirmDsp){
	$adminBody="「".$subject."」からメールが届きました\n\n";
	$adminBody .="────────────────────────\n\n";
	$adminBody.= postToMail($arr); // POSTデータを関数からセット
	$adminBody.="\n────────────────────────\n";
	$adminBody.="送信された日時: ".date( "Y/m/d (D) H:i:s", time() )."\n";
	$adminBody.="送信者のIPアドレス: ".@$_SERVER["REMOTE_ADDR"]."\n";
	$adminBody.="送信者のホスト名: ".getHostByAddr(getenv('REMOTE_ADDR'))."\n";
	if($confirmDsp != 1){
		$adminBody.="問い合わせのページURL: ".@$_SERVER['HTTP_REFERER']."\n";
	}else{
		$adminBody.="問い合わせのページURL: ".@$arr['httpReferer']."\n";
	}
	if($mailFooterDsp == 1) $adminBody.= $mailSignature;
	return mb_convert_encoding($adminBody,"JIS",$encode);
}

// ユーザ宛送信メールヘッダ
function userHeader($refrom_name,$to,$encode){
	$reheader = "From: ";
	if(!empty($refrom_name)){
		$default_internal_encode = mb_internal_encoding();
		if($default_internal_encode != $encode){
			mb_internal_encoding($encode);
		}
		$reheader .= mb_encode_mimeheader($refrom_name)." <".$to.">\nReply-To: ".$to;
	}else{
		$reheader .= "$to\nReply-To: ".$to;
	}
	$reheader .= "\nContent-Type: text/plain;charset=iso-2022-jp\nX-Mailer: PHP/".phpversion();
	return $reheader;
}
// ユーザ宛送信メールボディ
function mailToUser($arr,$dsp_name,$remail_text,$mailFooterDsp,$mailSignature,$encode){
	$userBody = '';
	if(isset($arr[$dsp_name])) $userBody = h($arr[$dsp_name]). " 様\n";
	$userBody.= $remail_text;
	$userBody.="\n────────────────────────\n\n";
	$userBody.= postToMail($arr); // POSTデータを関数からセット
	$userBody.="\n────────────────────────\n\n";
	$userBody.="送信日時: ".date( "Y/m/d (D) H:i:s", time() )."\n";
	if($mailFooterDsp == 1) $userBody.= $mailSignature;
	return mb_convert_encoding($userBody,"JIS",$encode);
}
// 必須チェック関数
function requireCheck($require){
	$res['errm'] = '';
	$res['empty_flag'] = 0;
	foreach($require as $requireVal){
		$existsFalg = '';
		foreach($_POST as $key => $val) {
			if($key == $requireVal) {
				
				// 連結指定の項目（配列）のための必須チェック
				if(is_array($val)){
					$connectEmpty = 0;
					foreach($val as $kk => $vv){
						if(is_array($vv)){
							foreach($vv as $kk02 => $vv02){
								if($vv02 == ''){
									$connectEmpty++;
								}
							}
						}
						
					}
					if($connectEmpty > 0){
						$res['errm'] .= "<p class=\"error_messe\">【".h($key)."】は必須項目です。</p>\n";
						$res['empty_flag'] = 1;
					}
				}
				// デフォルト必須チェック
				elseif($val == ''){
					$res['errm'] .= "<p class=\"error_messe\">【".h($key)."】は必須項目です。</p>\n";
					$res['empty_flag'] = 1;
				}
				
				$existsFalg = 1;
				break;
			}
			
		}
		if($existsFalg != 1){
				$res['errm'] .= "<p class=\"error_messe\">【".$requireVal."】が未選択です。</p>\n";
				$res['empty_flag'] = 1;
		}
	}
	
	return $res;
}
// リファラチェック
function refererCheck($Referer_check,$Referer_check_domain){
	if($Referer_check == 1 && !empty($Referer_check_domain)){
		if(strpos($_SERVER['HTTP_REFERER'],$Referer_check_domain) === false){
			return exit('<p>リファラチェックエラー。フォームページのドメインとこのファイルのドメインが一致しません</p>');
		}
	}
}

?>
