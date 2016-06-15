# Article Miner

An article parser for publication databases, when using the database's API is unfeasible.
Takes the output formats of publication databases as input and stores their articles in a local MySQL database for futher processing, currently the following databases and formats are supported:
- Pubmed (XML)
- Pubmed Central (XML)
- Ovid (XML)
- Scopus (CSV)
- Web of Science (tab delimited)

If available in the specific output Article Miner extracts and stores:
- title
- abstract
- journal (title, ISO, ISSN)
- publication date
- DOI

Additionally Article Miner attempts to associate the journal as defined by [ISI](http://isindexing.com/isi/journals.php) to the parsed articles. To support this function add a CSV file containing the list of ISI journals to the `/files` directory, containing: Title, ISO, ISSN, Category code, and Category description. If your institution has a librarian this list is most likely available through them.

### Installation

Article Miner has been developed and tested under [MAMP](https://www.mamp.info/en/) with PHP 5.5.3 and MySQL 5.5.33.
Before running the code, initialise the database by executing the schema.sql file.
When using the AlchemyAPI version of this software also add your API key to `alchemyAPI/api_key.txt`.

Further setup can be found in the `config.php` file.

### Operation

All the following functions are available through the `index.php` file:
- Initialise the database (`init.php`): read the available publication database files and store their articles in the local database.
- Re-initialise the database (`init.php?reinit=true`): first clear the database, then invoke the initialise database function.
- Removed duplicates articles (`compare.php`): tries to find matches in articles by comparing their titles and DOIs, deletes matches from the local database.
- Get articles CSV (`csv.php`): output a CSV where each line is the title and abstract of an article from the local database, strips all special characters (i.e. regex: `[^a-zA-Z'\-\ ]`)
 
The AlchemyAPI version of this software also provides:
- Run AlchemyAPI (`analyse.php`): passes the articles stored in the local database to the AlchemyAPI service and stores the results in the local database.