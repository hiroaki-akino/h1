<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：解答結果表示ページ
番号：⑩

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include 'config.php';

// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認）
$pflag;							// 中身：false
$head_common_tag;				// 中身：head タグ内で規定するメタタグとか
$header_common_tag;				// 中身：header タグ内で規定するタイトルタグとか

/* ここで使うconfig.php内の関数とか処理。(発火するとマズイので全部コメントアウト)
session_user_check($session_user)
create_input($type,$id,$name,$size,$val,$attribute,$attr_val,$placeholder)
sql($type,$userno,$sqlno,$funcno,$val,$val2,$val_array) 
sql_func($row,$funcno,$check,$val,$val2)
*/

// ここで使う変数の初期化。
$pflag					= "false";
$form1_submit_disable 	= "false";
$disable_type			= "false";
$form2_submit  			= "false";
$action_submit 			= "false";
$metod_submit  			= "false";
$question_q_array		= array();// 中身：question.php から取得した問題の情報（１問単位）

/* --------------------------------------------------------------------------------------- */



// 各種タイプ毎にタイトル変更
switch($_SESSION["question"]["type"]){
	case "dojo":
		$title_type = "一問一答道場";
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

/* 評価送信処理のキャンセル判定変数の設定（JSで使う）。*/
// レビュー時は評価不可にする。
if($_SESSION["question"]["type"] == "create" ||
  $_SESSION["question"]["type"] == "confirm" ||
  $_SESSION["question"]["type"] ==  "modify" ||
  $_SESSION["question"]["type"] == "admin_approval"){
	$form1_submit_disable = "true";
	$disable_type = "レビュー";
}else{
	// ゲストの場合は評価不可にする。
	if($_SESSION["user"]["name"] == "guest"){
		$form1_submit_disable = "true";
		$disable_type = "ゲスト";
	}else{
		// ログイン者と作成者が同一人物の場合は評価不可にする。
		if(!sql("select",$_SESSION["user"]["type"],"answer1","answer1",$_SESSION["user"]["name"],$_SESSION["question"]["qu_no"],"")){
			$form1_submit_disable = "true";
			$disable_type = "同一人物";
		}
	}
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;

	/* 評価された時の処理 */
	if(isset($_POST["an_evaluation"])){
		$question_q_array["qu_title"]		= htmlspecialchars($_POST["an_eva_when_4_title"],ENT_QUOTES);
		$question_q_array["qu_question"]	= htmlspecialchars($_POST["an_eva_when_4_que"],ENT_QUOTES);
		$an_eva								= htmlspecialchars($_POST["an_evaluation"],ENT_QUOTES);

		// k1_answer テーブルの累計評価回数を+1に、累計評価を取得値分追記。(config.php参照)
		sql("select",$_SESSION["user"]["type"],"answer2","answer2",$an_eva,$_SESSION["question"]["qu_no"],"");

		// 評価送信処理のキャンセル判定変数の設定（JSで使う）。
		$form1_submit_disable = "true";
		$disable_type = "評価済み";
	}

	/* 各ボタンごとの処理 */
	if(isset($_POST["btn"])){
		switch($_POST["btn"]){
			case "新規会員登録をする！":
				$_SESSION["user"]["name"] = "pre";
				$_SESSION["user"]["type"] = "3";
				$_SESSION["msg_type"]     = "3";
				header("Location:form.php");
				exit;
			case "一問一答道場に戻る":
				header("Location:question_dojo.php");
				exit;
			case "メインメニューに戻る":
				header("Location:main.php");
				exit;
			case "再度挑戦する（同じ問題）":
				header("Location:question.php");
				exit;
			case "編集（修正）する":
				// 処理なし（実際の処理はJSで処理）
				break;
			case "編集を破棄する":
				// 処理なし（実際の処理はJSで処理）
				break;
			case "登録申請！":
				sql("insert",$_SESSION["user"]["type"],"answer3","answer3",$_SESSION["user"]["name"],"",$_POST["question"]);
				sql("insert",$_SESSION["user"]["type"],"answer4","answer4","","","");
				header("Location:question_create_result.php");
				exit;
				break;
			case "修正登録":
				sql("insert",$_SESSION["user"]["type"],"answer5","answer5",$_SESSION["question"]["qu_no"],"",$_POST["question"]);
				header("Location:question_create_result.php");
				exit;
				break;
			case "承認する":
				$q_reason = htmlspecialchars($_POST["qu_reason"],ENT_QUOTES);
				sql("update",$_SESSION["user"]["type"],"answer6","answer6",$q_reason,$_SESSION["question"]["qu_no"],"");
				header("Location:main.php");
				exit;
			case "却下する":
				$q_reason = htmlspecialchars($_POST["qu_reason"],ENT_QUOTES);
				sql("update",$_SESSION["user"]["type"],"answer7","answer7",$q_reason,$_SESSION["question"]["qu_no"],"");
				header("Location:main.php");
				exit;
		}
	}
}else{
	// GET時の不正アクセスの制御処理（直リンクアクセスはindex.phpにリダイレクト。詳細な処理は config.php を確認）
	session_user_check($_SESSION["user"]);
	// 問題情報の取得（ create の時は hidden タグに、その他はsessionに入ってる。）
	if($_SESSION["question"]["type"] == "create" || $_SESSION["question"]["type"] == "modify"){
		$question_q_array = $_GET["question"];
	}else{
		$question_q_array["qu_no"]			= $_SESSION["question"]["qu_no"];
		$question_q_array["qu_title"]		= $_SESSION["question"]["qu_title"];
		$question_q_array["qu_question"]	= $_SESSION["question"]["qu_question"];
		$question_q_array["qu_explanation"] = $_SESSION["question"]["qu_explanation"];
	}
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<?= $head_common_tag ?>
	<script>
		window.onload = function(){

			// ログアウト（トップに戻る）の確認処理。
			const a1_tag = document.getElementById("a1");
			a1_tag.addEventListener("click",function(){
				event.preventDefault();
				var confirm_result = confirm("「ログアウト」してログイン画面に戻りますか？");
				if(confirm_result){ 
					location.href = "index.php";
				}
			});

			// 動的な brタグの作成
			const br_tag  = document.createElement("br");

			// 評価用のタグに関する各種処理。（色々な技術使ってるから解説は省く笑）
			// 割と頑張って作ったとこの1つ。
			const form1_tag = document.getElementById("form1");
			const i_tags	= form1_tag.children;
			const hd_tag	= document.getElementById("an_evaluation");
			for(var i = 0 ; i < i_tags.length ; i++){
				i_tags[i].style.color	 = "silver";
				i_tags[i].style.fontSize = "30px";
				i_tags[i].addEventListener("click",function(e){
					// 迷走してた時に書いたやつ。ここでは使ってないけど後学の為に残す。
					for(var j = 0 ; j < i_tags.length ; j++){
						i_tags[j].setAttribute("data-num","1");
					}
					if(<?= $form1_submit_disable ?> && !document.getElementById("err_msg")){
						const msg_tag = document.createElement("span");
						msg_tag.id				= "err_msg";
						msg_tag.style.color		= "red";
						msg_tag.style.fontSize	= "18px";
						switch("<?= $disable_type ?>"){
							case "レビュー":
								msg_tag.innerHTML = "<i class=\"fas fa-exclamation-triangle\"></i> レビュー時は評価を送信できません。<br>(星を選択することは可能です。)";
								break;
							case "ゲスト":
								msg_tag.innerHTML = "<i class=\"fas fa-exclamation-triangle\"></i> 申し訳ございません。<br>ゲスト様は評価を送信できません。<br>(会員登録を行えば評価することができるようになります。)";
								break;
							case "評価済み":
								msg_tag.innerHTML = "<i class=\"fas fa-exclamation-triangle\"></i> 申し訳ございません。<br>評価の送信は１回のみとなります。<br>(再度問題を回答すると評価することができます。)";
								break;
							case "同一人物":
								msg_tag.innerHTML = "<i class=\"fas fa-exclamation-triangle\"></i> 申し訳ございません。<br>自身で作成した問題の評価はできません。";
								break;
						}
						form1_tag.appendChild(br_tag);
						form1_tag.appendChild(msg_tag);
					}else{
						if(!document.getElementById("err_msg")){
							alert("評価に御協力いただき、\nありがとうございました！");
							hd_tag.value = (Number(e.target.id) + 1);
							form1_tag.submit();
						}
					}
				});
				i_tags[i].addEventListener("mousemove",function(e){
					var k = e.target.id ;
					for(k ; k >= 0 ; k--){
						console.log(k);
						i_tags[k].style.color = "gold";
						i_tags[k].classList.add("fa-spin");
					}
				});
				i_tags[i].addEventListener("mouseout",function(e){
					var l = e.target.id ;
					console.log(l);
					for(l ; l >= 0 ; l--){
						console.log(l);
						i_tags[l].style.color = "silver";
						i_tags[l].classList.remove("fa-spin");
					}
				});
			}
		}

		// ボタンに合わせた送信先の変更
		function again_edit(){
			const form2_tag = document.getElementById("form2");
			form2_tag.setAttribute("action","question_create.php");
			form2_tag.submit();
		}
		function cxl_edit(){
			var confirm2_result = confirm("編集中のデータは破棄されますが、\nよろしいでしょうか？");
			sessionStorage.clear();
			if(confirm2_result){ 
				location.href = "main.php";
			}
		}
		function modify(){
			const form2_tag = document.getElementById("form2");
			var confirm2_result = confirm("修正する場合、再審査が必要となります。\nよろしいでしょうか？");
			if(confirm2_result){ 
				form2_tag.submit();
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
		.left{
			text-align : left;
		}
		.result{
			text-align  : center;
			font-size   : 3em;
			font-weight : bold;
		}
		.result_state{
			text-align  : left;
			font-size   : 2em;
			font-weight : bold;
		}
		.correct{
			color:blue;
		}
		.wrong{
			color:red;
		}
		.explanation{
			text-indent: 1em;
		}
		input[value="再度挑戦する（同じ問題）"]{
			width:45%;
		}
		input[value="編集を破棄する"],
		input[value="却下する"]{
			width: 30%;
			background-color: lightgrey;
			font-size: 15px;
		}
		input[value="編集を破棄する"]:hover,
		input[value="却下する"]:hover{
			background-color: lightgrey;
			font-size: 20px;
			color:black;
		}
	</style>
	<title>問題解答</title>
</head>

<body>
	<header>
		<?= $header_common_tag ?>
	</header>
	<main>
		<header>
			<h1><?= $title_type ?></h1>
			<span id="err"><?= $err_msg ?></span>
		</header>
		<section>
			<header>
				<h2>
					<?= $question_q_array["qu_title"] ?>
				</h2>
				<span id="err"><?= $err_msg ?></span>
			</header>
			<span id="err"><?= $err_msg ?></span>
			<br>
			<section>
				<header class="left">
					<h3>
						<?= $question_q_array["qu_question"] ?>
					</h3>
				</header>
				<p class="result">
					<?php 
						if($_SESSION["question"]["result"] == 1){
							echo "<font class=\"correct\">正解！</font>";
						}else{
							echo "<font class=\"wrong\">残念！不正解！</font>";
						}
					?>
				</p>
				<p class="result_state">
					<?php
						// 各問題の正解内容（番号ではない）を表示するタメの処理。
						// 後から無理やり付けたのであまり綺麗な処理ではない。
						echo "正解：";
						switch($_SESSION["question"]["type"]){
							case "dojo":
								echo $_SESSION["question"]["qu_answer_correct_state"];
								break;
							case "create":
							case "modify":
								$su = $_SESSION["question"]["qu_answer_correct"];
								echo $_GET["question"]["qu_answer_${su}"];
								break;
						}
					?>
				 </p>
				<p class="left explanation">
					解説文：
					<?= $_SESSION["question"]["qu_explanation"] ?>
				</p>
			</section>
			<br>
			<section>
				<header>
					<h3>問題の評価</h3>
				</header>
				<p>星をクリックすると問題の評価を行うことができます。（最大：星５）</p>
				<form id="form1" action="answer.php" method="POST">
					<i id="0" class="fa fa-star" ></i>
					<i id="1" class="fa fa-star" ></i>
					<i id="2" class="fa fa-star" ></i>
					<i id="3" class="fa fa-star" ></i>
					<i id="4" class="fa fa-star" ></i>
					<!-- ここからPOST送信されたかどうか（評価されたかどうか）を判定するページ-->
					<?= create_input("hidden","an_evaluation","an_evaluation","","","","","") ?>
					<?= create_input("hidden","an_eva_when_4_title","an_eva_when_4_title",$question_q_array["qu_title"],"","","","") ?>
					<?= create_input("hidden","an_eva_when_4_title","an_eva_when_4_que",$question_q_array["qu_question"],"","","","") ?>
					<span id="new"><span>
				</form>
			</section>
			<br>
			<section>
				<?php
					echo "<form id=\"form2\" action=\"answer.php\" method=\"POST\">";
					switch($_SESSION["question"]["type"]){
						case "dojo":
							echo create_input("submit","btn","btn","10","再度挑戦する（同じ問題）","","","");
							echo "<br>";
							echo create_input("submit","btn","btn","10","一問一答道場に戻る","","","");
							echo "<br>";
							echo create_input("submit","btn","btn","10","メインメニューに戻る","","","");
							echo "<br>";
							if($_SESSION["user"]["name"] == "guest"){
								echo create_input("submit","btn","btn","10","新規会員登録をする！","","","");
							}
							break;
						case "confirm":
							echo create_input("submit","btn","btn","10","再度挑戦する（同じ問題）","","","");
							echo "<br>";
							echo create_input("submit","btn","btn","10","メインメニューに戻る","","","");
							break;
						case "create":
						case "modify":
							foreach($question_q_array as $key => $val){
								$name = "question[{$key}]";
								if(is_array($val)){
									foreach($val as $key2 => $val2){
										echo create_input("hidden","question",$name,"",$val2,"","","");
									}
								}else{
									echo create_input("hidden","question",$name,"",$val,"","","");
								}
							}
							if( $_SESSION["question"]["type"] ==  "create"){
								echo create_input("submit","btn","btn","10","登録申請！","","","");
								echo "<br>";
							}else{
								echo create_input("hidden","btn","btn","","修正登録","","","");
								echo create_input("button","btn","btn","10","修正内容を再登録申請！","onclick","modify()","");
								echo "<br>";
							}
							echo create_input("button","btn","btn","10","編集（修正）する","onclick","again_edit()","");
							echo "<br>";
							echo create_input("button","btn","btn","10","編集を破棄する","onclick","cxl_edit()","");
							break;
						case "admin_approval":
							echo "コメント欄（却下時はその理由など）";
							echo "<br>";
							echo "<textarea id=\"qu_reason\" name=\"qu_reason\" cols=\"20\" rows=\"5\" placeholder=\"卑猥な表現を含むため不承認と致しました。\"></textarea>";
							echo "<br>";
							echo create_input("submit","btn","btn","10","承認する","","","");
							echo "<br>";
							echo create_input("submit","btn","btn","10","却下する","","","");
							break;
					}
					echo "</form>";
				?>
			</section>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>

