<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：全てのファイルに影響する共通処理用のファイル

【主な処理】
k1_config.phpの読み込み
session_start()
各種var_dump(検証用なので本番時はココをコメントアウトする)
共通使用変数の宣言
各種エラー内容の配列宣言(JSで使うエラーは除く)
index.php以外で使う直接アクセスの制御関数
共通headタグ変数作成(各ページの使いたいとこでechoする)
共通headerタグ変数作成(各ページの使いたいとこでechoする)
共通footerタグ変数作成(各ページの使いたいとこでechoする)
共通inputタグ(POST時再表示)変数作成(各ページの使いたいとこでechoする、radio除く)
共通boxタグ変数作成(各ページの使いたいとこでechoする)
【要修正】共通PDO処理(SQLインジェクション万歳、静的プレースホルダーつける)
個別のSQL関連の処理関数

----------------------------------------------------------------------------------------- */


/* ----- 共通処理 ----------------------------------------------------------------------- */

// k1_config.phpのパスとファイル名を変数にしとく。
$config_path = "../";
$config_file = "k1_config.php";

// k1_config.phpをインクルード。
include($config_path.$config_file);

// セッションスタート。
session_start();

// 【要削除】検証用
// var_dump($_COOKIE);
// var_dump($_SESSION);
// var_dump($_POST);
// var_dump($_GET);

// 共通で使う変数（初期値つき）。
// 使用するファイルが限定される場合は変数のミドルネームにファイル名を入れてる。
$pflag									= false;
$err_msg								= "";
$head_common_tag						= "";
$header_common_tag						= "";
$footer_common_tag						= "";
$sql_output_index_new_algo_pw			= "";
$sql_output_form_sq_array				= array();
$sql_output_form_colomn_array			= array();
$sql_output_repass2_user_sq 			= "";
$sql_output_repass2_user_miss			= "";
$sql_output_main2_rtest_gr_id_array		= array();
$sql_output_main2_rtest_gr_score_array	= array();
$sql_output_main2_rtest_gr_time_array	= array();
$sql_output_main2_dtest_gr_id_array		= array();
$sql_output_main2_dtest_gr_score_array	= array();
$sql_output_main2_dtest_gr_time_array	= array();
$sql_output_main2_etest_gr_id_array		= array();
$sql_output_main2_etest_gr_score_array	= array();
$sql_output_main2_etest_gr_time_array	= array();
$sql_output_main4_qu_status_array		= array();
$sql_output_main4_qu_no_array			= array();
$sql_output_main4_qu_title_array		= array();
$sql_output_main4_qu_cor_array			= array();
$sql_output_main4_qu_eva_array			= array();
$sql_output_main4_colomn_array			= array();
$sql_output_main4_qu_comment_array		= array();
$sql_output_main5_user_array			= array();
$sql_output_main5_user_total			= "";
$sql_output_main5_user_total_effective	= "";
$sql_output_main6_qu_status_array		= array();
$sql_output_main6_qu_del_array			= array();
$sql_output_main6_qu_id_array			= array();
$sql_output_main6_qu_no_array			= array();
$sql_output_main6_qu_title_array		= array();
$sql_output_main6_qu_cor_array			= array();
$sql_output_main6_qu_eva_array			= array();
$sql_output_main6_qu_comment_array		= array();
$sql_output_main7_user_status_array		= array();
$sql_output_main7_user_status_2_array	= array();
$sql_output_main7_user_id_array			= array();
$sql_output_main7_user_regist_array		= array();
$sql_output_main7_user_lastlog_array	= array();
$sql_output_main7_user_loginsu_array	= array();
$sql_output_main7_user_qusum_array		= array();
$sql_output_main8_user_total			= "";
$sql_output_main8_user_total_effective	= "";
$sql_output_main8_qu_total				= "";
$sql_output_main8_qu_total_effective	= "";
$sql_output_main8_log_total				= "";
$sql_output_main8_log_total_user		= "";
$sql_output_main8_log_total_guest		= "";
$sql_output_dojo_qu_title_array			= array();
$sql_output_dojo_qu_id_array			= array();
$sql_output_dojo_qu_cor_array			= array();
$sql_output_dojo_qu_eva_array			= array();
$sql_output_test_qno_array				= array();
$sql_output_question_qu_array			= array();
$sql_output_ans_test_status				= "false";
$sql_output_ans_test_highscore			= "";
$sql_output_que_create_qu_array			= array();

// 各種ページで使用するエラー一覧。適当に使いまわす。
// JS処理によるエラーは各ファイルのJSにしかないのであしからず。
$err_array = array(
	"all"		=> "<i class=\"fas fa-exclamation-triangle\"></i> ",
	"index0"	=> "当サイトに直接ログインがあった為、トップ画面に戻ります。",
	"index1"	=> "IDとPWの両方を入力してください。",
	"index2"	=> "IDもしくはPWが間違っています。",
	"index3"	=> "連続して一定回数の誤入力があった為、暫くログインできません。",
	"repass1_1"	=> "そのようなIDは存在しません。",
	"repass1_2"	=> "連続して一定回数の誤入力があった為、暫くログインできません。",
	"repass1_3"	=> "IDを入力して下さい。",
	"repass2_1"	=> "秘密の質問の答えが違います。",
	"repass2_2"	=> "連続して一定回数の誤入力があった為、暫くログインできません。",
	"repass2_3"	=> "秘密の質問の答えを入力して下さい。",
	"form1"		=> "IDを半角英数字で入力して下さい。",
	"form2"		=> "該当IDは既に他のユーザが使用済みです。",
	"confilm1"	=> "該当IDは別会員と重複している為、登録できません。"
);

