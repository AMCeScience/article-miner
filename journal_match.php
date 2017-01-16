<!--
// Article Miner, a document parser for Pubmed, Pubmed Central, Ovid, Scopus, and Web of Science
// Copyright (C) 2016 Allard van Altena

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.

// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
-->

<!DOCTYPE html>
<html>
	<head>
		<title></title>
	</head>
	<body>
		<table>
			<tr><th>Journal ID</th><th>Count</th><th>Matching Count</th><th>Matches</th></tr>
			<?php
				require_once("config.php");
				require_once("database.php");
				require_once("models/articles.php");

				$db = new Connector();

				$db->connect($config);

				$article_model = new Articles();

				$pubmed_count = $article_model->count_by_journal($db, 'pubmed');
				$robot_count = $article_model->count_by_journal($db, 'robot');

				$tot = 0;
				$pub_miss = 0;
				$non_matches = 0;
				$fold_requirement = 1;

				foreach ($pubmed_count as $journal => $count) {
					$matching = 0;

					if (array_key_exists($journal, $robot_count)) {
						$matching = $robot_count[$journal];
					}

					$is_sufficient = ($count * $fold_requirement <= $matching);

					if (!$is_sufficient) {
						$non_matches++;
						$pub_miss += $count;
					}

					$tot += $matching;

					echo '<tr><td>' . $journal . '</td><td>' . $count . '</td><td>' . $matching . '</td><td>' . ($is_sufficient ? 'true' : 'false') . '</td></tr>';
				}
			?>
		</table>

		<?php echo 'Non-sufficient: ' . $non_matches; ?>
		<?php echo $pub_miss; echo ' ' . $tot; ?>
	</body>
</html>