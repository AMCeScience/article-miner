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

				$reqs = [1,2,3,4,5,6,7,8,9,10];

				foreach ($reqs as $fold_requirement) {
					$included = 0;
					$BD_pubs_missed = 0;
					$publications_missed = 0;
					$matches = 0;
					$non_matches = 0;
					$fold_multiplication = 2;

					foreach ($pubmed_count as $journal => $count) {
						$matching = 0;

						if (array_key_exists($journal, $robot_count)) {
							$matching = $robot_count[$journal];
						}

						$is_sufficient = ($count * $fold_multiplication * $fold_requirement <= $matching);

						if (!$is_sufficient) {
							$non_matches++;
							$BD_pubs_missed += $count;
							// $publications_missed += $count + $matching;
						} else {
							$matches++;
							// Add BD count plus the number of NBD that will be included based on the BD count
							// BD count times the fold multiplication times the number of folds
							$included += $count + ($count * $fold_multiplication * $fold_requirement);
						}

						// echo '<tr><td>' . $journal . '</td><td>' . $count . '</td><td>' . $matching . '</td><td>' . ($is_sufficient ? 'true' : 'false') . '</td></tr>';
					}

					echo 'Sufficient: ' . $matches . '<br/>';
					echo 'Non-sufficient: ' . $non_matches . '<br/>';
					echo 'Big Data publications missed: ' . $BD_pubs_missed . '<br/>';
					// echo 'Publications missed: ' . $publications_missed . '<br/>';
					echo 'Total publications included: ' . $included . '<br/><br/>';
				}
			?>
		</table>
	</body>
</html>