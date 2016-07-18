<?php
	
	if(!isset($page_title)) { // redirect to subdir if visited directly
		header("Location: ../blog");
		exit();
	}

	function GetStoredPostsByRange($mysqli, $start, $amount) {

		$query_start = $mysqli->real_escape_string($start);
		$query_amount = $mysqli->real_escape_string($amount);

		$retval = array();
		if($result = $mysqli->query("SELECT content FROM blogposts ORDER BY urlkey DESC LIMIT $query_start, $query_amount")) {
			while($row = mysqli_fetch_array($result))
				array_push($retval, $row['content']);

			$result->close();
		}

		return $retval;
	}

	function GetStoredPostsCount($mysqli) {
		if($result = $mysqli->query("SELECT COUNT(*) FROM blogposts")) {
			while($row = mysqli_fetch_array($result)) {
				foreach($row as $value) {
					if($value) {
						$result->close();
						return $value;
					}
				}
			}

			$result->close();
		}

		return 0;
	}

	function GetPostByURLKey($mysqli, $urlkey) {

		$query_urlkey = $mysqli->real_escape_string($urlkey);

		if($result = $mysqli->query("SELECT content FROM blogposts WHERE urlkey = '$query_urlkey' LIMIT 1")) {
			while($row = mysqli_fetch_array($result)) {
				if($row['content']) {
					$result->close();
					return $row['content'];
				}
			}

			$result->close();
		}

		return "";
	}

	$path = "posts";

	if(isset($_GET['post'])) { // direct link to post
		$post = GetPostByURLKey($mysqli, urldecode($_GET['post']));
		if($post == "")
			include("../inc/no_post_found.php");
		else
			echo $post;

		echo "<a href=\"../blog\"><<< Go to Blog <<<</a>";

		return; // exit just this included file
	}

	// pagination...

	$page_num = 0;
	if(isset($_GET['page']))
		$page_num = (int)$_GET['page'];

	$posts_per_page = 3;
	if(isset($_GET['posts_per']))
		$posts_per_page = (int)$_GET['posts_per'];

	$posts = GetStoredPostsByRange($mysqli, $page_num * $posts_per_page, $posts_per_page);
	$total_posts_count = GetStoredPostsCount($mysqli);

	if(count($posts) == 0)
		echo "<p>Nothing to see here... Come back later.";

	foreach($posts as $post) {
		echo $post;
		if($post != end($posts)) // add the line if it's not the last entry
			echo "<hr>";
	}

	// next page / prev page
	if(!isset($_GET['page']) && $total_posts_count > $posts_per_page)
		echo "<a href=\"?page=1\"><div class=\"nextPage\">>>> Next Page >>></div></a>";
	else {
		if($page_num != 0) {
			echo "<a href=\"?page=";
			echo $page_num - 1;
			echo "\"><div class=\"prevPage\"><<< Previous Page <<<</div></a>";
		}

		if(($page_num + 1) * $posts_per_page < $total_posts_count && $total_posts_count > $posts_per_page) {
			echo "<a href=\"?page=";
			echo $page_num + 1;
			echo "\"><div class=\"nextPage\">>>> Next Page >>></div></a>";
		}
	}

?>