/* --------------------------------------------------------------------------------------- */


/* ----- 不正アクセス処理系 ------------------------------------------------------------------ */

// index.php（トップページ）以外でGET送信があった時の処理。
// session["user"]がない ＝ index.phpを通過していない ＝ 不正アクセス。
// session["user"]はindex.phpのみで作成される。
function session_user_check($session_user){
	if(!isset($session_user)){
		header("Location:index.php");
		exit;
	}
}

/* --------------------------------------------------------------------------------------- */


/* ----- HTMLタグ作成系 ------------------------------------------------------------------ */

// 共有のhead内のタグ
$head_common_tag .= "<meta charset=\"UTF-8\">";		// charset=utf8。常識やけど。
$head_common_tag .= "<meta name=\"robots\" content=\"noindex,follow\">";	// SEO対策（インデックス:×、クロール:〇）
$head_common_tag .= "<meta name=\"format-detection\" content=\"telephone=no\">";	// IOS対策（電話番号表示を電話リンク化しない）
$head_common_tag .= "<link rel=\"icon\" type=\"image/png\" href=\"./image/mi.png\" >";	// ファビコンのリンク
$head_common_tag .= "<link rel=\"stylesheet\" href=\"./css/default.css\" >";		// 共通CSSのリンク
$head_common_tag .= "<link href=\"https://use.fontawesome.com/releases/v5.6.1/css/all.css\" rel=\"stylesheet\">";	// fontawesomeの読み込み

// 共有のheader内のタグ（index.php以外）
$header_common_tag .= "<h1><img src=\"./image/mi_1.png\" width=\"50px\" alt=\"み\">んなのクイズ</h1>";
if(isset($_SESSION["user"]["name"])){
	if($_SESSION["user"]["name"] != "pre"){
		if($_SESSION["user"]["name"] == "guest"){
			$header_common_tag .= "<p>ゲスト 様</p>";
		}else{
			$header_common_tag .= "<p>{$_SESSION["user"]["name"]} 様</p>";
		}
		$header_common_tag .= "<a id=\"a1\" href=\"index.php\">ログアウト</a>";
	}else{
		$header_common_tag .= "<a id=\"a1\" href=\"index.php\">トップ（ログイン画面）に戻る</a>";
	}
}

// 共有のfooter内のタグ
if(isset($_SESSION["user"]["name"])){
	if($_SESSION["user"]["name"] != "pre"){
		if($_SESSION["user"]["name"] != "guest"){
			$footer_common_tag .= "<p>お問い合わせは<a href=\"mailto:minami.gisen@gmail.com?subject=お問い合わせ&amp;body=----------------------------------------%0D%0A会員ID：{$_SESSION["user"]["name"]} 様%0D%0A 当項目は削除しないで下さい。%0D%0A----------------------------------------%0D%0A 以降にお問い合わせ内容を記載下さい。\">コチラ</a></p>";
		}else{
			$footer_common_tag .= "<p>お問い合わせは<a href=\"mailto:minami.gisen@gmail.com?subject=お問い合わせ&amp;body=----------------------------------------%0D%0A会員ID：ゲスト 様%0D%0A 当項目は削除しないで下さい。%0D%0A----------------------------------------%0D%0A 以降にお問い合わせ内容を記載下さい。\">コチラ</a></p>";
		}
	}else{
		$footer_common_tag .= "<p>お問い合わせは<a href=\"mailto:minami.gisen@gmail.com?subject=お問い合わせ&amp;body=----------------------------------------%0D%0A会員ID：ゲスト 様%0D%0A 当項目は削除しないで下さい。%0D%0A----------------------------------------%0D%0A 以降にお問い合わせ内容を記載下さい。\">コチラ</a></p>";
	}
}
$footer_common_tag .= "<i class=\"far fa-copyright\"></i>";
$footer_common_tag .= "<small> 2019<a href=\"https://www.g096407.shop/hiroaki-akino/self_introduction.html\">Hiroaki Akino</a></small>";


// 引数：0,(global)$pflag 1,タイプ(String) 2,id名(String) 3,name名(String) 3,サイズ値(Int) 4,value値(変数) 5,placeholder値(String)
// 処理：引数を基にしたinputタグを作成（post時の再表示機能付き）
// 戻値：上記inputタグ
// 備考：使用時はechoすること。hidden,button,submit にはPOST再表示機能はつけてない。 
function create_input($type,$id,$name,$size,$val,$attribute,$attr_val,$placeholder){
	global $pflag;
	$input_tag = "";
	$input_tag .= "<input type=\"{$type}\"" ;
	$input_tag .= "id=\"{$id}\" name=\"{$name}\" size=\"{$size}\"";
	if($type == "hidden" || $type == "button" || $type == "submit" ){
		$input_tag .= "value=\"{$val}\"";
	}else{
		if($pflag && !empty($val)){
			$input_tag .= "value=\"{$val}\"";
		}
	}
	if(!empty($attribute)){
		$input_tag .= "$attribute=\"{$attr_val}\"";
	}
	if(!empty($placeholder)){
		$input_tag .= "placeholder=\"{$placeholder}\"";
	}
	$input_tag .= ">";
	return $input_tag;
}

