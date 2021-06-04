<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：問題表示ページ
番号：⑨

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include 'config.php';

// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認）
$pflag;							// 中身：false
$head_common_tag;				// 中身：head タグ内で規定するメタタグとか
$header_common_tag;				// 中身：header タグ内で規定するタイトルタグとか
$sql_output_question_qu_array;

/* ここで使うconfig.php内の関数とか処理。(発火するとマズイので全部コメントアウト)
session_user_check($session_user)
create_input($type,$id,$name,$size,$val,$attribute,$attr_val,$placeholder)
sql($type,$userno,$sqlno,$funcno,$val,$val2,$val_array) 
sql_func($row,$funcno,$check,$val,$val2)
*/

// ここで使う変数の初期化。
$pflag	= "false";
$qu_no	= "";
$qu_create_to_ans_submit = "false";
if(!isset($_SESSION["question"]["su"])){
	$_SESSION["question"]["su"] = 0;
}
/** 乱数用 **/
$rands = [];
for($i=1;$i<=4;$i++){
	while(true){
		/** 一時的な乱数を作成 */
		$tmp = mt_rand(1,4);
		if(!in_array($tmp,$rands)){
			array_push($rands,$tmp);
			break;
		}
	}
}
$input = array();

/* --------------------------------------------------------------------------------------- */



if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;

	// 選択した解答を取得（未選択時は空値を代入）
	$qu_no = htmlspecialchars($_POST["qu_no"],ENT_QUOTES);
	if(isset($_POST["answer"])){
		$an_no = htmlspecialchars($_POST["answer"],ENT_QUOTES);
	}else{
		$an_no = "";
	}

	/** 選択された回答の正誤判断チェック **/
	if($_SESSION["question"]["qu_answer_correct"] == $an_no){
		/* 以降、正解してた時の処理*/
		switch($_SESSION["question"]["type"]){
			case "dojo":
				// k1_answer テーブルの累計回答数と累計正解数を+1にする処理（config.php参照）
				sql("select",$_SESSION["user"]["type"],"question2","question2",$qu_no,"","");
				// 正誤結果をセッションに代入（0:不正解、1:正解）
				// 結果表示ページにリダイレクト
				$_SESSION["question"]["result"] = 1;
				header("Location:answer.php");
				exit;

			case "r_test":
			case "d_test":
			case "e_test":
				// 上記の通り。
				sql("select",$_SESSION["user"]["type"],"question2","question2",$qu_no,"","");
				// 正誤結果を各問題毎のセッションに代入（0:不正解、1:正解）
				// 問題数カウントを+1にする(10問で終了(下の処理で判定))
				$_SESSION["question"]["result"][] = 1;
				$_SESSION["question"]["su"]++;
				break;

			case "create":
			case "confirm":
			case "modify":
			case "admin_approval":
				// 正誤結果をセッションに代入（0:不正解、1:正解）
				// 結果表示ページにリダイレクト（試験運用なので累計回答数とかはDBに登録しない。というか未登録なのでできない。）
				$_SESSION["question"]["result"] = 1;
				// 荒技（JSを参照）
				$qu_create_to_ans_submit = "true";
				break;
		}
	}else{
		/* 以降、不正解の時の処理*/
		switch($_SESSION["question"]["type"]){
			case "dojo":
				// k1_answer テーブルの累計回答数「だけ」+1にする処理（config.php参照）
				sql("select",$_SESSION["user"]["type"],"question3","question3",$qu_no,"","");
				// 正誤結果をセッションに代入（0:不正解、1:正解）
				// 結果表示ページにリダイレクト
				$_SESSION["question"]["result"] = 0;
				header("Location:answer.php");
				exit;

			case "r_test":
			case "d_test":
			case "e_test":
				// 上記の通り。
				sql("select",$_SESSION["user"]["type"],"question3","question3",$qu_no,"","");
				// 正誤結果を各問題毎のセッションに代入（0:不正解、1:正解）
				// 問題数カウントを+1にする(10問で終了(下の処理で判定))
				$_SESSION["question"]["result"][] = 0;
				$_SESSION["question"]["su"]++;
				break;

			case "create":
			case "confirm":
			case "modify":
			case "admin_approval":
				// 正誤結果をセッションに代入（0:不正解、1:正解）
				// 結果表示ページにリダイレクト
				$_SESSION["question"]["result"] = 0;
				// 荒技（JSを参照）
				$qu_create_to_ans_submit = "true";
				break;
		}
	}
	// 各種テスト実施時の回答問題数カウントチェック（10回で終了）
	if($_SESSION["question"]["su"] >= 10){
		// 結果表示ページにリダイレクト
		header("Location:answer_test.php");
		exit;
	}
}else{
	// GET時の不正アクセスの制御処理（直リンクアクセスはindex.phpにリダイレクト。詳細な処理は config.php を確認）
	session_user_check($_SESSION["user"]);
}

