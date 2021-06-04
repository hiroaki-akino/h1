<?php

/* --------------------------------------------------------------------------------------

【基本情報】
作成：秋野浩朗（web1902)
概要：一問一答スタートページ
番号：⑧

----------------------------------------------------------------------------------------- */



/* ----- 共通処理 ----------------------------------------------------------------------- */

include 'config.php';

// ここで使うconfig.php内の変数。（必要に応じて var_dump で確認）
$pflag;							// 中身：false
$head_common_tag;				// 中身：head タグ内で規定するメタタグとか
$header_common_tag;				// 中身：header タグ内で規定するタイトルタグとか
$sql_output_dojo_qu_no_array;
$sql_output_dojo_qu_title_array;
$sql_output_dojo_qu_id_array;
$sql_output_dojo_qu_cor_array;
$sql_output_dojo_qu_eva_array;

/* ここで使うconfig.php内の関数とか処理。(発火するとマズイので全部コメントアウト)
session_user_check($session_user)
create_input($type,$id,$name,$size,$val,$attribute,$attr_val,$placeholder)
sql($type,$userno,$sqlno,$funcno,$val,$val2,$val_array) 
sql_func($row,$funcno,$check,$val,$val2)
*/

// ここで使う変数の初期化。
$pflag     = "false";
$display   = array("10問表示","全問表示");

/* --------------------------------------------------------------------------------------- */



if($_SERVER["REQUEST_METHOD"] == "POST"){
	$pflag = true;
	$_SESSION["question"]["qu_no"] = $_POST["qu_no"];
	header("Location:question.php");
	exit;
}else{
	// GET時の不正アクセスの制御処理（直リンクアクセスはindex.phpにリダイレクト。詳細な処理は config.php を確認）
	session_user_check($_SESSION["user"]);
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<?= $head_common_tag ?>
	<script type="text/javascript" src="./js/default.js"></script>
	<script>
		function qu_display_change(){
			event.srcElement.form.submit();
		}
	</script>
	<style>
		body > header{
			margin: auto;
			max-width: 1200px;
		}
		section{
			margin: auto;
			text-align:center;
			max-width: 1200px;
		}
		section > p {
			width: 100%;
			text-align: left;
		}
		section > form.form1{
			text-align: right;
		}
		section > form::after{
			content: "";
			display: block;
			clear: both;
		}
		table{
			width: 100%;
			text-align: center;
			border-collapse: separate;
		}
		.t1 th:nth-of-type(1){
			width:5%;
		}
		.t1 th:nth-of-type(2){
			width:45%;
		}
		.t1 th:nth-of-type(3){
			width:15%;
		}
		.t1 th:nth-of-type(4){
			width:10%;
		}
		.t1 th:nth-of-type(5){
			width:10%;
		}
		.t1 th:nth-of-type(6){
			width:15%;
		}
		.t1 input[type="submit"]{
			margin: 5px auto 5px;
			height: 30px;
			width: 80%;
			border-radius: 5px;
			box-shadow: 4px 4px 6px gray;
			background-color: paleturquoise;
			font-size: 15px;
			transition: all 0.8s ease;
		}
		.t1 input[type="submit"]:hover {
			border: 1px solid blue;
			background:none;
			background-color:blue;
			font-size: 18px;
			font-style: normal;	
			font-weight: bold;
			color: white;
			text-decoration-line: none;
			opacity: 0.7;
			transition: all 0.5s ease;
		}
	</style>
	<title>一問一答道場</title>
</head>
<body>
	<header>
		<?= $header_common_tag ?>
	</header>
	<main>
		<section>
			<header>
				<h2>一問一答道場</h2>
			</header>
			<span id="err"><?= $err_msg ?></span>
			<br>
			<?php
				if(!isset($_GET["qu_display_type"])){
					echo "<p>挑戦回数が少ない順、問題が投稿された順に10問表示されます。</p>";
				}else{
					switch($_GET["qu_display_type"]){
						case "10問表示":
							echo "<p>挑戦回数が少ない順、問題が投稿された順に10問表示されます。</p>";
							break;
						case "全問表示":
							echo "<p>問題番号順に全問題が表示されます。</p>";
							break;
					}
				}
				echo "<form id=\"form1\" class=\"form1\" action=\"question_dojo.php\" method=\"GET\">";
				echo "表示形式 <select name=\"qu_display_type\" onchange=\"qu_display_change()\">";
				for($i=0;$i<count($display);$i++){
					echo "<option value=\"$display[$i]\"";
					if(!isset($_GET["qu_display_type"])){
						if($i == 0 ){ 
							echo "selected";
						}
					}else{
						if($_GET["qu_display_type"] == $display[$i]){
							echo "selected";
						}
					}
					echo ">$display[$i]</option>";
				}
				echo "</select></form>";
			?>
			<table class="t1" border="1">
				<tr>
					<th>No.</th>
					<th>問題タイトル</th>
					<th>作成者</th>
					<th>正答率</th>
					<th>平均評価数</th>
					<th>挑戦</th>
				</tr>
				<?php
					// 全問題から有効な問題を回答数が少ない順かつ問題番号が大きい順で10問取得。
					// 問題の情報は config.php にて $sql_output_dojo_qu_no_array に代入。
					if(!isset($_GET["qu_display_type"])){
						sql("select",$_SESSION["user"]["type"],"dojo1","dojo1","","","");
					}else{
						switch($_GET["qu_display_type"]){
							case "10問表示":
								sql("select",$_SESSION["user"]["type"],"dojo1","dojo1","","","");
								break;
							case "全問表示":
								sql("select",$_SESSION["user"]["type"],"dojo2","dojo2","","","");
								break;
						}
					}
					// 上記で取得した問題情報をテーブルにして表示
					for($i=0;$i<count($sql_output_dojo_qu_no_array);$i++){
						$td = "";
						$td .= "<tr>";
						$td .= "<td> $sql_output_dojo_qu_no_array[$i] </td>";
						$td .= "<td> $sql_output_dojo_qu_title_array[$i] </td>";
						$td .= "<td> $sql_output_dojo_qu_id_array[$i] </td>";
						$su = round($sql_output_dojo_qu_cor_array[$i],3) * 100;
						$td .= "<td> $su % </td>";
						$su = round($sql_output_dojo_qu_eva_array[$i],2);
						$td .= "<td> $su </td>";
						// 問題番号毎にform を作成（hiddenタグに問題番号を代入）
						// 注意：hiddenタグは全て同じ名前にしてるので、テーブルを１つのformにすると最後のhiddenタグの内容しか処理されない。
						$td .= "<td><form action=\"question_dojo.php\" method=\"POST\">";
						$td .= create_input("hidden","qu_no","qu_no","10",$sql_output_dojo_qu_no_array[$i] ,"","","");
						$td .= create_input("submit","btn","btn","10","挑戦する","","","");
						$td .= "</form></td>";
						$td .= "</tr>";
						echo $td;
					}
				?>
			</table>
			<br>
			<form action="main.php" method="GET">
				<?= create_input("submit","btn","btn","10","メインメニューに戻る","","","") ?>
			</form>
		</section>
	</main>
	<footer>
		<?= $footer_common_tag ?>
	</footer>
</body>
</html>