// 引数：0,(global)$pflag 1,タイプ(String) 2,id名(String) 3,name名(String) 
//		4,element_array(配列変数):配列の内容(引数1でselectbox指定時は連想配列も可)
//		5,chose_no(推奨：変数)   ：POST送信再表示用に前画面で選択した番号or値
//		6,val_type(true/false) ：valueの値（true：引数4で指定した$element_arrayがそのまま入る、false:0から採番したものが入る）
//		7,$default(true/false) ：valueの値（true：引数4で指定した$element_arrayの0番目が初期値として選択された状態で表示される。false：逆（何も選択されていない状態で表示される））
// 処理：引数を基にしたcheck/selectboxタグを作成（post時の再表示機能付き）
// 戻値：上記boxタグ(各boxのvalue値は引数4の添字、表示は引数4のデータ値)
// 備考：引数１はcheckbox または selectbox を指定のコト。使用時はechoすること。
//		selectbox の時は引数4に連想配列を指定するとkeyがoptgroupとして生成される。
function create_box($type,$id,$name,$element_array,$chose_no,$val_type,$default){
	global $pflag;
	$result = "";
	if($type == "checkbox"){
		for($i=0;$i<count($element_array);$i++){
			$result .= "<label><input type=\"checkbox\" id=\"{$id}\" name=\"{$name}[]\" ";
			if($val_type){
				$result .= " value=\"$element_array[$i]\" ";
			}else{
				$result .= " value=\"{$i}\" ";
			}
			if($pflag){
				for($j=0;$j<count($chose_no);$j++){
					if($val_type){
						if($element_array[$i] == $chose_no[$j]){
							$result .= " checked=\"checked\" ";
						}
					}else{
						if($i == $chose_no[$j]){
							$result .= " checked=\"checked\" ";
						}
					}
				}
			}else{
				if($i == 0 && $default){
					$result .= " checked=\"checked\" ";
				}
			}
			$result .= ">" . $element_array[$i] . "</label>";
		}
	}
	if($type == "selectbox"){
		$result .= "<select size=\"1\" id=\"{$id}\" name=\"{$name}[]\" >";
		// 普通の配列か連想配列かを判定
		if(array_values($element_array) === $element_array){
			// $element_array が普通の配列の時の処理（optgroup タグなし)
			for($i=0;$i<count($element_array);$i++){
				if($i == 0 && $default){
					$result .= "<option value=\"\"";
				}else{
					if($val_type){
						$result .= "<option value=\"{$element_array[$i]}\"";
					}else{
						$result .= "<option value=\"{$i}\"";
					}
				}
				if($pflag){
					if(is_array($chose_no) && 
					  ($chose_no[0] == $i || $chose_no[0] == $element_array[$i])){
						$result .= "selected";
					}else{
						if($chose_no == $i || $chose_no == $element_array[$i]){
							$result .= "selected";
						}
					}
				}
				$result .= ">$element_array[$i]</option>";
			}
			$result .= "</select>";
		}else{
			// $element_array が連想配列の時の処理（optgroup タグ作成)
			$i = 0;
			foreach($element_array as $key => $val){
				if($i == 0 && $default){
					$result .= "<option value=\"\">$val</option>";
					$i++;
				}else{
					$result .= "<optgroup label=\"{$key}\">";
					foreach($val as $val2){
						if($val_type){
							$result .= "<option value=\"{$val2}\"";
						}else{
							$result .= "<option value=\"{$i}\"";
						}
						if($pflag){
							if(is_array($chose_no) && 
							  ($chose_no[0] == $i || $chose_no[0] == $val2)){
								$result .= "selected";
							}else{
								if($chose_no == $i || $chose_no == $val2){
									$result .= "selected";
								}
							}
						}
						$result .= ">$val2</option>";
						$i++;
					}
				}
			}
			$result .= "</select>";
		}
	}
	return $result;
}

// ミスって作ってしまって運用してしまったやつ
// form.phpとかの「秘密の質問」で使ってる。時間ないので特別に残す。
function create_box_sq($type,$id,$name,$element_array){
	global $pflag;
	$result = "";
	if($type == "selectbox"){
		$result .= "<select size=\"1\" id=\"{$id}\" name=\"{$name}[]\" >";
		for($i=1;$i<count($element_array)+1;$i++){
			$result .=  "<option value=\"{$i}\"";
			if($pflag && isset($element_array[$i][1])){
				$result .= "\"selected\"";
			}
			$result .= ">$element_array[$i] </option>";
		}
		$result .= "</select>";
	}
	return $result;
}

/* --------------------------------------------------------------------------------------- */


/* ----- PDO処理系 ----------------------------------------------------------------------- */