// 各種タイプ毎にタイトル変更
switch($_SESSION["question"]["type"]){
	case "dojo":
		$title_type = "一問一答道場";
		break;
	case "r_test":
		$title_type = "ランダムテスト";
		break;
	case "d_test":
		$title_type = "難問テスト";
		break;
	case "e_test":
		$title_type = "高評価問題テスト";
		break;
	case "create":
	case "confirm":
		$title_type = "レビューモード（内容確認）";
		break;
	case "modify":
		$title_type = "修正モード（修正内容確認）";
		break;
	case "admin_approval":
		$title_type = "承認モード（管理者確認）";
		break;
}

// 各種タイプ毎に問題番号を取得（問題は前ページでセッションに代入済み）
switch($_SESSION["question"]["type"]){
	case "dojo":
		// 一問だけなのでここで問題番号取得。
		$qu_no = $_SESSION["question"]["qu_no"];
		break;
	case "r_test":
	case "d_test":
	case "e_test":
		// 次に表示する問題番号をセッション（テストの時だけ連想配列）から取得
		// セッション（テストの時だけ連想配列）の添字と$_SESSION["question"]["su"]の初期値は0からスタート
		foreach($_SESSION["question"]["qu_no"] as $key => $val){
			if($key == $_SESSION["question"]["su"]){
				$qu_no = $val;
			}
		}
		break;
	case "create":
	case "confirm":
	case "modify":
	case "admin_approval":
		// create の場合は採番されていないので番号なし。
		$qu_no = $_SESSION["question"]["qu_no"];
		break;
}