// 引数：1,ユーザ種類(int) 2,$SQL文の添え字(int) 2,id名(String) 3,name名(String) 4,element_array:配列の内容
// 処理：引数を基にしたPDO処理
// 戻値：
// 備考：
function sql($type,$userno,$sqlno,$funcno,$val,$val2,$val_array) {
	global $database_dsn,$dbname,$database_user,$database_password,$err_array,$sql_array;
	$userno = "0"; // 強制的に検証したい時に使う。通常はコメントアウトする行。
	$dsn   = $database_dsn;
	$user  = $database_user[$userno];
	$pass  = $database_password[$userno];
	$check = false;
	$same  = false;
	
	// 使用するSQL文を各ページの名前をキーに一覧で配列可
	$sql_array = array(
		"index1"		=> "select user_id,user_pw from k1_user where user_id = '${val}' and user_delete = 0 and user_freeze = 0",
		"index2"		=> "update k1_user set user_pw = '${val2}' where user_id = '${val}'",
		"index3"		=> "insert into k1_log set log_id = '${val}' , log_log = now()",
		"form1"			=> "select * from k1_secret_q",
		"form2"			=> "select column_name from information_schema.columns where table_schema = '${dbname}' and table_name = 'k1_user'",
		"form3"			=> "select user_id from k1_user",
		"confilm1"		=> "select user_id from k1_user",
		"confilm2"		=> "insert into k1_user set ",
		"confilm3"		=> "insert into k1_grades (gr_id,gr_type,gr_su) values('${val}',0,0),('${val}',1,0),('${val}',2,0)",
		"confilm4"		=> "insert into k1_log set log_id = '${val}',log_log = now()",
		"confilm5"		=> "update k1_user set ",
		"repass1"		=> "select user_id from k1_user where user_id = '${val}' ",
		"repass2_1"		=> "select user_secret_q from k1_user where user_id = '${val}' ",
		"repass2_2"		=> "select user_secret_a from k1_user where user_id = '${val}' ",
		"repass2_3"		=> "update k1_user set user_miss = 0 where user_id = '${val}' ",
		"repass2_4"		=> "update k1_user set user_miss = user_miss + 1 where user_id = '${val}' ",
		"repass2_5"		=> "select user_miss from k1_user where user_id = '${val}' ",
		"repass2_6"		=> "update k1_user set user_freeze_time = ( now() + interval 30 second ) where user_id = '${val}' ",
		"repass2_7"		=> "update k1_user set ",
		"main2_1"		=> "select gr_id,gr_score,gr_time from k1_grades where gr_id != 'admin' and gr_type = 0 and gr_score IS NOT NULL order by gr_score desc , gr_time desc limit ${val}",
		"main2_2"		=> "select gr_id,gr_score,gr_time from k1_grades where gr_id != 'admin' and gr_type = 1 and gr_score != 'NULL' order by gr_score desc , gr_time desc limit ${val}",
		"main2_3"		=> "select gr_id,gr_score,gr_time from k1_grades where gr_id != 'admin' and gr_type = 2 and gr_score != 'NULL' order by gr_score desc , gr_time desc limit ${val}",
		"main4_1"		=> "select qu_status,qu_no,qu_title,qu_id,an_su_c/an_su_a,an_evaluation/an_su_e,qu_comment from k1_question,k1_answer where qu_no = an_no and qu_id = '${val}' and qu_delete = 0 order by qu_status asc,qu_no desc",
		"main4_2"		=> "update k1_question set qu_delete = 1 where qu_no = '${val}' ",
		"main5_1"		=> "select column_name from information_schema.columns where table_schema = '${dbname}' and table_name = 'k1_user'",
		"main5_2"		=> "select * from k1_user where user_id = '${val}' ",
		"main5_3"		=> "update k1_user set user_delete = 1 where user_id = '${val}' ",
		"main6_1"		=> "select qu_status,qu_delete,qu_id,qu_no,qu_title,an_su_c/an_su_a,an_evaluation/an_su_e,qu_comment from k1_question,k1_answer where qu_no = an_no order by qu_delete asc,qu_status asc,qu_no desc",
		"main6_2"		=> "select qu_status,qu_delete,qu_id,qu_no,qu_title,an_su_c/an_su_a,an_evaluation/an_su_e,qu_comment from k1_question,k1_answer where qu_no = an_no and qu_status = 0 order by qu_delete asc,qu_status asc,qu_no desc",
		"main6_3"		=> "select qu_status,qu_delete,qu_id,qu_no,qu_title,an_su_c/an_su_a,an_evaluation/an_su_e,qu_comment from k1_question,k1_answer where qu_no = an_no and qu_status = 1 order by qu_delete asc,qu_status asc,qu_no desc",
		"main6_4"		=> "select qu_status,qu_delete,qu_id,qu_no,qu_title,an_su_c/an_su_a,an_evaluation/an_su_e,qu_comment from k1_question,k1_answer where qu_no = an_no and qu_status = 2 order by qu_delete asc,qu_status asc,qu_no desc",
		"main6_5"		=> "select qu_status,qu_delete,qu_id,qu_no,qu_title,an_su_c/an_su_a,an_evaluation/an_su_e,qu_comment from k1_question,k1_answer where qu_no = an_no and qu_delete = 1 order by qu_delete asc,qu_status asc,qu_no desc",
		"main6_6"		=> "update k1_question set qu_status = 2 where qu_no = '${val}' ",
		"main6_7"		=> "update k1_question set qu_status = 1 where qu_no = '${val}' ",
		"main7_1"		=> "select user_delete,user_freeze,user_id,user_registration from k1_user order by user_registration asc",
		"main7_2"		=> "select max(log_log),count(log_log) from k1_log group by log_id having log_id = '${val}' ",
		"main7_3"		=> "select count(qu_no) from k1_question,k1_user where qu_id = user_id and user_id = '${val}' ",
		"main7_4"		=> "update k1_user set user_freeze = 1 where user_id = '${val}' ",
		"main8_1"		=> "select count(user_id) from k1_user where user_id != 'admin' ",
		"main8_2"		=> "select count(user_id) from k1_user where user_id != 'admin' and user_delete = 0 and user_freeze = 0",
		"main8_3"		=> "select count(qu_no) from k1_question",
		"main8_4"		=> "select count(qu_no) from k1_question where qu_status = 2 and qu_delete = 0 ",
		"main8_5"		=> "select count(log_id) from k1_log where log_id != 'admin'",
		"main8_6"		=> "select count(log_id) from k1_log where log_id != 'admin' and log_id != 'guest'",
		"main8_7"		=> "select count(log_id) from k1_log where log_id = 'guest'",
		"dojo1"			=> "select qu_no,qu_title,qu_id,an_su_c/an_su_a,an_evaluation/an_su_e from k1_question,k1_answer where qu_no = an_no and qu_status = 2 and qu_delete = 0 order by an_su_a asc ,an_no desc limit 10",
		"dojo2"			=> "select qu_no,qu_title,qu_id,an_su_c/an_su_a,an_evaluation/an_su_e from k1_question,k1_answer where qu_no = an_no and qu_status = 2 and qu_delete = 0",
		"que_test1"		=> "select qu_no from k1_question where qu_status = 2 and qu_delete = 0 order by RAND() limit 10",
		"que_test2"		=> "select qu_no from k1_question,k1_answer where qu_no = an_no and qu_status = 2 and qu_delete = 0 order by an_su_c asc , qu_no desc limit 10",
		"que_test3"		=> "select qu_no from k1_question,k1_answer where qu_no = an_no and qu_status = 2 and qu_delete = 0 order by an_evaluation desc , qu_no desc limit 10",
		"question1"		=> "select qu_no,qu_title,qu_id,qu_question,qu_answer_1,
							qu_answer_2,qu_answer_3,qu_answer_4,qu_answer_correct,
							qu_time_limit,qu_explanation from k1_question where qu_no = '${val}' ",
		"question2"		=> "update k1_answer set an_su_a = an_su_a + 1 , an_su_c = an_su_c + 1 where an_no = '${val}' ",
		"question3"		=> "update k1_answer set an_su_a = an_su_a + 1 where an_no = '${val}' ",
		"ans_test1"		=> "select gr_score,gr_time from k1_grades where gr_id = '${val2}' and gr_type = '${val_array}' ",
		"ans_test2"		=> "update k1_grades set gr_score = '${val}',gr_time = now(),gr_su = gr_su + 1 where gr_id = '${val2}' and gr_type = '${val_array}'",
		"ans_test3"		=> "update k1_grades set gr_su = gr_su + 1  where gr_id = '${val2}' and gr_type = '${val_array}'",
		"que_create1"	=> "select column_name from information_schema.columns where table_schema = '${dbname}' and table_name = 'k1_question' ",
		"que_create2"	=> "select * from k1_question where qu_no = '${val}' ",
		"answer1"		=> "select qu_id from k1_question where qu_no = '${val2}'",
		"answer2"		=> "update k1_answer set an_evaluation = an_evaluation + '${val}' , an_su_e = an_su_e + 1 where an_no = '${val2}' ",
		"answer3"		=> "insert into k1_question set qu_id = '${val}',",
		"answer4"		=> "insert into k1_answer set an_su_a='0'",
		"answer5"		=> "update k1_question set ",
		"answer6"		=> "update k1_question set qu_comment = '${val}' , qu_status = 2 where qu_no = '${val2}' ",
		"answer7"		=> "update k1_question set qu_comment = '${val}' , qu_status = 1 where qu_no = '${val2}' "
	);

	if($sqlno == "confilm2"){
		// 新規会員登録時の処理。
		// 大量の入力値を配列にしてココに送ってるので、配列を分解して set 以降に組み込む処理。
		foreach($val_array as $key => $val){
			// 以下の項目以外をINSERTする。
			if($key == "user_registration" ||  $key == "user_miss" ||
			  $key == "user_freeze_time" || $key == "user_delete" ||
			  $key == "user_freeze" || $key == "user_mod_date"){
			}else{
				if($key == "user_pw"){
					$val = password_hash($val,PASSWORD_DEFAULT);
				}
			  $sql_array[$sqlno] .= $key . "='" . $val . "' ,";
			}
		}
		// 最後に現在日時を、登録日時（カラム名：user_registration）に加える。
		$sql_array[$sqlno] .= "user_registration = now()";
	}
	if($sqlno == "confilm5"){
		// 会員修正時の処理。
		// 大量の入力値を配列にしてココに送ってるので、配列を分解して set 以降に組み込む処理。
		foreach($val_array as $key => $val_f ){
			if($key == "user_registration" ||  $key == "user_miss" ||
			  $key == "user_freeze_time" || $key == "user_delete" ||
			  $key == "user_freeze" || $key == "user_mod_date"){
			}else{
				if($key == "user_pw"){
					$val_f = password_hash($val_f,PASSWORD_DEFAULT);
				}
				$sql_array[$sqlno] .= $key . "='" . $val_f . "' ,";
			}
		}
		// 最後に現在日時を、修正日時（カラム名：user_mod_date）に加える。
		$sql_array[$sqlno] .= "user_mod_date = now() ";
		// update の条件に user_id = 現在ログインしているID を加える。
		$sql_array[$sqlno] .= "where user_id = '{$val_array["user_id"]}' ";
	}
	if($sqlno == "repass2_7"){
		// PW再登録時の処理。
		$val2 = password_hash($val2,PASSWORD_DEFAULT);
		$sql_array[$sqlno] .=  "user_pw = '" . $val2 . "'," ;
		// 最後に現在日時を、修正日時（カラム名：user_mod_date）に加える。
		$sql_array[$sqlno] .= "user_mod_date = now() ";
		// update の条件に user_id = 現在ログインしているID を加える。
		$sql_array[$sqlno] .= "where user_id = '${val}' ";
	}
	if($sqlno == "answer3" || $sqlno == "answer5"){
		// 問題新規登録時の処理。
		// 大量の入力値を配列にしてココに送ってるので、配列を分解して set 以降に組み込む処理。
		foreach($val_array as $key => $val_f){
			$sql_array[$sqlno] .= $key . "='" . $val_f . "',";
		}
		if($sqlno == "answer3"){
			// 最後に現在日時を、登録日時（カラム名：qu_create_date ）に加える。
			$sql_array[$sqlno] .= " qu_create_date = now() ";
		}else{
			// 現在日時を、修正日時（カラム名：qu_mod_date ）に加える。
			$sql_array[$sqlno] .= " qu_mod_date = now() ,  ";
			// 登録状態を、未承認（値：0）に戻す。
			$sql_array[$sqlno] .= " qu_status = '0' ";
			$sql_array[$sqlno] .= " where qu_no = '${val}' ";
		}
	}

	try{
		$db = new PDO($dsn,$user,$pass);
		$db->exec("SET NAMES utf8");
		$db->setAttribute(PDO::ATTR_CASE,PDO::CASE_LOWER);

		// sql文の選択。候補は上の$sql_array参照のこと。
		$sql = $sql_array[$sqlno];

		// 以降、文章毎に切り分け。（insert,update時は排他ロック。になってるはず…）
		if($type == "select"){
			$result = $db->prepare($sql);
			if(!$result->execute()){
				echo "【SQL:err1】［sqlno］",$sqlno,"［内容］構文エラー(文法or記述ミス)［入力したSQL文］",$sql;
				return false;
			}
			$count = $result->rowCount();
		}else{
			$db->beginTransaction();
			//プリペアドステートメント / ロック
			$result = $db->prepare($sql);
			$count  = $result->execute();
		}
		if($count !== FALSE){
			if($count == 0){
				// 空値が変える可能性があるSQL文はここで除外しとく。
				if($sqlno == "repass1" || 
				$sqlno == "main2_1" || $sqlno == "main2_2" || $sqlno == "main2_3" ||
				$sqlno == "main4_1" || $sqlno == "main6_1" || 
				$sqlno == "main7_1" || $sqlno == "main7_2" || $sqlno == "main7_3" ||
				$sqlno == "main8_1" || $sqlno == "main8_2" || $sqlno == "main8_3" || 
				$sqlno == "main8_4" || $sqlno == "main8_5" || $sqlno == "main8_6" || $sqlno ==  "main8_7"){
					$db = NULL;
					return false;
				}
				//echo "【SQL:err2】［sqlno］",$sqlno,"［内容］対象行なし［実行したSQL文］",$sql;
				if($type != "select"){
					$db->rollback();
				}
				$db = NULL;
				return false;
			}else{
				$rows = $result->fetchall(PDO::FETCH_ASSOC);
				foreach($rows as $row){

					// 各ページに合わせて関数処理。内容は下記参照のコト。
					$check = sql_func($row,$funcno,$check,$val,$val2);

				}
			}
		}else{
			echo "【SQL:err3】［sqlno］",$sqlno,"［内容］構文エラー（実行時）［実行したSQL文］",$sql;
			if($type != "select"){
				$db->rollback();
			}
			$db = NULL;
			return false;
		}
		if($type != "select"){
			$db->commit();
		}
		$db = NULL;
	}
	catch (Exception $e){
		echo "MSG:" .$e->getMessage()."<br>";
		echo "CODE:".$e->getCode()."<br>";
		echo "LINE:".$e->getLine()."<br>";
		$db->rollback();
		$db = NULL;
		return false;
	}

	if($check){
		return true;
	}else{
		return false;
	}
}