// 各種タイプ毎に問題の情報を取得
switch($_SESSION["question"]["type"]){
	case "dojo":
	case "r_test":
	case "d_test":
	case "e_test":
	case "confirm":
	case "admin_approval":
		// 表示する問題の情報を上記の問題番号をキーにDBから取得
		// config.phpで$sql_output_question_qu_arrayが書き換えられて、当該ページに各項目に自動で代入。便利。
		// DBのカラム名と↑の変数のキー名合わせてるからできる技。設計勝ち。だから、変にイジると全部崩れる笑。
		// ただし、制限時間はJSでいじるのでJS側に代入。
		// 加えて、答えと解説文をHTML系（hiddenタグとか）に入れるとおもしろくないのでセッションに格納。
		sql("select",$_SESSION["user"]["type"],"question1","question1",$qu_no,"","");
		$_SESSION["question"]["qu_title"]			= $sql_output_question_qu_array["qu_title"];
		$_SESSION["question"]["qu_question"]		= $sql_output_question_qu_array["qu_question"];
		$_SESSION["question"]["qu_answer_correct"]	= $sql_output_question_qu_array["qu_answer_correct"];
		$_SESSION["question"]["qu_explanation"]		= $sql_output_question_qu_array["qu_explanation"];
		// 解答結果表示ページ（answer.php）で、各問題の正解内容（番号ではない）を表示するタメの処理。
		// 後から無理やり付けたのであまり綺麗な処理ではない。
		$w													= $sql_output_question_qu_array["qu_answer_correct"];
		$_SESSION["question"]["qu_answer_correct_state"]	= $sql_output_question_qu_array["qu_answer_${w}"];
		break;
	case "create":
	case "modify":
		// GET送信されて来た値を連想配列で取得 & $sql_output_question_qu_arrayの必要項目に自動代入。便利。
		// 前ページの各タグのname名と↑の変数のキー名合わせてるからできる技。設計勝ち。だから変に名前イジると大変なことになる。
		// あとは上記と同じ。
		if($_SERVER["REQUEST_METHOD"] == "GET"){
			foreach($_GET["question"] as $key => $val){
				if(is_array($val)){
					foreach($val as $key2 => $val2){
						$sql_output_question_qu_array[$key] = $val2;
					}
				}else{
					$sql_output_question_qu_array[$key] = $_GET["question"][$key];
				}
			}
		}else{
			// 荒技（問題の情報をセッションでやりとりすればこんな作業は不要やった）
			// 再編集することも考慮してhiddenタグを使う事にした苦渋の決断の結果。
			// 問題作成(question_create.php) → [GET] → レビュー/回答(question.php）
			// → [POST] → レビュー(question.php)の時の処理。
			// この後は、JSで無理矢理宛先変えて「answer.php」にGET送信
			foreach($_POST["question"] as $key => $val){
				if(is_array($val)){
					foreach($val as $key2 => $val2){
						$sql_output_question_qu_array[$key] = $val2;
					}
				}else{
					$sql_output_question_qu_array[$key] = $_POST["question"][$key];
				}
			}
		}
		// セッションから取ってるけど create の場合は空値（番号なし）。
		$qu_no = $_SESSION["question"]["qu_no"];
		$_SESSION["question"]["qu_answer_correct"] = $sql_output_question_qu_array["qu_answer_correct"];
		$_SESSION["question"]["qu_explanation"] = $sql_output_question_qu_array["qu_explanation"];
		break;
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<?= $head_common_tag ?>
	<script>
		window.onload = function(){

			// ログアウト（トップに戻る）の確認処理。
			var a1_tag = document.getElementById("a1");
			a1_tag.addEventListener("click",function(){
				event.preventDefault();
				var confirm_result = confirm("「ログアウト」してログイン画面に戻りますか？");
				if(confirm_result){ 
					location.href = "index.php";
				}
			});

			// 制限時間タイマー処理。
			var limit_time = <?= $sql_output_question_qu_array["qu_time_limit"] ?> * 1000;
			var limit_progress_tag = document.getElementById("limit_progress");
			limit_progress_tag.setAttribute("max",limit_time/10);
			var old_time   = Date.now();
			
			var timer = setInterval( function(){
				var now_time = Date.now();
				// console.log(old_time + " " + now_time);
				var zan   = now_time - old_time; 
				var limit = limit_time-zan;
				var limit_1 = ("00" + (Math.ceil((limit)/1000)-1)).slice(-2);
				var limit_2 = ("00" + Math.ceil((limit)/10)).slice(-2);
				// document.getElementById("limit").style.transition = "0.5s";
				document.getElementById("limit").style.fontSize = "50px";
				// console.log(limit_1 + " " + limit_2);
				var limit_val = limit_1 * 1 + limit_2;
				// console.log(limit_val);
				limit_progress_tag.value = limit_val;
				document.getElementById("limit").innerHTML = limit_1 + ":" + limit_2;
				document.getElementById("limit").style.color = "black";
				
				// limit_progress_tag.style.borderColor = "white"
				// limit_progress_tag.style.background = "red";
				limit_progress_tag.style.height = "20px";
				
				if(limit_1 < 3){
					document.getElementById("limit").style.color = "red";
					limit_progress_tag.style.color = "red";
					// document.getElementById("limit").style.fontSize = "60px";
					// document.getElementById("limit").style.transition = "all 2s";
				}
				if(limit_2 > 90 || limit_2 < 10 ){
					// setInterval(function(){
					// document.getElementById("limit").style.transition = "0.2s";
					// document.getElementById("limit").style.fontWeight = "normal";
					// document.getElementById("limit").style.fontSize   = "20px";
					// },90);
					document.getElementById("limit").style.transition = "0.2s";
					document.getElementById("limit").style.fontWeight = "bold";
					document.getElementById("limit").style.fontSize   = "52px";
					// document.getElementById("limit").style.transform = "scale(2,2)";
					// clearInterval(timer);
				}else{
					document.getElementById("limit").style.transition = "0.2s";
					document.getElementById("limit").style.fontWeight = "normal";
					document.getElementById("limit").style.fontSize   = "50px";
				}
				if(limit <= 0){
					clearInterval(timer);
					document.getElementById("limit").innerHTML = "00:00";
					document.getElementById("form").submit();
				}
			},10);

			// 超荒技による送信先変更（不本意やけど理由は210行目付近の通り。）
			if(<?= $qu_create_to_ans_submit ?>){
				const form_tag = document.getElementById("form");
				form_tag.setAttribute("action","answer.php");
				form_tag.setAttribute("method","GET");
				form_tag.submit();
			}

		}
	</script>
	<style>
		body > header,
		main > header{
			margin    : auto;
			max-width : 800px;
		}
		section{
			margin: auto;
			text-align : center;
			max-width  : 800px;
		}
		section > header{
			position : relative;
		}
		.limit{
			position   : absolute ; 
			top        : -50px; 
			right      : 20px;
			text-align : center; 
		}
		section > header{
			text-align : left;
		}
		fieldset{
			padding : 20px 0px 30px;
		}
		fieldset legend{
			padding : 0px 20px;
		}
		fieldset label{
			display        : inline-block;
			width          : 20%;
			vertical-align : middle;
			font-size      : 20px;
		}
		input[type="submit"]{
			width  : 40%;
			height : 60px;
		}
	</style>
	<title>問題</title>
</head>

<body>
	<header>
		<?= $header_common_tag ?>
	</header>
	<main>
		<header>
			<h1><?= $title_type ?>
			<?php
				// テストの時は現在10問中、何問目かを表示する。
				if($_SESSION["question"]["type"] == "r_test" ||
				$_SESSION["question"]["type"] == "d_test" ||
				$_SESSION["question"]["type"] == "e_test"){
					echo "(" , $_SESSION["question"]["su"] + 1 ," / 10問目）</h1>";
				}else{
					echo "</h1>";
				}
			?>
		</header>
		<section>
			<header>
				<?php 
					// if($_SESSION["question"]["type"] == "create"){
					// 	echo "<span>※ 登録申請前は、問題番号が表示されません。</span><br>";
					// }
				?>
				<h2>
					<?php 
						// create(新規作成時)の時は問題番号が採番されてないので「なし」と表示する。
						if($_SESSION["question"]["type"] == "create"){
							echo "【問題番号 なし 】";
						}else{
							echo "【問題 No." , $qu_no , "】";
						}
					?>
					<?= $sql_output_question_qu_array["qu_title"] ?>
				</h2>
				<div class="limit">
					<font>制限時間</font><br>
					<span id="limit"></span>秒<br>
					<progress id="limit_progress" value="0" ></progress>
				</div>
			</header>
			<span id="err"><?= $err_msg ?></span>
			<br>
			<section>
				<header>
					<h3><?= $sql_output_question_qu_array["qu_question"] ?></h3>
				</header>
				<br>
				<form id="form" action="question.php" method="POST">
					<fieldset>
						<legend>解答群</legend>
						<?php
							// DB から解答群(1~4)の内容をinputタグ(lavel)内に代入
							for($i=1;$i<5;$i++){
								$input[$i] = "";
								$input[$i] .= "<label for=\"$i\">";
								$input[$i] .= "<input type=\"radio\" name=\"answer\" id=\"$i\" value=\"$i\"> ";
								switch($i){
									case 1:
										$input[$i] .= $sql_output_question_qu_array["qu_answer_1"];
										break;
									case 2:
										$input[$i] .= $sql_output_question_qu_array["qu_answer_2"];
										break;
									case 3:
										$input[$i] .= $sql_output_question_qu_array["qu_answer_3"];
										break;
									case 4:
										$input[$i] .= $sql_output_question_qu_array["qu_answer_4"];
										break;
								}
								$input[$i] .= "</label>";
							}
							// 解答群の位置を毎回ランダムで表示
							foreach($rands as $val){
								echo $input[$val], "　"; 
							}
						?>
					</fieldset>
					<br>
					<?php
						if($_SESSION["question"]["type"] == "create" || $_SESSION["question"]["type"] == "modify"){
							foreach($sql_output_question_qu_array as $key => $val){
								$name = "question[{$key}]";
								if(is_array($val)){
									foreach($val as $key2 => $val2){
										echo create_input("hidden","question",$name,"",$val2,"","","");
									}
								}else{
									echo create_input("hidden","question",$name,"",$val,"","","");
								}
							}
						}
						echo create_input("hidden","qu_no","qu_no","",$qu_no,"","","");
						echo create_input("submit","btn","btn","10","解答","","","");
					?>
				</form>
			</section>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>