/* --------------------------------------------------------------------------------------- */


function sql_func($row,$funcno,$check,$val,$val2){
	global 
	$sql_output_index_new_algo_pw,
	$sql_output_form_sq_array,
	$sql_output_form_colomn_array,
	$sql_output_repass2_user_sq,
	$sql_output_repass2_user_miss,
	$sql_output_main2_rtest_gr_id_array,
	$sql_output_main2_rtest_gr_score_array,
	$sql_output_main2_rtest_gr_time_array,
	$sql_output_main2_dtest_gr_id_array,
	$sql_output_main2_dtest_gr_score_array,
	$sql_output_main2_dtest_gr_time_array,
	$sql_output_main2_etest_gr_id_array,
	$sql_output_main2_etest_gr_score_array,
	$sql_output_main2_etest_gr_time_array,
	$sql_output_main4_qu_status_array,
	$sql_output_main4_qu_no_array,
	$sql_output_main4_qu_title_array,
	$sql_output_main4_qu_cor_array,
	$sql_output_main4_qu_eva_array,
	$sql_output_main4_qu_comment_array,
	$sql_output_main5_colomn_array,
	$sql_output_main5_user_array,
	$sql_output_main5_user_total,
	$sql_output_main5_user_total_effective,
	$sql_output_main6_qu_status_array,
	$sql_output_main6_qu_del_array,
	$sql_output_main6_qu_id_array,
	$sql_output_main6_qu_no_array,
	$sql_output_main6_qu_title_array,
	$sql_output_main6_qu_cor_array,
	$sql_output_main6_qu_eva_array,
	$sql_output_main6_qu_comment_array,
	$sql_output_main7_user_status_array,
	$sql_output_main7_user_status_2_array,
	$sql_output_main7_user_id_array,
	$sql_output_main7_user_regist_array,
	$sql_output_main7_user_lastlog_array,
	$sql_output_main7_user_loginsu_array,
	$sql_output_main7_user_qusum_array,
	$sql_output_main8_user_total,
	$sql_output_main8_user_total_effective,
	$sql_output_main8_qu_total,
	$sql_output_main8_qu_total_effective,
	$sql_output_main8_log_total,
	$sql_output_main8_log_total_user,
	$sql_output_main8_log_total_guest,
	$sql_output_dojo_qu_no_array,
	$sql_output_dojo_qu_title_array,
	$sql_output_dojo_qu_id_array,
	$sql_output_dojo_qu_cor_array,
	$sql_output_dojo_qu_eva_array,
	$sql_output_test_qno_array,
	$sql_output_question_qu_array,
	$same,
	$sql_output_ans_test_status,
	$sql_output_ans_test_highscore,
	$sql_output_que_create_qu_array;

	switch ($funcno){
		case "index1":
			if($val == $row["user_id"]){
				if(password_verify($val2,$row["user_pw"])){
					$check = true;
					if(password_needs_rehash($row["user_pw"],PASSWORD_DEFAULT)){
						$sql_output_index_new_algo_pw = password_hash($val2,PASSWORD_DEFAULT);
					}
					break;
				}
			}
			$check = false;
			break;
		case "form1":
			$sql_output_form_sq_array[$row["sq_id"]] = $row["sq_q"];
			break;
		case "form2":
			$sql_output_form_colomn_array[] = $row["column_name"];
			break;
		case "form3":
			if(!$same){
				if($val === $row["user_id"]){
					$check = false;
					$same  = true;
					break;
				}else{
					$check = true;
				}
			}
			break;
		case "confilm1":
			if(!$same){
				if($val === $row["user_id"]){
					$check = false;
					$same  = true;
					break;
				}else{
					$check = true;
				}
			}
			break;
		case "confilm2":
			$check = true;
			break;
		case "repass2_1":
			$check = true;
			$sql_output_repass2_user_sq = $row["user_secret_q"];
			break;
		case "repass2_2":
			if($val2 == $row["user_secret_a"]){
				$check = true;
				break;
			}else{
				$check = false;
				break;
			}
		case "repass2_5":
			$check = true;
			$sql_output_repass2_user_miss = $row["user_miss"];
			break;
		case "main2_1":
			$sql_output_main2_rtest_gr_id_array[]		= $row["gr_id"];
			$sql_output_main2_rtest_gr_score_array[]	= $row["gr_score"];
			$sql_output_main2_rtest_gr_time_array[]		= $row["gr_time"];
			break;
		case "main2_2":
			$sql_output_main2_dtest_gr_id_array[]		= $row["gr_id"];
			$sql_output_main2_dtest_gr_score_array[]	= $row["gr_score"];
			$sql_output_main2_dtest_gr_time_array[]		= $row["gr_time"];
			break;
		case "main2_3":
			$sql_output_main2_etest_gr_id_array[]		= $row["gr_id"];
			$sql_output_main2_etest_gr_score_array[]	= $row["gr_score"];
			$sql_output_main2_etest_gr_time_array[]		= $row["gr_time"];
			break;
		case "main4_1":
			$sql_output_main4_qu_status_array[]		= $row["qu_status"];
			$sql_output_main4_qu_no_array[]			= $row["qu_no"];
			$sql_output_main4_qu_title_array[]		= $row["qu_title"];
			$sql_output_main4_qu_cor_array[]		= $row["an_su_c/an_su_a"];
			$sql_output_main4_qu_eva_array[]		= $row["an_evaluation/an_su_e"];
			$sql_output_main4_qu_comment_array[]	= $row["qu_comment"];
			break;
		case "main5_1":
			$sql_output_main5_colomn_array[] = $row["column_name"];
			break;
		case "main5_2":
			foreach($sql_output_main5_colomn_array as $val){
				$sql_output_main5_user_array[$val] = $row[$val];
			}
			break;
		case "main6_1":
		case "main6_2":
		case "main6_3":
		case "main6_4":
		case "main6_5":
			$sql_output_main6_qu_status_array[]		= $row["qu_status"];
			$sql_output_main6_qu_del_array[]		= $row["qu_delete"];
			$sql_output_main6_qu_id_array[]			= $row["qu_id"];
			$sql_output_main6_qu_no_array[]			= $row["qu_no"];
			$sql_output_main6_qu_title_array[]		= $row["qu_title"];
			$sql_output_main6_qu_cor_array[]		= $row["an_su_c/an_su_a"];
			$sql_output_main6_qu_eva_array[]		= $row["an_evaluation/an_su_e"];
			$sql_output_main6_qu_comment_array[]	= $row["qu_comment"];
			break;
		case "main7_1":
			$sql_output_main7_user_status_array[]	= $row["user_delete"];
			$sql_output_main7_user_status_2_array[]	= $row["user_freeze"];
			$sql_output_main7_user_id_array[]		= $row["user_id"];
			$sql_output_main7_user_regist_array[]	= $row["user_registration"];
			break;
		case "main7_2":
			$sql_output_main7_user_lastlog_array[] = $row["max(log_log)"];
			$sql_output_main7_user_loginsu_array[] = $row["count(log_log)"];
			break;
		case "main7_3":
			$sql_output_main7_user_qusum_array[] = $row["count(qu_no)"];
			break;
		case "main8_1":
			$sql_output_main8_user_total = $row["count(user_id)"];
			break;
		case "main8_2":
			$sql_output_main8_user_total_effective = $row["count(user_id)"];
			break;
		case "main8_3":
			$sql_output_main8_qu_total = $row["count(qu_no)"];
			break;
		case "main8_4":
			$sql_output_main8_qu_total_effective  = $row["count(qu_no)"];
			break;
		case "main8_5":
			$sql_output_main8_log_total = $row["count(log_id)"];
			break;
		case "main8_6":
			$sql_output_main8_log_total_user = $row["count(log_id)"];
			break;
		case "main8_7":
			$sql_output_main8_log_total_guest = $row["count(log_id)"];
			break;
		case "dojo1":
		case "dojo2":
			//select qu_no,qu_title,qu_id,an_su_c/an_su_a,an_evaluation/an_su_e from k1_question,k1_answer where qu_no = an_no order by an_su_a asc ,an_no desc;
			$sql_output_dojo_qu_no_array[]		= $row["qu_no"];
			$sql_output_dojo_qu_title_array[]	= $row["qu_title"];
			$sql_output_dojo_qu_id_array[]		= $row["qu_id"];
			$sql_output_dojo_qu_cor_array[]		= $row["an_su_c/an_su_a"];
			$sql_output_dojo_qu_eva_array[]		= $row["an_evaluation/an_su_e"];
			break;
		case "que_test1":
		case "que_test2":
		case "que_test3":
			$sql_output_test_qno_array[] = $row["qu_no"];
			$check = true;
			break;
		case "question1":
			$sql_output_question_qu_array["qu_no"] 				= $row["qu_no"];
			$sql_output_question_qu_array["qu_title"] 			= $row["qu_title"];
			$sql_output_question_qu_array["qu_id"] 				= $row["qu_id"];
			$sql_output_question_qu_array["qu_question"] 		= $row["qu_question"];
			$sql_output_question_qu_array["qu_answer_1"] 		= $row["qu_answer_1"];
			$sql_output_question_qu_array["qu_answer_2"] 		= $row["qu_answer_2"];
			$sql_output_question_qu_array["qu_answer_3"] 		= $row["qu_answer_3"];
			$sql_output_question_qu_array["qu_answer_4"] 		= $row["qu_answer_4"];
			$sql_output_question_qu_array["qu_answer_correct"] 	= $row["qu_answer_correct"];
			$sql_output_question_qu_array["qu_time_limit"] 		= $row["qu_time_limit"];
			$sql_output_question_qu_array["qu_explanation"] 	= $row["qu_explanation"];
			$check = true;
			break;
		case "answer1":
			if($val == $row["qu_id"]){
				$check = false;
				break;
			}
			$check = true;
			break;
		case "ans_test1":
			$sql_output_ans_test_highscore = $row["gr_score"];
			if($row["gr_score"] == ""){
				$sql_output_ans_test_status = "first_time";
				$check = true;
				break;
			}
			if($val > $row["gr_score"]){
				$sql_output_ans_test_status = "new_record";
				$check = true;
			}else{
				if($val == $row["gr_score"]){
					$sql_output_ans_test_status = "same_record";
					$check = true;
				}else{
					$check = false;
				}
			}
			break;
		case "que_create1":
			$sql_output_form_colomn_array[] = $row["column_name"];
			break;
		case "que_create2":
			$sql_output_que_create_qu_array["qu_no"]				= $row["qu_no"];
			$sql_output_que_create_qu_array["qu_title"]				= $row["qu_title"];
			$sql_output_que_create_qu_array["qu_id"]				= $row["qu_id"];
			$sql_output_que_create_qu_array["qu_question"]			= $row["qu_question"];
			$sql_output_que_create_qu_array["qu_answer_1"]			= $row["qu_answer_1"];
			$sql_output_que_create_qu_array["qu_answer_2"]			= $row["qu_answer_2"];
			$sql_output_que_create_qu_array["qu_answer_3"]			= $row["qu_answer_3"];
			$sql_output_que_create_qu_array["qu_answer_4"]			= $row["qu_answer_4"];
			$sql_output_que_create_qu_array["qu_answer_correct"]	= $row["qu_answer_correct"];
			$sql_output_que_create_qu_array["qu_time_limit"]		= $row["qu_time_limit"];
			$sql_output_que_create_qu_array["qu_explanation"]		= $row["qu_explanation"];
			break;
		default:
			$check = true;
	}
	return $check;
}

?>